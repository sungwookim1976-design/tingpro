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

function handle_case_image_upload($existing_img = '', $file_field = 'case_img1_file') {
    if (isset($_FILES[$file_field]) && $_FILES[$file_field]['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES[$file_field]['tmp_name'];
        $file_name = $_FILES[$file_field]['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        if (in_array($ext, $allowed_exts)) {
            $target_dir = __DIR__ . "/../../imgs/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $new_name = "case_quote_" . time() . "_" . mt_rand(100, 999) . "_" . $file_field . "." . $ext;
            $target_path = $target_dir . $new_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                return "imgs/" . $new_name;
            }
        }
    }
    return $existing_img;
}

$mode = (isset($_POST['mode']) ? $_POST['mode'] : (isset($_GET['mode']) ? $_GET['mode'] : ''));

$allowed_states = ['신규', '상담중', '완료'];

if ($mode === 'update_case_imgs') {
    $no = intval((isset($_POST['no']) ? $_POST['no'] : 0));
    $redirect_to = (isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'cases.php?no=' . $no);

    if ($no <= 0) {
        respond_alert("올바르지 않은 접근입니다.");
    }

    $existing = sql_one_one('quotes', '*', "and no=" . $no);
    if (!$existing) {
        respond_alert("존재하지 않는 견적건입니다.");
    }

    $up_fields = [];
    for ($i = 1; $i <= 5; $i++) {
        $file_field = "case_img" . $i . "_file";
        $text_field = "case_img" . $i;
        $del_field  = "del_case_img" . $i;

        $cur_img = isset($_POST[$text_field]) ? trim($_POST[$text_field]) : (isset($existing['case_img' . $i]) ? $existing['case_img' . $i] : '');
        if (isset($_POST[$del_field]) && $_POST[$del_field] == '1') {
            $cur_img = '';
        }
        $final_img = handle_case_image_upload($cur_img, $file_field);
        $up_fields[] = "case_img" . $i . "='" . mysqli_real_escape_string($conn, $final_img) . "'";
    }

    $sql_update = implode(", ", $up_fields);
    $ret = sql_up('quotes', $sql_update, "and no=" . $no);
    if ($ret) {
        respond_alert("시공사례 이미지 5장이 성공적으로 저장되었습니다.", $redirect_to);
    } else {
        respond_alert("시공사례 이미지 저장 처리에 실패하였습니다.");
    }
}

