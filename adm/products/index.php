<?php
$page_title = "상품관리";
$active_page = "products";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

@mysqli_query($conn, "ALTER TABLE `products` MODIFY COLUMN `category` VARCHAR(100) NOT NULL DEFAULT ''");
@mysqli_query($conn, "ALTER TABLE `products` ADD COLUMN `brand` VARCHAR(100) NOT NULL DEFAULT 'VULUX'");

// ai_category 테이블 자동 생성 및 기본 대분류 시딩
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `ai_category` (
  `idx`          int(11)      NOT NULL AUTO_INCREMENT,
  `parent_idx`   int(11)      NOT NULL DEFAULT '0',
  `depth`        tinyint(1)   NOT NULL DEFAULT '1',
  `cat_name`     varchar(100) NOT NULL,
  `apply_target` varchar(20)  NOT NULL DEFAULT '',
  `sort_order`   tinyint(3)   NOT NULL DEFAULT '1',
  `use_yn`       tinyint(1)   NOT NULL DEFAULT '1',
  `reg_dt`       datetime     NOT NULL,
  PRIMARY KEY (`idx`),
  KEY `idx_parent` (`parent_idx`),
  KEY `idx_depth`  (`depth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$chk_cat_cnt = sql_cnt('ai_category', 'and depth=1');
if ($chk_cat_cnt == 0) {
    mysqli_query($conn, "INSERT INTO ai_category (parent_idx, depth, cat_name, apply_target, sort_order, use_yn, reg_dt) VALUES
    (0, 1, '아파트 시공용 필름', 'content', 1, 1, NOW()),
    (0, 1, '건물/빌딩 시공용 필름', 'content', 2, 1, NOW()),
    (0, 1, 'DIY 자가설치 키트', 'content', 3, 1, NOW()),
    (0, 1, '자동차/차량 썬팅', 'content', 4, 1, NOW())");
}

$chk_mid_cnt = sql_cnt('ai_category', 'and depth=2');
if ($chk_mid_cnt == 0) {
    $p1 = sql_one_one('ai_category', 'idx', "and depth=1 and (cat_name like '%아파트%' or cat_name like '%필름%')");
    $p1_idx = $p1 ? $p1['idx'] : 1;
    mysqli_query($conn, "INSERT INTO ai_category (parent_idx, depth, cat_name, apply_target, sort_order, use_yn, reg_dt) VALUES
    ({$p1_idx}, 2, '아파트/주택베란다', 'content', 1, 1, NOW()),
    ({$p1_idx}, 2, '오피스텔', 'content', 2, 1, NOW()),
    ({$p1_idx}, 2, '단독주택', 'content', 3, 1, NOW())");

    $p2 = sql_one_one('ai_category', 'idx', "and depth=1 and (cat_name like '%건물%' or cat_name like '%빌딩%')");
    $p2_idx = $p2 ? $p2['idx'] : 2;
    mysqli_query($conn, "INSERT INTO ai_category (parent_idx, depth, cat_name, apply_target, sort_order, use_yn, reg_dt) VALUES
    ({$p2_idx}, 2, '빌딩', 'content', 1, 1, NOW()),
    ({$p2_idx}, 2, '사무실/관공서', 'content', 2, 1, NOW())");

    $p3 = sql_one_one('ai_category', 'idx', "and depth=1 and cat_name like '%DIY%'");
    $p3_idx = $p3 ? $p3['idx'] : 3;
    mysqli_query($conn, "INSERT INTO ai_category (parent_idx, depth, cat_name, apply_target, sort_order, use_yn, reg_dt) VALUES
    ({$p3_idx}, 2, 'DIY자가설치', 'content', 1, 1, NOW()),
    ({$p3_idx}, 2, '자가시공 도구', 'content', 2, 1, NOW())");
}

// ai_category 중분류(depth=2) / 대분류 카테고리 로딩 (카테고리관리 >> 상품분류 중분류내용)
$large_categories = [];
$cat_label = [
    'apt'                  => '아파트/주택베란다',
    'building'             => '빌딩',
    'diy'                  => 'DIY자가설치',
    'auto'                 => '자동차',
    'officetel'            => '오피스텔',
    'house'                => '단독주택',
    '아파트 시공용 필름'   => '아파트/주택베란다',
    '건물/빌딩 시공용 필름' => '빌딩',
    'DIY 자가설치 키트'    => 'DIY자가설치',
    '자동차/차량 썬팅'     => '자동차'
];

$res_mid_cat = mysqli_query($conn, "SELECT c2.*, c1.cat_name as parent_name FROM ai_category c2 LEFT JOIN ai_category c1 ON c2.parent_idx = c1.idx WHERE c2.depth=2 AND c2.use_yn=1 ORDER BY c2.sort_order ASC, c2.idx ASC");
if ($res_mid_cat && mysqli_num_rows($res_mid_cat) > 0) {
    while ($cr = mysqli_fetch_assoc($res_mid_cat)) {
        $large_categories[] = $cr;
        $cat_label[$cr['idx']] = $cr['cat_name'];
        $cat_label[$cr['cat_name']] = $cr['cat_name'];
    }
} else {
    $res_all_cat = mysqli_query($conn, "SELECT * FROM ai_category WHERE use_yn=1 ORDER BY depth ASC, sort_order ASC, idx ASC");
    if ($res_all_cat) {
        while ($cr = mysqli_fetch_assoc($res_all_cat)) {
            $large_categories[] = $cr;
            $cat_label[$cr['idx']] = $cr['cat_name'];
            $cat_label[$cr['cat_name']] = $cr['cat_name'];
        }
    }
}

$cat_filter   = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$state_filter = isset($_GET['state']) && $_GET['state'] !== '' ? intval($_GET['state']) : '';
$sk           = trim((isset($_GET['sk']) ? $_GET['sk'] : ''));

$where = " where 1=1";
if ($cat_filter !== '') {
    $cat_esc = mysqli_real_escape_string($conn, $cat_filter);
    $cat_item = sql_one_one('ai_category', 'idx, cat_name', "and depth=2 and (idx='" . $cat_esc . "' or cat_name='" . $cat_esc . "')");
    $target_name = ($cat_item && isset($cat_item['cat_name'])) ? $cat_item['cat_name'] : $cat_filter;
    $target_idx = ($cat_item && isset($cat_item['idx'])) ? (int)$cat_item['idx'] : 0;
    
    $target_esc = mysqli_real_escape_string($conn, $target_name);
    
    $cat_conds = array();
    $cat_conds[] = "category='$cat_esc'";
    if ($target_name !== '') {
        $cat_conds[] = "category='$target_esc'";
        $cat_conds[] = "category LIKE '%" . $target_esc . "%'";
    }
    if ($target_idx > 0) {
        $cat_conds[] = "category='" . $target_idx . "'";
    }

    if (mb_strpos($target_name, '아파트') !== false || $cat_filter === 'apt') {
        $cat_conds[] = "category='apt'";
        $cat_conds[] = "category='아파트'";
        $cat_conds[] = "category LIKE '%아파트%'";
    }
    if (mb_strpos($target_name, '빌딩') !== false || mb_strpos($target_name, '건물') !== false || $cat_filter === 'building') {
        $cat_conds[] = "category='building'";
        $cat_conds[] = "category='빌딩'";
        $cat_conds[] = "category LIKE '%빌딩%'";
        $cat_conds[] = "category LIKE '%건물%'";
    }
    if (mb_strpos($target_name, '자동차') !== false || mb_strpos($target_name, '차량') !== false || $cat_filter === 'car') {
        $cat_conds[] = "category='car'";
        $cat_conds[] = "category='자동차'";
        $cat_conds[] = "category LIKE '%자동차%'";
        $cat_conds[] = "category LIKE '%차량%'";
    }
    if (mb_strpos($target_name, 'DIY') !== false || mb_strpos($target_name, '자가') !== false || $cat_filter === 'diy') {
        $cat_conds[] = "category='diy'";
        $cat_conds[] = "category='DIY자가설치'";
        $cat_conds[] = "category LIKE '%DIY%'";
    }

    $where .= " and (" . implode(" OR ", array_unique($cat_conds)) . ")";
}
if ($state_filter !== '') {
    $where .= " and state=" . intval($state_filter);
}
if ($sk !== '') {
    $sk_esc = mysqli_real_escape_string($conn, $sk);
    $where .= " and (name like '%$sk_esc%' or spec like '%$sk_esc%')";
}

// 통계 수치
$total_cnt    = sql_cnt('products', '');
$apt_cnt      = sql_cnt('products', "and (category='apt' or category='1' or category='아파트 시공용 필름')");
$building_cnt = sql_cnt('products', "and (category='building' or category='2' or category='건물/빌딩 시공용 필름')");
$diy_cnt      = sql_cnt('products', "and (category='diy' or category='3' or category='DIY 자가설치 키트')");

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$filtered_total = sql_cnt('products', str_replace(' where 1=1', '', $where));
$total_pages = max(1, (int)ceil($filtered_total / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$products = sql_one('products', '*', str_replace(' where 1=1', '', $where) . " order by category asc, sort_order asc, no asc limit $offset, $limit");

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div class="adm-stat-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-box-open"></i> 전체 상품</div>
        <div class="val"><?php echo number_format($total_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">개</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-building-user" style="color:#0077B6;"></i> 아파트용 필름</div>
        <div class="val" style="color:#0077B6;"><?php echo number_format($apt_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">개</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-city" style="color:#0284C7;"></i> 건물/빌딩용 필름</div>
        <div class="val" style="color:#0284C7;"><?php echo number_format($building_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">개</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-wrench" style="color:#D97706;"></i> DIY 자가설치 키트</div>
        <div class="val" style="color:#D97706;"><?php echo number_format($diy_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">개</span></div>
    </div>
</div>

<div class="adm-section">
    <div class="adm-section-head" style="flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <h3><i class="fa-solid fa-boxes-stacked"></i> 등록 상품 목록 (<?php echo number_format($filtered_total); ?>개)</h3>
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <a href="?cat=&state=<?php echo $state_filter; ?>&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $cat_filter === '' ? '#0077B6' : '#F1F5F9'; ?>; color:<?php echo $cat_filter === '' ? '#fff' : '#334155'; ?>;">전체 카테고리</a>
                <?php foreach ($large_categories as $lc): 
                    $lc_val = (string)$lc['cat_name'];
                    $is_act = ($cat_filter === $lc_val || $cat_filter === (string)$lc['idx']);
                ?>
                    <a href="?cat=<?php echo urlencode($lc_val); ?>&state=<?php echo $state_filter; ?>&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $is_act ? '#0284C7' : '#F1F5F9'; ?>; color:<?php echo $is_act ? '#fff' : '#334155'; ?>;"><?php echo htmlspecialchars($lc['cat_name']); ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <form method="get" style="display:flex; gap:6px; align-items:center;">
                <input type="hidden" name="cat" value="<?php echo htmlspecialchars($cat_filter); ?>">
                <input type="hidden" name="state" value="<?php echo htmlspecialchars($state_filter); ?>">
                <input type="text" name="sk" value="<?php echo htmlspecialchars($sk); ?>" placeholder="상품명 / 스펙 검색" style="padding:6px 12px; border:1px solid var(--adm-border); border-radius:6px; font-size:0.85rem; outline:none; width:180px;">
                <button type="submit" class="adm-btn" style="padding:6px 12px; font-size:0.82rem;"><i class="fa-solid fa-magnifying-glass"></i> 검색</button>
            </form>

            <div style="display:flex; align-items:center; gap:4px; background:#F8FAFC; padding:4px 8px; border:1px solid var(--adm-border); border-radius:8px;">
                <select id="target_cat_select" style="padding:5px 8px; border:1px solid var(--adm-border); border-radius:6px; font-size:0.83rem; outline:none; cursor:pointer; background:#fff;">
                    <option value="">-- 카테고리 선택 --</option>
                    <?php foreach ($large_categories as $lc): ?>
                        <option value="<?php echo htmlspecialchars($lc['cat_name']); ?>"><?php echo htmlspecialchars(!empty($lc['parent_name']) ? '[' . $lc['parent_name'] . '] ' . $lc['cat_name'] : $lc['cat_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" onclick="changeSelectedCategory()" class="adm-btn" style="padding:6px 12px; font-size:0.83rem; background:#0077B6; color:#fff;"><i class="fa-solid fa-folder-tree"></i> 일괄 카테고리 변경</button>
            </div>

            <button type="button" onclick="copySelectedProducts()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#0284C7; color:#0284C7;"><i class="fa-solid fa-copy"></i> 선택 상품 복사</button>
            <button type="button" onclick="deleteSelectedProducts()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#EF4444; color:#EF4444;"><i class="fa-solid fa-trash"></i> 선택 상품 삭제</button>
            <a href="write.php" class="adm-btn" style="padding:7px 14px; font-size:0.85rem; background:#059669;"><i class="fa-solid fa-plus"></i> 새 상품 등록</a>
        </div>
    </div>

    <form id="bulkForm" method="post" action="proc.php">
        <input type="hidden" name="mode" id="bulk_mode" value="copy_bulk">
        <input type="hidden" name="target_category" id="bulk_target_category" value="">

        <div style="overflow-x:auto;">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;"><input type="checkbox" id="chk_all" onclick="toggleCheckAll(this)" style="cursor:pointer;"></th>
                        <th style="width:60px; text-align:center;">번호</th>
                        <th>구분</th>
                        <th style="width:60px; text-align:center;">썸네일</th>
                        <th>상품명</th>
                        <th>스펙 설명</th>
                        <th>가격 / 단위</th>
                        <th style="width:70px; text-align:center;">정렬</th>
                        <th style="text-align:center;">상태</th>
                        <th>등록일</th>
                        <th style="width:130px; text-align:center;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr class="empty-row"><td colspan="11">등록된 상품이 없습니다.</td></tr>
                    <?php else: foreach ($products as $p): ?>
                        <tr>
                            <td style="text-align:center;"><input type="checkbox" name="chk_no[]" value="<?php echo $p['no']; ?>" class="chk-item" style="cursor:pointer;"></td>
                            <td style="text-align:center; font-weight:600; color:var(--adm-muted);"><?php echo $p['no']; ?></td>
                            <td>
                                <?php
                                $c_val = $p['category'];
                                $sub_cat_map = [
                                    'apt'                  => '아파트/주택베란다',
                                    'building'             => '빌딩',
                                    'diy'                  => 'DIY자가설치',
                                    'auto'                 => '자동차',
                                    'officetel'            => '오피스텔',
                                    'house'                => '단독주택',
                                    '아파트 시공용 필름'   => '아파트/주택베란다',
                                    '건물/빌딩 시공용 필름' => '빌딩',
                                    'DIY 자가설치 키트'    => 'DIY자가설치',
                                    '자동차/차량 썬팅'     => '자동차'
                                ];
                                $sub_name = isset($sub_cat_map[$c_val]) ? $sub_cat_map[$c_val] : $c_val;
                                $full_cat_display = "상품분류 >> " . $sub_name;
                                echo '<span class="adm-badge" style="background:#E0F2FE; color:#0369A1; font-weight:700;"><i class="fa-solid fa-folder-tree"></i> ' . htmlspecialchars($full_cat_display) . '</span>';
                                ?>
                                <div style="margin-top:4px; font-size:0.78rem; color:#475569; font-weight:700;">
                                    <i class="fa-solid fa-tag" style="color:#D97706;"></i> 브랜드: <span style="color:#1E293B; font-weight:800;"><?php echo htmlspecialchars(!empty($p['brand']) ? $p['brand'] : 'VULUX'); ?></span>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($p['thumb']): ?>
                                    <img src="<?php echo $site_path_prefix . htmlspecialchars($p['thumb']); ?>" alt="" style="width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid var(--adm-border); background:#0F172A;" onerror="this.onerror=null; this.src='https://via.placeholder.com/40';">
                                <?php else: ?>
                                    <div style="width:40px; height:40px; border-radius:6px; background:#F1F5F9; display:inline-flex; align-items:center; justify-content:center; color:#94A3B8;"><i class="fa-solid fa-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color:var(--adm-text);"><?php echo htmlspecialchars($p['name']); ?></strong>
                                <?php if ($p['show_lineup']): ?><i class="fa-solid fa-star" style="color:#F59E0B; font-size:0.78rem; margin-left:4px;" title="서비스페이지 필름 라인업 노출중"></i><?php endif; ?>
                            </td>
                            <td style="color:var(--adm-muted); font-size:0.82rem; max-width:240px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?php echo htmlspecialchars($p['spec']); ?>">
                                <?php echo htmlspecialchars($p['spec'] ? $p['spec'] : '-'); ?>
                            </td>
                            <td><strong style="color:var(--adm-primary);"><?php echo number_format($p['price']); ?>원</strong> <span style="font-size:0.78rem; color:var(--adm-muted);">/ <?php echo htmlspecialchars($p['price_unit']); ?></span></td>
                            <td style="text-align:center; font-size:0.82rem; font-weight:600; color:var(--adm-muted);"><?php echo $p['sort_order']; ?></td>
                            <td style="text-align:center;">
                                <select onchange="changeProductState(<?php echo $p['no']; ?>, this.value)" class="state-select state-<?php echo $p['state']; ?>">
                                    <option value="1" <?php echo $p['state'] == 1 ? 'selected' : ''; ?>>판매중</option>
                                    <option value="0" <?php echo $p['state'] == 0 ? 'selected' : ''; ?>>숨김</option>
                                </select>
                            </td>
                            <td style="font-size:0.82rem; color:var(--adm-muted);"><?php echo htmlspecialchars($p['reg_date']); ?></td>
                            <td style="text-align:center; white-space:nowrap;">
                                <a href="write.php?no=<?php echo $p['no']; ?>" class="adm-btn adm-btn-outline" style="padding:4px 9px; font-size:0.78rem;"><i class="fa-solid fa-pen-to-square"></i> 수정</a>
                                <a href="proc.php?mode=delete&no=<?php echo $p['no']; ?>" onclick="return confirm('이 상품을 정말로 삭제하시겠습니까?');" class="adm-btn" style="padding:4px 9px; font-size:0.78rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 삭제</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </form>
    <?php render_adm_pagination($page, $total_pages, $_GET); ?>
</div>

<p style="font-size:0.8rem; color:var(--adm-muted); margin-top:8px;">* 아파트용/건물용 시공 상품(견적 계산기 필름 단가) 및 DIY 쇼핑몰 자가설치 키트 상품을 통합 관리합니다.</p>

<script>
function toggleCheckAll(master) {
    const items = document.querySelectorAll('.chk-item');
    items.forEach(el => el.checked = master.checked);
}

function changeSelectedCategory() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('카테고리를 변경할 상품을 하나 이상 선택해 주세요.');
        return;
    }
    const selectEl = document.getElementById('target_cat_select');
    const targetCat = selectEl ? selectEl.value : '';
    if (!targetCat) {
        alert('변경할 카테고리를 선택해 주세요.');
        if (selectEl) selectEl.focus();
        return;
    }

    const catTextMap = <?php echo json_encode($cat_label, JSON_UNESCAPED_UNICODE); ?>;
    const selectedOptText = (selectEl && selectEl.options[selectEl.selectedIndex]) ? selectEl.options[selectEl.selectedIndex].text : targetCat;
    const catName = catTextMap[targetCat] || selectedOptText;

    if (confirm('선택한 ' + checked.length + '개 상품의 카테고리를 [' + catName + '](으)로 변경하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'change_category_bulk';
        document.getElementById('bulk_target_category').value = targetCat;
        document.getElementById('bulkForm').submit();
    }
}

function copySelectedProducts() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('복사할 상품을 하나 이상 선택해 주세요.');
        return;
    }
    if (confirm('선택한 ' + checked.length + '개 상품을 복사하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'copy_bulk';
        document.getElementById('bulkForm').submit();
    }
}

function deleteSelectedProducts() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('삭제할 상품을 하나 이상 선택해 주세요.');
        return;
    }
    if (confirm('선택한 ' + checked.length + '개 상품을 정말로 삭제하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'delete_bulk';
        document.getElementById('bulkForm').submit();
    }
}

function changeProductState(no, state) {
    fetch('update_state.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'no=' + no + '&state=' + state
    })
    .then(res => res.json())
    .then(data => {
        if(data.ok) {
            location.reload();
        } else {
            alert('상태 변경 중 오류가 발생했습니다.');
        }
    })
    .catch(err => {
        location.reload();
    });
}
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
