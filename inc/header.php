<?php
$base_path = isset($path_prefix) ? $path_prefix : './';
$current_page = isset($active_menu) ? $active_menu : 'home';

// ai_category 중분류 (depth=2) 카테고리 로딩 (시공사례 서브메뉴 연동)
$gnb_mid_cats = array();
if (isset($conn) && $conn) {
    $res_gnb_cat = @mysqli_query($conn, "SELECT * FROM ai_category WHERE depth=2 AND use_yn=1 ORDER BY sort_order ASC, idx ASC");
    if ($res_gnb_cat && mysqli_num_rows($res_gnb_cat) > 0) {
        while ($r = mysqli_fetch_assoc($res_gnb_cat)) {
            $gnb_mid_cats[] = $r;
        }
    }
}

// 최근 공지사항 1개 로딩 (상단 배너 연동)
$latest_notice_banner = null;
if (isset($conn) && $conn) {
    $latest_notice_banner = sql_one_one('community_posts', '*', "and board_type='notice' and state=1 order by no desc limit 1");
}
?>
<!-- Top Banner Bar -->
<div class="top-banner">
    <!-- 기존 EVENT 배너 주석 처리
    <span class="badge">EVENT</span>
    <span>2026년 가을 맞이 썬팅 시공 20% 특별 할인 + 무료 방문 실측 진행 중!</span>
    <a href="<?php echo $base_path; ?>calculator/" style="text-decoration: underline; color: #E0F7FA; margin-left: 8px;">실시간 견적 확인하기 &rarr;</a>
    -->
    <span class="badge" style="background:#0077B6; color:#ffffff;">공지</span>
    <?php if ($latest_notice_banner): ?>
        <a href="<?php echo $base_path; ?>comm/view.php?id=<?php echo (int)$latest_notice_banner['no']; ?>&cat=notice" style="color:#ffffff; text-decoration:none; font-weight:700; margin-left:6px;">
            <span><?php echo htmlspecialchars($latest_notice_banner['title']); ?></span>
        </a>
        <a href="<?php echo $base_path; ?>comm/view.php?id=<?php echo (int)$latest_notice_banner['no']; ?>&cat=notice" style="text-decoration: underline; color: #E0F7FA; margin-left: 10px; font-weight:700;">바로가기 &rarr;</a>
    <?php else: ?>
        <a href="<?php echo $base_path; ?>comm/view.php?id=6&cat=notice" style="color:#ffffff; text-decoration:none; font-weight:700; margin-left:6px;">
            <span>시스템 정기 점검 안내 (9/28 02:00~04:00)</span>
        </a>
        <a href="<?php echo $base_path; ?>comm/view.php?id=6&cat=notice" style="text-decoration: underline; color: #E0F7FA; margin-left: 10px; font-weight:700;">바로가기 &rarr;</a>
    <?php endif; ?>
</div>

