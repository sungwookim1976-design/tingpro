<?php
$page_title = "틴팅 마스터 | 아파트 베란다 & 건물 썬팅 전문 플랫폼";
$active_menu = "home";
$path_prefix = "./";

include_once __DIR__ . "/inc/dbconn.php";
include_once __DIR__ . "/inc/head.php";
include_once __DIR__ . "/inc/header.php";
?>

<!-- Main Hero Section (Full Width High Clarity Slider Banner with 5-Cut Backgrounds) -->
<style>
    #mainHeroSection {
        position: relative;
        width: 100%;
        min-height: 600px;
        overflow: hidden;
        background: #0B132B;
        border-bottom: 1px solid #1E293B;
    }
    #heroSliderContainer {
        position: relative;
        width: 100%;
        min-height: 600px;
        display: flex;
        align-items: center;
    }
    .hero-slide {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        display: flex !important;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        z-index: 1;
        pointer-events: none;
        transition: opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1), transform 0.6s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.6s ease;
        transform: scale(0.98);
    }
    .hero-slide.active {
        opacity: 1 !important;
        visibility: visible !important;
        z-index: 2 !important;
        pointer-events: auto !important;
        transform: scale(1);
    }
    .hero-slide .slide-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        z-index: 0;
        filter: brightness(1.02) contrast(1.05);
        transition: transform 6s ease;
    }
    .hero-slide.active .slide-bg {
        transform: scale(1.04);
    }
    /* 배경 농도(어두움)를 훨씬 밝게 낮춤 (overlay opacity low) */
    .hero-slide .slide-overlay {
        position: absolute;
        inset: 0;
        z-index: 1;
        background: linear-gradient(180deg, rgba(11, 19, 43, 0.15) 0%, rgba(11, 19, 43, 0.42) 100%);
    }
    .hero-slide .slide-content {
        position: relative;
        z-index: 4;
        width: 100%;
        max-width: 980px;
        margin: 0 auto;
        padding: 60px 24px 130px 24px;
        text-align: center;
        color: #FFFFFF;
    }
    .hero-slide .slide-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(0, 119, 182, 0.65);
        border: 1px solid rgba(56, 189, 248, 0.9);
        color: #FFFFFF;
        padding: 8px 24px;
        border-radius: 9999px;
        font-size: 0.95rem;
        font-weight: 800;
        margin-bottom: 22px;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
        text-shadow: 0 1px 3px rgba(0,0,0,0.8);
    }
    .hero-slide .slide-content h1 {
        font-size: clamp(2.2rem, 4.2vw, 3.4rem);
        font-weight: 900;
        line-height: 1.25;
        letter-spacing: -0.5px;
        margin-bottom: 20px;
        word-break: keep-all;
        color: #FFFFFF;
        text-shadow: 0 3px 15px rgba(0, 0, 0, 0.95), 0 1px 3px rgba(0, 0, 0, 0.9);
    }
    .hero-slide .slide-content h1 .highlight {
        color: #38BDF8;
        font-weight: 900;
        text-shadow: 0 0 25px rgba(56, 189, 248, 0.9), 0 3px 12px rgba(0, 0, 0, 0.95);
    }
    .hero-slide .slide-content p.sub-desc {
        color: #FFFFFF;
        font-size: clamp(1.05rem, 1.7vw, 1.25rem);
        margin: 0 auto 34px;
        line-height: 1.7;
        word-break: keep-all;
        max-width: 780px;
        font-weight: 700;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.95), 0 1px 4px rgba(0, 0, 0, 0.9);
    }
    .hero-slide .slide-cta-group {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .btn-slide-outline {
        background: rgba(15, 23, 42, 0.55);
        color: #FFFFFF;
        border: 1.5px solid rgba(255, 255, 255, 0.8);
        padding: 14px 32px;
        border-radius: 9999px;
        font-weight: 800;
        font-size: 1.05rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.25s ease;
        backdrop-filter: blur(8px);
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.35);
        text-shadow: 0 1px 3px rgba(0,0,0,0.8);
    }
    .btn-slide-outline:hover {
        background: #FFFFFF;
        color: #0F172A;
        text-shadow: none;
    }
    .hero-dot-btn {
        transition: all 0.3s ease;
    }
    .hero-dot-btn:hover {
        transform: scale(1.2);
    }
    @media (max-width: 768px) {
        #mainHeroSection, #heroSliderContainer { min-height: 480px; }
        .hero-slide .slide-content { padding: 36px 14px 100px 14px; }
        .hero-slide .slide-badge { font-size: 0.78rem; padding: 6px 14px; margin-bottom: 14px; }
        .hero-slide .slide-content h1 { font-size: 1.6rem; margin-bottom: 12px; }
        .hero-slide .slide-content p.sub-desc { font-size: 0.88rem; margin-bottom: 20px; line-height: 1.5; }
        .hero-slide .slide-cta-group { flex-direction: column; gap: 10px; width: 100%; padding: 0 10px; }
        .hero-slide .slide-cta-group .btn, 
        .hero-slide .slide-cta-group .btn-slide-outline { width: 100%; justify-content: center; padding: 12px 20px !important; font-size: 0.95rem !important; }
        .section { padding: 40px 14px !important; }
        .section-title { font-size: 1.65rem !important; }
    }
    @media (max-width: 992px) {
        .gallery-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 16px !important; }
    }
    @media (max-width: 640px) {
        .gallery-grid { grid-template-columns: 1fr !important; gap: 16px !important; }
        .hero-stats-box { padding: 12px 8px !important; margin: -30px 12px 20px 12px !important; border-radius: 14px !important; }
        .hero-stats-box .stat-num { font-size: 1.35rem !important; }
        .hero-stats-box .stat-label { font-size: 0.75rem !important; }
    }
</style>