if ($mode === 'insert') {
    $name          = trim((isset($_POST['name']) ? $_POST['name'] : ''));
    $hphone        = trim((isset($_POST['hphone']) ? $_POST['hphone'] : ''));
    $space_type    = trim((isset($_POST['space_type']) ? $_POST['space_type'] : '아파트'));
    $py            = intval((isset($_POST['py']) ? $_POST['py'] : 0));
    $film_name     = trim((isset($_POST['film_name']) ? $_POST['film_name'] : ''));
    $film_price_py = intval((isset($_POST['film_price_py']) ? $_POST['film_price_py'] : 0));
    $total_price   = intval((isset($_POST['total_price']) ? $_POST['total_price'] : ($film_price_py * $py)));
    $addr          = trim((isset($_POST['addr']) ? $_POST['addr'] : ''));
    $request_date  = trim((isset($_POST['request_date']) ? $_POST['request_date'] : ''));
    $memo          = trim((isset($_POST['memo']) ? $_POST['memo'] : ''));
    $state         = in_array((isset($_POST['state']) ? $_POST['state'] : ''), $allowed_states) ? $_POST['state'] : '신규';

    if (empty($name) || empty($hphone)) {
        respond_alert("신청자 성함과 연락처를 입력해 주세요.");
    }
    if (empty($film_name)) {
        respond_alert("필름 종류를 입력해 주세요.");
    }

    $req_date_val = !empty($request_date) ? "'" . mysqli_real_escape_string($conn, $request_date) . "'" : "NULL";

    $fields = "space_type='" . mysqli_real_escape_string($conn, $space_type) . "', "
            . "py=" . $py . ", "
            . "film_name='" . mysqli_real_escape_string($conn, $film_name) . "', "
            . "film_price_py=" . $film_price_py . ", "
            . "total_price=" . $total_price . ", "
            . "name='" . mysqli_real_escape_string($conn, $name) . "', "
            . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
            . "addr='" . mysqli_real_escape_string($conn, $addr) . "', "
            . "request_date=" . $req_date_val . ", "
            . "memo='" . mysqli_real_escape_string($conn, $memo) . "', "
            . "agree_privacy=1, "
            . "state='" . mysqli_real_escape_string($conn, $state) . "', "
            . "reg_date=NOW()";

    $ret = sql_in('quotes', $fields);
    if ($ret) {
        respond_alert("견적 신청이 성공적으로 등록되었습니다.", "index.php");
    } else {
        respond_alert("견적 등록 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'update') {
    $no            = intval((isset($_POST['no']) ? $_POST['no'] : 0));
    $name          = trim((isset($_POST['name']) ? $_POST['name'] : ''));
    $hphone        = trim((isset($_POST['hphone']) ? $_POST['hphone'] : ''));
    $space_type    = trim((isset($_POST['space_type']) ? $_POST['space_type'] : '아파트'));
    $py            = intval((isset($_POST['py']) ? $_POST['py'] : 0));
    $film_name     = trim((isset($_POST['film_name']) ? $_POST['film_name'] : ''));
    $film_price_py = intval((isset($_POST['film_price_py']) ? $_POST['film_price_py'] : 0));
    $total_price   = intval((isset($_POST['total_price']) ? $_POST['total_price'] : ($film_price_py * $py)));
    $addr          = trim((isset($_POST['addr']) ? $_POST['addr'] : ''));
    $request_date  = trim((isset($_POST['request_date']) ? $_POST['request_date'] : ''));
    $memo          = trim((isset($_POST['memo']) ? $_POST['memo'] : ''));
    $state         = in_array((isset($_POST['state']) ? $_POST['state'] : ''), $allowed_states) ? $_POST['state'] : '신규';

    if ($no <= 0) {
        respond_alert("올바르지 않은 접근입니다.");
    }
    if (empty($name) || empty($hphone)) {
        respond_alert("신청자 성함과 연락처를 입력해 주세요.");
    }

    $req_date_val = !empty($request_date) ? "'" . mysqli_real_escape_string($conn, $request_date) . "'" : "NULL";

    $fields = "space_type='" . mysqli_real_escape_string($conn, $space_type) . "', "
            . "py=" . $py . ", "
            . "film_name='" . mysqli_real_escape_string($conn, $film_name) . "', "
            . "film_price_py=" . $film_price_py . ", "
            . "total_price=" . $total_price . ", "
            . "name='" . mysqli_real_escape_string($conn, $name) . "', "
            . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
            . "addr='" . mysqli_real_escape_string($conn, $addr) . "', "
            . "request_date=" . $req_date_val . ", "
            . "memo='" . mysqli_real_escape_string($conn, $memo) . "', "
            . "state='" . mysqli_real_escape_string($conn, $state) . "'";

    $ret = sql_up('quotes', $fields, "and no=" . $no);
    if ($ret) {
        respond_alert("견적 정보가 수정되었습니다.", "index.php");
    } else {
        respond_alert("견적 정보 수정 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'delete') {
    $no = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    if ($no <= 0) {
        respond_alert("올바르지 않은 요청입니다.");
    }

    $ret = sql_del('quotes', "and no=" . $no);
    if ($ret) {
        respond_alert("견적 신청건이 삭제되었습니다.", "index.php");
    } else {
        respond_alert("견적 신청건 삭제 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'copy_bulk') {
    $chk_no = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("복사할 견적을 선택해 주세요.");
    }

    $copied_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $target = sql_one_one('quotes', '*', "and no=" . $no);
        if ($target) {
            $new_name = $target['name'] . " (복사본)";
            $req_date_val = !empty($target['request_date']) ? "'" . mysqli_real_escape_string($conn, $target['request_date']) . "'" : "NULL";

            $fields = "space_type='" . mysqli_real_escape_string($conn, $target['space_type']) . "', "
                    . "py=" . intval($target['py']) . ", "
                    . "film_name='" . mysqli_real_escape_string($conn, $target['film_name']) . "', "
                    . "film_price_py=" . intval($target['film_price_py']) . ", "
                    . "total_price=" . intval($target['total_price']) . ", "
                    . "name='" . mysqli_real_escape_string($conn, $new_name) . "', "
                    . "hphone='" . mysqli_real_escape_string($conn, $target['hphone']) . "', "
                    . "addr='" . mysqli_real_escape_string($conn, $target['addr']) . "', "
                    . "request_date=" . $req_date_val . ", "
                    . "memo='" . mysqli_real_escape_string($conn, $target['memo']) . "', "
                    . "agree_privacy=1, "
                    . "state='" . mysqli_real_escape_string($conn, $target['state']) . "', "
                    . "reg_date=NOW()";

            $ret = sql_in('quotes', $fields);
            if ($ret) $copied_cnt++;
        }
    }

    if ($copied_cnt > 0) {
        respond_alert("선택한 " . $copied_cnt . "개 견적건이 복사되었습니다.", "index.php");
    } else {
        respond_alert("견적 복사 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'delete_bulk') {
    $chk_no = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("삭제할 견적을 선택해 주세요.");
    }

    $deleted_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $ret = sql_del('quotes', "and no=" . $no);
        if ($ret) $deleted_cnt++;
    }

    if ($deleted_cnt > 0) {
        respond_alert("선택한 " . $deleted_cnt . "개 견적 신청건이 삭제되었습니다.", "index.php");
    } else {
        respond_alert("견적 삭제 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'change_state') {
    $no    = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    $state = (isset($_POST['state']) ? $_POST['state'] : (isset($_GET['state']) ? $_GET['state'] : ''));

    if ($no > 0 && in_array($state, $allowed_states, true)) {
        sql_up('quotes', "state='" . mysqli_real_escape_string($conn, $state) . "'", "and no=" . $no);
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => true]);
        exit;
    }
    header("Location: index.php");
    exit;
}
else {
    header("Location: index.php");
    exit;
}
