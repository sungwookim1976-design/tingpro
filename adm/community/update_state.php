<?php
include_once __DIR__ . "/../../inc/dbconn.php";
$adm_path_prefix = "../";
include_once __DIR__ . "/../inc/auth_check.php";
header('Content-Type: application/json; charset=utf-8');

$no    = (int)((isset($_POST['no']) ? $_POST['no'] : 0));
$state = (isset($_POST['state']) ? $_POST['state'] : '');

if (!$no || !in_array($state, ['0', '1'], true)) {
    echo json_encode(['ok' => false, 'msg' => '잘못된 요청입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

sql_up('community_posts', "state=" . (int)$state, "and no=" . $no);
echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