<section id="mainHeroSection">
    <div id="heroSliderContainer">
        
        <!-- Slide 1: 아파트 & 주거 틴팅 -->
        <div class="hero-slide active">
            <div class="slide-bg" style="background-image: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80');"></div>
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <div class="slide-badge">
                    <i class="fa-solid fa-house-chimney-window"></i> 아파트 베란다 &amp; 주거 열차단 틴팅
                </div>
                <h1>
                    여름은 시원하게, 겨울은 따뜻하게<br>
                    <span class="highlight">아파트 프리미엄 단열 필름</span>
                </h1>
                <p class="sub-desc">
                    실내 온도 상승 차단부터 자외선 99.9% 완벽 방어!<br>
                    커튼 없이 시원하고 쾌적한 아파트 뷰 조망을 경험해 보세요.
                </p>
                <div class="slide-cta-group">
                    <a href="./calculator/index.php" class="btn btn-accent" style="padding:14px 32px; font-size:1.05rem; box-shadow:0 8px 25px rgba(255,107,53,0.5);">
                        <i class="fa-solid fa-calculator"></i> 1초 실시간 견적 확인
                    </a>
                    <a href="./gallery/index.php" class="btn-slide-outline">
                        <i class="fa-solid fa-circle-play"></i> 시공 전후 비교 보기
                    </a>
                </div>
            </div>
        </div>

        <!-- Slide 2: 건물 & 상가 오피스 -->
        <div class="hero-slide">
            <div class="slide-bg" style="background-image: url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1600&q=80');"></div>
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <div class="slide-badge">
                    <i class="fa-solid fa-building"></i> 빌딩 &amp; 상가 오피스 시선차단 썬팅
                </div>
                <h1>
                    사생활 보호 &amp; 에너지 비용 절감<br>
                    <span class="highlight">건물·상가 전문 틴팅 솔루션</span>
                </h1>
                <p class="sub-desc">
                    외부 시선 100% 차단으로 업무 몰입도 UP!<br>
                    냉난방 에너지를 최대 30% 절감하는 맞춤형 건축 틴팅 시공.
                </p>
                <div class="slide-cta-group">
                    <a href="./calculator/index.php" class="btn btn-accent" style="padding:14px 32px; font-size:1.05rem; box-shadow:0 8px 25px rgba(255,107,53,0.5);">
                        <i class="fa-solid fa-calculator"></i> 1초 실시간 견적 확인
                    </a>
                    <a href="./gallery/index.php" class="btn-slide-outline">
                        <i class="fa-solid fa-images"></i> 건물 시공 갤러리
                    </a>
                </div>
            </div>
        </div>

        <!-- Slide 3: 자동차 & 프리미엄 차량 -->
        <div class="hero-slide">
            <div class="slide-bg" style="background-image: url('https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=1600&q=80');"></div>
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <div class="slide-badge">
                    <i class="fa-solid fa-car"></i> 자동차 &amp; 프리미엄 차량 틴팅
                </div>
                <h1>
                    눈부심 방지 &amp; 선명한 시야 확보<br>
                    <span class="highlight">전국 최저가 자동차 틴팅 비교</span>
                </h1>
                <p class="sub-desc">
                    국산차·수입차 맞춤 프리미엄 시공 마스터 매칭!<br>
                    야간 운전에도 눈이 편안한 최고급 열차단 필름 시공.
                </p>
                <div class="slide-cta-group">
                    <a href="./calculator/index.php" class="btn btn-accent" style="padding:14px 32px; font-size:1.05rem; box-shadow:0 8px 25px rgba(255,107,53,0.5);">
                        <i class="fa-solid fa-calculator"></i> 차종별 견적 뽑기
                    </a>
                    <a href="./gallery/index.php" class="btn-slide-outline">
                        <i class="fa-solid fa-star"></i> 고객 실제 시공후기
                    </a>
                </div>
            </div>
        </div>

        <!-- Slide 4: DIY 자가시공 키트 -->
        <div class="hero-slide">
            <div class="slide-bg" style="background-image: url('https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1600&q=80');"></div>
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <div class="slide-badge">
                    <i class="fa-solid fa-toolbox"></i> 초간단 DIY 자가시공 필름 키트
                </div>
                <h1>
                    창문 치수만 입력하면 정밀 재단 배송<br>
                    <span class="highlight">셀프 틴팅 맞춤 재단 DIY 키트</span>
                </h1>
                <p class="sub-desc">
                    누구나 10분 만에 전문가처럼 완벽 시공 가능!<br>
                    시공 도구 풀세트 무상 증정 및 가이드 영상 제공.
                </p>
                <div class="slide-cta-group">
                    <a href="./calculator/index.php" class="btn btn-accent" style="padding:14px 32px; font-size:1.05rem; box-shadow:0 8px 25px rgba(255,107,53,0.5);">
                        <i class="fa-solid fa-cart-shopping"></i> DIY 키트 주문하기
                    </a>
                    <a href="./gallery/index.php" class="btn-slide-outline">
                        <i class="fa-solid fa-play"></i> DIY 셀프 시공방법 영상
                    </a>
                </div>
            </div>
        </div>

        <!-- Slide 5: 10년 품질 보증 마스터 -->
        <div class="hero-slide">
            <div class="slide-bg" style="background-image: url('https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1600&q=80');"></div>
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <div class="slide-badge">
                    <i class="fa-solid fa-award"></i> VULUX 10년 정품 품질 보증
                </div>
                <h1>
                    탈색·변색 없는 10년 무상 A/S 보증<br>
                    <span class="highlight">대한민국 No.1 틴팅 마스터</span>
                </h1>
                <p class="sub-desc">
                    전국 검증 마스터 시공 네트워크망 구축.<br>
                    모든 시공 건 100% 모바일 정품 품질 보증서 발급.
                </p>
                <div class="slide-cta-group">
                    <a href="./calculator/index.php" class="btn btn-accent" style="padding:14px 32px; font-size:1.05rem; box-shadow:0 8px 25px rgba(255,107,53,0.5);">
                        <i class="fa-solid fa-user-check"></i> 검증 마스터 매칭
                    </a>
                    <a href="./comm/index.php" class="btn-slide-outline">
                        <i class="fa-solid fa-certificate"></i> 품질 보증 안내
                    </a>
                </div>
            </div>
        </div>

        <!-- Controls and Navigation Dots (농도 낮추고 반투명하게 개선) -->
        <div style="position:absolute; bottom:30px; left:50%; transform:translateX(-50%); z-index:10; display:flex; align-items:center; gap:16px; background:rgba(15, 23, 42, 0.45); border:1px solid rgba(255,255,255,0.3); padding:8px 22px; border-radius:9999px; backdrop-filter:blur(12px); box-shadow:0 8px 25px rgba(0,0,0,0.3);">
            <button onclick="prevHeroSlide()" style="background:none; border:none; color:#FFFFFF; font-size:1.1rem; cursor:pointer; padding:4px 8px; transition:all 0.2s;" title="이전 슬라이드">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            
            <div style="display:flex; gap:8px; align-items:center;">
                <button class="hero-dot hero-dot-btn active" onclick="setHeroSlide(0)" style="width:28px; height:8px; border-radius:4px; border:none; background:#38BDF8; cursor:pointer; box-shadow:0 0 10px rgba(56,189,248,0.8);"></button>
                <button class="hero-dot hero-dot-btn" onclick="setHeroSlide(1)" style="width:10px; height:8px; border-radius:4px; border:none; background:rgba(255,255,255,0.5); cursor:pointer;"></button>
                <button class="hero-dot hero-dot-btn" onclick="setHeroSlide(2)" style="width:10px; height:8px; border-radius:4px; border:none; background:rgba(255,255,255,0.5); cursor:pointer;"></button>
                <button class="hero-dot hero-dot-btn" onclick="setHeroSlide(3)" style="width:10px; height:8px; border-radius:4px; border:none; background:rgba(255,255,255,0.5); cursor:pointer;"></button>
                <button class="hero-dot hero-dot-btn" onclick="setHeroSlide(4)" style="width:10px; height:8px; border-radius:4px; border:none; background:rgba(255,255,255,0.5); cursor:pointer;"></button>
            </div>
            
            <button onclick="nextHeroSlide()" style="background:none; border:none; color:#FFFFFF; font-size:1.1rem; cursor:pointer; padding:4px 8px; transition:all 0.2s;" title="다음 슬라이드">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>

    </div>

    <!-- Floating High-Contrast Stats Bar (Bottom Center Overlay - 농도를 한층 낮추고 더 투명하고 시원하게) -->
    <div style="position:relative; z-index:5; max-width:960px; margin:-40px auto 30px auto; padding:0 16px;">
        <div class="hero-stats-box" style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px; background:rgba(15, 23, 42, 0.85); border:1px solid rgba(56, 189, 248, 0.5); border-radius:20px; padding:22px 28px; box-shadow:0 15px 35px rgba(0,0,0,0.35); backdrop-filter:blur(14px);">
            <div style="text-align:center;">
                <div class="stat-num" style="font-size:2rem; font-weight:900; color:#38BDF8; text-shadow:0 0 14px rgba(56, 189, 248, 0.6);">15,400+</div>
                <div class="stat-label" style="font-size:0.9rem; color:#FFFFFF; font-weight:700; margin-top:4px;">누적 시공 건수</div>
            </div>
            <div style="text-align:center; border-left:1px solid rgba(255,255,255,0.2); border-right:1px solid rgba(255,255,255,0.2);">
                <div class="stat-num" style="font-size:2rem; font-weight:900; color:#38BDF8; text-shadow:0 0 14px rgba(56, 189, 248, 0.6);">99.4%</div>
                <div class="stat-label" style="font-size:0.9rem; color:#FFFFFF; font-weight:700; margin-top:4px;">고객 만족도</div>
            </div>
            <div style="text-align:center;">
                <div class="stat-num" style="font-size:2rem; font-weight:900; color:#38BDF8; text-shadow:0 0 14px rgba(56, 189, 248, 0.6);">10년</div>
                <div class="stat-label" style="font-size:0.9rem; color:#FFFFFF; font-weight:700; margin-top:4px;">무상 A/S 보증</div>
            </div>
        </div>
    </div>

    <script>
        let currentHeroSlide = 0;
        let heroSlideTimer = null;

        function setHeroSlide(index) {
            const $slides = $('.hero-slide');
            const $dots = $('.hero-dot');

            if (!$slides.length) return;

            $slides.removeClass('active').eq(index).addClass('active');

            $dots.each(function(i) {
                if (i === index) {
                    $(this).css({ 'background': '#38BDF8', 'width': '28px' });
                } else {
                    $(this).css({ 'background': 'rgba(255, 255, 255, 0.4)', 'width': '10px' });
                }
            });

            currentHeroSlide = index;
            resetHeroTimer();
        }

        function nextHeroSlide() {
            let next = (currentHeroSlide + 1) % 5;
            setHeroSlide(next);
        }

        function prevHeroSlide() {
            let prev = (currentHeroSlide - 1 + 5) % 5;
            setHeroSlide(prev);
        }

        function resetHeroTimer() {
            if (heroSlideTimer) clearInterval(heroSlideTimer);
            heroSlideTimer = setInterval(function() { nextHeroSlide(); }, 4500);
        }

        $(document).ready(function() {
            resetHeroTimer();
        });
    </script>
