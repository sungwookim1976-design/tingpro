<?php
include_once __DIR__ . "/../../inc/dbconn.php";
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['s_adm_id'])) {
    echo json_encode(array('result' => 'fail', 'msg' => '로그인이 필요합니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

$mode = isset($_REQUEST['mode']) ? trim($_REQUEST['mode']) : '';

// ── 자식 목록 조회 ──
if ($mode === 'get_children') {
    $parent_idx = isset($_GET['parent_idx']) ? (int)$_GET['parent_idx'] : 0;
    $que  = "SELECT * FROM ai_category WHERE parent_idx=$parent_idx ORDER BY sort_order ASC, idx ASC";
    $res  = mysqli_query($conn, $que);
    $list = array();
    if ($res) while ($r = mysqli_fetch_assoc($res)) $list[] = $r;
    echo json_encode(array('result' => 'ok', 'list' => $list));
    exit;
}

// ── 삭제 ──
if ($mode === 'delete') {
    $idx = isset($_POST['idx']) ? (int)$_POST['idx'] : 0;
    if (!$idx) { echo json_encode(array('result'=>'fail','msg'=>'잘못된 요청입니다.')); exit; }
    $que = "SELECT COUNT(*) FROM ai_category WHERE parent_idx=$idx";
    $chk = mysqli_fetch_row(mysqli_query($conn, $que));
    if ($chk[0] > 0) { echo json_encode(array('result'=>'fail','msg'=>'하위 카테고리가 있어 삭제할 수 없습니다. 하위 항목을 먼저 삭제해주세요.')); exit; }
    $que = "DELETE FROM ai_category WHERE idx=$idx";
    mysqli_query($conn, $que);
    echo json_encode(array('result'=>'ok','msg'=>'삭제되었습니다.'));
    exit;
}

// ── INSERT ──
if ($mode === 'insert') {
    $parent_idx   = isset($_POST['parent_idx'])   ? (int)$_POST['parent_idx']   : 0;
    $depth        = isset($_POST['depth'])        ? (int)$_POST['depth']        : 1;
    $sort_order   = isset($_POST['sort_order'])   ? (int)$_POST['sort_order']   : 1;
    $use_yn       = isset($_POST['use_yn'])       ? (int)$_POST['use_yn']       : 1;
    $cat_name_raw     = isset($_POST['cat_name'])     ? trim($_POST['cat_name'])     : '';
    $apply_target_raw = isset($_POST['apply_target']) ? trim($_POST['apply_target']) : '';

    if ($cat_name_raw === '') { echo json_encode(array('result'=>'fail','msg'=>'카테고리명을 입력해주세요.')); exit; }
    if ($depth < 1 || $depth > 3) { echo json_encode(array('result'=>'fail','msg'=>'잘못된 depth입니다.')); exit; }

    $cat_name     = mysqli_real_escape_string($conn, $cat_name_raw);
    $apply_target = ($depth == 1) ? mysqli_real_escape_string($conn, $apply_target_raw) : '';

    $que = "INSERT INTO ai_category SET
        parent_idx=$parent_idx, depth=$depth,
        cat_name='$cat_name', apply_target='$apply_target',
        sort_order=$sort_order, use_yn=$use_yn, reg_dt=NOW()";
    $res = mysqli_query($conn, $que);

    if ($res) {
        echo json_encode(array('result'=>'ok','msg'=>'추가되었습니다.','idx'=>mysqli_insert_id($conn)));
    } else {
        echo json_encode(array('result'=>'fail','msg'=>'오류: '.mysqli_error($conn)));
    }
    exit;
}

// ── UPDATE ──
if ($mode === 'update') {
    $idx        = isset($_POST['idx'])        ? (int)$_POST['idx']        : 0;
    $depth      = isset($_POST['depth'])      ? (int)$_POST['depth']      : 1;
    $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 1;
    $use_yn     = isset($_POST['use_yn'])     ? (int)$_POST['use_yn']     : 1;
    $cat_name_raw     = isset($_POST['cat_name'])     ? trim($_POST['cat_name'])     : '';
    $apply_target_raw = isset($_POST['apply_target']) ? trim($_POST['apply_target']) : '';

    if (!$idx)            { echo json_encode(array('result'=>'fail','msg'=>'잘못된 요청입니다.')); exit; }
    if ($cat_name_raw==='') { echo json_encode(array('result'=>'fail','msg'=>'카테고리명을 입력해주세요.')); exit; }

    $cat_name     = mysqli_real_escape_string($conn, $cat_name_raw);
    $apply_target = ($depth == 1) ? mysqli_real_escape_string($conn, $apply_target_raw) : '';

    $que = "UPDATE ai_category SET
        cat_name='$cat_name', apply_target='$apply_target',
        sort_order=$sort_order, use_yn=$use_yn
        WHERE idx=$idx";
    $res = mysqli_query($conn, $que);

    if ($res) {
        echo json_encode(array('result'=>'ok','msg'=>'수정되었습니다.'));
    } else {
        echo json_encode(array('result'=>'fail','msg'=>'오류: '.mysqli_error($conn)));
    }
    exit;
}

echo json_encode(array('result'=>'fail','msg'=>'허용되지 않은 요청입니다.'));
