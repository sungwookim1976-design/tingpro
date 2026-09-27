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

$name          = trim(isset($_POST['name']) ? $_POST['name'] : '');
$hphone        = trim(isset($_POST['hphone']) ? $_POST['hphone'] : '');
$space_type    = trim(isset($_POST['space_type']) ? $_POST['space_type'] : '아파트');
$py            = (int)(isset($_POST['py']) ? $_POST['py'] : 33);
$addr          = trim(isset($_POST['addr']) ? $_POST['addr'] : '');
$desired_price = (int)(isset($_POST['desired_price']) ? $_POST['desired_price'] : 0);
$memo          = trim(isset($_POST['memo']) ? $_POST['memo'] : '');

if ($name === '' || $hphone === '') {
    fail('신청자 성함과 연락처를 입력해 주세요.');
}
if ($addr === '') {
    fail('시공 장소 주소를 입력해 주세요.');
}

$mem_no_sql = !empty($_SESSION['s_mem_no']) ? (int)$_SESSION['s_mem_no'] : 'NULL';

$fields = "mem_no=" . $mem_no_sql . ", "
        . "space_type='" . mysqli_real_escape_string($conn, $space_type) . "', "
        . "py=" . (int)$py . ", "
        . "addr='" . mysqli_real_escape_string($conn, $addr) . "', "
        . "name='" . mysqli_real_escape_string($conn, $name) . "', "
        . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
        . "desired_price=" . (int)$desired_price . ", "
        . "memo='" . mysqli_real_escape_string($conn, $memo) . "', "
        . "state='입찰대기', "
        . "reg_date=NOW()";

sql_in('auctions', $fields);

echo json_encode([
    'ok'  => true,
    'no'  => mysqli_insert_id($conn),
    'msg' => '역경매 신청이 성공적으로 접수되었습니다. 검증 마스터들의 입찰이 시작됩니다!'
], JSON_UNESCAPED_UNICODE);