</section>

<!-- Section: Notice, FAQ & News Tab Section -->
<?php
$main_notices = sql_one('community_posts', '*', "and board_type='notice' and state=1 order by no desc limit 5");
$main_faqs    = sql_one('community_posts', '*', "and board_type='faq' and state=1 order by no desc limit 5");
$main_news    = sql_one('community_posts', '*', "and board_type='news' and state=1 order by no desc limit 5");
?>
<section class="section" id="notice-faq-section" style="background:#FFFFFF; padding:60px 24px; border-bottom:1px solid #E2E8F0;">
    <div class="container" style="max-width:1280px; margin:0 auto;">
        <div style="background:#F8FAFC; border:1px solid #BAE6FD; border-radius:var(--radius-lg); padding:32px; box-shadow:0 10px 30px rgba(0, 180, 216, 0.06);">
            
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #E0F7FA; padding-bottom:16px; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
                <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                    <h3 style="font-size:1.3rem; font-weight:900; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-bullhorn" style="color:var(--primary-dark);"></i> 틴팅프로 새소식 &amp; 커뮤니티
                    </h3>
                    <div style="display:flex; gap:8px;">
                        <button id="tabBtnNotice" class="btn btn-sm btn-primary" onclick="switchHeroTab('notice')" style="padding:6px 16px; font-size:0.9rem;">
                            공지사항
                        </button>
                        <button id="tabBtnFaq" class="btn btn-sm btn-outline" onclick="switchHeroTab('faq')" style="padding:6px 16px; font-size:0.9rem;">
                            자주 묻는 질문(FAQ)
                        </button>
                        <button id="tabBtnNews" class="btn btn-sm btn-outline" onclick="switchHeroTab('news')" style="padding:6px 16px; font-size:0.9rem;">
                            뉴스
                        </button>
                    </div>
                </div>
                <a href="./comm/index.php" style="font-size:0.85rem; color:var(--primary-dark); font-weight:700; text-decoration:none;">전체보기 +</a>
            </div>

            <!-- 1. 공지사항 탭 리스트 -->
            <ul id="heroTabNoticeList" style="display:block; list-style:none; padding:0; margin:0;">
                <?php if (empty($main_notices)): ?>
                    <li style="padding:20px; text-align:center; color:#94A3B8;">등록된 공지사항이 없습니다.</li>
                <?php else: foreach ($main_notices as $n): ?>
                    <li style="display:flex; justify-content:space-between; align-items:center; padding:12px 8px; border-bottom:1px dashed #E2E8F0; font-size:0.95rem;">
                        <a href="./comm/view.php?id=<?php echo $n['no']; ?>&amp;cat=notice" style="font-weight:700; color:#0F172A; text-decoration:none; display:flex; align-items:center; gap:10px;">
                            <span style="background:#E0F7FA; color:var(--primary-dark); font-size:0.75rem; padding:3px 8px; border-radius:4px; font-weight:800;">공지</span>
                            <?php echo htmlspecialchars($n['title']); ?>
                        </a>
                        <span style="font-size:0.85rem; color:#94A3B8;"><?php echo date('Y.m.d', strtotime($n['reg_date'])); ?></span>
                    </li>
                <?php endforeach; endif; ?>
            </ul>

            <!-- 2. FAQ 탭 리스트 -->
            <ul id="heroTabFaqList" style="display:none; list-style:none; padding:0; margin:0;">
                <?php if (empty($main_faqs)): ?>
                    <li style="padding:20px; text-align:center; color:#94A3B8;">등록된 FAQ가 없습니다.</li>
                <?php else: foreach ($main_faqs as $f): ?>
                    <li style="display:flex; justify-content:space-between; align-items:center; padding:12px 8px; border-bottom:1px dashed #E2E8F0; font-size:0.95rem;">
                        <a href="./comm/view.php?id=<?php echo $f['no']; ?>&amp;cat=faq" style="font-weight:700; color:#0F172A; text-decoration:none; display:flex; align-items:center; gap:10px;">
                            <span style="background:#FFEDD5; color:#EA580C; font-size:0.75rem; padding:3px 8px; border-radius:4px; font-weight:800;">Q</span>
                            <?php echo htmlspecialchars($f['title']); ?>
                        </a>
                        <span style="font-size:0.85rem; color:#94A3B8;"><?php echo date('Y.m.d', strtotime($f['reg_date'])); ?></span>
                    </li>
                <?php endforeach; endif; ?>
            </ul>

            <!-- 3. 뉴스 탭 리스트 -->
            <ul id="heroTabNewsList" style="display:none; list-style:none; padding:0; margin:0;">
                <?php if (empty($main_news)): ?>
                    <li style="padding:20px; text-align:center; color:#94A3B8;">등록된 뉴스가 없습니다.</li>
                <?php else: foreach ($main_news as $w): ?>
                    <li style="display:flex; justify-content:space-between; align-items:center; padding:12px 8px; border-bottom:1px dashed #E2E8F0; font-size:0.95rem;">
                        <a href="./comm/view.php?id=<?php echo $w['no']; ?>&amp;cat=news" style="font-weight:700; color:#0F172A; text-decoration:none; display:flex; align-items:center; gap:10px;">
                            <span style="background:#ECFDF5; color:#059669; font-size:0.75rem; padding:3px 8px; border-radius:4px; font-weight:800;">뉴스</span>
                            <?php echo htmlspecialchars($w['title']); ?>
                        </a>
                        <span style="font-size:0.85rem; color:#94A3B8;"><?php echo date('Y.m.d', strtotime($w['reg_date'])); ?></span>
                    </li>
                <?php endforeach; endif; ?>
            </ul>

        </div>
    </div>
