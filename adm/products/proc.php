<?php
$adm_path_prefix = "../";
include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

// products 테이블의 category 컬럼 길이를 VARCHAR(100)으로 확장하여 Data truncated 에러 방지
@mysqli_query($conn, "ALTER TABLE `products` MODIFY COLUMN `category` VARCHAR(100) NOT NULL DEFAULT ''");

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

// 상품 이미지 업로드 처리 함수 ($file_field: thumb_file / thumb2_file / thumb3_file)
function handle_thumb_upload($existing_thumb = '', $file_field = 'thumb_file') {
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
            $new_name = "prod_" . time() . "_" . mt_rand(100, 999) . "." . $ext;
            $target_path = $target_dir . $new_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                return "imgs/" . $new_name;
            }
        }
    }
    return $existing_thumb;
}

if ($mode === 'insert') {
    $category   = trim(isset($_POST['category']) ? $_POST['category'] : '');
    if (!$category) $category = 'diy';
    $brand      = trim(isset($_POST['brand']) ? $_POST['brand'] : 'VULUX');
    $name       = trim((isset($_POST['name']) ? $_POST['name'] : ''));
    $spec       = trim((isset($_POST['spec']) ? $_POST['spec'] : ''));
    $price      = intval((isset($_POST['price']) ? $_POST['price'] : 0));
    $price_unit = trim((isset($_POST['price_unit']) ? $_POST['price_unit'] : '세트'));
    $sort_order = intval((isset($_POST['sort_order']) ? $_POST['sort_order'] : 0));
    $state      = isset($_POST['state']) && $_POST['state'] == '1' ? 1 : 0;

    $desc            = trim((isset($_POST['desc']) ? $_POST['desc'] : ''));
    $badge           = trim((isset($_POST['badge']) ? $_POST['badge'] : ''));
    $badge_color     = trim((isset($_POST['badge_color']) ? $_POST['badge_color'] : '#0077B6'));
    $uv_rate         = trim((isset($_POST['uv_rate']) ? $_POST['uv_rate'] : ''));
    $ir_rate         = trim((isset($_POST['ir_rate']) ? $_POST['ir_rate'] : ''));
    $warranty        = trim((isset($_POST['warranty']) ? $_POST['warranty'] : ''));
    $recommend_place = trim((isset($_POST['recommend_place']) ? $_POST['recommend_place'] : ''));
    $show_lineup     = isset($_POST['show_lineup']) && $_POST['show_lineup'] == '1' ? 1 : 0;

    $thumb_input  = trim((isset($_POST['thumb']) ? $_POST['thumb'] : ''));
    $thumb2_input = trim((isset($_POST['thumb2']) ? $_POST['thumb2'] : ''));
    $thumb3_input = trim((isset($_POST['thumb3']) ? $_POST['thumb3'] : ''));
    $thumb  = handle_thumb_upload($thumb_input, 'thumb_file');
    $thumb2 = handle_thumb_upload($thumb2_input, 'thumb2_file');
    $thumb3 = handle_thumb_upload($thumb3_input, 'thumb3_file');

    if (empty($name)) {
        respond_alert("상품명을 입력해 주세요.");
    }
    if ($price < 0) {
        respond_alert("가격은 0원 이상 입력해 주세요.");
    }

    $fields = "category='" . mysqli_real_escape_string($conn, $category) . "', "
            . "brand='" . mysqli_real_escape_string($conn, $brand) . "', "
            . "name='" . mysqli_real_escape_string($conn, $name) . "', "
            . "spec='" . mysqli_real_escape_string($conn, $spec) . "', "
            . "`desc`='" . mysqli_real_escape_string($conn, $desc) . "', "
            . "badge='" . mysqli_real_escape_string($conn, $badge) . "', "
            . "badge_color='" . mysqli_real_escape_string($conn, $badge_color) . "', "
            . "uv_rate='" . mysqli_real_escape_string($conn, $uv_rate) . "', "
            . "ir_rate='" . mysqli_real_escape_string($conn, $ir_rate) . "', "
            . "warranty='" . mysqli_real_escape_string($conn, $warranty) . "', "
            . "recommend_place='" . mysqli_real_escape_string($conn, $recommend_place) . "', "
            . "show_lineup=" . $show_lineup . ", "
            . "price=" . $price . ", "
            . "price_unit='" . mysqli_real_escape_string($conn, $price_unit) . "', "
            . "thumb='" . mysqli_real_escape_string($conn, $thumb) . "', "
            . "thumb2='" . mysqli_real_escape_string($conn, $thumb2) . "', "
            . "thumb3='" . mysqli_real_escape_string($conn, $thumb3) . "', "
            . "sort_order=" . $sort_order . ", "
            . "state=" . $state . ", "
            . "reg_date=NOW()";

    $ret = sql_in('products', $fields);
    if ($ret) {
        respond_alert("상품이 성공적으로 등록되었습니다.", "index.php");
    } else {
        respond_alert("상품 등록 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'update') {
    $no         = intval((isset($_POST['no']) ? $_POST['no'] : 0));
    $category   = trim(isset($_POST['category']) ? $_POST['category'] : '');
    if (!$category) $category = 'diy';
    $brand      = trim(isset($_POST['brand']) ? $_POST['brand'] : 'VULUX');
    $name       = trim((isset($_POST['name']) ? $_POST['name'] : ''));
    $spec       = trim((isset($_POST['spec']) ? $_POST['spec'] : ''));
    $price      = intval((isset($_POST['price']) ? $_POST['price'] : 0));
    $price_unit = trim((isset($_POST['price_unit']) ? $_POST['price_unit'] : '세트'));
    $sort_order = intval((isset($_POST['sort_order']) ? $_POST['sort_order'] : 0));
    $state      = isset($_POST['state']) && $_POST['state'] == '1' ? 1 : 0;

    $desc            = trim((isset($_POST['desc']) ? $_POST['desc'] : ''));
    $badge           = trim((isset($_POST['badge']) ? $_POST['badge'] : ''));
    $badge_color     = trim((isset($_POST['badge_color']) ? $_POST['badge_color'] : '#0077B6'));
    $uv_rate         = trim((isset($_POST['uv_rate']) ? $_POST['uv_rate'] : ''));
    $ir_rate         = trim((isset($_POST['ir_rate']) ? $_POST['ir_rate'] : ''));
    $warranty        = trim((isset($_POST['warranty']) ? $_POST['warranty'] : ''));
    $recommend_place = trim((isset($_POST['recommend_place']) ? $_POST['recommend_place'] : ''));
    $show_lineup     = isset($_POST['show_lineup']) && $_POST['show_lineup'] == '1' ? 1 : 0;

    if ($no <= 0) {
        respond_alert("올바르지 않은 접근입니다.");
    }
    if (empty($name)) {
        respond_alert("상품명을 입력해 주세요.");
    }

    // 기존 이미지 조회
    $old_prod = sql_one_one('products', 'thumb, thumb2, thumb3', "and no=" . $no);
    $thumb_input  = trim((isset($_POST['thumb']) ? $_POST['thumb'] : ''));
    $thumb2_input = trim((isset($_POST['thumb2']) ? $_POST['thumb2'] : ''));
    $thumb3_input = trim((isset($_POST['thumb3']) ? $_POST['thumb3'] : ''));
    $thumb  = handle_thumb_upload($thumb_input !== '' ? $thumb_input : ((isset($old_prod['thumb']) ? $old_prod['thumb'] : '')), 'thumb_file');
    $thumb2 = handle_thumb_upload($thumb2_input !== '' ? $thumb2_input : ((isset($old_prod['thumb2']) ? $old_prod['thumb2'] : '')), 'thumb2_file');
    $thumb3 = handle_thumb_upload($thumb3_input !== '' ? $thumb3_input : ((isset($old_prod['thumb3']) ? $old_prod['thumb3'] : '')), 'thumb3_file');

    $fields = "category='" . mysqli_real_escape_string($conn, $category) . "', "
            . "brand='" . mysqli_real_escape_string($conn, $brand) . "', "
            . "name='" . mysqli_real_escape_string($conn, $name) . "', "
            . "spec='" . mysqli_real_escape_string($conn, $spec) . "', "
            . "`desc`='" . mysqli_real_escape_string($conn, $desc) . "', "
            . "badge='" . mysqli_real_escape_string($conn, $badge) . "', "
            . "badge_color='" . mysqli_real_escape_string($conn, $badge_color) . "', "
            . "uv_rate='" . mysqli_real_escape_string($conn, $uv_rate) . "', "
            . "ir_rate='" . mysqli_real_escape_string($conn, $ir_rate) . "', "
            . "warranty='" . mysqli_real_escape_string($conn, $warranty) . "', "
            . "recommend_place='" . mysqli_real_escape_string($conn, $recommend_place) . "', "
            . "show_lineup=" . $show_lineup . ", "
            . "price=" . $price . ", "
            . "price_unit='" . mysqli_real_escape_string($conn, $price_unit) . "', "
            . "thumb='" . mysqli_real_escape_string($conn, $thumb) . "', "
            . "thumb2='" . mysqli_real_escape_string($conn, $thumb2) . "', "
            . "thumb3='" . mysqli_real_escape_string($conn, $thumb3) . "', "
            . "sort_order=" . $sort_order . ", "
            . "state=" . $state;

    $ret = sql_up('products', $fields, "and no=" . $no);
    if ($ret) {
        respond_alert("상품 정보가 수정되었습니다.", "index.php");
    } else {
        respond_alert("상품 정보 수정 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'delete') {
    $no = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    if ($no <= 0) {
        respond_alert("올바르지 않은 요청입니다.");
    }

    $ret = sql_del('products', "and no=" . $no);
    if ($ret) {
        respond_alert("상품이 삭제되었습니다.", "index.php");
    } else {
        respond_alert("상품 삭제 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'copy_bulk') {
    $chk_no = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("복사할 상품을 선택해 주세요.");
    }

    $copied_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $target = sql_one_one('products', '*', "and no=" . $no);
        if ($target) {
            $new_name = $target['name'] . " (복사본)";
            $fields = "category='" . mysqli_real_escape_string($conn, $target['category']) . "', "
                    . "brand='" . mysqli_real_escape_string($conn, isset($target['brand']) ? $target['brand'] : 'VULUX') . "', "
                    . "name='" . mysqli_real_escape_string($conn, $new_name) . "', "
                    . "spec='" . mysqli_real_escape_string($conn, $target['spec']) . "', "
                    . "`desc`='" . mysqli_real_escape_string($conn, $target['desc']) . "', "
                    . "badge='" . mysqli_real_escape_string($conn, $target['badge']) . "', "
                    . "badge_color='" . mysqli_real_escape_string($conn, $target['badge_color']) . "', "
                    . "uv_rate='" . mysqli_real_escape_string($conn, $target['uv_rate']) . "', "
                    . "ir_rate='" . mysqli_real_escape_string($conn, $target['ir_rate']) . "', "
                    . "warranty='" . mysqli_real_escape_string($conn, $target['warranty']) . "', "
                    . "recommend_place='" . mysqli_real_escape_string($conn, $target['recommend_place']) . "', "
                    . "show_lineup=" . intval($target['show_lineup']) . ", "
                    . "price=" . intval($target['price']) . ", "
                    . "price_unit='" . mysqli_real_escape_string($conn, $target['price_unit']) . "', "
                    . "thumb='" . mysqli_real_escape_string($conn, $target['thumb']) . "', "
                    . "thumb2='" . mysqli_real_escape_string($conn, isset($target['thumb2']) ? $target['thumb2'] : '') . "', "
                    . "thumb3='" . mysqli_real_escape_string($conn, isset($target['thumb3']) ? $target['thumb3'] : '') . "', "
                    . "sort_order=" . (intval($target['sort_order']) + 1) . ", "
                    . "state=" . intval($target['state']) . ", "
                    . "reg_date=NOW()";

            $ret = sql_in('products', $fields);
            if ($ret) $copied_cnt++;
        }
    }

    if ($copied_cnt > 0) {
        respond_alert("선택한 " . $copied_cnt . "개 상품이 복사되었습니다.", "index.php");
    } else {
        respond_alert("상품 복사 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'delete_bulk') {
    $chk_no = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("삭제할 상품을 선택해 주세요.");
    }

    $deleted_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $ret = sql_del('products', "and no=" . $no);
        if ($ret) $deleted_cnt++;
    }

    if ($deleted_cnt > 0) {
        respond_alert("선택한 " . $deleted_cnt . "개 상품이 삭제되었습니다.", "index.php");
    } else {
        respond_alert("상품 삭제 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'change_category_bulk') {
    $chk_no          = (isset($_POST['chk_no']) ? $_POST['chk_no'] : []);
    $target_category = trim(isset($_POST['target_category']) ? $_POST['target_category'] : '');

    if (empty($chk_no) || !is_array($chk_no)) {
        respond_alert("카테고리를 변경할 상품을 선택해 주세요.");
    }
    if ($target_category === '') {
        respond_alert("올바른 변경 카테고리를 선택해 주세요.");
    }

    $cat_name = $target_category;
    $cat_row = sql_one_one('ai_category', 'cat_name', "and (idx='" . mysqli_real_escape_string($conn, $target_category) . "' or cat_name='" . mysqli_real_escape_string($conn, $target_category) . "')");
    if ($cat_row && isset($cat_row['cat_name'])) {
        $cat_name = $cat_row['cat_name'];
    }

    $updated_cnt = 0;
    foreach ($chk_no as $no) {
        $no = intval($no);
        if ($no <= 0) continue;

        $ret = sql_up('products', "category='" . mysqli_real_escape_string($conn, $target_category) . "'", "and no=" . $no);
        if ($ret) $updated_cnt++;
    }

    if ($updated_cnt > 0) {
        respond_alert("선택한 " . $updated_cnt . "개 상품의 카테고리가 [" . $cat_name . "](으)로 변경되었습니다.", "index.php");
    } else {
        respond_alert("카테고리 변경 처리에 실패하였습니다.");
    }
}
elseif ($mode === 'change_state') {
    $no    = intval((isset($_POST['no']) ? $_POST['no'] : (isset($_GET['no']) ? $_GET['no'] : 0)));
    $state = intval((isset($_POST['state']) ? $_POST['state'] : (isset($_GET['state']) ? $_GET['state'] : 0)));

    if ($no > 0) {
        sql_up('products', "state=" . ($state == 1 ? 1 : 0), "and no=" . $no);
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
