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

if ($mode === 'delete') {
    $no = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    $board = trim((isset($_POST['board']) ? $_POST['board'] : (isset($_GET['board']) ? $_GET['board'] : '')));
    if ($no <= 0) {
        respond_alert("올바르지 않은 요청입니다.");
    }

    $ret = sql_del('community_posts', "and no=" . $no);
    if ($ret) {
        respond_alert("게시물이 삭제되었습니다.", "index.php" . ($board ? "?board=" . urlencode($board) : ""));
    } else {
        respond_alert("게시물 삭제 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'copy_bulk') {
    $chk_no = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    $board = trim((isset($_POST['board']) ? $_POST['board'] : (isset($_GET['board']) ? $_GET['board'] : '')));

    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("복사할 게시물을 선택해 주세요.");
    }

    $copied_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $target = sql_one_one('community_posts', '*', "and no=" . $no);
        if ($target) {
            $new_title = $target['title'] . " (복사본)";
            $fields = "board_type='" . mysqli_real_escape_string($conn, $target['board_type']) . "', "
                    . "badge='" . mysqli_real_escape_string($conn, $target['badge']) . "', "
                    . "title='" . mysqli_real_escape_string($conn, $new_title) . "', "
                    . "answer='" . mysqli_real_escape_string($conn, $target['answer']) . "', "
                    . "content='" . mysqli_real_escape_string($conn, $target['content']) . "', "
                    . "state=" . intval($target['state']) . ", "
                    . "reg_date=NOW()";

            $ret = sql_in('community_posts', $fields);
            if ($ret) $copied_cnt++;
        }
    }

    if ($copied_cnt > 0) {
        respond_alert("선택한 " . $copied_cnt . "개 게시물이 복사되었습니다.", "index.php" . ($board ? "?board=" . urlencode($board) : ""));
    } else {
        respond_alert("게시물 복사 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'delete_bulk') {
    $chk_no = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    $board = trim((isset($_POST['board']) ? $_POST['board'] : (isset($_GET['board']) ? $_GET['board'] : '')));

    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("삭제할 게시물을 선택해 주세요.");
    }

    $deleted_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $ret = sql_del('community_posts', "and no=" . $no);
        if ($ret) $deleted_cnt++;
    }

    if ($deleted_cnt > 0) {
        respond_alert("선택한 " . $deleted_cnt . "개 게시물이 삭제되었습니다.", "index.php" . ($board ? "?board=" . urlencode($board) : ""));
    } else {
        respond_alert("게시물 삭제 처리에 실패하였습니다.");
    }
}
else {
    header("Location: index.php");
    exit;
}
