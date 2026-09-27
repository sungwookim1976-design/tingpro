<?php
$adm_path_prefix = "../";
include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

function respond_alert($msg, $url = '') {
    echo "<script>alert('" . addslashes($msg) . "');";
    if ($url) {
        echo "location.href='" . $url . "';";
    } else {
        echo "history.back();";
    }
    echo "</script>";
    exit;
}

$mode = (isset($_POST['mode']) ? $_POST['mode'] : (isset($_GET['mode']) ? $_GET['mode'] : ''));

if ($mode === 'insert') {
    $uid     = trim((isset($_POST['uid']) ? $_POST['uid'] : ''));
    $passwd  = (isset($_POST['passwd']) ? $_POST['passwd'] : '');
    $name    = trim((isset($_POST['name']) ? $_POST['name'] : ''));
    $grade   = in_array((isset($_POST['grade']) ? $_POST['grade'] : ''), ['super', 'manager', 'staff']) ? $_POST['grade'] : 'staff';
    $state   = isset($_POST['state']) && $_POST['state'] == '1' ? 1 : 0;

    if (!preg_match('/^[a-zA-Z0-9]{4,20}$/', $uid)) {
        respond_alert("아이디는 영문/숫자 4~20자로 입력해 주세요.");
    }
    if (strlen($passwd) < 4) {
        respond_alert("비밀번호는 4자 이상 입력해 주세요.");
    }
    if (empty($name)) {
        respond_alert("이름을 입력해 주세요.");
    }

    // 아이디 중복 체크
    $dup = sql_cnt('admins', "and uid='" . mysqli_real_escape_string($conn, $uid) . "'");
    if ($dup > 0) {
        respond_alert("이미 존재하는 관리자 아이디입니다.");
    }

    $passwd_hash = password_hash($passwd, PASSWORD_DEFAULT);
    $fields = "uid='" . mysqli_real_escape_string($conn, $uid) . "', "
            . "passwd='" . mysqli_real_escape_string($conn, $passwd_hash) . "', "
            . "name='" . mysqli_real_escape_string($conn, $name) . "', "
            . "grade='" . mysqli_real_escape_string($conn, $grade) . "', "
            . "state=" . intval($state) . ", "
            . "reg_date=NOW()";

    $ret = sql_in('admins', $fields);
    if ($ret) {
        respond_alert("관리자가 성공적으로 등록되었습니다.", "index.php");
    } else {
        respond_alert("관리자 등록에 실패하였습니다.");
    }
}
elseif ($mode === 'update') {
    $no      = intval((isset($_POST['no']) ? $_POST['no'] : 0));
    $name    = trim((isset($_POST['name']) ? $_POST['name'] : ''));
    $grade   = in_array((isset($_POST['grade']) ? $_POST['grade'] : ''), ['super', 'manager', 'staff']) ? $_POST['grade'] : 'staff';
    $state   = isset($_POST['state']) && $_POST['state'] == '1' ? 1 : 0;
    $passwd  = (isset($_POST['passwd']) ? $_POST['passwd'] : '');

    if ($no <= 0) {
        respond_alert("올바르지 않은 요청입니다.");
    }
    if (empty($name)) {
        respond_alert("이름을 입력해 주세요.");
    }

    $fields = "name='" . mysqli_real_escape_string($conn, $name) . "', "
            . "grade='" . mysqli_real_escape_string($conn, $grade) . "', "
            . "state=" . intval($state);

    if (!empty($passwd)) {
        if (strlen($passwd) < 4) {
            respond_alert("비밀번호는 4자 이상 입력해 주세요.");
        }
        $passwd_hash = password_hash($passwd, PASSWORD_DEFAULT);
        $fields .= ", passwd='" . mysqli_real_escape_string($conn, $passwd_hash) . "'";
    }

    $ret = sql_up('admins', $fields, "and no=" . $no);
    if ($ret) {
        respond_alert("관리자 정보가 수정되었습니다.", "index.php");
    } else {
        respond_alert("관리자 정보 수정에 실패하였습니다.");
    }
}
elseif ($mode === 'change_state') {
    $no    = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    $state = intval((isset($_POST['state']) ? $_POST['state'] : (isset($_GET['state']) ? $_GET['state'] : 0)));

    if ($no > 0) {
        sql_up('admins', "state=" . ($state == 1 ? 1 : 0), "and no=" . $no);
    }
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => true]);
        exit;
    }
    header("Location: index.php");
    exit;
}
elseif ($mode === 'delete') {
    $no = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    if ($no <= 0) {
        respond_alert("올바르지 않은 요청입니다.");
    }

    // 기본 최고관리자 보호 (no=1 또는 uid=admin 보호)
    $target = sql_one_one('admins', 'uid', "and no=" . $no);
    if ($target && $target['uid'] === 'admin') {
        respond_alert("최고 관리자 계정(admin)은 삭제할 수 없습니다.");
    }

    $ret = sql_del('admins', "and no=" . $no);
    if ($ret) {
        respond_alert("관리자 계정이 삭제되었습니다.", "index.php");
    } else {
        respond_alert("관리자 계정 삭제에 실패하였습니다.");
    }
}
else {
    header("Location: index.php");
    exit;
}