<!-- Header Navigation (GNB Include) -->
<header class="header">
    <div class="header-container">
        <a href="<?php echo $base_path; ?>index.php" class="text-logo-brand">
            <div class="text-logo-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="text-logo-content">
                <div class="text-logo-main">TINTING PRO<span class="accent">.</span></div>
                <div class="text-logo-sub">VULUX MASTER</div>
            </div>
        </a>

        <ul class="gnb">
            <li>
                <a href="<?php echo $base_path; ?>about/index.php" class="<?php echo $current_page == 'about' ? 'active' : ''; ?>">
                    VULUX 대리점소개 <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="<?php echo $base_path; ?>about/index.php"><i class="fa-solid fa-building" style="color:var(--primary-dark);"></i> 대리점 소개</a></li>
                    <li><a href="<?php echo $base_path; ?>master/index.php"><i class="fa-solid fa-award" style="color:#D97706;"></i> 마스터 인증</a></li>
                    <li><a href="<?php echo $base_path; ?>about/location.php"><i class="fa-solid fa-location-dot" style="color:#059669;"></i> 오시는 길</a></li>
                </ul>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>diy/index.php" class="<?php echo ($current_page == 'services' || $current_page == 'diy') ? 'active' : ''; ?>">
                    VULUX 견적구매 <span class="gnb-badge hot">HOT</span>  <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="<?php echo $base_path; ?>diy/index.php"><i class="fa-solid fa-cart-shopping" style="color:#059669;"></i> 상품견적구매 </a></li>
                 </ul>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>gallery/index.php" class="<?php echo $current_page == 'gallery' ? 'active' : ''; ?>">
                    시공사례 <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="<?php echo $base_path; ?>gallery/index.php"><i class="fa-solid fa-border-all" style="color:var(--primary-dark);"></i> 전체 시공사례</a></li>
                    <?php if (!empty($gnb_mid_cats)): ?>
                        <?php foreach ($gnb_mid_cats as $gmc): ?>
                            <li><a href="<?php echo $base_path; ?>gallery/index.php?cat=<?php echo urlencode($gmc['cat_name']); ?>"><i class="fa-solid fa-chevron-right" style="font-size:0.75rem; color:#94A3B8;"></i> <?php echo htmlspecialchars($gmc['cat_name']); ?></a></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="<?php echo $base_path; ?>gallery/index.php?cat=아파트/주택베란다">아파트/주택베란다</a></li>
                        <li><a href="<?php echo $base_path; ?>gallery/index.php?cat=빌딩">빌딩</a></li>
                        <li><a href="<?php echo $base_path; ?>gallery/index.php?cat=자동차">자동차</a></li>
                        <li><a href="<?php echo $base_path; ?>gallery/index.php?cat=DIY자가설치">DIY자가설치</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>process/index.php#apply" class="<?php echo $current_page == 'process' ? 'active' : ''; ?>">
                    역경매 마켓 <span class="gnb-badge new">NEW</span> <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="<?php echo $base_path; ?>process/index.php#apply">무료 역경매 신청</a></li>
                    <li><a href="<?php echo $base_path; ?>process/index.php#live">진행중인 역경매 현황</a></li>
                </ul>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>comm/index.php" class="<?php echo ($current_page == 'community' || $current_page == 'reviews') ? 'active' : ''; ?>">
                    커뮤니티 <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="<?php echo $base_path; ?>comm/index.php?tab=notice"><i class="fa-solid fa-bullhorn" style="color:var(--primary-dark);"></i> 공지사항</a></li>
                    <li><a href="<?php echo $base_path; ?>comm/index.php?tab=news"><i class="fa-solid fa-newspaper" style="color:#D97706;"></i> 뉴스</a></li>
                    <li><a href="<?php echo $base_path; ?>comm/index.php?tab=faq"><i class="fa-solid fa-circle-question" style="color:#EA580C;"></i> FAQ</a></li>
                    <li><a href="<?php echo $base_path; ?>reviews/index.php"><i class="fa-solid fa-star" style="color:#F59E0B;"></i> 고객후기</a></li>
                </ul>
            </li>
        </ul>

        <div class="header-actions">
            <a href="tel:1544-0000" class="phone-call-btn" title="전화 상담 연결">
                <i class="fa-solid fa-phone"></i>
                <span>1544-0000</span>
            </a>
            <?php if (!empty($_SESSION['s_mem_id'])): ?>
                <a href="<?php echo $base_path; ?>mypage/index.php?tab=edit" class="btn btn-outline btn-sm" title="회원정보수정 바로가기"><i class="fa-solid fa-circle-user"></i> <?php echo htmlspecialchars($_SESSION['s_mem_name']); ?>님</a>
                <a href="<?php echo $base_path; ?>mypage/index.php" class="btn btn-outline btn-sm" style="color:#EF4444; border-color:#EF4444; font-weight:700;"><i class="fa-solid fa-user-gear"></i> 마이페이지</a>
                <a href="<?php echo $base_path; ?>member/logout.php" class="btn btn-outline btn-sm">로그아웃</a>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>member/login.php" class="btn btn-outline btn-sm">로그인/회원가입</a>
            <?php endif; ?>
            <button class="btn btn-primary btn-sm" onclick="openFreeVisitModal()">무료방문실측&amp;상담</button>
            <button class="menu-toggle" onclick="toggleMobileNav()" aria-label="모바일 메뉴 열기"><i class="fa-solid fa-bars"></i></button>
        </div>
    </div>
</header>

