<?php
include_once __DIR__ . "/../../inc/dbconn.php";
$adm_path_prefix = "../";
include_once __DIR__ . "/../inc/auth_check.php";
header('Content-Type: application/json; charset=utf-8');

$no    = (int)((isset($_POST['no']) ? $_POST['no'] : 0));
$state = (isset($_POST['state']) ? $_POST['state'] : '');
$allowed = ['신규', '상담중', '완료'];

if (!$no || !in_array($state, $allowed, true)) {
    echo json_encode(['ok' => false, 'msg' => '잘못된 요청입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

sql_up('quotes', "state='" . mysqli_real_escape_string($conn, $state) . "'", "and no=" . $no);
echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
