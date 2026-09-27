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
            $new_name = "case_auc_" . time() . "_" . mt_rand(100, 999) . "_" . $file_field . "." . $ext;
            $target_path = $target_dir . $new_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                return "imgs/" . $new_name;
            }
        }
    }
    return $existing_img;
}

$mode = (isset($_POST['mode']) ? $_POST['mode'] : (isset($_GET['mode']) ? $_GET['mode'] : ''));
$allowed_states = ['입찰대기', '입찰중', '매칭완료', '취소'];

if ($mode === 'update_case_imgs') {
    $no = intval((isset($_POST['no']) ? $_POST['no'] : 0));
    $redirect_to = (isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'cases.php?no=' . $no);

    if ($no <= 0) {
        respond_alert("올바르지 않은 접근입니다.");
    }

    $existing = sql_one_one('auctions', '*', "and no=" . $no);
    if (!$existing) {
        respond_alert("존재하지 않는 역경매건입니다.");
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
    $ret = sql_up('auctions', $sql_update, "and no=" . $no);
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
    $addr          = trim((isset($_POST['addr']) ? $_POST['addr'] : ''));
    $desired_price = intval((isset($_POST['desired_price']) ? $_POST['desired_price'] : 0));
    $bid_count     = intval((isset($_POST['bid_count']) ? $_POST['bid_count'] : 0));
    $memo          = trim((isset($_POST['memo']) ? $_POST['memo'] : ''));
    $state         = in_array((isset($_POST['state']) ? $_POST['state'] : ''), $allowed_states) ? $_POST['state'] : '입찰대기';
    $mem_no        = intval((isset($_POST['mem_no']) ? $_POST['mem_no'] : 0));

    if (empty($name) || empty($hphone)) {
        respond_alert("신청자 성함과 연락처를 입력해 주세요.");
    }
    if (empty($addr)) {
        respond_alert("시공 주소를 입력해 주세요.");
    }

    $fields = "mem_no=" . $mem_no . ", "
            . "space_type='" . mysqli_real_escape_string($conn, $space_type) . "', "
            . "py=" . $py . ", "
            . "addr='" . mysqli_real_escape_string($conn, $addr) . "', "
            . "name='" . mysqli_real_escape_string($conn, $name) . "', "
            . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
            . "desired_price=" . $desired_price . ", "
            . "bid_count=" . $bid_count . ", "
            . "memo='" . mysqli_real_escape_string($conn, $memo) . "', "
            . "state='" . mysqli_real_escape_string($conn, $state) . "', "
            . "reg_date=NOW()";

    $ret = sql_in('auctions', $fields);
    if ($ret) {
        respond_alert("역경매 신청이 성공적으로 등록되었습니다.", "index.php");
    } else {
        respond_alert("역경매 등록 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'update') {
    $no            = intval((isset($_POST['no']) ? $_POST['no'] : 0));
    $name          = trim((isset($_POST['name']) ? $_POST['name'] : ''));
    $hphone        = trim((isset($_POST['hphone']) ? $_POST['hphone'] : ''));
    $space_type    = trim((isset($_POST['space_type']) ? $_POST['space_type'] : '아파트'));
    $py            = intval((isset($_POST['py']) ? $_POST['py'] : 0));
    $addr          = trim((isset($_POST['addr']) ? $_POST['addr'] : ''));
    $desired_price = intval((isset($_POST['desired_price']) ? $_POST['desired_price'] : 0));
    $bid_count     = intval((isset($_POST['bid_count']) ? $_POST['bid_count'] : 0));
    $memo          = trim((isset($_POST['memo']) ? $_POST['memo'] : ''));
    $state         = in_array((isset($_POST['state']) ? $_POST['state'] : ''), $allowed_states) ? $_POST['state'] : '입찰대기';

    if ($no <= 0) {
        respond_alert("올바르지 않은 접근입니다.");
    }
    if (empty($name) || empty($hphone)) {
        respond_alert("신청자 성함과 연락처를 입력해 주세요.");
    }

    $fields = "space_type='" . mysqli_real_escape_string($conn, $space_type) . "', "
            . "py=" . $py . ", "
            . "addr='" . mysqli_real_escape_string($conn, $addr) . "', "
            . "name='" . mysqli_real_escape_string($conn, $name) . "', "
            . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
            . "desired_price=" . $desired_price . ", "
            . "bid_count=" . $bid_count . ", "
            . "memo='" . mysqli_real_escape_string($conn, $memo) . "', "
            . "state='" . mysqli_real_escape_string($conn, $state) . "'";

    $ret = sql_up('auctions', $fields, "and no=" . $no);
    if ($ret) {
        respond_alert("역경매 정보가 수정되었습니다.", "index.php");
    } else {
        respond_alert("역경매 정보 수정 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'delete') {
    $no = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    if ($no <= 0) {
        respond_alert("올바르지 않은 요청입니다.");
    }

    $ret = sql_del('auctions', "and no=" . $no);
    if ($ret) {
        respond_alert("역경매 신청건이 삭제되었습니다.", "index.php");
    } else {
        respond_alert("역경매 신청건 삭제 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'copy_bulk') {
    $chk_no = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("복사할 역경매 항목을 선택해 주세요.");
    }

    $copied_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $target = sql_one_one('auctions', '*', "and no=" . $no);
        if ($target) {
            $new_name = $target['name'] . " (복사본)";
            $fields = "mem_no=" . intval($target['mem_no']) . ", "
                    . "space_type='" . mysqli_real_escape_string($conn, $target['space_type']) . "', "
                    . "py=" . intval($target['py']) . ", "
                    . "addr='" . mysqli_real_escape_string($conn, $target['addr']) . "', "
                    . "name='" . mysqli_real_escape_string($conn, $new_name) . "', "
                    . "hphone='" . mysqli_real_escape_string($conn, $target['hphone']) . "', "
                    . "desired_price=" . intval($target['desired_price']) . ", "
                    . "bid_count=" . intval($target['bid_count']) . ", "
                    . "memo='" . mysqli_real_escape_string($conn, $target['memo']) . "', "
                    . "state='" . mysqli_real_escape_string($conn, $target['state']) . "', "
                    . "reg_date=NOW()";

            $ret = sql_in('auctions', $fields);
            if ($ret) $copied_cnt++;
        }
    }

    if ($copied_cnt > 0) {
        respond_alert("선택한 " . $copied_cnt . "개 역경매 항목이 복사되었습니다.", "index.php");
    } else {
        respond_alert("역경매 복사 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'delete_bulk') {
    $chk_no = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("삭제할 역경매 항목을 선택해 주세요.");
    }

    $deleted_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $ret = sql_del('auctions', "and no=" . $no);
        if ($ret) $deleted_cnt++;
    }

    if ($deleted_cnt > 0) {
        respond_alert("선택한 " . $deleted_cnt . "개 역경매 항목이 삭제되었습니다.", "index.php");
    } else {
        respond_alert("역경매 삭제 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'change_state') {
    $no    = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    $state = (isset($_POST['state']) ? $_POST['state'] : (isset($_GET['state']) ? $_GET['state'] : ''));

    if ($no > 0 && in_array($state, $allowed_states, true)) {
        sql_up('auctions', "state='" . mysqli_real_escape_string($conn, $state) . "'", "and no=" . $no);
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['ok' => true, 'success' => true]);
        exit;
    }
    header("Location: index.php");
    exit;
}
elseif ($mode === 'comment_insert') {
    $auction_no = intval((isset($_POST['auction_no']) ? $_POST['auction_no'] : 0));
    $writer     = trim((isset($_POST['writer']) ? $_POST['writer'] : '관리자'));
    $content    = trim((isset($_POST['content']) ? $_POST['content'] : ''));

    if ($auction_no <= 0 || empty($content)) {
        respond_alert("댓글 내용을 입력해 주세요.");
    }

    $fields = "auction_no=" . $auction_no . ", "
            . "writer='" . mysqli_real_escape_string($conn, $writer) . "', "
            . "content='" . mysqli_real_escape_string($conn, $content) . "', "
            . "reg_date=NOW()";

    $ret = sql_in('auction_comments', $fields);
    if ($ret) {
        respond_alert("댓글이 등록되었습니다.", "write.php?no=" . $auction_no . "#comments");
    } else {
        respond_alert("댓글 등록에 실패하였습니다.");
    }
}
elseif ($mode === 'comment_update') {
    $no         = intval((isset($_POST['no']) ? $_POST['no'] : 0));
    $auction_no = intval((isset($_POST['auction_no']) ? $_POST['auction_no'] : 0));
    $content    = trim((isset($_POST['content']) ? $_POST['content'] : ''));

    if ($no <= 0 || empty($content)) {
        respond_alert("댓글 내용을 입력해 주세요.");
    }

    $fields = "content='" . mysqli_real_escape_string($conn, $content) . "'";
    $ret = sql_up('auction_comments', $fields, "and no=" . $no);
    if ($ret) {
        respond_alert("댓글이 수정되었습니다.", "write.php?no=" . $auction_no . "#comments");
    } else {
        respond_alert("댓글 수정에 실패하였습니다.");
    }
}
elseif ($mode === 'comment_delete') {
    $no         = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    $auction_no = intval((isset($_POST['auction_no']) ? $_POST['auction_no'] : (isset($_GET['auction_no']) ? $_GET['auction_no'] : 0)));

    if ($no <= 0) {
        respond_alert("올바르지 않은 요청입니다.");
    }

    $ret = sql_del('auction_comments', "and no=" . $no);
    if ($ret) {
        respond_alert("댓글이 삭제되었습니다.", "write.php?no=" . $auction_no . "#comments");
    } else {
        respond_alert("댓글 삭제에 실패하였습니다.");
    }
}
elseif ($mode === 'bid_insert') {
    $auction_no    = intval((isset($_POST['auction_no']) ? $_POST['auction_no'] : 0));
    $bidder_name   = trim((isset($_POST['bidder_name']) ? $_POST['bidder_name'] : ''));
    $bidder_type   = trim((isset($_POST['bidder_type']) ? $_POST['bidder_type'] : '프리랜서(개인)'));
    $bidder_hphone = trim((isset($_POST['bidder_hphone']) ? $_POST['bidder_hphone'] : ''));
    $bid_price     = intval((isset($_POST['bid_price']) ? $_POST['bid_price'] : 0));
    $work_duration = trim((isset($_POST['work_duration']) ? $_POST['work_duration'] : '1일 소요'));
    $memo          = trim((isset($_POST['memo']) ? $_POST['memo'] : ''));
    $state         = trim((isset($_POST['state']) ? $_POST['state'] : '입찰중'));

    if ($auction_no <= 0 || empty($bidder_name) || $bid_price <= 0) {
        respond_alert("입찰자명과 입찰 금액을 올바르게 입력해 주세요.");
    }

    $fields = "auction_no=" . $auction_no . ", "
            . "bidder_name='" . mysqli_real_escape_string($conn, $bidder_name) . "', "
            . "bidder_type='" . mysqli_real_escape_string($conn, $bidder_type) . "', "
            . "bidder_hphone='" . mysqli_real_escape_string($conn, $bidder_hphone) . "', "
            . "bid_price=" . $bid_price . ", "
            . "work_duration='" . mysqli_real_escape_string($conn, $work_duration) . "', "
            . "memo='" . mysqli_real_escape_string($conn, $memo) . "', "
            . "state='" . mysqli_real_escape_string($conn, $state) . "', "
            . "reg_date=NOW()";

    $ret = sql_in('auction_bids', $fields);
    if ($ret) {
        // Auctions 테이블의 bid_count 동기화
        $cnt = sql_cnt('auction_bids', "and auction_no=" . $auction_no);
        sql_up('auctions', "bid_count=" . $cnt, "and no=" . $auction_no);

        respond_alert("입찰 제안이 성공적으로 등록되었습니다.", "write.php?no=" . $auction_no . "#bids");
    } else {
        respond_alert("입찰 제안 등록에 실패하였습니다.");
    }
}
elseif ($mode === 'bid_update') {
    $no            = intval((isset($_POST['no']) ? $_POST['no'] : 0));
    $auction_no    = intval((isset($_POST['auction_no']) ? $_POST['auction_no'] : 0));
    $bidder_name   = trim((isset($_POST['bidder_name']) ? $_POST['bidder_name'] : ''));
    $bid_price     = intval((isset($_POST['bid_price']) ? $_POST['bid_price'] : 0));
    $work_duration = trim((isset($_POST['work_duration']) ? $_POST['work_duration'] : '1일 소요'));
    $memo          = trim((isset($_POST['memo']) ? $_POST['memo'] : ''));
    $state         = trim((isset($_POST['state']) ? $_POST['state'] : '입찰중'));

    if ($no <= 0 || empty($bidder_name) || $bid_price <= 0) {
        respond_alert("입찰자명과 입찰 금액을 올바르게 입력해 주세요.");
    }

    $fields = "bidder_name='" . mysqli_real_escape_string($conn, $bidder_name) . "', "
            . "bid_price=" . $bid_price . ", "
            . "work_duration='" . mysqli_real_escape_string($conn, $work_duration) . "', "
            . "memo='" . mysqli_real_escape_string($conn, $memo) . "', "
            . "state='" . mysqli_real_escape_string($conn, $state) . "'";

    $ret = sql_up('auction_bids', $fields, "and no=" . $no);
    if ($ret) {
        respond_alert("입찰 제안 정보가 수정되었습니다.", "write.php?no=" . $auction_no . "#bids");
    } else {
        respond_alert("입찰 제안 수정에 실패하였습니다.");
    }
}
elseif ($mode === 'bid_delete') {
    $no         = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    $auction_no = intval((isset($_POST['auction_no']) ? $_POST['auction_no'] : (isset($_GET['auction_no']) ? $_GET['auction_no'] : 0)));

    if ($no <= 0) {
        respond_alert("올바르지 않은 요청입니다.");
    }

    $ret = sql_del('auction_bids', "and no=" . $no);
    if ($ret) {
        if ($auction_no > 0) {
            $cnt = sql_cnt('auction_bids', "and auction_no=" . $auction_no);
            sql_up('auctions', "bid_count=" . $cnt, "and no=" . $auction_no);
        }
        respond_alert("입찰 제안이 삭제되었습니다.", "write.php?no=" . $auction_no . "#bids");
    } else {
        respond_alert("입찰 제안 삭제에 실패하였습니다.");
    }
}
elseif ($mode === 'select_winner') {
    $no         = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    $auction_no = intval((isset($_POST['auction_no']) ? $_POST['auction_no'] : (isset($_GET['auction_no']) ? $_GET['auction_no'] : 0)));

    if ($no <= 0 || $auction_no <= 0) {
        respond_alert("올바르지 않은 요청입니다.");
    }

    // 1. 해당 역경매의 다른 기존 낙찰자 입찰을 '입찰중'으로 변경
    sql_up('auction_bids', "state='입찰중'", "and auction_no=" . $auction_no . " and state='낙찰'");

    // 2. 선택한 입찰자를 '낙찰'로 설정
    $ret = sql_up('auction_bids', "state='낙찰'", "and no=" . $no);

    // 3. 해당 역경매 상태를 '매칭완료'로 업그레이드
    sql_up('auctions', "state='매칭완료'", "and no=" . $auction_no);

    if ($ret) {
        respond_alert("최종 낙찰자로 선정되었습니다! 역경매 상태가 '매칭완료'로 변경되었습니다.", "write.php?no=" . $auction_no . "#bids");
    } else {
        respond_alert("낙찰 처리 중 오류가 발생하였습니다.");
    }
}
else {
    header("Location: index.php");
    exit;
}
