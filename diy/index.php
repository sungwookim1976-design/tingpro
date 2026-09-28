<?php
$page_title = "VULUX 견적구매";
$active_menu = "diy";
$path_prefix = "../";

include_once __DIR__ . "/../inc/dbconn.php";
include_once __DIR__ . "/../inc/head.php";
include_once __DIR__ . "/../inc/header.php";
?>

<div class="sub-hero hero-mobile-pad" style="background:linear-gradient(135deg, #E0F2FE 0%, #F0F9FF 100%); padding:60px 24px; text-align:center; border-bottom:1px solid #E0F2FE;">
    <h1 style="font-size:2.5rem; font-weight:900; color:var(--secondary); margin-bottom:12px;">VULUX 견적구매</h1>
    <p style="font-size:1.1rem; color:var(--text-muted);">직접 시공이 가능한 DIY 키트를 합리적인 가격에 만나보세요.</p>
</div>

<div class="container container-mobile-pad" style="max-width:1280px; margin:0 auto; padding:60px 24px;">
    <?php
    if (file_exists(__DIR__ . '/content.html')) {
        include __DIR__ . '/content.html';
    }
    ?>
</div>

<?php
include_once __DIR__ . "/../inc/footer.php";
?>
