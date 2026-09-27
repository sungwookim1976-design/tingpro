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
    $mem_type = in_array((isset($_POST['mem_type']) ? $_POST['mem_type'] : ''), ['customer', 'freelance', 'partner']) ? $_POST['mem_type'] : 'customer';
    $email    = trim((isset($_POST['email']) && $_POST['email'] !== '' ? $_POST['email'] : (isset($_POST['uid']) ? $_POST['uid'] : '')));
    $passwd   = (isset($_POST['passwd']) ? $_POST['passwd'] : '');
    $name     = trim((isset($_POST['name']) ? $_POST['name'] : ''));
    $hphone   = trim((isset($_POST['hphone']) ? $_POST['hphone'] : ''));
    $zipcode  = trim((isset($_POST['zipcode']) ? $_POST['zipcode'] : ''));
    $addr1    = trim((isset($_POST['addr1']) ? $_POST['addr1'] : ''));
    $addr2    = trim((isset($_POST['addr2']) ? $_POST['addr2'] : ''));
    $region   = trim((isset($_POST['region']) ? $_POST['region'] : ''));
    $career   = trim((isset($_POST['career']) ? $_POST['career'] : ''));
    $biz_name = trim((isset($_POST['biz_name']) ? $_POST['biz_name'] : ''));
    $biz_no   = trim((isset($_POST['biz_no']) ? $_POST['biz_no'] : ''));
    $mem_state= isset($_POST['mem_state']) && $_POST['mem_state'] == '1' ? 1 : 0;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond_alert("올바른 이메일 주소(아이디)를 입력해 주세요.");
    }
    if (strlen($passwd) < 4) {
        respond_alert("비밀번호는 4자 이상 입력해 주세요.");
    }
    if (empty($name) || empty($hphone)) {
        respond_alert("이름과 연락처는 필수 입력 항목입니다.");
    }

    $uid = $email;

    // 아이디 / 이메일 중복 체크
    $dup = sql_cnt('members', "and (uid='" . mysqli_real_escape_string($conn, $uid) . "' or email='" . mysqli_real_escape_string($conn, $email) . "')");
    if ($dup > 0) {
        respond_alert("이미 사용 중인 이메일(아이디)입니다.");
    }

    $passwd_hash = password_hash($passwd, PASSWORD_DEFAULT);

    $fields = "mem_type='" . mysqli_real_escape_string($conn, $mem_type) . "', "
            . "uid='" . mysqli_real_escape_string($conn, $uid) . "', "
            . "email='" . mysqli_real_escape_string($conn, $email) . "', "
            . "passwd='" . mysqli_real_escape_string($conn, $passwd_hash) . "', "
            . "name='" . mysqli_real_escape_string($conn, $name) . "', "
            . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
            . "zipcode='" . mysqli_real_escape_string($conn, $zipcode) . "', "
            . "addr1='" . mysqli_real_escape_string($conn, $addr1) . "', "
            . "addr2='" . mysqli_real_escape_string($conn, $addr2) . "', "
            . "region='" . mysqli_real_escape_string($conn, $region) . "', "
            . "career='" . mysqli_real_escape_string($conn, $career) . "', "
            . "biz_name='" . mysqli_real_escape_string($conn, $biz_name) . "', "
            . "biz_no='" . mysqli_real_escape_string($conn, $biz_no) . "', "
            . "mem_state=" . intval($mem_state) . ", "
            . "agree_privacy=1, "
            . "reg_date=NOW()";

    $ret = sql_in('members', $fields);
    if ($ret) {
        respond_alert("회원이 성공적으로 등록되었습니다.", "index.php");
    } else {
        respond_alert("회원 등록 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'update') {
    $no       = intval((isset($_POST['no']) ? $_POST['no'] : 0));
    $mem_type = in_array((isset($_POST['mem_type']) ? $_POST['mem_type'] : ''), ['customer', 'freelance', 'partner']) ? $_POST['mem_type'] : 'customer';
    $email    = trim((isset($_POST['email']) ? $_POST['email'] : ''));
    $name     = trim((isset($_POST['name']) ? $_POST['name'] : ''));
    $hphone   = trim((isset($_POST['hphone']) ? $_POST['hphone'] : ''));
    $zipcode  = trim((isset($_POST['zipcode']) ? $_POST['zipcode'] : ''));
    $addr1    = trim((isset($_POST['addr1']) ? $_POST['addr1'] : ''));
    $addr2    = trim((isset($_POST['addr2']) ? $_POST['addr2'] : ''));
    $region   = trim((isset($_POST['region']) ? $_POST['region'] : ''));
    $career   = trim((isset($_POST['career']) ? $_POST['career'] : ''));
    $biz_name = trim((isset($_POST['biz_name']) ? $_POST['biz_name'] : ''));
    $biz_no   = trim((isset($_POST['biz_no']) ? $_POST['biz_no'] : ''));
    $mem_state= isset($_POST['mem_state']) && $_POST['mem_state'] == '1' ? 1 : 0;
    $passwd   = (isset($_POST['passwd']) ? $_POST['passwd'] : '');

    if ($no <= 0) {
        respond_alert("올바르지 않은 접근입니다.");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond_alert("올바른 이메일 주소(아이디)를 입력해 주세요.");
    }
    if (empty($name) || empty($hphone)) {
        respond_alert("이름과 연락처는 필수 입력 항목입니다.");
    }

    $uid = $email;

    // 이메일 중복 체크 (자기 자신 제외)
    $dup = sql_cnt('members', "and (uid='" . mysqli_real_escape_string($conn, $uid) . "' or email='" . mysqli_real_escape_string($conn, $email) . "') and no<>" . $no);
    if ($dup > 0) {
        respond_alert("이미 다른 회원이 사용 중인 이메일(아이디)입니다.");
    }

    $fields = "mem_type='" . mysqli_real_escape_string($conn, $mem_type) . "', "
            . "uid='" . mysqli_real_escape_string($conn, $uid) . "', "
            . "email='" . mysqli_real_escape_string($conn, $email) . "', "
            . "name='" . mysqli_real_escape_string($conn, $name) . "', "
            . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
            . "zipcode='" . mysqli_real_escape_string($conn, $zipcode) . "', "
            . "addr1='" . mysqli_real_escape_string($conn, $addr1) . "', "
            . "addr2='" . mysqli_real_escape_string($conn, $addr2) . "', "
            . "region='" . mysqli_real_escape_string($conn, $region) . "', "
            . "career='" . mysqli_real_escape_string($conn, $career) . "', "
            . "biz_name='" . mysqli_real_escape_string($conn, $biz_name) . "', "
            . "biz_no='" . mysqli_real_escape_string($conn, $biz_no) . "', "
            . "mem_state=" . intval($mem_state);

    if (!empty($passwd)) {
        if (strlen($passwd) < 4) {
            respond_alert("비밀번호는 4자 이상 입력해 주세요.");
        }
        $passwd_hash = password_hash($passwd, PASSWORD_DEFAULT);
        $fields .= ", passwd='" . mysqli_real_escape_string($conn, $passwd_hash) . "'";
    }

    $ret = sql_up('members', $fields, "and no=" . $no);
    if ($ret) {
        respond_alert("회원 정보가 수정되었습니다.", "index.php");
    } else {
        respond_alert("회원 정보 수정에 실패하였습니다.");
    }
}
elseif ($mode === 'change_state') {
    $no        = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    $mem_state = intval((isset($_POST['mem_state']) ? $_POST['mem_state'] : (isset($_GET['mem_state']) ? $_GET['mem_state'] : 0)));

    if ($no > 0) {
        sql_up('members', "mem_state=" . ($mem_state == 1 ? 1 : 0), "and no=" . $no);
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

    $ret = sql_del('members', "and no=" . $no);
    if ($ret) {
        respond_alert("회원 계정이 삭제되었습니다.", "index.php");
    } else {
        respond_alert("회원 계정 삭제 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'copy_bulk') {
    $chk_no = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("복사할 회원을 선택해 주세요.");
    }

    $copied_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $target = sql_one_one('members', '*', "and no=" . $no);
        if ($target) {
            $new_name = $target['name'] . " (복사본)";
            $rand_suffix = mt_rand(100, 999);
            $email_parts = explode('@', $target['email']);
            if (count($email_parts) == 2) {
                $new_email = $email_parts[0] . "_copy" . $rand_suffix . "@" . $email_parts[1];
            } else {
                $new_email = "copy_" . time() . "_" . $rand_suffix . "@example.com";
            }
            $new_uid = $new_email;

            $fields = "mem_type='" . mysqli_real_escape_string($conn, $target['mem_type']) . "', "
                    . "uid='" . mysqli_real_escape_string($conn, $new_uid) . "', "
                    . "email='" . mysqli_real_escape_string($conn, $new_email) . "', "
                    . "passwd='" . mysqli_real_escape_string($conn, $target['passwd']) . "', "
                    . "name='" . mysqli_real_escape_string($conn, $new_name) . "', "
                    . "hphone='" . mysqli_real_escape_string($conn, $target['hphone']) . "', "
                    . "zipcode='" . mysqli_real_escape_string($conn, $target['zipcode']) . "', "
                    . "addr1='" . mysqli_real_escape_string($conn, $target['addr1']) . "', "
                    . "addr2='" . mysqli_real_escape_string($conn, $target['addr2']) . "', "
                    . "region='" . mysqli_real_escape_string($conn, $target['region']) . "', "
                    . "career='" . mysqli_real_escape_string($conn, $target['career']) . "', "
                    . "biz_name='" . mysqli_real_escape_string($conn, $target['biz_name']) . "', "
                    . "biz_no='" . mysqli_real_escape_string($conn, $target['biz_no']) . "', "
                    . "mem_state=" . intval($target['mem_state']) . ", "
                    . "agree_privacy=1, "
                    . "reg_date=NOW()";

            $ret = sql_in('members', $fields);
            if ($ret) $copied_cnt++;
        }
    }

    if ($copied_cnt > 0) {
        respond_alert("선택한 " . $copied_cnt . "명의 회원이 복사되었습니다.", "index.php");
    } else {
        respond_alert("회원 복사 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'delete_bulk') {
    $chk_no = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("삭제할 회원을 선택해 주세요.");
    }

    $deleted_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $ret = sql_del('members', "and no=" . $no);
        if ($ret) $deleted_cnt++;
    }

    if ($deleted_cnt > 0) {
        respond_alert("선택한 " . $deleted_cnt . "명의 회원이 삭제되었습니다.", "index.php");
    } else {
        respond_alert("회원 삭제 처리에 실패하였습니다.");
    }
}
else {
    header("Location: index.php");
    exit;
}
