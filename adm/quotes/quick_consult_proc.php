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
$allowed_states = ['신규', '상담중', '완료'];

if ($mode === 'change_state') {
    $idx   = intval((isset($_POST['idx']) ? $_POST['idx'] : (isset($_GET['idx']) ? $_GET['idx'] : 0)));
    $state = (isset($_POST['state']) ? $_POST['state'] : (isset($_GET['state']) ? $_GET['state'] : ''));

    if ($idx > 0 && in_array($state, $allowed_states, true)) {
        sql_up('tb_consult_request', "state='" . mysqli_real_escape_string($conn, $state) . "'", "and idx=" . $idx);
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => true]);
        exit;
    }
    header("Location: quick_consult.php");
    exit;
}
elseif ($mode === 'delete') {
    $idx = intval((isset($_POST['idx']) ? $_POST['idx'] : (isset($_GET['idx']) ? $_GET['idx'] : 0)));
    if ($idx <= 0) {
        respond_alert("올바르지 않은 요청입니다.");
    }

    $ret = sql_del('tb_consult_request', "and idx=" . $idx);
    if ($ret) {
        respond_alert("빠른 시공 상담 신청건이 삭제되었습니다.", "quick_consult.php");
    } else {
        respond_alert("삭제 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'delete_bulk') {
    $chk_idx = (isset($_POST['chk_idx']) ? $_POST['chk_idx'] : []);
    if (empty($chk_idx) || !is_array($chk_idx)) {
        respond_alert("삭제할 신청건을 선택해 주세요.");
    }

    $deleted_cnt = 0;
    foreach ($chk_idx as $idx) {
        $idx = intval($idx);
        if ($idx <= 0) continue;

        $ret = sql_del('tb_consult_request', "and idx=" . $idx);
        if ($ret) $deleted_cnt++;
    }

    if ($deleted_cnt > 0) {
        respond_alert("선택한 " . $deleted_cnt . "개 상담 신청건이 삭제되었습니다.", "quick_consult.php");
    } else {
        respond_alert("삭제 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'copy_bulk') {
    $chk_idx = (isset($_POST['chk_idx']) ? $_POST['chk_idx'] : []);
    if (empty($chk_idx) || !is_array($chk_idx)) {
        respond_alert("복사할 신청건을 선택해 주세요.");
    }

    $copied_cnt = 0;
    foreach ($chk_idx as $idx) {
        $idx = intval($idx);
        if ($idx <= 0) continue;

        $target = sql_one_one('tb_consult_request', '*', "and idx=" . $idx);
        if ($target) {
            $new_name = $target['company_name'] . " (복사본)";
            $fields = "order_type='" . mysqli_real_escape_string($conn, $target['order_type']) . "', "
                    . "company_name='" . mysqli_real_escape_string($conn, $new_name) . "', "
                    . "contact_phone='" . mysqli_real_escape_string($conn, $target['contact_phone']) . "', "
                    . "building_car_info='" . mysqli_real_escape_string($conn, $target['building_car_info']) . "', "
                    . "car_number='" . mysqli_real_escape_string($conn, $target['car_number']) . "', "
                    . "email='" . mysqli_real_escape_string($conn, $target['email']) . "', "
                    . "addr='" . mysqli_real_escape_string($conn, $target['addr']) . "', "
                    . "reserve_date='" . mysqli_real_escape_string($conn, $target['reserve_date']) . "', "
                    . "reserve_time='" . mysqli_real_escape_string($conn, $target['reserve_time']) . "', "
                    . "brand='" . mysqli_real_escape_string($conn, $target['brand']) . "', "
                    . "budget='" . mysqli_real_escape_string($conn, $target['budget']) . "', "
                    . "message='" . mysqli_real_escape_string($conn, $target['message']) . "', "
                    . "ip_addr='" . mysqli_real_escape_string($conn, $target['ip_addr']) . "', "
                    . "state='" . mysqli_real_escape_string($conn, isset($target['state']) ? $target['state'] : '신규') . "', "
                    . "reg_date=NOW()";

            $ret = sql_in('tb_consult_request', $fields);
            if ($ret) $copied_cnt++;
        }
    }

    if ($copied_cnt > 0) {
        respond_alert("선택한 " . $copied_cnt . "개 상담 신청건이 복사되었습니다.", "quick_consult.php");
    } else {
        respond_alert("복사 처리에 실패하였습니다.");
    }
}
else {
    header("Location: quick_consult.php");
    exit;
}
