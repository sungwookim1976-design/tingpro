<?php
include_once __DIR__ . "/../inc/dbconn.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$uid    = trim((isset($_POST['uid']) ? $_POST['uid'] : ''));
$passwd = (isset($_POST['passwd']) ? $_POST['passwd'] : '');

if ($uid === '' || $passwd === '') {
    header("Location: login.php?err=" . urlencode('아이디와 비밀번호를 입력해 주세요.') . "&uid=" . urlencode($uid));
    exit;
}

$row = sql_one_one('admins', '*', "and uid='" . mysqli_real_escape_string($conn, $uid) . "'");

if (!$row || !password_verify($passwd, $row['passwd'])) {
    header("Location: login.php?err=" . urlencode('아이디 또는 비밀번호가 일치하지 않습니다.') . "&uid=" . urlencode($uid));
    exit;
}

if ($row['state'] != 1) {
    header("Location: login.php?err=" . urlencode('이용이 정지된 관리자 계정입니다.'));
    exit;
}

$_SESSION['s_adm_id']    = $row['uid'];
$_SESSION['s_adm_no']    = $row['no'];
$_SESSION['s_adm_name']  = $row['name'];
$_SESSION['s_adm_grade'] = $row['grade'];

sql_up('admins', "last_login=NOW()", "and no=" . (int)$row['no']);

header("Location: index.php");
exit;