</section>

<script>
    function switchHeroTab(tabName) {
        $('#heroTabNoticeList, #heroTabFaqList, #heroTabNewsList').hide();
        $('#tabBtnNotice, #tabBtnFaq, #tabBtnNews').attr('class', 'btn btn-sm btn-outline');

        if (tabName === 'notice') {
            $('#heroTabNoticeList').stop(true, true).fadeIn(200);
            $('#tabBtnNotice').attr('class', 'btn btn-sm btn-primary');
        } else if (tabName === 'faq') {
            $('#heroTabFaqList').stop(true, true).fadeIn(200);
            $('#tabBtnFaq').attr('class', 'btn btn-sm btn-primary');
        } else if (tabName === 'news') {
            $('#heroTabNewsList').stop(true, true).fadeIn(200);
            $('#tabBtnNews').attr('class', 'btn btn-sm btn-primary');
        }
    }
</script>


<!-- Section: Category Portfolio Gallery (GNB 시공사례 서브메뉴 ai_category DB 연동) -->
<?php
// ai_category 중분류 (depth=2) 카테고리 로딩 (GNB 시공사례 서브메뉴 연동)
$main_mid_cats = array();
$res_main_cat = @mysqli_query($conn, "SELECT * FROM ai_category WHERE depth=2 AND use_yn=1 ORDER BY sort_order ASC, idx ASC");
if ($res_main_cat && mysqli_num_rows($res_main_cat) > 0) {
    while ($r = mysqli_fetch_assoc($res_main_cat)) {
        $main_mid_cats[] = $r;
    }
}

$main_cases = sql_one('auctions', '*', "order by no desc limit 30");

$img_thumb_map = [
    '아파트'    => ['./imgs/hero_balcony.png', './imgs/ba0.png', './imgs/ba1.png', './imgs/ba2.png', './imgs/ba3.png'],
    '상가/빌딩' => ['./imgs/building_office.png', './imgs/craftsman.png', './imgs/durability_sample.png', './imgs/heat_reflection_sample.png'],
    '건물/빌딩' => ['./imgs/building_office.png', './imgs/craftsman.png', './imgs/durability_sample.png', './imgs/heat_reflection_sample.png'],
    '자동차'    => ['./imgs/use.png', './imgs/tr_use.png', './imgs/ba0.png', './imgs/ba1.png'],
    'default'   => ['./imgs/hero_balcony.png', './imgs/building_office.png', './imgs/use.png']
];

$badge_style_map = [
    '아파트'    => ['bg' => '#E0F7FA', 'color' => 'var(--primary-dark)'],
    '상가/빌딩' => ['bg' => '#FEF3C7', 'color' => '#D97706'],
    '건물/빌딩' => ['bg' => '#FEF3C7', 'color' => '#D97706'],
    '자동차'    => ['bg' => '#ECFDF5', 'color' => '#059669'],
    '오피스텔'  => ['bg' => '#EDE9FE', 'color' => '#7C3AED'],
    '단독주택'  => ['bg' => '#ECFDF5', 'color' => '#059669']
];

