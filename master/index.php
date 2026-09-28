<?php
$page_title = "마스터 인증 | 틴팅 마스터 VULUX";
$active_menu = "master";
$path_prefix = "../";

include_once __DIR__ . "/../inc/head.php";
include_once __DIR__ . "/../inc/header.php";
?>

<div class="sub-hero hero-mobile-pad" style="background:linear-gradient(135deg, #E0F2FE 0%, #F0F9FF 100%); padding:60px 24px; text-align:center; border-bottom:1px solid #E0F2FE;">
    <h1 style="font-size:2.4rem; font-weight:900; color:var(--secondary); margin-bottom:10px;">마스터 기술 자격 인증</h1>
    <p style="font-size:1.1rem; color:var(--text-muted);">검증된 틴팅 마스터의 엄격한 자격 심사 기준 및 전문 자격 인증 시스템을 소개합니다.</p>
</div>

<section class="container container-mobile-pad" style="max-width:1280px; margin:0 auto; padding:60px 24px;">
    <?php include_once __DIR__ . "/content.html"; ?>
</section>

<?php
include_once __DIR__ . "/../inc/footer.php";
?>
