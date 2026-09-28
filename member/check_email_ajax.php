<?php
header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . "/../inc/dbconn.php";

$email = isset($_POST['email']) ? trim($_POST['email']) : (isset($_GET['email']) ? trim($_GET['email']) : '');
$no    = isset($_POST['no']) ? intval($_POST['no']) : (isset($_GET['no']) ? intval($_GET['no']) : 0);

if (empty($email)) {
    echo json_encode(['exists' => false, 'valid' => false, 'msg' => '이메일 주소를 입력해 주세요.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['exists' => false, 'valid' => false, 'msg' => '올바른 이메일 형식이 아닙니다.']);
    exit;
}

$email_esc = mysqli_real_escape_string($conn, $email);
$where = "and (uid='{$email_esc}' or email='{$email_esc}')";
if ($no > 0) {
    $where .= " and no <> {$no}";
}

$cnt = sql_cnt('members', $where);

if ($cnt > 0) {
    echo json_encode(['exists' => true, 'valid' => true, 'msg' => '이미 등록되었거나 사용 중인 이메일(아이디)입니다.']);
} else {
    echo json_encode(['exists' => false, 'valid' => true, 'msg' => '사용 가능한 이메일(아이디)입니다.']);
}
exit;