$total_case_cnt = is_array($main_cases) ? count($main_cases) : 0;
?>
<section class="section" id="gallery-category-section" style="background:#F8FAFC; padding:80px 24px;">
    <div class="container" style="max-width:1280px; margin:0 auto;">
        <div class="section-header" style="text-align:center; max-width:760px; margin:0 auto 40px auto;">
            <span class="section-subtitle" style="font-size:0.9rem; font-weight:800; color:var(--primary-dark); text-transform:uppercase; letter-spacing:1.5px; margin-bottom:8px; display:block;">Portfolio Gallery</span>
            <h2 class="section-title" style="font-size:2.4rem; font-weight:900; color:var(--secondary); margin-bottom:12px;">카테고리별 시공 사례</h2>
            <p class="section-desc" style="color:var(--text-muted); font-size:1.05rem;">VULUX 공식 인증 마스터의 완벽한 시공 현장을 카테고리별로 확인해 보세요.</p>
        </div>

        <!-- GNB 시공사례 서브메뉴 연동 Filter Category Tabs -->
        <div class="horizontal-scroll-tab" style="display:flex; justify-content:center; gap:10px; margin-bottom:30px;">
            <button class="btn btn-primary gallery-cat-btn active" onclick="filterGallery('all', '', this)" style="padding:10px 22px; font-size:0.92rem; font-weight:800;">
                <i class="fa-solid fa-border-all"></i> 전체 시공사례
            </button>
            <?php if (!empty($main_mid_cats)): ?>
                <?php foreach ($main_mid_cats as $mc_cat): 
                    $c_name = $mc_cat['cat_name'];
                    $icon_cls = 'fa-tag';
                    if (mb_strpos($c_name, '아파트') !== false || mb_strpos($c_name, '주택') !== false) $icon_cls = 'fa-building-user';
                    elseif (mb_strpos($c_name, '빌딩') !== false || mb_strpos($c_name, '건물') !== false) $icon_cls = 'fa-city';
                    elseif (mb_strpos($c_name, '자동차') !== false || mb_strpos($c_name, '차량') !== false) $icon_cls = 'fa-car';
                    elseif (mb_strpos($c_name, 'DIY') !== false || mb_strpos($c_name, '자가') !== false) $icon_cls = 'fa-wrench';
                ?>
                    <button class="btn btn-outline gallery-cat-btn" onclick="filterGallery('<?php echo htmlspecialchars($c_name); ?>', '<?php echo urlencode($c_name); ?>', this)" style="padding:10px 22px; font-size:0.92rem; font-weight:800;">
                        <i class="fa-solid <?php echo $icon_cls; ?>"></i> <?php echo htmlspecialchars($c_name); ?>
                    </button>
                <?php endforeach; ?>
            <?php else: ?>
                <button class="btn btn-outline gallery-cat-btn" onclick="filterGallery('아파트', '아파트/주택베란다', this)" style="padding:10px 22px; font-size:0.92rem; font-weight:800;">
                    <i class="fa-solid fa-building-user"></i> 아파트/주택베란다
                </button>
                <button class="btn btn-outline gallery-cat-btn" onclick="filterGallery('빌딩', '빌딩', this)" style="padding:10px 22px; font-size:0.92rem; font-weight:800;">
                    <i class="fa-solid fa-city"></i> 빌딩
                </button>
                <button class="btn btn-outline gallery-cat-btn" onclick="filterGallery('자동차', '자동차', this)" style="padding:10px 22px; font-size:0.92rem; font-weight:800;">
                    <i class="fa-solid fa-car"></i> 자동차
                </button>
                <button class="btn btn-outline gallery-cat-btn" onclick="filterGallery('DIY', 'DIY자가설치', this)" style="padding:10px 22px; font-size:0.92rem; font-weight:800;">
                    <i class="fa-solid fa-wrench"></i> DIY자가설치
                </button>
            <?php endif; ?>
        </div>

        <!-- Dynamic DB Gallery Grid Cards -->
        <div class="gallery-grid" id="galleryGrid" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:24px;">
            <?php 
            if (empty($main_cases)):
            ?>
                <div style="grid-column:1/-1; text-align:center; padding:60px 20px; background:#fff; border-radius:12px; border:1px solid #E2E8F0; color:var(--text-muted);">
                    등록된 시공사례가 없습니다.
                </div>
            <?php
            else:
                foreach ($main_cases as $idx => $c):
                    $type = $c['space_type'];
                    $t_imgs = isset($img_thumb_map[$type]) ? $img_thumb_map[$type] : $img_thumb_map['default'];
                    $thumb_img = $t_imgs[$idx % count($t_imgs)];
                    
                    $b_style = isset($badge_style_map[$type]) ? $badge_style_map[$type] : array('bg' => '#E0F7FA', 'color' => 'var(--primary-dark)');
                    $addr_parts = preg_split('/\s+/', trim($c['addr']));
                    $short_addr = implode(' ', array_slice($addr_parts, 0, 2));
                    $detail_link = "./gallery/index.php?cat=" . urlencode($type) . "&no=" . (int)$c['no'];
            ?>
                <div class="gallery-card g-item" data-cat="<?php echo htmlspecialchars($type); ?>" onclick="location.href='<?php echo $detail_link; ?>'" style="background:white; border:1px solid #E2E8F0; border-radius:var(--radius-md); overflow:hidden; box-shadow:var(--shadow-sm); transition:transform 0.25s ease, box-shadow 0.25s ease; cursor:pointer;">
                    <div style="position:relative; overflow:hidden;">
                        <img src="<?php echo $thumb_img; ?>" alt="<?php echo htmlspecialchars($type); ?>" style="width:100%; height:200px; object-fit:cover; transition:transform 0.3s ease;">
                        <span style="position:absolute; top:12px; left:12px; background:<?php echo $b_style['bg']; ?>; color:<?php echo $b_style['color']; ?>; font-size:0.75rem; font-weight:800; padding:4px 10px; border-radius:4px; box-shadow:0 2px 6px rgba(0,0,0,0.1); z-index:2;"><?php echo htmlspecialchars($type); ?></span>
                    </div>
                    <div style="padding:20px;">
                        <h4 style="font-size:1.05rem; font-weight:800; margin:0 0 8px 0; color:#0F172A; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; height:2.8em;">
                            <?php echo htmlspecialchars($short_addr); ?> <?php echo (int)$c['py'] > 1 ? (int)$c['py'] . '평 ' : ''; ?><?php echo htmlspecialchars($type); ?> 썬팅
                        </h4>
                        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
                            <span>시공비: <strong style="color:var(--primary-dark); font-weight:800;"><?php echo number_format(isset($c['desired_price']) ? $c['desired_price'] : 0); ?>원</strong></span>
                            <span style="font-size:0.8rem; color:#94A3B8;"><i class="fa-regular fa-calendar-check"></i> <?php echo substr($c['reg_date'], 0, 10); ?></span>
                        </p>
                        <div style="border-top:1px dashed #E2E8F0; padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:0.8rem; color:#64748B; line-height:1.3; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:170px;"><?php echo htmlspecialchars($c['memo'] ? $c['memo'] : 'VULUX 전문 시공 완료'); ?></span>
                            <a href="<?php echo $detail_link; ?>" class="btn btn-outline btn-sm" onclick="event.stopPropagation();" style="padding:4px 12px; font-size:0.8rem; font-weight:800; flex-shrink:0;">
                                상세보기 <i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div style="text-align:center; margin-top:40px; display:flex; justify-content:center; gap:12px; flex-wrap:wrap;">
            <button id="galleryLoadMoreBtn" class="btn btn-outline" onclick="loadMoreGallery()" style="padding:12px 36px; font-weight:800;">
                시공사례 더보기 (2줄 추가) <i class="fa-solid fa-chevron-down"></i>
            </button>
            <a id="galleryMoreLink" href="./gallery/index.php" class="btn btn-primary" style="padding:12px 36px; font-weight:800;">
                실제 시공사례 전체보기 <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        <p style="text-align:center; font-size:0.8rem; color:var(--text-muted); margin-top:10px;">* 시공사례 카드를 클릭하면 해당 시공 현장의 상세 정보 페이지로 이동합니다.</p>
    </div>
</section>

<script>
    let currentGalleryCat = 'all';
    let currentCatParam = '';
    let visibleCount = 6;

    function filterGallery(categoryName, catUrlParam, btnElem) {
        currentGalleryCat = categoryName;
        currentCatParam = catUrlParam;
        visibleCount = 6;

        document.querySelectorAll('.gallery-cat-btn').forEach(btn => {
            btn.className = 'btn btn-outline gallery-cat-btn';
        });
        if (btnElem) {
            btnElem.className = 'btn btn-primary gallery-cat-btn';
        }

        const moreLink = document.getElementById('galleryMoreLink');
        if (moreLink) {
            moreLink.href = currentCatParam ? ('./gallery/index.php?cat=' + currentCatParam) : './gallery/index.php';
        }

        renderGalleryItems();
    }

    function renderGalleryItems() {
        const items = document.querySelectorAll('.g-item');
        let matchedCount = 0;
        let shownCount = 0;

        items.forEach(item => {
            const itemCat = item.getAttribute('data-cat') || '';
            let matches = false;

            if (currentGalleryCat === 'all') {
                matches = true;
            } else if (itemCat.includes(currentGalleryCat) || currentGalleryCat.includes(itemCat)) {
                matches = true;
            } else if (currentGalleryCat.includes('아파트') && (itemCat.includes('아파트') || itemCat.includes('주택'))) {
                matches = true;
            } else if (currentGalleryCat.includes('빌딩') && (itemCat.includes('빌딩') || itemCat.includes('상가') || itemCat.includes('건물'))) {
                matches = true;
            } else if (currentGalleryCat.includes('자동차') && (itemCat.includes('자동차') || itemCat.includes('차량'))) {
                matches = true;
            }

            if (matches) {
                matchedCount++;
                if (shownCount < visibleCount) {
                    item.style.display = 'block';
                    shownCount++;
                } else {
                    item.style.display = 'none';
                }
            } else {
                item.style.display = 'none';
            }
        });

        const loadMoreBtn = document.getElementById('galleryLoadMoreBtn');
        if (loadMoreBtn) {
            if (shownCount >= matchedCount) {
                loadMoreBtn.style.display = 'none';
            } else {
                loadMoreBtn.style.display = 'inline-flex';
            }
        }
    }

    function loadMoreGallery() {
        visibleCount += 6;
        renderGalleryItems();
    }

    window.addEventListener('DOMContentLoaded', () => {
        renderGalleryItems();
    });