<!-- Off-Canvas Mobile Drawer Include -->
<div class="mobile-drawer-overlay" id="mobileDrawerOverlay" onclick="closeMobileDrawer()"></div>
<div class="mobile-drawer" id="mobileDrawer">
    <div>
        <div class="drawer-header">
            <a href="<?php echo $base_path; ?>index.php" class="text-logo-brand" onclick="closeMobileDrawer()">
                <div class="text-logo-icon" style="width: 32px; height: 32px; font-size: 0.95rem; border-radius: 8px;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="text-logo-content">
                    <div class="text-logo-main" style="font-size: 1.1rem;">TINTING PRO<span class="accent">.</span></div>
                    <div class="text-logo-sub" style="font-size: 0.58rem;">VULUX MASTER</div>
                </div>
            </a>
            <button class="drawer-close-btn" onclick="closeMobileDrawer()" aria-label="메뉴 닫기">&times;</button>
        </div>
        <ul class="drawer-gnb">
            <li>
                <a href="<?php echo $base_path; ?>about/index.php" onclick="closeMobileDrawer()">
                    <span><i class="fa-solid fa-building" style="color:var(--primary-dark); width:20px;"></i> VULUX 대리점소개</span>
                    <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; color: #94A3B8;"></i>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>diy/index.php" onclick="closeMobileDrawer()">
                    <span><i class="fa-solid fa-cart-shopping" style="color:#059669; width:20px;"></i> VULUX 견적구매 <span class="gnb-badge hot">HOT</span></span>
                    <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; color: #94A3B8;"></i>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>calculator/index.php" onclick="closeMobileDrawer()">
                    <span><i class="fa-solid fa-calculator" style="color:#0077B6; width:20px;"></i> 실시간 견적계산기</span>
                    <span class="gnb-badge hot">HOT</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>gallery/index.php" onclick="closeMobileDrawer()">
                    <span><i class="fa-solid fa-border-all" style="color:var(--primary-dark); width:20px;"></i> 시공사례</span>
                    <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; color: #94A3B8;"></i>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>process/index.php#apply" onclick="closeMobileDrawer()">
                    <span><i class="fa-solid fa-gavel" style="color:#D97706; width:20px;"></i> 역경매 마켓</span>
                    <span class="gnb-badge new">NEW</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>comm/index.php" onclick="closeMobileDrawer()">
                    <span><i class="fa-solid fa-comments" style="color:#EA580C; width:20px;"></i> 커뮤니티</span>
                    <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; color: #94A3B8;"></i>
                </a>
                <div style="padding-left:24px; margin-top:6px; display:flex; flex-direction:column; gap:8px;">
                    <a href="<?php echo $base_path; ?>comm/index.php?tab=notice" onclick="closeMobileDrawer()" style="font-size:0.88rem; color:#475569;">▪ 공지사항</a>
                    <a href="<?php echo $base_path; ?>comm/index.php?tab=news" onclick="closeMobileDrawer()" style="font-size:0.88rem; color:#475569;">▪ 뉴스</a>
                    <a href="<?php echo $base_path; ?>comm/index.php?tab=faq" onclick="closeMobileDrawer()" style="font-size:0.88rem; color:#475569;">▪ FAQ</a>
                    <a href="<?php echo $base_path; ?>reviews/index.php" onclick="closeMobileDrawer()" style="font-size:0.88rem; color:#475569;">▪ 고객후기</a>
                </div>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>about/location.php" onclick="closeMobileDrawer()">
                    <span><i class="fa-solid fa-location-dot" style="color:#059669; width:20px;"></i> 오시는 길</span>
                    <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; color: #94A3B8;"></i>
                </a>
            </li>
        </ul>
    </div>
    <div class="drawer-actions">
        <a href="tel:1544-0000" class="btn btn-primary" style="width: 100%;">
            <i class="fa-solid fa-phone"></i> 1544-0000 전화 연결
        </a>
        <button class="btn btn-accent" style="width: 100%;" onclick="closeMobileDrawer(); openFreeVisitModal();">
            <i class="fa-solid fa-clipboard-user"></i> 무료방문실측 &amp; 상담 신청
        </button>
        <?php if (!empty($_SESSION['s_mem_id'])): ?>
            <a href="<?php echo $base_path; ?>mypage/index.php" class="btn btn-outline" style="width: 100%; color:#EF4444; border-color:#EF4444; font-weight:700;">
                <i class="fa-solid fa-user-gear"></i> 마이페이지 (<?php echo htmlspecialchars($_SESSION['s_mem_name']); ?>님)
            </a>
            <a href="<?php echo $base_path; ?>member/logout.php" class="btn btn-outline" style="width: 100%;">
                로그아웃
            </a>
        <?php else: ?>
            <a href="<?php echo $base_path; ?>member/login.php" class="btn btn-outline" style="width: 100%;">
                로그인 / 회원가입
            </a>
        <?php endif; ?>
    </div>
</div>
