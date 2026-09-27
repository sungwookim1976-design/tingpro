<?php
include_once __DIR__ . "/../../inc/dbconn.php";
$adm_path_prefix = "../";
include_once __DIR__ . "/../inc/auth_check.php";
header('Content-Type: application/json; charset=utf-8');

$no    = (int)((isset($_POST['no']) ? $_POST['no'] : 0));
$state = (isset($_POST['state']) ? $_POST['state'] : '');
$allowed = ['주문접수', '결제확인중', '배송준비', '배송중', '배송완료', '취소'];

if (!$no || !in_array($state, $allowed, true)) {
    echo json_encode(['ok' => false, 'msg' => '잘못된 요청입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

sql_up('orders', "state='" . mysqli_real_escape_string($conn, $state) . "'", "and no=" . $no);
echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