</script>




<!-- Section: Reverse Auction Matching List (게시판 리스트 형태 역경매 위탁) -->
<section class="section" id="reverse-auction-section" style="background:#FFFFFF; padding:90px 24px; border-top:1px solid #E2E8F0; border-bottom:1px solid #E2E8F0;">
    <div class="container" style="max-width:1280px; margin:0 auto;">
        
        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:32px; flex-wrap:wrap; gap:16px;">
            <div>
                <span class="section-subtitle" style="font-size:0.85rem; font-weight:800; color:var(--primary-dark); text-transform:uppercase; letter-spacing:1.5px; margin-bottom:8px; display:block;">
                    <i class="fa-solid fa-gavel"></i> Reverse Auction Matching
                </span>
                <h2 class="section-title" style="font-size:2.2rem; font-weight:900; color:var(--secondary); margin-bottom:8px;">
                    실시간 역경매 마켓 신청 목록
                </h2>
                <p style="color:var(--text-muted); font-size:1rem; margin:0;">
                    고객님이 등록한 시공에 검증된 마스터들이 최적의 맞춤 견적을 경쟁 제안합니다.
                </p>
            </div>
            <div style="display:flex; gap:12px;">
                <a href="./process/index.php#apply" class="btn btn-accent" style="padding:12px 24px; font-size:0.95rem; font-weight:800;">
                    <i class="fa-solid fa-pen-to-square"></i> 무료 역경매 신청하기
                </a>
                <a href="./process/index.php#apply" class="btn btn-outline" style="padding:12px 20px; font-size:0.95rem;">
                    전체목록 보기 +
                </a>
            </div>
        </div>

        <!-- Board Table Card Wrapper -->
        <div style="background:#FFFFFF; border:1px solid #CBD5E1; border-radius:var(--radius-lg); overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.04);">
            <div class="table-responsive" style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                <table style="width:100%; min-width:720px; border-collapse:collapse; text-align:left; font-size:0.95rem;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:2px solid #E2E8F0; color:#475569; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.5px;">
                            <th style="padding:16px 20px; width:100px;">상태</th>
                            <th style="padding:16px 20px; width:120px;">구분</th>
                            <th style="padding:16px 20px;">시공 요청 제목 / 현장 상세</th>
                            <th style="padding:16px 20px; width:140px;">지역</th>
                            <th style="padding:16px 20px; width:130px; text-align:right;">희망 예산</th>
                            <th style="padding:16px 20px; width:110px; text-align:center;">참여 입찰</th>
                            <th style="padding:16px 20px; width:110px; text-align:right;">등록일</th>
                        </tr>
                    </thead>
                    <tbody style="color:#1E293B;">
                        <?php
                        $main_auctions = sql_one('auctions', '*', 'order by no desc limit 5');
                        if (empty($main_auctions)):
                        ?>
                            <tr><td colspan="7" style="padding:36px; text-align:center; color:#94A3B8;">등록된 실시간 역경매 신청 내역이 없습니다.</td></tr>
                        <?php else: foreach ($main_auctions as $ma): 
                            $ma_no = (int)$ma['no'];
                            $ma_url = "./process/index.php?cat=&no=" . $ma_no;
                            $ma_addr_parts = preg_split('/\s+/', trim($ma['addr']));
                            $ma_short_addr = implode(' ', array_slice($ma_addr_parts, 0, 2));
                            if (empty($ma_short_addr)) $ma_short_addr = '전국';

                            $st_badge = '<span style="background:#DCFCE7; color:#15803D; font-size:0.75rem; font-weight:800; padding:4px 8px; border-radius:4px; display:inline-block;">입찰대기</span>';
                            if ($ma['state'] === '입찰중') {
                                $st_badge = '<span style="background:#FEF3C7; color:#D97706; font-size:0.75rem; font-weight:800; padding:4px 8px; border-radius:4px; display:inline-block;">입찰중</span>';
                            } elseif ($ma['state'] === '매칭완료') {
                                $st_badge = '<span style="background:#E0F2FE; color:#0369A1; font-size:0.75rem; font-weight:800; padding:4px 8px; border-radius:4px; display:inline-block;">매칭완료</span>';
                            } elseif ($ma['state'] === '취소') {
                                $st_badge = '<span style="background:#FEE2E2; color:#B91C1C; font-size:0.75rem; font-weight:800; padding:4px 8px; border-radius:4px; display:inline-block;">취소</span>';
                            }
                        ?>
                            <tr onclick="location.href='<?php echo $ma_url; ?>'" style="border-bottom:1px solid #F1F5F9; transition:background 0.2s; cursor:pointer;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                                <td style="padding:16px 20px;"><?php echo $st_badge; ?></td>
                                <td style="padding:16px 20px; font-weight:700; color:var(--primary-dark);">
                                    <i class="fa-solid fa-building-user"></i> <?php echo htmlspecialchars($ma['space_type']); ?>
                                </td>
                                <td style="padding:16px 20px;">
                                    <a href="<?php echo $ma_url; ?>" style="text-decoration:none; color:#0F172A; font-weight:800; display:block; margin-bottom:2px;">
                                        <?php echo htmlspecialchars($ma_short_addr); ?> <?php echo (int)$ma['py'] > 0 ? (int)$ma['py'] . '평 ' : ''; ?><?php echo htmlspecialchars($ma['space_type']); ?> 썬팅 시공 역경매
                                    </a>
                                    <span style="font-size:0.82rem; color:#64748B;"><?php echo htmlspecialchars($ma['memo'] ? $ma['memo'] : '검증 틴팅프로 입찰 모집 중'); ?></span>
                                </td>
                                <td style="padding:16px 20px; color:#475569; font-size:0.9rem;"><?php echo htmlspecialchars($ma_short_addr); ?></td>
                                <td style="padding:16px 20px; text-align:right; font-weight:900; color:var(--primary-dark);">
                                    <?php echo number_format($ma['desired_price']); ?>원
                                </td>
                                <td style="padding:16px 20px; text-align:center;">
                                    <span style="background:#E0F2FE; color:#0369A1; font-weight:800; font-size:0.8rem; padding:2px 8px; border-radius:12px;"><?php echo (int)$ma['bid_count']; ?>건 입찰</span>
                                </td>
                                <td style="padding:16px 20px; text-align:right; color:#94A3B8; font-size:0.85rem;"><?php echo substr($ma['reg_date'], 0, 10); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>


