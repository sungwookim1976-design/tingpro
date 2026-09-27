<style>
    .case-filter { display:flex; gap:8px; justify-content:center; flex-wrap:wrap; margin-bottom:20px; }
    .case-filter a { font-size:0.88rem; font-weight:700; padding:9px 20px; border-radius:var(--radius-full); background:#F1F5F9; color:#334155; transition:all 0.2s ease; border:1px solid #E2E8F0; }
    .case-filter a.active { background:var(--primary); color:#fff; border-color:var(--primary); box-shadow:0 4px 12px rgba(0,180,216,0.3); }
    .case-filter a:hover:not(.active) { background:#E2E8F0; }

    .case-tab-bar { display:flex; gap:12px; justify-content:center; margin-bottom:32px; }
    .case-tab-btn { font-size:0.95rem; font-weight:800; padding:11px 28px; border-radius:30px; transition:all 0.2s ease; display:inline-flex; align-items:center; gap:8px; text-decoration:none; }
    .case-tab-btn.active { background:var(--secondary); color:#fff; box-shadow:0 6px 16px rgba(15,23,42,0.25); }
    .case-tab-btn:not(.active) { background:#fff; color:#475569; border:1.5px solid #CBD5E1; }
    .case-tab-btn:hover:not(.active) { background:#F8FAFC; color:#0F172A; border-color:#94A3B8; }

    .case-card { background:white; border:1px solid #E2E8F0; border-radius:var(--radius-md); overflow:hidden; box-shadow:var(--shadow-sm); transition:transform 0.25s ease, box-shadow 0.25s ease; cursor:pointer; }
    .case-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); border-color:#BAE6FD; }
    .case-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:28px; }
    
    /* Detail View Styling */
    .case-detail-box { background:white; border:1px solid #E2E8F0; border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-md); margin-bottom:40px; }
    .case-detail-header { background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color:white; padding:32px; }
    .case-spec-grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; background:#F8FAFC; padding:20px 32px; border-bottom:1px solid #E2E8F0; }
    .case-spec-item { text-align:center; }
    .case-spec-item .lbl { font-size:0.8rem; color:#64748B; font-weight:700; margin-bottom:4px; }
    .case-spec-item .val { font-size:1.15rem; color:#0F172A; font-weight:900; }

    @media (max-width:900px) {
        .case-grid { grid-template-columns:repeat(2, 1fr); }
        .case-spec-grid { grid-template-columns:repeat(2, 1fr); }
    }
    @media (max-width:600px) {
        .case-grid { grid-template-columns:1fr; }
        .case-spec-grid { grid-template-columns:1fr; }
    }
</style>

<?php
// ai_category 중분류 (depth=2) 카테고리 로딩
$db_mid_categories = array();
$res_db_mid = @mysqli_query($conn, "SELECT * FROM ai_category WHERE depth=2 AND use_yn=1 ORDER BY sort_order ASC, idx ASC");
if ($res_db_mid && mysqli_num_rows($res_db_mid) > 0) {
    while ($r = mysqli_fetch_assoc($res_db_mid)) {
        $db_mid_categories[] = $r;
    }
}

$cat_label = array('' => '전체');
if (!empty($db_mid_categories)) {
    foreach ($db_mid_categories as $dmc) {
        $cat_label[$dmc['cat_name']] = $dmc['cat_name'];
    }
} else {
    $cat_label += [
        'apt'       => '아파트/주택베란다',
        'building'  => '빌딩',
        'auto'      => '자동차',
        'diy'       => 'DIY자가설치',
    ];
}

$cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$tab = (isset($_GET['tab']) && $_GET['tab'] === 'auction') ? 'auction' : 'quote';
$detail_no = isset($_GET['no']) ? (int)$_GET['no'] : 0;

$img_map = [
    '아파트'    => '../imgs/hero_balcony.png',
    '상가/빌딩' => '../imgs/building_office.png',
    '건물/빌딩' => '../imgs/building_office.png',
    '상가/사무실' => '../imgs/building_office.png',
    '자동차'    => '../imgs/use.png',
    '오피스텔'  => '../imgs/building_office.png',
    '단독주택'  => '../imgs/craftsman.png',
    '주택'      => '../imgs/craftsman.png',
];
$badge_color_map = [
    '아파트'    => ['bg' => '#E0F7FA', 'fg' => 'var(--primary-dark)'],
    '상가/빌딩' => ['bg' => '#FEF3C7', 'fg' => '#D97706'],
    '건물/빌딩' => ['bg' => '#FEF3C7', 'fg' => '#D97706'],
    '상가/사무실' => ['bg' => '#FEF3C7', 'fg' => '#D97706'],
    '자동차'    => ['bg' => '#ECFDF5', 'fg' => '#059669'],
    '오피스텔'  => ['bg' => '#EDE9FE', 'fg' => '#7C3AED'],
    '단독주택'  => ['bg' => '#ECFDF5', 'fg' => '#059669'],
    '주택'      => ['bg' => '#ECFDF5', 'fg' => '#059669'],
];

// 시공 관련 가상 스펙 데이터 세팅 함수
function get_case_extra_info($c) {
    $films = [
        '아파트'    => 'VULUX Premium Nano-Ceramic 70',
        '상가/빌딩' => 'VULUX Sputter Dual Heat-Shield 99',
        '오피스텔'  => 'VULUX Privacy Guard Ceramic 80',
        '단독주택'  => 'VULUX All-Season Insulation 85'
    ];
    $parts = [
        '아파트'    => '거실 전면창, 안방 발코니, 주방 전면 유리',
        '상가/빌딩' => '1~2층 쇼룸 통유리, 전면 커튼월',
        '오피스텔'  => '전면 통창, 측면 창호 전체',
        '단독주택'  => '1층 거실 파티오, 2층 침실 창호'
    ];
    $reviews = [
        '아파트'    => '여름철 직사광선이 확실히 차단되고 시야가 눈부심 없이 너무 선명해졌습니다. 에어컨 청구서도 눈에 띄게 절감되었어요!',
        '상가/빌딩' => '매장 유리가 커서 실내 열기가 심했는데, 시공 후 실내 온도가 4도 이상 낮아졌습니다. 손님들도 훨씬 쾌적해 하십니다.',
        '오피스텔'  => '사생활 보호 효과가 무척 뛰어나고 시야는 여전히 밝아서 맘 편히 거주할 수 있게 되었습니다.',
        '단독주택'  => '자외선 99.9% 차단이라 가구 변색 걱정 없이 마음 놓고 햇살을 즐기고 있습니다. 대만족입니다.'
    ];

    $type = isset($c['space_type']) ? $c['space_type'] : '아파트';
    return array(
        'film_name'  => isset($c['film_name']) && !empty($c['film_name']) ? $c['film_name'] : (isset($films[$type]) ? $films[$type] : 'VULUX Nano-Ceramic 70'),
        'parts'      => isset($parts[$type]) ? $parts[$type] : '전면 및 측면 유리 일체',
        'ir_cut'     => '95.8%',
        'uv_cut'     => '99.9%',
        'vlt'        => '70.2%',
        'review'     => isset($reviews[$type]) ? $reviews[$type] : '꼼꼼하고 먼지 없는 완성도 높은 시공에 깊이 감사드립니다.',
        'rating'     => '5.0',
        'warranty'   => '10년 무상 품질보증'
    );
}

// 주소 생략 함수
function gallery_short_area($addr) {
    if (empty($addr)) return '서울';
    $parts = preg_split('/\s+/', trim($addr));
    return implode(' ', array_slice($parts, 0, 2));
}

// 카테고리 필터 SQL 조건절 생성 함수
function get_cat_where_clause($cat) {
    if (empty($cat)) return "";
    $conn = $GLOBALS['conn'];
    $cat_esc = mysqli_real_escape_string($conn, $cat);
    
    // idx 또는 cat_name으로 ai_category 조회
    $c_row = sql_one_one('ai_category', 'cat_name', "and depth=2 and (idx='" . $cat_esc . "' or cat_name='" . $cat_esc . "')");
    $target_name = ($c_row && isset($c_row['cat_name'])) ? $c_row['cat_name'] : $cat;
    $target_esc = mysqli_real_escape_string($conn, $target_name);
    
    $conds = array();
    $conds[] = "space_type = '$cat_esc'";
    $conds[] = "space_type = '$target_esc'";
    $conds[] = "space_type LIKE '%$target_esc%'";
    
    if (mb_strpos($target_name, '아파트') !== false || $cat === 'apt') {
        $conds[] = "space_type LIKE '%아파트%'";
        $conds[] = "space_type LIKE '%베란다%'";
    }
    if (mb_strpos($target_name, '빌딩') !== false || mb_strpos($target_name, '건물') !== false || mb_strpos($target_name, '상가') !== false || $cat === 'building') {
        $conds[] = "space_type LIKE '%빌딩%'";
        $conds[] = "space_type LIKE '%상가%'";
        $conds[] = "space_type LIKE '%건물%'";
        $conds[] = "space_type LIKE '%사무실%'";
    }
    if (mb_strpos($target_name, '자동차') !== false || mb_strpos($target_name, '차량') !== false || $cat === 'auto') {
        $conds[] = "space_type LIKE '%자동차%'";
        $conds[] = "space_type LIKE '%차량%'";
    }
    if (mb_strpos($target_name, '주택') !== false || mb_strpos($target_name, '단독') !== false || $cat === 'house') {
        $conds[] = "space_type LIKE '%주택%'";
        $conds[] = "space_type LIKE '%단독%'";
    }
    if (mb_strpos($target_name, '오피스텔') !== false || $cat === 'officetel') {
        $conds[] = "space_type LIKE '%오피스텔%'";
    }
    if (mb_strpos($target_name, 'DIY') !== false || mb_strpos($target_name, '자가') !== false || $cat === 'diy') {
        $conds[] = "space_type LIKE '%DIY%'";
        $conds[] = "space_type LIKE '%자가%'";
    }

    return " and (" . implode(" OR ", array_unique($conds)) . ")";
}

// 항목의 첫번째 첨부 이미지 구하기 함수 (리스트 섬네일용)
function get_case_first_image($c, $default_img = '../imgs/hero_balcony.png') {
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($c['case_img' . $i])) {
            $p = trim($c['case_img' . $i]);
            return (strpos($p, 'http') === 0 || strpos($p, '/') === 0 || strpos($p, '../') === 0) ? $p : '../' . $p;
        }
    }
    return $default_img;
}

// 항목의 모든 첨부 이미지 배열 구하기 함수 (모달 및 상세용)
function get_case_all_images($c, $default_img = '../imgs/hero_balcony.png') {
    $imgs = [];
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($c['case_img' . $i])) {
            $p = trim($c['case_img' . $i]);
            $imgs[] = (strpos($p, 'http') === 0 || strpos($p, '/') === 0 || strpos($p, '../') === 0) ? $p : '../' . $p;
        }
    }
    if (empty($imgs)) {
        $imgs[] = $default_img;
    }
    return $imgs;
}

// -------------------------------------------------------------
// 1. 단일 시공사례 상세 보기 모드 (no 파라미터가 지정된 경우)
// -------------------------------------------------------------
if ($detail_no > 0):
    $tbl = ($tab === 'quote') ? 'quotes' : 'auctions';
    $single_case = sql_one_one($tbl, '*', "and no=" . $detail_no);
    if (!$single_case) {
        $other_tbl = ($tab === 'quote') ? 'auctions' : 'quotes';
        $single_case = sql_one_one($other_tbl, '*', "and no=" . $detail_no);
    }

    if ($single_case):
        $default_fallback = isset($img_map[$single_case['space_type']]) ? $img_map[$single_case['space_type']] : '../imgs/hero_balcony.png';
        $case_imgs = get_case_all_images($single_case, $default_fallback);
        $bc   = isset($badge_color_map[$single_case['space_type']]) ? $badge_color_map[$single_case['space_type']] : array('bg' => '#E0F7FA', 'fg' => '#0077B6');
        $area = gallery_short_area($single_case['addr']);
        $extra = get_case_extra_info($single_case);
        $price = isset($single_case['total_price']) ? $single_case['total_price'] : (isset($single_case['desired_price']) ? $single_case['desired_price'] : 0);
?>
    <div style="margin-bottom:24px;">
        <a href="?cat=<?php echo urlencode($cat); ?>&tab=<?php echo $tab; ?>" class="btn btn-outline btn-sm" style="font-weight:700;"><i class="fa-solid fa-arrow-left"></i> 시공사례 목록으로 돌아가기</a>
    </div>

    <div class="case-detail-box">
        <div class="case-detail-header">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <span style="background:<?php echo $bc['bg']; ?>; color:<?php echo $bc['fg']; ?>; font-size:0.8rem; font-weight:800; padding:4px 10px; border-radius:4px;"><?php echo htmlspecialchars($single_case['space_type']); ?></span>
                <span style="background:rgba(255,255,255,0.15); color:white; font-size:0.8rem; padding:4px 10px; border-radius:4px; font-weight:700;"><i class="fa-solid fa-check-double"></i> <?php echo $tab === 'quote' ? '견적완료 시공사례' : '매칭완료 시공사례'; ?></span>
            </div>
            <h1 style="font-size:2rem; font-weight:900; margin-bottom:8px;"><?php echo htmlspecialchars($area); ?> <?php echo (int)$single_case['py']; ?>평 <?php echo htmlspecialchars($single_case['space_type']); ?> 프리미엄 단열필름 시공</h1>
            <p style="opacity:0.85; font-size:0.95rem;"><i class="fa-regular fa-calendar-check"></i> 시공완료일: <?php echo substr($single_case['reg_date'], 0, 10); ?> | VULUX 공식 인증 마스터 시공팀 진행 | 첨부파일 <?php echo count($case_imgs); ?>장</p>
        </div>

        <div class="case-spec-grid">
            <div class="case-spec-item">
                <div class="lbl">공간 및 평수</div>
                <div class="val"><?php echo htmlspecialchars($single_case['space_type']); ?> <?php echo (int)$single_case['py']; ?>평</div>
            </div>
            <div class="case-spec-item">
                <div class="lbl">최종 시공비</div>
                <div class="val" style="color:var(--primary-dark);"><?php echo number_format($price); ?>원</div>
            </div>
            <div class="case-spec-item">
                <div class="lbl">첨부 이미지 수</div>
                <div class="val"><?php echo count($case_imgs); ?>장 첨부됨</div>
            </div>
            <div class="case-spec-item">
                <div class="lbl">품질보증</div>
                <div class="val" style="color:#059669;"><?php echo $extra['warranty']; ?></div>
            </div>
        </div>

        <div style="padding:36px; display:grid; grid-template-columns:1fr 1fr; gap:36px;">
            <div>
                <img id="detailMainCaseImg" src="<?php echo htmlspecialchars($case_imgs[0]); ?>" alt="첫번째 첨부 이미지" style="width:100%; height:320px; object-fit:cover; border-radius:var(--radius-md); box-shadow:var(--shadow-sm); border:1px solid #E2E8F0; transition:all 0.2s;">
                
                <div style="margin-top:16px;">
                    <div style="font-size:0.85rem; font-weight:800; color:#475569; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <i class="fa-solid fa-paperclip" style="color:var(--primary);"></i> 시공사례 첨부파일 이미지 목록 (<?php echo count($case_imgs); ?>장)
                    </div>
                    <div style="display:flex; gap:10px; overflow-x:auto; padding-bottom:6px;">
                        <?php foreach ($case_imgs as $c_idx => $c_src): ?>
                            <div onclick="document.getElementById('detailMainCaseImg').src='<?php echo htmlspecialchars($c_src); ?>'; document.querySelectorAll('.detail-case-thumb').forEach(el=>el.style.border='2px solid transparent'); this.style.border='2px solid var(--primary)';" class="detail-case-thumb" style="width:80px; text-align:center; cursor:pointer; border:<?php echo $c_idx===0 ? '2px solid var(--primary)' : '2px solid transparent'; ?>; border-radius:8px; padding:2px; background:#F8FAFC; transition:all 0.15s; flex-shrink:0;">
                                <img src="<?php echo htmlspecialchars($c_src); ?>" alt="첨부 이미지 <?php echo $c_idx + 1; ?>" style="width:100%; height:56px; object-fit:cover; border-radius:6px;">
                                <span style="font-size:0.7rem; font-weight:700; color:#64748B; display:block; margin-top:2px;">[첨부 <?php echo $c_idx + 1; ?>]</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div>
                <h3 style="font-size:1.3rem; font-weight:800; color:#0F172A; margin-bottom:16px; border-bottom:2px solid #F1F5F9; padding-bottom:8px;">
                    <i class="fa-solid fa-list-check" style="color:var(--primary);"></i> 시공 상세 명세
                </h3>

                <ul style="list-style:none; padding:0; margin:0 0 24px 0; display:flex; flex-direction:column; gap:12px; font-size:0.95rem; color:#334155;">
                    <li><strong>적용 필름 모델:</strong> <span style="color:var(--primary-dark); font-weight:800;"><?php echo htmlspecialchars($extra['film_name']); ?></span></li>
                    <li><strong>주요 시공 부위:</strong> <?php echo $extra['parts']; ?></li>
                    <li><strong>열차단율 (IR Cut):</strong> <?php echo $extra['ir_cut']; ?></li>
                    <li><strong>자외선차단율 (UV Cut):</strong> <?php echo $extra['uv_cut']; ?></li>
                    <li><strong>가시광선투과율 (VLT):</strong> <?php echo $extra['vlt']; ?></li>
                </ul>

                <div style="background:#F0F9FF; border:1px solid #BAE6FD; border-radius:12px; padding:20px; margin-bottom:24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <strong style="color:#0F172A; font-size:0.95rem;"><i class="fa-solid fa-quote-left" style="color:var(--primary);"></i> 실제 고객 평가</strong>
                        <span style="color:#F59E0B; font-weight:900; font-size:0.9rem;">★★★★★ <?php echo $extra['rating']; ?></span>
                    </div>
                    <p style="font-size:0.9rem; color:#334155; line-height:1.6; margin:0;">"<?php echo $extra['review']; ?>"</p>
                </div>

                <div style="display:flex; gap:12px;">
                    <a href="../calculator/index.php" class="btn btn-primary" style="flex:1; text-align:center; padding:12px;"><i class="fa-solid fa-calculator"></i> 내 집 실시간 견적 산출</a>
                    <button onclick="openFreeVisitModal()" class="btn btn-accent" style="flex:1; padding:12px;"><i class="fa-solid fa-clipboard-user"></i> 방문 실측 신청</button>
                </div>
            </div>
        </div>

        <!-- ------------------------------------------------------------- -->
        <!-- 댓글 및 별점 후기 섹션 (회원 제한) -->
        <!-- ------------------------------------------------------------- -->
        <?php
        // case_comments 테이블 자동 생성 확인
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

        $case_cmts = sql_one('case_comments', '*', "and case_no=" . (int)$detail_no . " and tbl_type='" . mysqli_real_escape_string($conn, $tab) . "' order by no desc");
        $cmt_count = is_array($case_cmts) ? count($case_cmts) : 0;
        $avg_rating = 5.0;
        if ($cmt_count > 0) {
            $sum_r = array_sum(array_column($case_cmts, 'rating'));
            $avg_rating = round($sum_r / $cmt_count, 1);
        }
        ?>
        <div style="border-top:1px solid #E2E8F0; padding:36px; background:#FAFCFF;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
                <h3 style="font-size:1.35rem; font-weight:900; color:#0F172A; margin:0;">
                    <i class="fa-solid fa-comments" style="color:var(--primary);"></i> 고객 시공 리뷰 & 평점 (<?php echo $cmt_count; ?>개)
                </h3>
                <div style="font-size:1.1rem; font-weight:900; color:#D97706; background:#FFFBEB; border:1px solid #FDE68A; padding:6px 14px; border-radius:30px;">
                    평균 별점: <span style="color:#F59E0B;">★ <?php echo number_format($avg_rating, 1); ?></span> / 5.0
                </div>
            </div>

            <!-- 댓글 및 별점 작성 폼 (회원 제한 적용) -->
            <div style="background:#fff; border:1px solid #CBD5E1; border-radius:12px; padding:24px; margin-bottom:32px; box-shadow:0 2px 10px rgba(0,0,0,0.03);">
                <?php if (!empty($_SESSION['s_mem_id'])): ?>
                    <form id="galleryCmtForm" onsubmit="event.preventDefault(); submitGalleryComment();">
                        <input type="hidden" id="cmt_case_no" value="<?php echo $detail_no; ?>">
                        <input type="hidden" id="cmt_tbl_type" value="<?php echo htmlspecialchars($tab); ?>">
                        <input type="hidden" id="cmt_rating" value="5">

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
                            <div style="font-size:0.95rem; font-weight:800; color:#0F172A;">
                                <i class="fa-solid fa-user-check" style="color:#059669;"></i> 작성자: <span style="color:var(--primary-dark);"><?php echo htmlspecialchars($_SESSION['s_mem_name'] ? $_SESSION['s_mem_name'] : $_SESSION['s_mem_id']); ?></span> 회원님
                            </div>
                            <div style="display:flex; align-items:center; gap:10px; background:#F8FAFC; padding:6px 14px; border-radius:30px; border:1px solid #E2E8F0;">
                                <span style="font-size:0.85rem; font-weight:800; color:#475569;">별점 선택:</span>
                                <div id="starRatingBox" style="font-size:1.4rem; color:#CBD5E1; cursor:pointer; user-select:none; display:flex; gap:2px;">
                                    <span class="star-item" data-val="1" style="color:#F59E0B;">★</span>
                                    <span class="star-item" data-val="2" style="color:#F59E0B;">★</span>
                                    <span class="star-item" data-val="3" style="color:#F59E0B;">★</span>
                                    <span class="star-item" data-val="4" style="color:#F59E0B;">★</span>
                                    <span class="star-item" data-val="5" style="color:#F59E0B;">★</span>
                                </div>
                                <span id="starRatingText" style="font-size:0.85rem; font-weight:900; color:#D97706; margin-left:4px;">5점 (최고예요)</span>
                            </div>
                        </div>

                        <div style="display:flex; gap:12px;">
                            <textarea id="cmt_content" rows="3" placeholder="이 시공사례에 대한 후기 댓글이나 만족도를 작성해 주세요." style="flex:1; padding:12px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.92rem; outline:none; resize:vertical;" required></textarea>
                            <button type="submit" class="btn btn-primary" style="padding:0 28px; font-weight:800; white-space:nowrap; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                <i class="fa-solid fa-paper-plane" style="font-size:1.1rem; margin-bottom:4px;"></i> 등록하기
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div style="text-align:center; padding:24px; background:#F8FAFC; border-radius:10px; border:1px dashed #CBD5E1; color:#64748B;">
                        <i class="fa-solid fa-lock" style="font-size:1.8rem; color:#94A3B8; margin-bottom:8px; display:block;"></i>
                        <strong style="color:#334155; font-size:1rem;">댓글 및 별점 평가 등록은 회원만 가능합니다.</strong>
                        <p style="font-size:0.88rem; margin:6px 0 16px 0; color:#64748B;">로그인 후 소중한 시공 후기와 만족도 별점을 남겨보세요.</p>
                        <a href="../member/login.php?redirect=<?php echo urlencode('../gallery/index.php?cat=' . $cat . '&tab=' . $tab . '&no=' . $detail_no); ?>" class="btn btn-primary btn-sm" style="font-weight:800; padding:8px 20px;">
                            <i class="fa-solid fa-right-to-bracket"></i> 로그인하러 가기
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 댓글 리스트 목록 -->
            <div id="galleryCmtList">
                <?php if (empty($case_cmts)): ?>
                    <div style="text-align:center; padding:40px; color:#94A3B8; font-size:0.92rem; background:#fff; border-radius:10px; border:1px solid #E2E8F0;">
                        <i class="fa-solid fa-comment-dots" style="font-size:2rem; color:#CBD5E1; margin-bottom:10px; display:block;"></i>
                        아직 등록된 시공 후기 댓글이 없습니다. 첫번째 후기와 별점을 남겨주세요!
                    </div>
                <?php else: foreach ($case_cmts as $cmt): 
                    $r_val = (int)$cmt['rating'];
                    $stars_str = str_repeat('★', $r_val) . str_repeat('☆', 5 - $r_val);
                    $is_my_cmt = (!empty($_SESSION['s_mem_no']) && (int)$_SESSION['s_mem_no'] === (int)$cmt['mem_no']) || !empty($_SESSION['s_adm_id']);
                ?>
                    <div style="background:#fff; border:1px solid #E2E8F0; border-radius:10px; padding:18px 22px; margin-bottom:12px; box-shadow:0 2px 6px rgba(0,0,0,0.02);">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <strong style="font-size:0.95rem; color:#0F172A;"><?php echo htmlspecialchars($cmt['writer_name']); ?></strong>
                                <span style="color:#F59E0B; font-weight:900; font-size:0.95rem;"><?php echo $stars_str; ?> <span style="font-size:0.85rem; color:#D97706;">(<?php echo $r_val; ?>점)</span></span>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="font-size:0.82rem; color:#94A3B8;"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($cmt['reg_date']); ?></span>
                                <?php if ($is_my_cmt): ?>
                                    <button type="button" onclick="deleteGalleryComment(<?php echo $cmt['no']; ?>)" style="background:none; border:none; color:#EF4444; font-size:0.82rem; cursor:pointer; font-weight:700;">
                                        <i class="fa-solid fa-trash"></i> 삭제
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p style="font-size:0.92rem; color:#334155; margin:0; line-height:1.6; white-space:pre-line;"><?php echo htmlspecialchars($cmt['content']); ?></p>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
<?php 
    endif;
endif; 
?>

<!-- ------------------------------------------------------------- -->
<!-- 2. 시공사례 포트폴리오 리스트 뷰 -->
<!-- ------------------------------------------------------------- -->
<h2 style="font-size:2rem; font-weight:900; text-align:center; margin-bottom:12px; color:var(--secondary);">최근 시공 포트폴리오</h2>
<p style="text-align:center; color:var(--text-muted); margin-bottom:28px;">VULUX 전문 시공팀이 완성한 실제 현장별 시공 사례입니다.</p>

<?php
$cat_where = get_cat_where_clause($cat);

// 각 탭별 총건수 계산 (첨부파일 1개 이상 등록된 건만)
$img_condition = " and ((case_img1 IS NOT NULL and case_img1 != '') or (case_img2 IS NOT NULL and case_img2 != '') or (case_img3 IS NOT NULL and case_img3 != '') or (case_img4 IS NOT NULL and case_img4 != '') or (case_img5 IS NOT NULL and case_img5 != ''))";

$quote_cnt   = sql_cnt('quotes', "and state='완료'" . $img_condition . $cat_where);
$auction_cnt = sql_cnt('auctions', "and state='매칭완료'" . $img_condition . $cat_where);

// 현재 선택된 탭에 따라 데이터 쿼리
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 6;

if ($tab === 'quote') {
    $where = "and state='완료'" . $img_condition . $cat_where;
    $total_cnt = $quote_cnt;
    $target_tbl = 'quotes';
} else {
    $where = "and state='매칭완료'" . $img_condition . $cat_where;
    $total_cnt = $auction_cnt;
    $target_tbl = 'auctions';
}

$total_pages = max(1, (int)ceil($total_cnt / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$cases = sql_one($target_tbl, '*', $where . " order by no desc limit $offset, $limit");
?>

<!-- 카테고리 필터 탭 -->
<div class="case-filter">
    <?php foreach ($cat_label as $key => $label): ?>
        <a href="?cat=<?php echo urlencode($key); ?>&tab=<?php echo $tab; ?>" class="<?php echo $cat === $key ? 'active' : ''; ?>"><?php echo htmlspecialchars($label); ?></a>
    <?php endforeach; ?>
</div>

<!-- 견적시공 / 역경매시공 구분 탭 -->
<div class="case-tab-bar">
    <a href="?cat=<?php echo urlencode($cat); ?>&tab=quote" class="case-tab-btn <?php echo $tab === 'quote' ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-invoice"></i> 견적시공 사례 (<?php echo number_format($quote_cnt); ?>건)
    </a>
    <a href="?cat=<?php echo urlencode($cat); ?>&tab=auction" class="case-tab-btn <?php echo $tab === 'auction' ? 'active' : ''; ?>">
        <i class="fa-solid fa-gavel"></i> 역경매시공 사례 (<?php echo number_format($auction_cnt); ?>건)
    </a>
</div>

<!-- 시공사례 그리드 리스트 (6개씩 노출) -->
<div class="case-grid">
<?php if (empty($cases)): ?>
    <div style="grid-column:1 / -1; text-align:center; color:var(--text-muted); padding:60px 20px; background:white; border-radius:var(--radius-lg); border:1px solid #E2E8F0;">
        <i class="fa-solid fa-folder-open" style="font-size:3rem; color:#94A3B8; margin-bottom:16px;"></i>
        <h4 style="font-size:1.2rem; font-weight:800; color:#334155;">해당 조건의 <?php echo $tab === 'quote' ? '견적' : '역경매'; ?> 시공사례가 없습니다.</h4>
        <p style="font-size:0.9rem; margin-top:6px;">다른 카테고리나 탭을 선택해 보세요.</p>
    </div>
<?php else: foreach ($cases as $c):
    $default_fallback = isset($img_map[$c['space_type']]) ? $img_map[$c['space_type']] : '../imgs/hero_balcony.png';
    // 첫번째 첨부파일로 카드 섬네일 생성
    $first_img = get_case_first_image($c, $default_fallback);
    $all_imgs = get_case_all_images($c, $default_fallback);
    $bc   = isset($badge_color_map[$c['space_type']]) ? $badge_color_map[$c['space_type']] : array('bg' => '#F1F5F9', 'fg' => '#334155');
    $area = gallery_short_area($c['addr']);
    $price = isset($c['total_price']) ? $c['total_price'] : (isset($c['desired_price']) ? $c['desired_price'] : 0);
?>
    <div class="case-card" onclick="openCaseModal(<?php echo (int)$c['no']; ?>, '<?php echo $tab; ?>')">
        <div style="position:relative;">
            <!-- 첫번째 첨부파일로 작성된 카드 섬네일 -->
            <img src="<?php echo htmlspecialchars($first_img); ?>" alt="<?php echo htmlspecialchars($c['space_type']); ?>" style="width:100%; height:220px; object-fit:cover;">
            
            <span style="position:absolute; top:14px; left:14px; background:<?php echo $bc['bg']; ?>; color:<?php echo $bc['fg']; ?>; font-size:0.75rem; font-weight:800; padding:4px 10px; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,0.15);"><?php echo htmlspecialchars($c['space_type']); ?></span>
            
            <span style="position:absolute; bottom:12px; right:12px; background:rgba(15,23,42,0.75); color:#fff; font-size:0.75rem; font-weight:800; padding:3px 8px; border-radius:4px; backdrop-filter:blur(4px);">
                <i class="fa-solid fa-paperclip"></i> 첨부 <?php echo count($all_imgs); ?>장
            </span>
        </div>
        <div style="padding:22px;">
            <h4 style="font-size:1.1rem; font-weight:800; margin-bottom:8px; color:#0F172A; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; height:2.8em;">
                <?php echo htmlspecialchars($area); ?> <?php echo (int)$c['py']; ?>평 <?php echo htmlspecialchars($c['space_type']); ?> 썬팅
            </h4>
            <p style="font-size:0.85rem; color:#64748B; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
                <span>시공비: <strong style="color:var(--primary-dark); font-weight:900; font-size:0.95rem;"><?php echo number_format($price); ?>원</strong></span>
                <span class="adm-badge" style="background:<?php echo $tab === 'quote' ? '#E0F7FA' : '#FEF3C7'; ?>; color:<?php echo $tab === 'quote' ? '#0077B6' : '#D97706'; ?>; font-weight:800;"><?php echo $tab === 'quote' ? '견적완료' : '매칭완료'; ?></span>
            </p>
            
            <div style="border-top:1px dashed #E2E8F0; padding-top:14px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:0.8rem; color:#94A3B8;"><i class="fa-regular fa-calendar-check"></i> <?php echo substr($c['reg_date'], 0, 10); ?></span>
                <span class="btn btn-outline btn-sm" style="font-size:0.82rem; padding:5px 12px; font-weight:800;"><i class="fa-solid fa-circle-info"></i> 상세보기</span>
            </div>
        </div>
    </div>
<?php endforeach; endif; ?>
</div>

<!-- 6개씩 보여주고 페이징 처리 -->
<?php if ($total_pages > 1): ?>
<div style="display:flex; justify-content:center; align-items:center; gap:8px; margin-top:40px; margin-bottom:20px;">
    <?php if ($page > 1): ?>
        <a href="?cat=<?php echo urlencode($cat); ?>&tab=<?php echo $tab; ?>&page=<?php echo $page - 1; ?>" class="btn btn-outline btn-sm" style="font-weight:700;"><i class="fa-solid fa-chevron-left"></i> 이전</a>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <a href="?cat=<?php echo urlencode($cat); ?>&tab=<?php echo $tab; ?>&page=<?php echo $p; ?>" class="btn <?php echo $p === $page ? 'btn-primary' : 'btn-outline'; ?> btn-sm" style="min-width:36px; text-align:center; font-weight:700;">
            <?php echo $p; ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?cat=<?php echo urlencode($cat); ?>&tab=<?php echo $tab; ?>&page=<?php echo $page + 1; ?>" class="btn btn-outline btn-sm" style="font-weight:700;">다음 <i class="fa-solid fa-chevron-right"></i></a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ------------------------------------------------------------- -->
<!-- 3. 시공사례 상세보기 레이어 모달 (Case Detail Modal) -->
<!-- ------------------------------------------------------------- -->
<div class="modal-overlay" id="caseDetailModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.8); z-index:2500; align-items:center; justify-content:center; padding:20px; overflow-y:auto;">
    <div class="modal-card" style="background:#FFFFFF; border-radius:var(--radius-lg); max-width:760px; width:100%; max-height:90vh; overflow-y:auto; padding:0; position:relative; box-shadow:0 25px 60px rgba(0,0,0,0.35);">
        <button class="modal-close" onclick="closeModal('caseDetailModal')" style="position:absolute; top:16px; right:20px; background:rgba(0,0,0,0.4); border:none; width:36px; height:36px; border-radius:50%; font-size:1.4rem; cursor:pointer; color:#FFFFFF; z-index:10; display:flex; align-items:center; justify-content:center;">&times;</button>
        
        <div id="modalCaseBody">
            <!-- Dynamic Content loaded by JavaScript -->
        </div>
    </div>
</div>

<script>
    const casesData = <?php echo json_encode(array_values($cases ?: [])); ?>;
    const imgMap = <?php echo json_encode($img_map); ?>;
    const currentTab = '<?php echo $tab; ?>';
    const currentCat = '<?php echo $cat; ?>';

    function openCaseModal(caseNo, tabType) {
        const item = casesData.find(c => parseInt(c.no) === parseInt(caseNo));
        if (!item) return;

        let caseImgs = [];
        for (let i = 1; i <= 5; i++) {
            if (item['case_img' + i] && item['case_img' + i].trim() !== '') {
                let p = item['case_img' + i].trim();
                if (!p.startsWith('http') && !p.startsWith('/') && !p.startsWith('../')) {
                    p = '../' + p;
                }
                caseImgs.push(p);
            }
        }
        if (caseImgs.length === 0) {
            const defaultImg = imgMap[item.space_type] || '../imgs/hero_balcony.png';
            caseImgs = [defaultImg];
        }

        const priceVal = item.total_price ? item.total_price : (item.desired_price ? item.desired_price : 0);
        const formattedPrice = new Intl.NumberFormat().format(priceVal);
        const filmTitle = item.film_name ? item.film_name : 'VULUX Nano-Ceramic High-End';

        // 모달창에 첨부파일 이미지 표현 HTML 생성
        let attachmentListHtml = '';
        caseImgs.forEach((ci, idx) => {
            attachmentListHtml += `
                <div onclick="document.getElementById('modalMainCaseImg').src='${ci}'; document.querySelectorAll('.modal-attachment-item').forEach(el=>el.style.border='2px solid transparent'); this.style.border='2px solid #0077B6';" class="modal-attachment-item" style="width:90px; text-align:center; cursor:pointer; border:${idx===0 ? '2px solid #0077B6' : '2px solid transparent'}; border-radius:8px; padding:3px; background:#F8FAFC; transition:all 0.15s; flex-shrink:0;">
                    <img src="${ci}" style="width:100%; height:60px; object-fit:cover; border-radius:6px;">
                    <span style="font-size:0.72rem; font-weight:800; color:#475569; display:block; margin-top:3px;"><i class="fa-solid fa-paperclip"></i> 첨부 ${idx + 1}</span>
                </div>
            `;
        });

        const modalHtml = `
            <div style="background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color:white; padding:28px; border-top-left-radius:var(--radius-lg); border-top-right-radius:var(--radius-lg);">
                <div style="display:flex; gap:8px; margin-bottom:8px;">
                    <span style="background:#E0F7FA; color:#0077B6; font-size:0.75rem; font-weight:800; padding:3px 8px; border-radius:4px; display:inline-block;">${item.space_type}</span>
                    <span style="background:rgba(255,255,255,0.2); color:#fff; font-size:0.75rem; font-weight:800; padding:3px 8px; border-radius:4px; display:inline-block;">${tabType === 'quote' ? '견적완료' : '매칭완료'}</span>
                </div>
                <h3 style="font-size:1.5rem; font-weight:900; margin:0 0 6px 0;">${item.addr ? item.addr.split(' ').slice(0, 2).join(' ') : '서울'} ${item.py}평 시공 사례</h3>
                <p style="font-size:0.85rem; opacity:0.85; margin:0;"><i class="fa-regular fa-calendar-check"></i> 시공일자: ${item.reg_date ? item.reg_date.substring(0, 10) : ''} | 첨부파일 ${caseImgs.length}장 등록</p>
            </div>

            <div style="padding:28px;">
                <!-- 메인 이미지 및 첨부파일 목록 섹션 -->
                <div style="margin-bottom:24px;">
                    <img id="modalMainCaseImg" src="${caseImgs[0]}" style="width:100%; height:280px; object-fit:cover; border-radius:12px; border:1px solid #E2E8F0; transition:all 0.2s; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                    
                    <div style="margin-top:14px; background:#F8FAFC; border:1px solid #E2E8F0; padding:14px; border-radius:10px;">
                        <div style="font-size:0.85rem; font-weight:800; color:#0F172A; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;">
                            <span><i class="fa-solid fa-paperclip" style="color:#0077B6;"></i> 시공사례 등록 첨부파일 (${caseImgs.length}장)</span>
                            <span style="font-size:0.78rem; color:#64748B; font-weight:600;">클릭 시 대표 이미지로 전환됩니다</span>
                        </div>
                        <div style="display:flex; gap:10px; overflow-x:auto; padding-bottom:4px;">
                            ${attachmentListHtml}
                        </div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
                    <div style="background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0;">
                        <strong style="color:#64748B; display:block; font-size:0.78rem;">총 시공비</strong>
                        <span style="color:#0077B6; font-size:1.35rem; font-weight:900;">${formattedPrice}원</span>
                    </div>
                    <div style="background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0;">
                        <strong style="color:#64748B; display:block; font-size:0.78rem;">적용 필름 모델</strong>
                        <span style="color:#0F172A; font-weight:800; font-size:0.95rem;">${filmTitle}</span>
                    </div>
                </div>

                <h4 style="font-size:1.05rem; font-weight:800; color:#0F172A; margin-bottom:10px; border-bottom:2px solid #F1F5F9; padding-bottom:6px;"><i class="fa-solid fa-square-check" style="color:#0077B6;"></i> 시공 성능 스펙</h4>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; text-align:center; margin-bottom:20px;">
                    <div style="background:#F0F9FF; border:1px solid #BAE6FD; padding:10px; border-radius:8px;">
                        <span style="font-size:0.75rem; color:#0077B6; font-weight:700; display:block;">열차단율 (IR Cut)</span>
                        <strong style="font-size:1.1rem; color:#0F172A; font-weight:900;">95.8%</strong>
                    </div>
                    <div style="background:#F0F9FF; border:1px solid #BAE6FD; padding:10px; border-radius:8px;">
                        <span style="font-size:0.75rem; color:#0077B6; font-weight:700; display:block;">UV 차단율</span>
                        <strong style="font-size:1.1rem; color:#0F172A; font-weight:900;">99.9%</strong>
                    </div>
                    <div style="background:#ECFDF5; border:1px solid #A7F3D0; padding:10px; border-radius:8px;">
                        <span style="font-size:0.75rem; color:#059669; font-weight:700; display:block;">품질보증</span>
                        <strong style="font-size:1.1rem; color:#059669; font-weight:900;">10년 무상</strong>
                    </div>
                </div>

                <div style="background:#FEF3C7; border:1px solid #FDE68A; padding:14px 18px; border-radius:10px; margin-bottom:24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <strong style="font-size:0.88rem; color:#92400E;">고객 만족 후기</strong>
                        <span style="color:#D97706; font-weight:900; font-size:0.85rem;">★★★★★ 5.0</span>
                    </div>
                    <p style="font-size:0.85rem; color:#78350F; margin:0;">"여름철 눈부심이 완벽히 해결되고 실내가 훨씬 서늘해졌습니다. 직영 시공팀의 마감이 아주 훌륭했습니다!"</p>
                </div>

                <div style="display:flex; gap:12px;">
                    <a href="?cat=${currentCat}&tab=${tabType}&no=${item.no}" class="btn btn-outline" style="flex:1; text-align:center; padding:11px; font-weight:700;"><i class="fa-solid fa-up-right-from-square"></i> 상세 페이지로 이동</a>
                    <button onclick="closeModal('caseDetailModal'); openFreeVisitModal();" class="btn btn-accent" style="flex:1; padding:11px; font-weight:800;"><i class="fa-solid fa-paper-plane"></i> 1:1 방문 실측 신청</button>
                </div>
            </div>
        `;

        document.getElementById('modalCaseBody').innerHTML = modalHtml;
        document.getElementById('caseDetailModal').style.display = 'flex';
    }

    document.addEventListener('DOMContentLoaded', function() {
        initStarRating();
    });

    function initStarRating() {
        const box = document.getElementById('starRatingBox');
        if (!box) return;
        const stars = box.querySelectorAll('.star-item');
        const input = document.getElementById('cmt_rating');
        const text = document.getElementById('starRatingText');
        const labels = {1: '1점 (아쉬워요)', 2: '2점 (그저그래요)', 3: '3점 (보통이에요)', 4: '4점 (좋아요)', 5: '5점 (최고예요)'};

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const val = parseInt(this.getAttribute('data-val'));
                if (input) input.value = val;
                if (text) text.innerText = labels[val] || (val + '점');

                stars.forEach(s => {
                    const sVal = parseInt(s.getAttribute('data-val'));
                    s.style.color = sVal <= val ? '#F59E0B' : '#CBD5E1';
                });
            });
        });
    }

    function submitGalleryComment() {
        const caseNoEl = document.getElementById('cmt_case_no');
        const tblTypeEl = document.getElementById('cmt_tbl_type');
        const ratingEl = document.getElementById('cmt_rating');
        const contentEl = document.getElementById('cmt_content');

        if (!caseNoEl || !contentEl) return;

        const caseNo = caseNoEl.value;
        const tblType = tblTypeEl ? tblTypeEl.value : 'quote';
        const rating = ratingEl ? ratingEl.value : 5;
        const content = contentEl.value.trim();

        if (!content) {
            alert('댓글 내용을 입력해 주세요.');
            return;
        }

        const formData = new FormData();
        formData.append('case_no', caseNo);
        formData.append('tbl_type', tblType);
        formData.append('rating', rating);
        formData.append('content', content);

        fetch('comment_proc.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert(data.msg || '댓글과 별점이 정상 등록되었습니다.');
                location.reload();
            } else {
                alert(data.msg || '댓글 등록 중 오류가 발생했습니다.');
            }
        })
        .catch(() => {
            alert('네트워크 오류가 발생했습니다.');
        });
    }

    function deleteGalleryComment(commentNo) {
        if (!confirm('이 댓글을 정말로 삭제하시겠습니까?')) return;

        const formData = new FormData();
        formData.append('mode', 'delete');
        formData.append('comment_no', commentNo);

        fetch('comment_proc.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert(data.msg || '댓글이 삭제되었습니다.');
                location.reload();
            } else {
                alert(data.msg || '댓글 삭제 중 오류가 발생했습니다.');
            }
        })
        .catch(() => {
            alert('네트워크 오류가 발생했습니다.');
        });
    }
</script>
