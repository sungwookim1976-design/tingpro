<?php
include_once __DIR__ . "/../inc/dbconn.php";

function back_to_join($msg, $post = []) {
    $qs = "err=" . urlencode($msg);
    if (!empty($post['mem_type'])) $qs .= "&type=" . urlencode($post['mem_type']);
    if (!empty($post['uid'])) $qs .= "&uid=" . urlencode($post['uid']);
    header("Location: join.php?" . $qs);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: join.php");
    exit;
}

$mem_type = in_array((isset($_POST['mem_type']) ? $_POST['mem_type'] : ''), ['customer', 'freelance', 'partner']) ? $_POST['mem_type'] : 'customer';
$email    = trim((isset($_POST['email']) && $_POST['email'] !== '' ? $_POST['email'] : (isset($_POST['uid']) ? $_POST['uid'] : '')));
$uid      = $email;
$passwd   = (isset($_POST['passwd']) ? $_POST['passwd'] : '');
$passwd2  = (isset($_POST['passwd2']) ? $_POST['passwd2'] : '');
$name     = trim((isset($_POST['name']) ? $_POST['name'] : ''));
$hphone   = trim((isset($_POST['hphone']) ? $_POST['hphone'] : ''));
$zipcode  = trim((isset($_POST['zipcode']) ? $_POST['zipcode'] : ''));
$addr1    = trim((isset($_POST['addr1']) ? $_POST['addr1'] : ''));
$addr2    = trim((isset($_POST['addr2']) ? $_POST['addr2'] : ''));
$region   = trim((isset($_POST['region']) ? $_POST['region'] : ''));
$career   = trim((isset($_POST['career']) ? $_POST['career'] : ''));
$biz_name = trim((isset($_POST['biz_name']) ? $_POST['biz_name'] : ''));
$biz_no   = trim((isset($_POST['biz_no']) ? $_POST['biz_no'] : ''));
$agree    = isset($_POST['agree_privacy']) && $_POST['agree_privacy'] == '1';

// 서버측 검증
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    back_to_join('올바른 이메일 주소(아이디)를 입력해 주세요.', $_POST);
}
if (strlen($passwd) < 6) {
    back_to_join('비밀번호는 6자 이상 입력해 주세요.', $_POST);
}
if ($passwd !== $passwd2) {
    back_to_join('비밀번호가 일치하지 않습니다.', $_POST);
}
if ($name === '' || $hphone === '') {
    back_to_join('이름과 연락처를 입력해 주세요.', $_POST);
}
if (!$agree) {
    back_to_join('개인정보 수집 및 이용에 동의해 주세요.', $_POST);
}

// 아이디 및 이메일 중복 체크
$dup_email = sql_cnt('members', "and (email='" . mysqli_real_escape_string($conn, $email) . "' or uid='" . mysqli_real_escape_string($conn, $uid) . "')");
if ($dup_email > 0) {
    back_to_join('이미 가입된 이메일(아이디)입니다.', $_POST);
}

$passwd_hash = password_hash($passwd, PASSWORD_DEFAULT);

$fields = "mem_type='" . mysqli_real_escape_string($conn, $mem_type) . "', "
        . "uid='" . mysqli_real_escape_string($conn, $uid) . "', "
        . "passwd='" . mysqli_real_escape_string($conn, $passwd_hash) . "', "
        . "name='" . mysqli_real_escape_string($conn, $name) . "', "
        . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
        . "email='" . mysqli_real_escape_string($conn, $email) . "', "
        . "zipcode='" . mysqli_real_escape_string($conn, $zipcode) . "', "
        . "addr1='" . mysqli_real_escape_string($conn, $addr1) . "', "
        . "addr2='" . mysqli_real_escape_string($conn, $addr2) . "', "
        . "region='" . mysqli_real_escape_string($conn, $region) . "', "
        . "career='" . mysqli_real_escape_string($conn, $career) . "', "
        . "biz_name='" . mysqli_real_escape_string($conn, $biz_name) . "', "
        . "biz_no='" . mysqli_real_escape_string($conn, $biz_no) . "', "
        . "agree_privacy=1";

sql_in('members', $fields);

// 가입 즉시 로그인 처리
$_SESSION['s_mem_id']    = $uid;
$_SESSION['s_mem_no']    = mysqli_insert_id($conn);
$_SESSION['s_mem_type']  = $mem_type;
$_SESSION['s_mem_name']  = $name;
$_SESSION['s_mem_email'] = $email;
$_SESSION['s_mem_hphone'] = $hphone;
$_SESSION['s_mem_zipcode'] = $zipcode;
$_SESSION['s_mem_addr1'] = $addr1;
$_SESSION['s_mem_addr2'] = $addr2;

header("Location: join_done.php");
exit;