<!-- Section: Recent Customer Reviews & Comments (최근 시공 후기 및 댓글 10개) -->
<section class="section" id="recent-reviews-section" style="background:#F8FAFC; padding:90px 24px; border-bottom:1px solid #E2E8F0;">
    <div class="container" style="max-width:1280px; margin:0 auto;">
        
        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:36px; flex-wrap:wrap; gap:16px;">
            <div>
                <span class="section-subtitle" style="font-size:0.85rem; font-weight:800; color:var(--primary-dark); text-transform:uppercase; letter-spacing:1.5px; margin-bottom:8px; display:block;">
                    <i class="fa-solid fa-star" style="color:#F59E0B;"></i> Real Customer Reviews
                </span>
                <h2 class="section-title" style="font-size:2.2rem; font-weight:900; color:var(--secondary); margin-bottom:8px;">
                    생생 시공 후기 & 실시간 댓글 (최근 10건)
                </h2>
                <p style="color:var(--text-muted); font-size:1rem; margin:0;">
                    틴팅 마스터를 통해 직접 시공받은 고객님들의 만족도 99.4% 실제 후기입니다.
                </p>
            </div>
            <div>
                <a href="./reviews/index.php" class="btn btn-outline" style="padding:12px 24px; font-size:0.95rem; font-weight:800; background:white;">
                    시공 후기 전체보기 (1,580+) +
                </a>
            </div>
        </div>

        <!-- 10 Reviews Board List Wrapper -->
        <div style="background:#FFFFFF; border:1px solid #CBD5E1; border-radius:var(--radius-lg); padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.03);">
            <div style="display:flex; flex-direction:column; gap:16px;">
<?php
$main_recent_comments = array();
if (isset($conn) && $conn) {
    $res_mc = @mysqli_query($conn, "SELECT c.*, q.space_type, q.addr, q.film_name, q.py FROM case_comments c LEFT JOIN quotes q ON c.case_no = q.no ORDER BY c.no DESC LIMIT 10");
    if ($res_mc && mysqli_num_rows($res_mc) > 0) {
        while ($r = mysqli_fetch_assoc($res_mc)) {
            $main_recent_comments[] = $r;
        }
    }
}

// DB에 저장된 실제 댓글 표시
foreach ($main_recent_comments as $rc):
    $st = (int)$rc['rating'];
    if ($st <= 0) $st = 5;
    $sp_type = !empty($rc['space_type']) ? $rc['space_type'] : '아파트';
    $addr_parts = preg_split('/\s+/', trim($rc['addr']));
    $short_addr = implode(' ', array_slice($addr_parts, 0, 2));
    $case_title = ($short_addr ? $short_addr . ' ' : '') . ((int)$rc['py'] > 0 ? (int)$rc['py'] . '평 ' : '') . $sp_type . ' 썬팅 후기';
    $tbl_type = !empty($rc['tbl_type']) ? $rc['tbl_type'] : 'quote';
    $rc_url = "./gallery/index.php?cat=" . urlencode($sp_type) . "&tab=" . $tbl_type . "&no=" . (int)$rc['case_no'];
?>
    <div onclick="location.href='<?php echo $rc_url; ?>'" style="padding:16px 20px; background:#F0F9FF; border:1px solid #BAE6FD; border-radius:var(--radius-md); display:flex; flex-direction:column; gap:8px; cursor:pointer; transition:transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="color:#F59E0B; font-size:0.9rem;">
                    <?php for ($i=0; $i<$st; $i++): ?><i class="fa-solid fa-star"></i><?php endfor; ?>
                </span>
                <span style="font-weight:900; color:#0F172A; font-size:1.05rem;"><?php echo htmlspecialchars($case_title); ?></span>
                <span style="background:#0077B6; color:#ffffff; font-size:0.75rem; font-weight:800; padding:2px 8px; border-radius:4px;"><?php echo htmlspecialchars($sp_type); ?></span>
            </div>
            <span style="font-size:0.85rem; color:#64748B;">작성자: <strong><?php echo htmlspecialchars($rc['writer_name']); ?></strong> | <?php echo substr($rc['reg_date'], 0, 10); ?></span>
        </div>
        <p style="font-size:0.95rem; color:#334155; margin:0; line-height:1.5; font-weight:500;">
            "<?php echo htmlspecialchars($rc['content']); ?>"
        </p>
        <div style="font-size:0.82rem; color:#64748B; display:flex; justify-content:space-between; align-items:center; margin-top:4px;">
            <span><i class="fa-solid fa-check" style="color:#059669;"></i> 시공필름: <?php echo htmlspecialchars($rc['film_name'] ? $rc['film_name'] : 'VULUX Premium Film'); ?></span>
            <span style="color:var(--primary-dark); font-weight:800;">해당 시공사례 이동 &rarr;</span>
        </div>
    </div>
<?php endforeach; ?>

