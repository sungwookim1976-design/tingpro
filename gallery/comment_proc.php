<?php
include_once __DIR__ . "/../inc/dbconn.php";
header('Content-Type: application/json; charset=utf-8');

// case_comments 테이블 자동 생성
@mysqli_query($conn, "CREATE TABLE IF NOT EXISTS case_comments (
    no INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tbl_type VARCHAR(20) NOT NULL DEFAULT 'quote',
    case_no INT UNSIGNED NOT NULL,
    mem_no INT UNSIGNED DEFAULT NULL,
    writer_name VARCHAR(50) NOT NULL,
    rating INT UNSIGNED NOT NULL DEFAULT 5,
    content TEXT NOT NULL,
    reg_date DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

function fail($msg) {
    echo json_encode(['ok' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('잘못된 요청입니다.');
}

$mode = isset($_POST['mode']) ? trim($_POST['mode']) : 'insert';

if ($mode === 'delete') {
    $comment_no = isset($_POST['comment_no']) ? (int)$_POST['comment_no'] : 0;
    if ($comment_no <= 0) fail('잘못된 요청입니다.');

    $cmt = sql_one_one('case_comments', '*', "and no=" . $comment_no);
    if (!$cmt) fail('존재하지 않는 댓글입니다.');

    $is_admin = !empty($_SESSION['s_adm_id']);
    $is_owner = !empty($_SESSION['s_mem_no']) && (int)$_SESSION['s_mem_no'] === (int)$cmt['mem_no'];

    if (!$is_admin && !$is_owner) {
        fail('댓글 삭제 권한이 없습니다.');
    }

    sql_del('case_comments', "and no=" . $comment_no);
    echo json_encode(['ok' => true, 'msg' => '댓글이 삭제되었습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 댓글 등록 (회원 제한)
if (empty($_SESSION['s_mem_id'])) {
    fail('댓글 및 별점 작성은 로그인한 회원만 가능합니다. 로그인 후 이용해 주세요.');
}

$case_no  = isset($_POST['case_no']) ? (int)$_POST['case_no'] : 0;
$tbl_type = (isset($_POST['tbl_type']) && $_POST['tbl_type'] === 'auction') ? 'auction' : 'quote';
$rating   = isset($_POST['rating']) ? max(1, min(5, (int)$_POST['rating'])) : 5;
$content  = trim(isset($_POST['content']) ? $_POST['content'] : '');

if ($case_no <= 0) {
    fail('시공사례 정보를 찾을 수 없습니다.');
}
if ($content === '') {
    fail('댓글 내용을 입력해 주세요.');
}

$mem_no = !empty($_SESSION['s_mem_no']) ? (int)$_SESSION['s_mem_no'] : 'NULL';
$writer_name = !empty($_SESSION['s_mem_name']) ? $_SESSION['s_mem_name'] : $_SESSION['s_mem_id'];

$fields = "tbl_type='" . mysqli_real_escape_string($conn, $tbl_type) . "', "
        . "case_no=" . (int)$case_no . ", "
        . "mem_no=" . $mem_no . ", "
        . "writer_name='" . mysqli_real_escape_string($conn, $writer_name) . "', "
        . "rating=" . (int)$rating . ", "
        . "content='" . mysqli_real_escape_string($conn, $content) . "', "
        . "reg_date=NOW()";

sql_in('case_comments', $fields);

echo json_encode([
    'ok'  => true,
    'msg' => '댓글과 별점이 정상적으로 등록되었습니다.'
], JSON_UNESCAPED_UNICODE);
