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

$bid_no     = (int)(isset($_POST['bid_no']) ? $_POST['bid_no'] : 0);
$auction_no = (int)(isset($_POST['auction_no']) ? $_POST['auction_no'] : 0);

if ($bid_no <= 0 && $auction_no <= 0) {
    fail('취소할 입찰 정보를 찾을 수 없습니다.');
}

$where = "and 1=1";
if ($bid_no > 0) {
    $where .= " and no=" . $bid_no;
}
if ($auction_no > 0) {
    $where .= " and auction_no=" . $auction_no;
}

if (!empty($_SESSION['s_mem_no'])) {
    $where .= " and (mem_no=" . (int)$_SESSION['s_mem_no'] . " or bidder_hphone='" . mysqli_real_escape_string($conn, isset($_SESSION['s_mem_hphone']) ? $_SESSION['s_mem_hphone'] : '') . "')";
}

$bid_row = sql_one_one('auction_bids', '*', $where . " order by no desc limit 1");
if (!$bid_row) {
    fail('취소할 수 있는 입찰 내역이 없거나 본인의 입찰이 아닙니다.');
}

$auc_no = (int)$bid_row['auction_no'];
$auc_item = sql_one_one('auctions', '*', "and no=" . $auc_no);
if (!$auc_item) {
    fail('역경매 정보가 존재하지 않습니다.');
}

// 입찰 마감 시간 체크
$reg_time = strtotime($auc_item['reg_date']);
$now = time();
$elapsed = max(0, $now - $reg_time);
$days_added = (int)floor($elapsed / 86400) + 1;
$deadline_time = $reg_time + ($days_added * 86400);

if ($now > $deadline_time) {
    fail('입찰 마감 시각이 경과되어 입찰을 취소할 수 없습니다.');
}

// 입찰 삭제 처리
sql_del('auction_bids', "and no=" . (int)$bid_row['no']);

// 역경매 bid_count 차감
$new_bid_cnt = max(0, (int)$auc_item['bid_count'] - 1);
$new_state = ($new_bid_cnt == 0) ? '입찰대기' : $auc_item['state'];

sql_up('auctions', "bid_count=" . $new_bid_cnt . ", state='" . $new_state . "'", "and no=" . $auc_no);

if (isset($_SESSION['submitted_bids'][$auc_no])) {
    unset($_SESSION['submitted_bids'][$auc_no]);
}

echo json_encode([
    'ok'      => true,
    'bid_cnt' => $new_bid_cnt,
    'msg'     => '입찰 제안이 성공적으로 취소되었습니다.'
], JSON_UNESCAPED_UNICODE);
