<?php
include_once __DIR__ . "/../inc/dbconn.php";
header('Content-Type: application/json; charset=utf-8');

function fail($msg) {
    echo json_encode(['ok' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('잘못된 요청입니다.');
}

if (empty($_SESSION['s_mem_id'])) {
    echo json_encode(['ok' => false, 'need_login' => true, 'msg' => 'DIY 필름 구매는 회원만 가능합니다. 로그인 후 다시 시도해 주세요.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$product_name  = trim((isset($_POST['product_name']) ? $_POST['product_name'] : ''));
$product_price = (int)((isset($_POST['product_price']) ? $_POST['product_price'] : 0));
$qty           = (int)((isset($_POST['qty']) ? $_POST['qty'] : 1));
$name          = trim((isset($_POST['name']) ? $_POST['name'] : ''));
$hphone        = trim((isset($_POST['hphone']) ? $_POST['hphone'] : ''));
$zipcode       = trim((isset($_POST['zipcode']) ? $_POST['zipcode'] : ''));
$addr1         = trim((isset($_POST['addr1']) ? $_POST['addr1'] : ''));
$addr2         = trim((isset($_POST['addr2']) ? $_POST['addr2'] : ''));
$install_type   = trim(isset($_POST['install_type']) ? $_POST['install_type'] : '자가설치');
$pay_method     = trim(isset($_POST['pay_method']) ? $_POST['pay_method'] : 'vbank');
$vbank_name     = trim(isset($_POST['vbank_name']) ? $_POST['vbank_name'] : '');
$card_name      = trim(isset($_POST['card_name']) ? $_POST['card_name'] : '');
$depositor_name = trim(isset($_POST['depositor_name']) ? $_POST['depositor_name'] : '');

$pay_info = ($pay_method === 'vbank') ? "무통장입금(" . $vbank_name . " / 입금자:" . $depositor_name . ")" : "신용/체크카드(" . $card_name . ")";
$full_memo = "[설치: " . $install_type . "] [" . $pay_info . "] " . $memo;

if ($product_name === '' || $product_price <= 0) {
    fail('상품 정보를 확인할 수 없습니다.');
}
if ($qty < 1 || $qty > 50) {
    $qty = 1;
}
if ($name === '' || $hphone === '') {
    fail('성함과 연락처를 입력해 주세요.');
}

$total_price = $product_price * $qty;
$mem_no_sql = !empty($_SESSION['s_mem_no']) ? (int)$_SESSION['s_mem_no'] : 'NULL';

$fields = "mem_no=" . $mem_no_sql . ", "
        . "product_name='" . mysqli_real_escape_string($conn, $product_name) . "', "
        . "product_price=" . (int)$product_price . ", "
        . "qty=" . (int)$qty . ", "
        . "total_price=" . (int)$total_price . ", "
        . "name='" . mysqli_real_escape_string($conn, $name) . "', "
        . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
        . "zipcode='" . mysqli_real_escape_string($conn, $zipcode) . "', "
        . "addr1='" . mysqli_real_escape_string($conn, $addr1) . "', "
        . "addr2='" . mysqli_real_escape_string($conn, $addr2) . "', "
        . "memo='" . mysqli_real_escape_string($conn, $full_memo) . "', "
        . "agree_privacy=1";

sql_in('orders', $fields);

if (!empty($_SESSION['s_mem_no'])) {
    check_and_update_partner_status($_SESSION['s_mem_no']);
}

if (mb_strpos($install_type, '위탁') !== false) {
    $auc_addr = trim($addr1 . ' ' . $addr2);
    $auc_memo = "DIY 위탁시공 신청 - 상품명: " . $product_name . " (" . $qty . "개)";
    $auc_fields = "mem_no=" . $mem_no_sql . ", "
                . "space_type='아파트', "
                . "py=33, "
                . "addr='" . mysqli_real_escape_string($conn, $auc_addr) . "', "
                . "name='" . mysqli_real_escape_string($conn, $name) . "', "
                . "hphone='" . mysqli_real_escape_string($conn, $hphone) . "', "
                . "desired_price=" . (int)$total_price . ", "
                . "memo='" . mysqli_real_escape_string($conn, $auc_memo) . "', "
                . "state='입찰대기', "
                . "reg_date=NOW()";
    sql_in('auctions', $auc_fields);
}

echo json_encode([
    'ok'    => true,
    'no'    => mysqli_insert_id($conn),
    'total' => number_format($total_price),
    'msg'   => '주문이 정상 접수되었습니다. 결제 안내는 영업일 기준 1일 이내 문자로 발송됩니다.',
], JSON_UNESCAPED_UNICODE);
