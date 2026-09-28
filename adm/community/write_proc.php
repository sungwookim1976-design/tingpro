<?php
include_once __DIR__ . "/../../inc/dbconn.php";

if (empty($_SESSION['s_adm_id'])) {
    header("Location: ../login.php");
    exit;
}

$no          = (int)((isset($_POST['no']) ? $_POST['no'] : 0));
$board_type  = trim((isset($_POST['board_type']) ? $_POST['board_type'] : ''));
$badge       = trim((isset($_POST['badge']) ? $_POST['badge'] : ''));
$title       = trim((isset($_POST['title']) ? $_POST['title'] : ''));
$answer      = trim((isset($_POST['answer']) ? $_POST['answer'] : ''));
$youtube_url = trim((isset($_POST['youtube_url']) ? $_POST['youtube_url'] : ''));
$content     = (isset($_POST['content']) ? $_POST['content'] : '');
$state       = isset($_POST['state']) && $_POST['state'] == '1' ? 1 : 0;

// DB youtube_url 컬럼 미존재시 자동 추가
$chk_col = @mysqli_query($conn, "SHOW COLUMNS FROM community_posts LIKE 'youtube_url'");
if ($chk_col && mysqli_num_rows($chk_col) == 0) {
    @mysqli_query($conn, "ALTER TABLE community_posts ADD COLUMN youtube_url VARCHAR(255) NULL");
}

// 유효한 게시판인지 확인 + 업로드 설정 조회
$cfg = sql_one_one('board_config', '*', "and brd_id='" . mysqli_real_escape_string($conn, $board_type) . "'");
if (!$cfg || $title === '') {
    $qs = "err=" . urlencode('게시판과 제목을 확인해 주세요.');
    header("Location: write.php" . ($no ? "?no=$no&$qs" : "?$qs"));
    exit;
}

// ── 업로드 디렉토리: D:\wwwroot\corea26\apt\uploads\{brd_id}\ ──
function community_upload_dir($brd_id) {
    $base = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $brd_id . DIRECTORY_SEPARATOR;
    if (!is_dir($base)) mkdir($base, 0755, true);
    return $base;
}

function community_process_files($conn, $post_no, $brd_id, $cfg) {
    $file_cnt = (int)$cfg['file_cnt'];
    $max_size = (int)$cfg['file_size'] * 1024;
    $allowed  = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip', 'xlsx', 'docx', 'hwp');
    $img_exts = array('jpg', 'jpeg', 'png', 'gif');
    $upload_dir = community_upload_dir($brd_id);

    for ($i = 0; $i < $file_cnt; $i++) {
        $key = 'file_' . $i;
        if (empty($_FILES[$key]['name'])) continue;
        if ($_FILES[$key]['error'] !== UPLOAD_ERR_OK) continue;
        if ($_FILES[$key]['size'] > $max_size) continue;

        $ori_name = $_FILES[$key]['name'];
        $ext      = strtolower(pathinfo($ori_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) continue;

        $save_name = time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        if (move_uploaded_file($_FILES[$key]['tmp_name'], $upload_dir . $save_name)) {
            $ori_esc  = mysqli_real_escape_string($conn, $ori_name);
            $save_esc = mysqli_real_escape_string($conn, $save_name);
            $fsize    = (int)$_FILES[$key]['size'];
            $img_yn   = in_array($ext, $img_exts) ? 1 : 0;
            $ext_esc  = mysqli_real_escape_string($conn, $ext);
            sql_in('community_post_file', "post_no=$post_no, ori_name='$ori_esc', save_name='$save_esc',
                file_size=$fsize, file_ext='$ext_esc', img_yn=$img_yn, reg_dt=NOW()");
        }
    }
}

$fields = "board_type='" . mysqli_real_escape_string($conn, $board_type) . "', "
        . "badge='" . mysqli_real_escape_string($conn, $badge) . "', "
        . "title='" . mysqli_real_escape_string($conn, $title) . "', "
        . "answer='" . mysqli_real_escape_string($conn, $answer) . "', "
        . "youtube_url='" . mysqli_real_escape_string($conn, $youtube_url) . "', "
        . "content='" . mysqli_real_escape_string($conn, $content) . "', "
        . "state=" . $state;

if ($no) {
    sql_up('community_posts', $fields, "and no=" . $no);
    $post_no = $no;

    // 삭제 체크된 기존 첨부파일 처리
    if (!empty($_POST['del_file'])) {
        $upload_dir = community_upload_dir($board_type);
        foreach ($_POST['del_file'] as $fid) {
            $fid = (int)$fid;
            if (!$fid) continue;
            $fr = sql_one_one('community_post_file', '*', "and idx=$fid and post_no=$post_no");
            if ($fr && file_exists($upload_dir . $fr['save_name'])) @unlink($upload_dir . $fr['save_name']);
            sql_del('community_post_file', "and idx=$fid and post_no=$post_no");
        }
    }
} else {
    sql_in('community_posts', $fields);
    $post_no = mysqli_insert_id($conn);
}

if ((int)$cfg['file_cnt'] > 0) community_process_files($conn, $post_no, $board_type, $cfg);

header("Location: index.php?board=" . urlencode($board_type));
exit;
