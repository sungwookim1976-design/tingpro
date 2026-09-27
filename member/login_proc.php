<?php
include_once __DIR__ . "/../inc/dbconn.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email    = trim((isset($_POST['email']) ? $_POST['email'] : ''));
$passwd   = (isset($_POST['passwd']) ? $_POST['passwd'] : '');
$redirect = (isset($_POST['redirect']) ? $_POST['redirect'] : '');

if ($email === '' || $passwd === '') {
    header("Location: login.php?err=" . urlencode('이메일과 비밀번호를 입력해 주세요.') . "&email=" . urlencode($email));
    exit;
}

$row = sql_one_one('members', '*', "and email='" . mysqli_real_escape_string($conn, $email) . "'");

if (!$row || !password_verify($passwd, $row['passwd'])) {
    header("Location: login.php?err=" . urlencode('이메일 또는 비밀번호가 일치하지 않습니다.') . "&email=" . urlencode($email));
    exit;
}

if ($row['mem_state'] != 1) {
    header("Location: login.php?err=" . urlencode('이용이 제한된 계정입니다. 관리자에게 문의해 주세요.'));
    exit;
}

$actual_mem_type = check_and_update_partner_status($row['no']);

$_SESSION['s_mem_id']    = $row['uid'];
$_SESSION['s_mem_no']    = $row['no'];
$_SESSION['s_mem_type']  = $actual_mem_type;
$_SESSION['s_mem_name']  = $row['name'];
$_SESSION['s_mem_email'] = $row['email'];
$_SESSION['s_mem_hphone'] = $row['hphone'];
$_SESSION['s_mem_zipcode'] = $row['zipcode'];
$_SESSION['s_mem_addr1'] = $row['addr1'];
$_SESSION['s_mem_addr2'] = $row['addr2'];

if ($redirect !== '' && strpos($redirect, '//') === false) {
    header("Location: " . $redirect);
} else {
    header("Location: ../index.php");
}
exit;