<?php
// DB 댓글 수가 10개 미만인 경우 정적 후기 항목으로 10개까지 채움
$static_items = array(
    array(
        'title' => '서초 래미안 34평 거실·베란다 썬팅 마감 완벽합니다!',
        'type' => '아파트', 'type_bg' => '#E0F7FA', 'type_color' => 'var(--primary-dark)',
        'author' => '김*우 님 (서울 서초구)', 'date' => '2026.09.22',
        'desc' => '남향이라 여름에 햇빛 때문에 에어컨을 틀어도 실내가 너무 뜨거웠는데 VULUX Sputter 99 필름 시공 후 실내 온도가 확실히 3도 이상 내려갔어요! 마스터 분도 너무 친절하시고 깔끔하게 작업해 주셨습니다.',
        'film' => 'VULUX Sputter Dual 99', 'master' => '박진우 마스터'
    ),
    array(
        'title' => '판교 IT 오피스 30평 사생활 보호 시선차단 썬팅 후기',
        'type' => '빌딩·오피스', 'type_bg' => '#FEF3C7', 'type_color' => '#D97706',
        'author' => '이*성 대표 (경기 성남)', 'date' => '2026.09.21',
        'desc' => '사생활 보호 필름 시공 후 외부에서 내부가 전혀 보이지 않아 직원들 만족도가 아주 높습니다. 역경매 입찰을 통해 예상 견적보다 20% 저렴하게 최저가로 매칭 받아 시공했습니다!',
        'film' => 'VULUX Nano Ceramic', 'master' => '최경환 마스터팀'
    ),
    array(
        'title' => '제네시스 G80 출장 틴팅 잡티 없이 너무 깨끗해요',
        'type' => '자동차', 'type_bg' => '#ECFDF5', 'type_color' => '#059669',
        'author' => '최*호 님 (인천 연수구)', 'date' => '2026.09.20',
        'desc' => '원하는 장소로 방문해 주시는 출장 시공 서비스를 이용했는데 프리미엄 반사필름 특유의 세련된 광택과 시인성이 예술입니다. 기포나 먼지 하나 없이 깨끗합니다.',
        'film' => '전면 30% / 측후면 15%', 'master' => '강성민 마스터'
    ),
    array(
        'title' => '잠실 엘스 33평 고층 단열필름 겨울철 바람까지 차단!',
        'type' => '아파트', 'type_bg' => '#E0F7FA', 'type_color' => 'var(--primary-dark)',
        'author' => '정*희 님 (서울 송파구)', 'date' => '2026.09.19',
        'desc' => '여름 열차단도 효과적이지만 겨울철 난방 온기를 잡아주는 단열 기능까지 있어서 기대 이상입니다. 모바일 정품 보증서도 시공 직후 바로 카카오톡으로 발송되네요.',
        'film' => 'VULUX Standard Thermal', 'master' => '윤동현 마스터'
    ),
    array(
        'title' => '마포 래미안 푸르지오 DIY 대신 전문가 위탁하길 잘했네요',
        'type' => '아파트', 'type_bg' => '#E0F7FA', 'type_color' => 'var(--primary-dark)',
        'author' => '한*수 님 (서울 마포구)', 'date' => '2026.09.18',
        'desc' => '자가설치 DIY 키트로 직접 하려다 실패할까봐 역경매 신청했는데 3시간 만에 거실과 베란다 전체를 눈부시게 매끄럽게 완성해 주셨습니다. 마스터 매칭 강추합니다.',
        'film' => 'VULUX Sputter 99', 'master' => '박진우 마스터'
    ),
    array(
        'title' => '해운대 오션뷰 레스토랑 통유리 눈부심 싹 잡혔습니다',
        'type' => '빌딩·상가', 'type_bg' => '#FEF3C7', 'type_color' => '#D97706',
        'author' => '송*경 점장 (부산 해운대)', 'date' => '2026.09.17',
        'desc' => '바다 햇빛 반사 때문에 창가 좌석 손님들이 불편해 하셨는데, 시인성은 유지하면서 강력하게 자외선과 눈부심을 잡아주어 매장 매출 상승에도 도움되고 있습니다!',
        'film' => 'VULUX Sputter Dual 99', 'master' => '부산경남 틴팅프로팀'
    ),
    array(
        'title' => '테슬라 모델Y 글라스루프 열차단 틴팅 대만족!',
        'type' => '자동차', 'type_bg' => '#ECFDF5', 'type_color' => '#059669',
        'author' => '임*혁 님 (경기 용인)', 'date' => '2026.09.16',
        'desc' => '여름철 테슬라 글라스루프 뜨거운 열기 때문에 머리가 지끈거렸는데 루프 전용 열차단 필름 시공 후 열감이 획기적으로 줄었습니다. 틴팅 마스터 분 기술력 최고입니다.',
        'film' => '루프 농도 15% / IR 98%', 'master' => '강성민 마스터'
    ),
    array(
        'title' => '광화문 타워 8층 사옥 전체 창호 빠른 시공 완료',
        'type' => '빌딩·상가', 'type_bg' => '#FEF3C7', 'type_color' => '#D97706',
        'author' => '윤*진 총무팀장 (서울 종로)', 'date' => '2026.09.15',
        'desc' => '기업 전용 세금계산서 발행 및 투명한 견적서 비교, 10년 보증서 발급까지 한결같이 신속하고 정직하게 진행해 주셨습니다. 다음 지사 시공도 계약 결정했습니다.',
        'film' => '총 65평 / 6시간 소요', 'master' => '서울강남 틴팅프로팀'
    ),
    array(
        'title' => '반포 자이 52평 시공 비용 대비 효과 200%입니다',
        'type' => '아파트', 'type_bg' => '#E0F7FA', 'type_color' => 'var(--primary-dark)',
        'author' => '서*은 님 (서울 서초구)', 'date' => '2026.09.14',
        'desc' => '필름 시공 후 집안이 어두워지지 않을까 걱정했는데 조망은 투명하게 살아있고 열기만 깔끔하게 막아주네요. 주변 아파트 이웃들에게도 자신 있게 추천 중입니다!',
        'film' => 'VULUX Standard', 'master' => '윤동현 마스터'
    ),
    array(
        'title' => '분당 백현마을 단독주택 통유리 시공 후 청소 꿀팁까지!',
        'type' => '주택·단독', 'type_bg' => '#E0F7FA', 'type_color' => 'var(--primary-dark)',
        'author' => '류*민 님 (경기 성남)', 'date' => '2026.09.12',
        'desc' => '방문 실측부터 필름 샘플 비교 설명까지 너무 꼼꼼하셨고 시공 완료 후 유리창 관리법과 청소 꿀팁까지 세심하게 챙겨주셔서 진심으로 감동했습니다. 10년 보증도 든든하네요.',
        'film' => 'VULUX Sputter Dual 99', 'master' => '최경환 마스터'
    )
);

$db_cnt = count($main_recent_comments);
$need_static_cnt = max(0, 10 - $db_cnt);

for ($s_idx = 0; $s_idx < $need_static_cnt; $s_idx++):
    $si = $static_items[$s_idx % count($static_items)];
?>
    <div style="padding:16px 20px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:var(--radius-md); display:flex; flex-direction:column; gap:8px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="color:#F59E0B; font-size:0.9rem;">
                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                </span>
                <span style="font-weight:900; color:#0F172A; font-size:1.05rem;"><?php echo htmlspecialchars($si['title']); ?></span>
                <span style="background:<?php echo $si['type_bg']; ?>; color:<?php echo $si['type_color']; ?>; font-size:0.75rem; font-weight:800; padding:2px 8px; border-radius:4px;"><?php echo htmlspecialchars($si['type']); ?></span>
            </div>
            <span style="font-size:0.85rem; color:#94A3B8;">작성자: <?php echo htmlspecialchars($si['author']); ?> | <?php echo $si['date']; ?></span>
        </div>
        <p style="font-size:0.95rem; color:#334155; margin:0; line-height:1.5;">
            <?php echo htmlspecialchars($si['desc']); ?>
        </p>
        <div style="font-size:0.82rem; color:#64748B; display:flex; gap:16px; margin-top:4px;">
            <span><i class="fa-solid fa-check" style="color:#059669;"></i> 시공필름: <?php echo htmlspecialchars($si['film']); ?></span>
            <span><i class="fa-solid fa-user-gear" style="color:var(--primary-dark);"></i> 담당: <?php echo htmlspecialchars($si['master']); ?></span>
        </div>
    </div>
<?php endfor; ?>

            </div>
        </div>

    </div>
</section>

<!-- Bottom Spacing Container Before Footer -->
<div style="height:80px; background:#F8FAFC;"></div>


<?php
include_once __DIR__ . "/inc/footer.php";
?>
