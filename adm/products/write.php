<?php
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

@mysqli_query($conn, "ALTER TABLE `products` MODIFY COLUMN `category` VARCHAR(100) NOT NULL DEFAULT ''");
@mysqli_query($conn, "ALTER TABLE `products` ADD COLUMN `brand` VARCHAR(100) NOT NULL DEFAULT 'VULUX'");

// tb_code 테이블 자동 생성 및 브랜드 그룹 데이터 시딩 (설정 >> 코드관리 >> 브랜드)
@mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS `tb_code` (
      `idx`        INT          NOT NULL AUTO_INCREMENT,
      `group_sort` INT          NOT NULL DEFAULT 1,
      `group_name` VARCHAR(100) NOT NULL,
      `code_sort`  INT          NOT NULL DEFAULT 1,
      `code_name`  VARCHAR(100) NOT NULL,
      `code_value` VARCHAR(100) NOT NULL,
      `reg_date`   DATETIME     NOT NULL DEFAULT NOW(),
      PRIMARY KEY (`idx`),
      KEY `idx_group` (`group_sort`, `group_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$chk_brand_cnt = sql_cnt('tb_code', "and group_name='브랜드'");
if ($chk_brand_cnt == 0) {
    mysqli_query($conn, "INSERT INTO tb_code (group_sort, group_name, code_sort, code_name, code_value, reg_date) VALUES
    (1, '브랜드', 1, 'VULUX (벌럭스)', 'VULUX', NOW()),
    (1, '브랜드', 2, 'NEXFIL (넥스필)', 'NEXFIL', NOW()),
    (1, '브랜드', 3, '3M (쓰리엠)', '3M', NOW()),
    (1, '브랜드', 4, 'LLumar (루마)', 'LLumar', NOW()),
    (1, '브랜드', 5, 'Solar Gard (솔라가드)', 'Solar Gard', NOW()),
    (1, '브랜드', 6, 'Rayno (레이노)', 'Rayno', NOW()),
    (1, '브랜드', 7, 'KUBE (큐브)', 'KUBE', NOW()),
    (1, '브랜드', 8, '기타 브랜드', 'ETC', NOW())");
}

$db_brand_codes = sql_one('tb_code', '*', "and group_name='브랜드' order by code_sort asc, idx asc");

$no = isset($_GET['no']) ? (int)$_GET['no'] : 0;
$row = null;
if ($no > 0) {
    $row = sql_one_one('products', '*', "and no=" . $no);
    if (!$row) {
        echo "<script>alert('존재하지 않는 상품입니다.'); location.href='index.php';</script>";
        exit;
    }
}

// ai_category 중분류(depth=2) 목록 로딩 (카테고리 관리 >> 상품분류 중분류내용)
$write_categories = [];
$res_wcat = mysqli_query($conn, "SELECT c2.*, c1.cat_name as parent_name FROM ai_category c2 LEFT JOIN ai_category c1 ON c2.parent_idx = c1.idx WHERE c2.depth=2 AND c2.use_yn=1 ORDER BY c2.sort_order ASC, c2.idx ASC");
if ($res_wcat && mysqli_num_rows($res_wcat) > 0) {
    while ($wcr = mysqli_fetch_assoc($res_wcat)) {
        $write_categories[] = $wcr;
    }
} else {
    $res_all_cat = mysqli_query($conn, "SELECT * FROM ai_category WHERE use_yn=1 ORDER BY depth ASC, sort_order ASC, idx ASC");
    if ($res_all_cat && mysqli_num_rows($res_all_cat) > 0) {
        while ($wcr = mysqli_fetch_assoc($res_all_cat)) {
            $write_categories[] = $wcr;
        }
    } else {
        $write_categories = [
            ['idx' => 'apt', 'cat_name' => '아파트/주택베란다'],
            ['idx' => 'building', 'cat_name' => '빌딩'],
            ['idx' => 'diy', 'cat_name' => 'DIY자가설치']
        ];
    }
}

$page_title = $no ? "상품 정보 수정" : "상품 신규 등록";
$active_page = "products";
$err_msg = isset($_GET['err']) ? $_GET['err'] : '';

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div style="margin-bottom:20px;">
    <a href="index.php" class="adm-btn adm-btn-outline" style="padding:6px 14px; font-size:0.85rem;"><i class="fa-solid fa-arrow-left"></i> 목록으로 돌아가기</a>
</div>

<?php if ($err_msg): ?>
    <div style="background:#FEF2F2; border:1px solid #FECACA; color:#B91C1C; padding:12px 16px; border-radius:8px; font-size:0.9rem; margin-bottom:18px;">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($err_msg); ?>
    </div>
<?php endif; ?>

<div class="adm-form-box" style="max-width:100%;">
    <h3 style="margin-bottom:20px; font-size:1.1rem; font-weight:800; border-bottom:1px solid var(--adm-border); padding-bottom:12px;">
        <i class="fa-solid <?php echo $no ? 'fa-box-archive' : 'fa-plus'; ?>" style="color:var(--adm-primary);"></i> <?php echo $page_title; ?>
    </h3>

    <form method="post" action="proc.php" enctype="multipart/form-data" onsubmit="return validateProductForm(this);">
        <input type="hidden" name="mode" value="<?php echo $no ? 'update' : 'insert'; ?>">
        <input type="hidden" name="no" value="<?php echo $no; ?>">

        <div style="background:#F8FAFC; padding:18px; border-radius:12px; border:1px solid var(--adm-border); margin-bottom:20px;">
            <h4 style="font-size:0.92rem; font-weight:800; color:var(--adm-primary); margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-code-branch"></i> 코드 관리 (상품 구분 &amp; 브랜드 지정)
            </h4>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                <div class="adm-form-row" style="margin-bottom:0;">
                    <label for="category">상품 구분 <span style="color:#EF4444;">*</span></label>
                    <select id="category" name="category" required>
                        <?php foreach ($write_categories as $wc): 
                            $val = (string)$wc['cat_name'];
                            $idx_val = (string)$wc['idx'];
                            $is_sel = ($row && ($row['category'] === $val || $row['category'] === $idx_val || (isset($wc['cat_name']) && $row['category'] === $wc['cat_name'])));
                            $label_text = !empty($wc['parent_name']) ? '[' . $wc['parent_name'] . '] ' . $wc['cat_name'] : $wc['cat_name'];
                        ?>
                            <option value="<?php echo htmlspecialchars($val); ?>" <?php echo $is_sel ? 'selected' : ''; ?>><?php echo htmlspecialchars($label_text); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="adm-form-row" style="margin-bottom:0;">
                    <label for="brand">브랜드 선택 <span style="color:#EF4444;">*</span></label>
                    <select id="brand" name="brand">
                        <?php
                        $curr_brand = ($row && isset($row['brand']) && $row['brand'] !== '') ? $row['brand'] : 'VULUX (벌럭스)';
                        if (!empty($db_brand_codes)):
                            foreach ($db_brand_codes as $bc):
                                $b_name = $bc['code_name'];
                                $b_val  = $bc['code_value'];
                                $is_sel = ($curr_brand === $b_name || $curr_brand === $b_val);
                        ?>
                            <option value="<?php echo htmlspecialchars($b_name); ?>" <?php echo $is_sel ? 'selected' : ''; ?>><?php echo htmlspecialchars($b_name); ?></option>
                        <?php
                            endforeach;
                        else:
                        ?>
                            <option value="VULUX (벌럭스)" selected>VULUX (벌럭스)</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="adm-form-row" style="margin-bottom:0;">
                    <label for="state">판매 상태</label>
                    <select id="state" name="state">
                        <option value="1" <?php echo (!$row || $row['state'] == 1) ? 'selected' : ''; ?>>판매중 (공개)</option>
                        <option value="0" <?php echo ($row && $row['state'] == 0) ? 'selected' : ''; ?>>숨김 (비공개)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="adm-form-row">
            <label for="name">상품명 <span style="color:#EF4444;">*</span></label>
            <input type="text" id="name" name="name" required value="<?php echo $row ? htmlspecialchars($row['name']) : ''; ?>" placeholder="예: VULUX X-Series DIY 키트">
        </div>

        <div class="adm-form-row">
            <label for="spec">스펙 설명</label>
            <input type="text" id="spec" name="spec" value="<?php echo $row ? htmlspecialchars($row['spec']) : ''; ?>" placeholder="예: 자외선 99.9% · 열차단 85% · 2m×1.5m">
        </div>

        <!-- 서비스페이지 필름 라인업(#sputter) 노출용 필드 -->
        <div class="adm-form-row" style="background:#F8FAFC; padding:16px; border-radius:10px; border:1px solid var(--adm-border);">
            <label style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
                <input type="checkbox" name="show_lineup" value="1" style="width:16px; height:16px;" <?php echo ($row && $row['show_lineup'] == 1) ? 'checked' : ''; ?>>
                <span><i class="fa-solid fa-star" style="color:var(--adm-primary);"></i> 서비스페이지 "필름 라인업" 카드로 노출 (/services#sputter)</span>
            </label>

            <div class="adm-form-row">
                <label for="desc">카드 설명문</label>
                <input type="text" id="desc" name="desc" value="<?php echo $row ? htmlspecialchars($row['desc']) : ''; ?>" placeholder="예: 아파트 베란다 및 가정용 거실 추천 1위...">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="adm-form-row">
                    <label for="badge">배지 텍스트</label>
                    <input type="text" id="badge" name="badge" value="<?php echo $row ? htmlspecialchars($row['badge']) : ''; ?>" placeholder="예: BEST HIT">
                </div>
                <div class="adm-form-row">
                    <label for="badge_color">배지 색상</label>
                    <input type="color" id="badge_color" name="badge_color" value="<?php echo $row && $row['badge_color'] ? htmlspecialchars($row['badge_color']) : '#0077B6'; ?>" style="height:42px; padding:4px;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="adm-form-row">
                    <label for="uv_rate">자외선 차단율</label>
                    <input type="text" id="uv_rate" name="uv_rate" value="<?php echo $row ? htmlspecialchars($row['uv_rate']) : ''; ?>" placeholder="99.9%">
                </div>
                <div class="adm-form-row">
                    <label for="ir_rate">열차단율(IR)</label>
                    <input type="text" id="ir_rate" name="ir_rate" value="<?php echo $row ? htmlspecialchars($row['ir_rate']) : ''; ?>" placeholder="85%">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="adm-form-row">
                    <label for="warranty">보증기간</label>
                    <input type="text" id="warranty" name="warranty" value="<?php echo $row ? htmlspecialchars($row['warranty']) : ''; ?>" placeholder="10년">
                </div>
                <div class="adm-form-row">
                    <label for="recommend_place">권장 장소</label>
                    <input type="text" id="recommend_place" name="recommend_place" value="<?php echo $row ? htmlspecialchars($row['recommend_place']) : ''; ?>" placeholder="아파트 베란다">
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="adm-form-row">
                <label for="price">가격 (원) <span style="color:#EF4444;">*</span></label>
                <input type="number" id="price" name="price" required min="0" step="100" value="<?php echo $row ? (int)$row['price'] : ''; ?>" placeholder="89000">
            </div>
            <div class="adm-form-row">
                <label for="price_unit">가격 단위</label>
                <input type="text" id="price_unit" name="price_unit" value="<?php echo $row ? htmlspecialchars($row['price_unit']) : '세트'; ?>" placeholder="세트 / 평 / m²">
            </div>
        </div>

        <!-- 상품 이미지 파일 업로드 (최대 3장) -->
        <div class="adm-form-row" style="background:#F8FAFC; padding:16px; border-radius:10px; border:1px solid var(--adm-border);">
            <label><i class="fa-solid fa-images" style="color:var(--adm-primary);"></i> 상품 이미지 (최대 3장, 1번째가 대표 썸네일)</label>

            <?php
            $img_slots = [
                ['field' => 'thumb',  'file' => 'thumb_file',  'label' => '대표 이미지 (필수)'],
                ['field' => 'thumb2', 'file' => 'thumb2_file', 'label' => '추가 이미지 2'],
                ['field' => 'thumb3', 'file' => 'thumb3_file', 'label' => '추가 이미지 3'],
            ];
            ?>
            <?php foreach ($img_slots as $i => $slot):
                $val = $row && !empty($row[$slot['field']]) ? $row[$slot['field']] : '';
            ?>
            <div style="display:flex; gap:16px; align-items:flex-start; margin-top:<?php echo $i === 0 ? '8' : '16'; ?>px; <?php echo $i > 0 ? 'border-top:1px dashed var(--adm-border); padding-top:16px;' : ''; ?>">
                <div style="width:84px; height:84px; border:1px dashed var(--adm-border); border-radius:8px; background:#fff; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                    <img id="<?php echo $slot['field']; ?>_preview" src="<?php echo $val ? $site_path_prefix . htmlspecialchars($val) : ''; ?>" alt="미리보기" style="width:100%; height:100%; object-fit:cover; <?php echo $val ? '' : 'display:none;'; ?>" onerror="this.style.display='none';">
                    <span id="<?php echo $slot['field']; ?>_no_img_label" style="font-size:0.75rem; color:var(--adm-muted); <?php echo $val ? 'display:none;' : ''; ?>">이미지 없음</span>
                </div>

                <div style="flex:1;">
                    <div style="font-size:0.82rem; font-weight:700; color:var(--adm-text); margin-bottom:6px;"><?php echo $slot['label']; ?></div>
                    <div style="margin-bottom:8px;">
                        <label style="font-size:0.8rem; font-weight:600; color:var(--adm-muted); margin-bottom:4px;">컴퓨터 파일 업로드:</label>
                        <input type="file" id="<?php echo $slot['file']; ?>" name="<?php echo $slot['file']; ?>" accept="image/*" onchange="previewThumbFile(this, '<?php echo $slot['field']; ?>')" style="font-size:0.85rem; padding:6px;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:var(--adm-muted); margin-bottom:4px;">또는 이미지 파일 경로 입력:</label>
                        <input type="text" id="<?php echo $slot['field']; ?>" name="<?php echo $slot['field']; ?>" value="<?php echo htmlspecialchars($val); ?>" placeholder="imgs/vulux_xseries_thumb.svg" oninput="updateThumbPreview(this.value, '<?php echo $slot['field']; ?>')">
                    </div>
                    <?php if ($i === 0): ?>
                    <div style="font-size:0.75rem; color:var(--adm-muted); margin-top:6px;">
                        * 추천 프리셋:
                        <a href="javascript:setPresetThumb('imgs/vulux_xseries_thumb.svg')" style="color:var(--adm-primary); font-weight:600; margin-right:8px;">VULUX X-Series</a>
                        <a href="javascript:setPresetThumb('imgs/rec_sputter_thumb.svg')" style="color:var(--adm-primary); font-weight:600; margin-right:8px;">Sputter 99</a>
                        <a href="javascript:setPresetThumb('imgs/rec_ceramic_thumb.svg')" style="color:var(--adm-primary); font-weight:600; margin-right:8px;">Nano Ceramic</a>
                        <a href="javascript:setPresetThumb('imgs/bluelight_thumb.svg')" style="color:var(--adm-primary); font-weight:600;">Privacy Shield</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="adm-form-row">
            <label for="sort_order">정렬 순서 <span style="font-size:0.78rem; color:var(--adm-muted); font-weight:normal;">(숫자가 작을수록 상단 노출)</span></label>
            <input type="number" id="sort_order" name="sort_order" value="<?php echo $row ? (int)$row['sort_order'] : 0; ?>" style="width:180px;">
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:28px; border-top:1px solid var(--adm-border); padding-top:20px;">
            <div style="display:flex; gap:10px;">
                <button type="submit" class="adm-btn" style="padding:11px 24px; font-size:0.92rem;"><i class="fa-solid fa-floppy-disk"></i> <?php echo $no ? '수정사항 저장' : '상품 등록 완료'; ?></button>
                <a href="index.php" class="adm-btn adm-btn-outline" style="padding:11px 20px; font-size:0.92rem;">취소</a>
            </div>
            <?php if ($no): ?>
                <a href="proc.php?mode=delete&no=<?php echo $no; ?>" onclick="return confirm('이 상품을 삭제하시겠습니까?');" class="adm-btn" style="padding:11px 18px; font-size:0.92rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 상품 삭제</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
const sitePrefix = '<?php echo $site_path_prefix; ?>';

function previewThumbFile(input, field) {
    field = field || 'thumb';
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(field + '_preview');
            const label = document.getElementById(field + '_no_img_label');
            img.src = e.target.result;
            img.style.display = 'block';
            label.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function updateThumbPreview(val, field) {
    field = field || 'thumb';
    const img = document.getElementById(field + '_preview');
    const label = document.getElementById(field + '_no_img_label');
    if (val.trim()) {
        img.src = sitePrefix + val.trim();
        img.style.display = 'block';
        label.style.display = 'none';
    } else {
        img.style.display = 'none';
        label.style.display = 'block';
    }
}

function setPresetThumb(path) {
    document.getElementById('thumb').value = path;
    document.getElementById('thumb_file').value = '';
    updateThumbPreview(path, 'thumb');
}

function validateProductForm(f) {
    if (!f.name.value.trim()) {
        alert('상품명을 입력해 주세요.');
        f.name.focus();
        return false;
    }
    if (f.price.value === '' || parseInt(f.price.value) < 0) {
        alert('올바른 가격을 입력해 주세요.');
        f.price.focus();
        return false;
    }
    return true;
}
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
