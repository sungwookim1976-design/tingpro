<?php
if (session_status() === PHP_SESSION_NONE) {
    $script_path = str_replace('\\', '/', (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : ''));
    $dir_path    = str_replace('\\', '/', __DIR__);
    $req_uri     = str_replace('\\', '/', (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''));
    if (strpos($script_path, '/adm/') !== false || strpos($req_uri, '/adm/') !== false || strpos($dir_path, '/adm') !== false) {
        session_name('APT_ADMIN_SESS');
    } else {
        session_name('APT_FRONT_SESS');
    }
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . "/dbconn.php";

$board_type        = isset($_POST['board_type']) ? trim($_POST['board_type']) : '';
$order_type        = isset($_POST['order_type']) ? trim($_POST['order_type']) : ($board_type ? $board_type : '상담문의');
$company_name      = isset($_POST['companyName']) ? trim($_POST['companyName']) : '';
$contact_phone     = isset($_POST['contactPhone']) ? trim($_POST['contactPhone']) : '';
$building_car_info = isset($_POST['contactName']) ? trim($_POST['contactName']) : '';
$car_number        = isset($_POST['carNumber']) ? trim($_POST['carNumber']) : '';
$email             = isset($_POST['contactEmail']) ? trim($_POST['contactEmail']) : '';
$addr              = isset($_POST['addr']) ? trim($_POST['addr']) : '';
$reserve_date      = isset($_POST['reserveDate']) ? trim($_POST['reserveDate']) : '';
$reserve_time      = isset($_POST['reserveTime']) ? trim($_POST['reserveTime']) : '';
$brand             = isset($_POST['interest']) ? trim($_POST['interest']) : '';
$budget            = isset($_POST['cost']) ? trim($_POST['cost']) : '';
$title             = isset($_POST['title']) ? trim($_POST['title']) : '';
$content           = isset($_POST['content']) ? trim($_POST['content']) : (isset($_POST['messageContent']) ? trim($_POST['messageContent']) : '');
$ip_addr           = $_SERVER['REMOTE_ADDR'];

if (empty($company_name) || empty($contact_phone)) {
    echo json_encode(['result' => 'fail', 'msg' => '성함과 연락처는 필수 입력 항목입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. 게시판 ID (consult, freeconsult 등) DB 저장 처리
if ($board_type === 'quickconsult') $board_type = 'freeconsult';
if ($order_type === 'quickconsult') $order_type = 'freeconsult';

$target_brd_id = $board_type !== '' ? $board_type : ($order_type !== '' ? $order_type : 'consult');
$target_brd_name = ($target_brd_id === 'freeconsult' || $target_brd_id === 'quickconsult') ? '무료방문실측' : (($target_brd_id === 'consult') ? '상담문의' : $target_brd_id);

// board_config 테이블 존재 및 해당 게시판 설정 등록 확인 (간편견적 삭제 및 quickconsult -> freeconsult 변경 포함)
$chk_cfg = mysqli_query($conn, "SHOW TABLES LIKE 'board_config'");
if ($chk_cfg && mysqli_num_rows($chk_cfg) > 0) {
    mysqli_query($conn, "DELETE FROM board_config WHERE brd_id = '간편견적' OR brd_name = '간편견적' OR brd_id = 'estimate'");
    mysqli_query($conn, "UPDATE board_config SET brd_id = 'freeconsult' WHERE brd_id = 'quickconsult'");
    mysqli_query($conn, "UPDATE community_posts SET board_type = 'freeconsult' WHERE board_type = 'quickconsult'");
    $chk_brd = mysqli_query($conn, "SELECT brd_id FROM board_config WHERE brd_id = '" . mysqli_real_escape_string($conn, $target_brd_id) . "'");
    if ($chk_brd && mysqli_num_rows($chk_brd) == 0) {
        $sort_num = ($target_brd_id === 'freeconsult') ? 11 : 10;
        mysqli_query($conn, "INSERT INTO board_config (brd_id, brd_name, brd_type, use_comment, use_write, file_cnt, file_size, list_cnt, sort_order, use_yn) VALUES ('" . mysqli_real_escape_string($conn, $target_brd_id) . "', '" . mysqli_real_escape_string($conn, $target_brd_name) . "', 3, 1, 2, 0, 5120, 15, $sort_num, 1)");
    }
}

// community_posts 테이블 저장
$chk_posts = mysqli_query($conn, "SHOW TABLES LIKE 'community_posts'");
if ($chk_posts && mysqli_num_rows($chk_posts) > 0) {
    $post_title = !empty($title) ? $title : ($company_name . ' 님의 ' . $target_brd_name);
    
    $content_prefix = "[성함: " . $company_name . " | 연락처: " . $contact_phone;
    if (!empty($addr)) {
        $content_prefix .= " | 주소: " . $addr;
    }
    $content_prefix .= "]\n\n";
    
    $post_content = $content_prefix . $content;

    $stmt_cp = mysqli_prepare($conn, "INSERT INTO community_posts (board_type, badge, title, content, state, reg_date) VALUES (?, ?, ?, ?, 1, NOW())");
    if ($stmt_cp) {
        mysqli_stmt_bind_param($stmt_cp, "ssss", $target_brd_id, $target_brd_name, $post_title, $post_content);
        mysqli_stmt_execute($stmt_cp);
        mysqli_stmt_close($stmt_cp);
    }
}

// 2. tb_consult_request 테이블 자동 생성 및 INSERT (기존 상담 데이터 기록)
$createTableSql = "
CREATE TABLE IF NOT EXISTS tb_consult_request (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    order_type VARCHAR(50) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(50) NOT NULL,
    building_car_info VARCHAR(255) DEFAULT '',
    car_number VARCHAR(50) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    addr VARCHAR(255) DEFAULT '',
    reserve_date VARCHAR(20) DEFAULT '',
    reserve_time VARCHAR(20) DEFAULT '',
    brand VARCHAR(100) DEFAULT '',
    budget VARCHAR(50) DEFAULT '',
    message TEXT,
    ip_addr VARCHAR(50) DEFAULT '',
    reg_date DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
mysqli_query($conn, $createTableSql);

$save_msg = !empty($title) ? ("제목: " . $title . "\n주소: " . $addr . "\n\n" . $content) : $content;

$stmt = mysqli_prepare($conn, "INSERT INTO tb_consult_request 
(order_type, company_name, contact_phone, building_car_info, car_number, email, addr, reserve_date, reserve_time, brand, budget, message, ip_addr) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssssssss", 
        $target_brd_id, $company_name, $contact_phone, $building_car_info, $car_number, 
        $email, $addr, $reserve_date, $reserve_time, $brand, $budget, $save_msg, $ip_addr
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// 3. 관리자 견적관리 목록(quotes 테이블) 자동 저장 처리
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `quotes` (
  `no` int(11) NOT NULL AUTO_INCREMENT,
  `mem_no` int(11) DEFAULT NULL,
  `space_type` varchar(50) NOT NULL DEFAULT '아파트',
  `py` int(11) NOT NULL DEFAULT '0',
  `film_name` varchar(100) NOT NULL DEFAULT '',
  `film_price_py` int(11) NOT NULL DEFAULT '0',
  `total_price` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `hphone` varchar(30) NOT NULL DEFAULT '',
  `addr` varchar(255) DEFAULT '',
  `request_date` date DEFAULT NULL,
  `memo` text,
  `agree_privacy` tinyint(1) NOT NULL DEFAULT '1',
  `state` varchar(20) NOT NULL DEFAULT '신규',
  `reg_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$quote_space_type = !empty($building_car_info) ? $building_car_info : ($target_brd_name !== '' ? $target_brd_name : '빠른시공상담');
$quote_film_name  = !empty($brand) ? $brand : '맞춤 상담';
$quote_price      = !empty($budget) ? (int)preg_replace('/[^0-9]/', '', $budget) : 0;
$quote_mem_no     = !empty($_SESSION['s_mem_no']) ? (int)$_SESSION['s_mem_no'] : 'NULL';
$quote_req_date   = (!empty($reserve_date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $reserve_date)) ? "'" . mysqli_real_escape_string($conn, $reserve_date) . "'" : "NULL";

$q_fields = "mem_no=" . $quote_mem_no . ", "
          . "space_type='" . mysqli_real_escape_string($conn, $quote_space_type) . "', "
          . "py=0, "
          . "film_name='" . mysqli_real_escape_string($conn, $quote_film_name) . "', "
          . "film_price_py=0, "
          . "total_price=" . (int)$quote_price . ", "
          . "name='" . mysqli_real_escape_string($conn, $company_name) . "', "
          . "hphone='" . mysqli_real_escape_string($conn, $contact_phone) . "', "
          . "addr='" . mysqli_real_escape_string($conn, $addr) . "', "
          . "request_date=" . $quote_req_date . ", "
          . "memo='" . mysqli_real_escape_string($conn, $save_msg) . "', "
          . "agree_privacy=1, "
          . "state='신규', "
          . "reg_date=NOW()";

sql_in('quotes', $q_fields);

echo json_encode([
    'result' => 'success',
    'msg' => $target_brd_name . ' 신청이 정상 접수되었습니다! (DB 저장 및 관리자 견적목록 자동 반영)'
], JSON_UNESCAPED_UNICODE);
