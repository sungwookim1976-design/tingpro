<?php
include_once __DIR__ . "/../inc/dbconn.php";
header('Content-Type: application/json; charset=utf-8');

function fail($msg) {
    echo json_encode(['ok' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('잘못된 요청입니다.');
}

// 1. 로그인 여부 필수 검증
if (empty($_SESSION['s_mem_id'])) {
    fail('입찰 제안은 로그인 후 이용 가능합니다. 로그인 페이지로 이동하여 로그인해 주세요.');
}

$auction_no    = (int)(isset($_POST['auction_no']) ? $_POST['auction_no'] : 0);
$bidder_name   = trim(isset($_POST['bidder_name']) ? $_POST['bidder_name'] : '');
$bidder_type   = trim(isset($_POST['bidder_type']) ? $_POST['bidder_type'] : '프리랜서');
$bidder_hphone = trim(isset($_POST['bidder_hphone']) ? $_POST['bidder_hphone'] : '');
$bid_price     = (int)(isset($_POST['bid_price']) ? $_POST['bid_price'] : 0);
$work_duration = trim(isset($_POST['work_duration']) ? $_POST['work_duration'] : '1일 소요');
$memo          = trim(isset($_POST['memo']) ? $_POST['memo'] : '');

// 2. 회원 구분 검증 및 수요고객 입찰 차단
$mem_type = isset($_SESSION['s_mem_type']) ? $_SESSION['s_mem_type'] : '';
if (!empty($_SESSION['s_mem_no'])) {
    $mem_type = check_and_update_partner_status($_SESSION['s_mem_no']);
}

if ($mem_type === 'customer' || $mem_type === '수요고객' || $bidder_type === '수요고객') {
    fail('입찰 제안은 수요고객을 제외한 시공업체(프리랜서, 기업, 대리점) 회원만 참여 가능합니다.');
}

if ($auction_no <= 0) {
    fail('대상 역경매 정보를 찾을 수 없습니다.');
}

$auc_row = sql_one_one('auctions', '*', "and no=" . $auction_no);
if (!$auc_row) {
    fail('존재하지 않는 역경매 항목입니다.');
}

if ($bidder_name === '' || $bidder_hphone === '') {
    fail('입찰자 성함/업체명과 연락처를 입력해 주세요.');
}
if ($bid_price <= 0) {
    fail('입찰 제안 금액을 정확히 입력해 주세요.');
}

// 입찰 단계 (Phase 1 / Phase 2) 및 회원 구분 규칙 검증
$reg_time = strtotime($auc_row['reg_date']);
$now = time();
$elapsed_hours = ($now - $reg_time) / 3600;

if ($elapsed_hours <= 24) {
    // 1차 진행: 대리점(구독사업자)만 진행 가능
    if ($mem_type !== 'partner' && mb_strpos($bidder_type, '대리점') === false && mb_strpos($bidder_type, '구독사업자') === false) {
        fail('1차 입찰 진행 단계(신청 후 24시간 이내)는 대리점(구독사업자) 회원만 참여 가능합니다.');
    }
}

// 중복 입찰 방지 체크 (세션, 연락처, 회원번호)
if (!isset($_SESSION['submitted_bids'])) {
    $_SESSION['submitted_bids'] = array();
}

if (isset($_SESSION['submitted_bids'][$auction_no])) {
    fail('이미 해당 역경매에 입찰 제안을 제출하셨습니다. 중복 제입찰은 불가능합니다.');
}

$hphone_esc = mysqli_real_escape_string($conn, $bidder_hphone);
$check_where = "and auction_no=" . $auction_no . " and (bidder_hphone='" . $hphone_esc . "'";
if (!empty($_SESSION['s_mem_no'])) {
    $check_where .= " or mem_no=" . (int)$_SESSION['s_mem_no'];
}
$check_where .= ")";

$existing_bid_cnt = sql_cnt('auction_bids', $check_where);
if ($existing_bid_cnt > 0) {
    fail('이미 동일한 연락처/계정으로 입찰 제안을 제출하셨습니다. 중복 제입찰은 제한됩니다.');
}

$mem_no_sql = !empty($_SESSION['s_mem_no']) ? (int)$_SESSION['s_mem_no'] : 'NULL';

$bid_fields = "auction_no=" . $auction_no . ", "
            . "mem_no=" . $mem_no_sql . ", "
            . "bidder_name='" . mysqli_real_escape_string($conn, $bidder_name) . "', "
            . "bidder_type='" . mysqli_real_escape_string($conn, $bidder_type) . "', "
            . "bidder_hphone='" . mysqli_real_escape_string($conn, $bidder_hphone) . "', "
            . "bid_price=" . (int)$bid_price . ", "
            . "work_duration='" . mysqli_real_escape_string($conn, $work_duration) . "', "
            . "memo='" . mysqli_real_escape_string($conn, $memo) . "', "
            . "state='입찰중', "
            . "reg_date=NOW()";

sql_in('auction_bids', $bid_fields);

// Update auction bid count and state
$new_bid_cnt = (int)$auc_row['bid_count'] + 1;
$new_state = ($auc_row['state'] === '입찰대기') ? '입찰중' : $auc_row['state'];

sql_up('auctions', "bid_count=" . $new_bid_cnt . ", state='" . $new_state . "'", "and no=" . $auction_no);

echo json_encode([
    'ok'      => true,
    'bid_cnt' => $new_bid_cnt,
    'msg'     => '역경매 입찰 제안이 정상 등록되었습니다!'
], JSON_UNESCAPED_UNICODE);
