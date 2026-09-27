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

if (empty($_SESSION['s_mem_id'])) {
    echo json_encode(['ok' => false, 'need_login' => true, 'msg' => '견적 신청은 회원만 가능합니다. 로그인 후 다시 시도해 주세요.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$space_type    = trim((isset($_POST['space_type']) ? $_POST['space_type'] : ''));
$py            = (int)((isset($_POST['py']) ? $_POST['py'] : 0));
$film_name     = trim((isset($_POST['film_name']) ? $_POST['film_name'] : ''));
$film_price_py = (int)((isset($_POST['film_price_py']) ? $_POST['film_price_py'] : 0));
$total_price   = (int)((isset($_POST['total_price']) ? $_POST['total_price'] : 0));
$name          = trim((isset($_POST['name']) ? $_POST['name'] : ''));
$hphone        = trim((isset($_POST['hphone']) ? $_POST['hphone'] : ''));
$addr          = trim((isset($_POST['addr']) ? $_POST['addr'] : ''));
$request_date  = trim((isset($_POST['request_date']) ? $_POST['request_date'] : ''));
$memo          = trim((isset($_POST['memo']) ? $_POST['memo'] : ''));
$agree         = isset($_POST['agree_privacy']) && $_POST['agree_privacy'] == '1';

if ($name === '' || $hphone === '') {
    fail('이름과 연락처를 입력해 주세요.');
}
if (!preg_match('/^[0-9\-]{9,14}$/', $hphone)) {
    fail('연락처 형식을 확인해 주세요. (예: 010-0000-0000)');
}
if (!$agree) {
    fail('개인정보 수집 및 이용에 동의해 주세요.');
}
if ($py <= 0 || $py > 500) {
    $py = 0;
}

$request_date_sql = 'NULL';
if ($request_date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $request_date)) {
    $request_date_sql = "'" . mysqli_real_escape_string($conn, $request_date) . "'";
}

$mem_no_sql = !empty($_SESSION['s_mem_no']) ? (int)$_SESSION['s_mem_no'] : 'NULL';

$fields = "mem_no=" . $mem_no_sql . ", "
        . "space_type='" . mysqli_real_escape_string($conn, $space_type) . "', "
        . "py=" . (int)$py . ", "
        . "film_name='" . mysqli_real_escape_string($conn, $film_name) . "', "
        . "film_price_py=" . (int)$film_price_py . ", "
        . "total_price=" . (int)$total_price . ", "
        . "name='" . mysqli_real_escape_string($conn, $name) . "', "
        . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
        . "addr='" . mysqli_real_escape_string($conn, $addr) . "', "
        . "request_date=" . $request_date_sql . ", "
        . "memo='" . mysqli_real_escape_string($conn, $memo) . "', "
        . "agree_privacy=1";

sql_in('quotes', $fields);

echo json_encode([
    'ok'  => true,
    'no'  => mysqli_insert_id($conn),
    'msg' => '견적 신청이 정상 접수되었습니다. 10분 내 전문 상담원이 연락드립니다.',
], JSON_UNESCAPED_UNICODE);
