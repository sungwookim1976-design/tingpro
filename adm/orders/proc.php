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

$allowed_states = ['주문접수', '결제확인중', '배송준비', '배송중', '배송완료', '취소'];

if ($mode === 'insert') {
    $name          = trim((isset($_POST['name']) ? $_POST['name'] : ''));
    $hphone        = trim((isset($_POST['hphone']) ? $_POST['hphone'] : ''));
    $product_name  = trim((isset($_POST['product_name']) ? $_POST['product_name'] : ''));
    $product_price = intval((isset($_POST['product_price']) ? $_POST['product_price'] : 0));
    $qty           = intval((isset($_POST['qty']) ? $_POST['qty'] : 1));
    if ($qty < 1) $qty = 1;
    $total_price   = intval((isset($_POST['total_price']) ? $_POST['total_price'] : ($product_price * $qty)));
    $zipcode       = trim((isset($_POST['zipcode']) ? $_POST['zipcode'] : ''));
    $addr1         = trim((isset($_POST['addr1']) ? $_POST['addr1'] : ''));
    $addr2         = trim((isset($_POST['addr2']) ? $_POST['addr2'] : ''));
    $memo          = trim((isset($_POST['memo']) ? $_POST['memo'] : ''));
    $state         = in_array((isset($_POST['state']) ? $_POST['state'] : ''), $allowed_states) ? $_POST['state'] : '주문접수';

    if (empty($name) || empty($hphone)) {
        respond_alert("주문자 성함과 연락처를 입력해 주세요.");
    }
    if (empty($product_name)) {
        respond_alert("주문 상품명을 입력해 주세요.");
    }

    $fields = "product_name='" . mysqli_real_escape_string($conn, $product_name) . "', "
            . "product_price=" . $product_price . ", "
            . "qty=" . $qty . ", "
            . "total_price=" . $total_price . ", "
            . "name='" . mysqli_real_escape_string($conn, $name) . "', "
            . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
            . "zipcode='" . mysqli_real_escape_string($conn, $zipcode) . "', "
            . "addr1='" . mysqli_real_escape_string($conn, $addr1) . "', "
            . "addr2='" . mysqli_real_escape_string($conn, $addr2) . "', "
            . "memo='" . mysqli_real_escape_string($conn, $memo) . "', "
            . "agree_privacy=1, "
            . "state='" . mysqli_real_escape_string($conn, $state) . "', "
            . "reg_date=NOW()";

    $ret = sql_in('orders', $fields);
    if ($ret) {
        respond_alert("주문이 성공적으로 등록되었습니다.", "index.php");
    } else {
        respond_alert("주문 등록 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'update') {
    $no            = intval((isset($_POST['no']) ? $_POST['no'] : 0));
    $name          = trim((isset($_POST['name']) ? $_POST['name'] : ''));
    $hphone        = trim((isset($_POST['hphone']) ? $_POST['hphone'] : ''));
    $product_name  = trim((isset($_POST['product_name']) ? $_POST['product_name'] : ''));
    $product_price = intval((isset($_POST['product_price']) ? $_POST['product_price'] : 0));
    $qty           = intval((isset($_POST['qty']) ? $_POST['qty'] : 1));
    if ($qty < 1) $qty = 1;
    $total_price   = intval((isset($_POST['total_price']) ? $_POST['total_price'] : ($product_price * $qty)));
    $zipcode       = trim((isset($_POST['zipcode']) ? $_POST['zipcode'] : ''));
    $addr1         = trim((isset($_POST['addr1']) ? $_POST['addr1'] : ''));
    $addr2         = trim((isset($_POST['addr2']) ? $_POST['addr2'] : ''));
    $memo          = trim((isset($_POST['memo']) ? $_POST['memo'] : ''));
    $state         = in_array((isset($_POST['state']) ? $_POST['state'] : ''), $allowed_states) ? $_POST['state'] : '주문접수';

    if ($no <= 0) {
        respond_alert("올바르지 않은 접근입니다.");
    }
    if (empty($name) || empty($hphone)) {
        respond_alert("주문자 성함과 연락처를 입력해 주세요.");
    }

    $fields = "product_name='" . mysqli_real_escape_string($conn, $product_name) . "', "
            . "product_price=" . $product_price . ", "
            . "qty=" . $qty . ", "
            . "total_price=" . $total_price . ", "
            . "name='" . mysqli_real_escape_string($conn, $name) . "', "
            . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
            . "zipcode='" . mysqli_real_escape_string($conn, $zipcode) . "', "
            . "addr1='" . mysqli_real_escape_string($conn, $addr1) . "', "
            . "addr2='" . mysqli_real_escape_string($conn, $addr2) . "', "
            . "memo='" . mysqli_real_escape_string($conn, $memo) . "', "
            . "state='" . mysqli_real_escape_string($conn, $state) . "'";

    $ret = sql_up('orders', $fields, "and no=" . $no);
    if ($ret) {
        respond_alert("주문 정보가 수정되었습니다.", "index.php");
    } else {
        respond_alert("주문 정보 수정 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'delete') {
    $no = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    if ($no <= 0) {
        respond_alert("올바르지 않은 요청입니다.");
    }

    $ret = sql_del('orders', "and no=" . $no);
    if ($ret) {
        respond_alert("주문 내역이 삭제되었습니다.", "index.php");
    } else {
        respond_alert("주문 내역 삭제 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'copy_bulk') {
    $chk_no = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("복사할 주문을 선택해 주세요.");
    }

    $copied_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $target = sql_one_one('orders', '*', "and no=" . $no);
        if ($target) {
            $new_name = $target['name'] . " (복사본)";
            $fields = "product_name='" . mysqli_real_escape_string($conn, $target['product_name']) . "', "
                    . "product_price=" . intval($target['product_price']) . ", "
                    . "qty=" . intval($target['qty']) . ", "
                    . "total_price=" . intval($target['total_price']) . ", "
                    . "name='" . mysqli_real_escape_string($conn, $new_name) . "', "
                    . "hphone='" . mysqli_real_escape_string($conn, $target['hphone']) . "', "
                    . "zipcode='" . mysqli_real_escape_string($conn, $target['zipcode']) . "', "
                    . "addr1='" . mysqli_real_escape_string($conn, $target['addr1']) . "', "
                    . "addr2='" . mysqli_real_escape_string($conn, $target['addr2']) . "', "
                    . "memo='" . mysqli_real_escape_string($conn, $target['memo']) . "', "
                    . "agree_privacy=1, "
                    . "state='" . mysqli_real_escape_string($conn, $target['state']) . "', "
                    . "reg_date=NOW()";

            $ret = sql_in('orders', $fields);
            if ($ret) $copied_cnt++;
        }
    }

    if ($copied_cnt > 0) {
        respond_alert("선택한 " . $copied_cnt . "개 주문건이 복사되었습니다.", "index.php");
    } else {
        respond_alert("주문 복사 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'delete_bulk') {
    $chk_no = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("삭제할 주문을 선택해 주세요.");
    }

    $deleted_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $ret = sql_del('orders', "and no=" . $no);
        if ($ret) $deleted_cnt++;
    }

    if ($deleted_cnt > 0) {
        respond_alert("선택한 " . $deleted_cnt . "개 주문 내역이 삭제되었습니다.", "index.php");
    } else {
        respond_alert("주문 삭제 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'change_state') {
    $no    = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    $state = (isset($_POST['state']) ? $_POST['state'] : (isset($_GET['state']) ? $_GET['state'] : ''));

    if ($no > 0 && in_array($state, $allowed_states, true)) {
        sql_up('orders', "state='" . mysqli_real_escape_string($conn, $state) . "'", "and no=" . $no);
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => true]);
        exit;
    }
    header("Location: index.php");
    exit;
}
elseif ($mode === 'comment_insert') {
    $order_no = intval((isset($_POST['order_no']) ? $_POST['order_no'] : 0));
    $writer   = trim((isset($_POST['writer']) ? $_POST['writer'] : '관리자'));
    $content  = trim((isset($_POST['content']) ? $_POST['content'] : ''));

    if ($order_no <= 0 || empty($content)) {
        respond_alert("댓글 내용을 입력해 주세요.");
    }

    $fields = "order_no=" . $order_no . ", "
            . "writer='" . mysqli_real_escape_string($conn, $writer) . "', "
            . "content='" . mysqli_real_escape_string($conn, $content) . "', "
            . "reg_date=NOW()";

    $ret = sql_in('order_comments', $fields);
    if ($ret) {
        respond_alert("댓글이 등록되었습니다.", "write.php?no=" . $order_no . "#comments");
    } else {
        respond_alert("댓글 등록에 실패하였습니다.");
    }
}
elseif ($mode === 'comment_update') {
    $no       = intval((isset($_POST['no']) ? $_POST['no'] : 0));
    $order_no = intval((isset($_POST['order_no']) ? $_POST['order_no'] : 0));
    $content  = trim((isset($_POST['content']) ? $_POST['content'] : ''));

    if ($no <= 0 || empty($content)) {
        respond_alert("댓글 내용을 입력해 주세요.");
    }

    $fields = "content='" . mysqli_real_escape_string($conn, $content) . "'";
    $ret = sql_up('order_comments', $fields, "and no=" . $no);
    if ($ret) {
        respond_alert("댓글이 수정되었습니다.", "write.php?no=" . $order_no . "#comments");
    } else {
        respond_alert("댓글 수정에 실패하였습니다.");
    }
}
elseif ($mode === 'comment_delete') {
    $no       = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    $order_no = intval((isset($_POST['order_no']) ? $_POST['order_no'] : (isset($_GET['order_no']) ? $_GET['order_no'] : 0)));

    if ($no <= 0) {
        respond_alert("올바르지 않은 요청입니다.");
    }

    $ret = sql_del('order_comments', "and no=" . $no);
    if ($ret) {
        respond_alert("댓글이 삭제되었습니다.", "write.php?no=" . $order_no . "#comments");
    } else {
        respond_alert("댓글 삭제에 실패하였습니다.");
    }
}
else {
    header("Location: index.php");
    exit;
}
