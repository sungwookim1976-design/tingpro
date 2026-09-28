<?php
$page_title = "VULUX 대리점";
$active_menu = "about";
$path_prefix = "../";

include_once __DIR__ . "/../inc/head.php";
include_once __DIR__ . "/../inc/header.php";
?>

<div class="sub-hero hero-mobile-pad" style="background:linear-gradient(135deg, #E0F2FE 0%, #F0F9FF 100%); padding:60px 24px; text-align:center; border-bottom:1px solid #E0F2FE;">
    <h1 style="font-size:2.5rem; font-weight:900; color:var(--secondary); margin-bottom:12px;">VULUX 대리점소개</h1>
    <p style="font-size:1.1rem; color:var(--text-muted);">TINTING PRO 대리점 및 시공 센터 소개 페이지입니다.</p>
</div>

<div class="container container-mobile-pad" style="max-width:1280px; margin:0 auto; padding:60px 24px;">
    <?php
    // Include specific sub-content
    if (file_exists(__DIR__ . '/content.html')) {
        include __DIR__ . '/content.html';
    } else {
        echo '<div style="padding:40px; text-align:center; background:#F8FAFC; border-radius:var(--radius-lg); border:1px solid #E2E8F0;"><h3 style="font-size:1.5rem; font-weight:800; color:var(--secondary);">' . $page_title . ' 전용 메인 페이지입니다.</h3><p style="color:var(--text-muted); margin-top:12px;">실시간 견적 및 1:1 방문 실측 상담을 지원합니다.</p><a href="../calculator/index.php" class="btn btn-primary" style="margin-top:20px;">실시간 견적 확인하기</a></div>';
    }
    ?>
</div>

<?php
include_once __DIR__ . "/../inc/footer.php";
?>
