<?php
include_once __DIR__ . "/../inc/dbconn.php";
header('Content-Type: application/json; charset=utf-8');

function respond_json($ok, $msg) {
    echo json_encode(['ok' => $ok, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_json(false, '잘못된 요청 방식입니다.');
}

if (empty($_SESSION['s_mem_id'])) {
    respond_json(false, '로그인이 필요합니다.');
}

$mem_id = $_SESSION['s_mem_id'];
$mem_no = intval((isset($_SESSION['s_mem_no']) ? $_SESSION['s_mem_no'] : 0));

$name    = trim((isset($_POST['name']) ? $_POST['name'] : ''));
$hphone  = trim((isset($_POST['hphone']) ? $_POST['hphone'] : ''));
$zipcode = trim((isset($_POST['zipcode']) ? $_POST['zipcode'] : ''));
$addr1   = trim((isset($_POST['addr1']) ? $_POST['addr1'] : ''));
$addr2   = trim((isset($_POST['addr2']) ? $_POST['addr2'] : ''));
$passwd  = (isset($_POST['passwd']) ? $_POST['passwd'] : '');

if (empty($name) || empty($hphone)) {
    respond_json(false, '성함과 연락처는 필수 입력 사항입니다.');
}

$fields = "name='" . mysqli_real_escape_string($conn, $name) . "', "
        . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
        . "zipcode='" . mysqli_real_escape_string($conn, $zipcode) . "', "
        . "addr1='" . mysqli_real_escape_string($conn, $addr1) . "', "
        . "addr2='" . mysqli_real_escape_string($conn, $addr2) . "'";

if (!empty($passwd)) {
    if (strlen($passwd) < 4) {
        respond_json(false, '비밀번호는 4자 이상 입력해 주세요.');
    }
    $passwd_hash = password_hash($passwd, PASSWORD_DEFAULT);
    $fields .= ", passwd='" . mysqli_real_escape_string($conn, $passwd_hash) . "'";
}

$where = "and (uid='" . mysqli_real_escape_string($conn, $mem_id) . "'";
if ($mem_no > 0) $where .= " or no=" . $mem_no;
$where .= ")";

$ret = sql_up('members', $fields, $where);

if ($ret) {
    $_SESSION['s_mem_name'] = $name;
    $_SESSION['s_mem_hphone'] = $hphone;
    $_SESSION['s_mem_zipcode'] = $zipcode;
    $_SESSION['s_mem_addr1'] = $addr1;
    $_SESSION['s_mem_addr2'] = $addr2;

    respond_json(true, '회원 정보가 성공적으로 수정되었습니다.');
} else {
    respond_json(false, '회원 정보 수정에 실패하였습니다.');
}
