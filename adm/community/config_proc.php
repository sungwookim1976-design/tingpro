<?php
include_once __DIR__ . "/../../inc/dbconn.php";
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['s_adm_id'])) {
    echo json_encode(['result' => 'fail', 'msg' => '로그인이 필요합니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$mode = isset($_POST['mode']) ? trim($_POST['mode']) : '';

if ($mode === 'insert' || $mode === 'update') {
    $brd_id      = preg_replace('/[^a-z0-9_]/', '', isset($_POST['brd_id']) ? strtolower(trim($_POST['brd_id'])) : '');
    $brd_name    = mysqli_real_escape_string($conn, isset($_POST['brd_name']) ? trim($_POST['brd_name']) : '');
    $brd_type    = isset($_POST['brd_type']) ? (int)$_POST['brd_type'] : 1;
    $use_comment = isset($_POST['use_comment']) ? (int)$_POST['use_comment'] : 0;
    $use_write   = isset($_POST['use_write']) ? (int)$_POST['use_write'] : 0;
    $file_cnt    = isset($_POST['file_cnt']) ? (int)$_POST['file_cnt'] : 0;
    $file_size   = isset($_POST['file_size']) ? (int)$_POST['file_size'] : 5120;
    $list_cnt    = isset($_POST['list_cnt']) ? (int)$_POST['list_cnt'] : 15;
    $sort_order  = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 1;
    $use_yn      = isset($_POST['use_yn']) ? (int)$_POST['use_yn'] : 1;

    if (!$brd_id || !$brd_name) {
        echo json_encode(['result' => 'fail', 'msg' => '게시판ID와 이름은 필수입니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($mode === 'insert') {
        $dup = sql_cnt('board_config', "and brd_id='" . $brd_id . "'");
        if ($dup > 0) {
            echo json_encode(['result' => 'fail', 'msg' => '이미 사용 중인 게시판ID입니다.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $fields = "brd_id='$brd_id', brd_name='$brd_name', brd_type=$brd_type, use_comment=$use_comment, "
                . "use_write=$use_write, file_cnt=$file_cnt, file_size=$file_size, list_cnt=$list_cnt, "
                . "sort_order=$sort_order, use_yn=$use_yn";
        $ok = sql_in('board_config', $fields);
        $msg = '게시판이 추가되었습니다.';
    } else {
        $fields = "brd_name='$brd_name', brd_type=$brd_type, use_comment=$use_comment, "
                . "use_write=$use_write, file_cnt=$file_cnt, file_size=$file_size, list_cnt=$list_cnt, "
                . "sort_order=$sort_order, use_yn=$use_yn";
        $ok = sql_up('board_config', $fields, "and brd_id='$brd_id'");
        $msg = '수정되었습니다.';
    }

    if ($ok) {
        echo json_encode(['result' => 'ok', 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['result' => 'fail', 'msg' => mysqli_error($conn)], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($mode === 'delete') {
    $brd_id = preg_replace('/[^a-z0-9_]/', '', isset($_POST['brd_id']) ? trim($_POST['brd_id']) : '');
    if (!$brd_id) {
        echo json_encode(['result' => 'fail', 'msg' => '잘못된 요청입니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $post_cnt = sql_cnt('community_posts', "and board_type='" . mysqli_real_escape_string($conn, $brd_id) . "'");
    if ($post_cnt > 0) {
        echo json_encode(['result' => 'fail', 'msg' => "이 게시판에 등록된 글이 {$post_cnt}건 있어 삭제할 수 없습니다. 먼저 글을 정리해 주세요."], JSON_UNESCAPED_UNICODE);
        exit;
    }
    sql_del('board_config', "and brd_id='$brd_id'");
    echo json_encode(['result' => 'ok', 'msg' => '삭제되었습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['result' => 'fail', 'msg' => '허용되지 않은 요청입니다.'], JSON_UNESCAPED_UNICODE);
