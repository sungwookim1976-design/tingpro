<?php
$page_title = "가입완료";
$active_menu = "";
$path_prefix = "../";

include_once __DIR__ . "/../inc/dbconn.php";

if (empty($_SESSION['s_mem_id'])) {
    header("Location: join.php");
    exit;
}

$mem_type = isset($_SESSION['s_mem_type']) ? $_SESSION['s_mem_type'] : 'customer';
$type_map = array(
    'customer'  => '수요 고객',
    'freelance' => '프리랜서 마스터',
    'partner'   => '기업 · 대리점'
);
$type_label = isset($type_map[$mem_type]) ? $type_map[$mem_type] : '회원';

include_once __DIR__ . "/../inc/head.php";
include_once __DIR__ . "/../inc/header.php";
?>

<div style="max-width:560px; margin:0 auto; padding:100px 24px; text-align:center;">
    <div style="width:80px; height:80px; border-radius:50%; background:var(--primary-light); color:var(--primary-dark); display:flex; align-items:center; justify-content:center; font-size:2.2rem; margin:0 auto 24px;">
        <i class="fa-solid fa-check"></i>
    </div>
    <h1 style="font-size:1.8rem; font-weight:900; color:var(--secondary); margin-bottom:12px;">
        <?php echo htmlspecialchars($_SESSION['s_mem_name']); ?>님, 가입을 환영합니다!
    </h1>
    <p style="color:var(--text-muted); font-size:1rem; margin-bottom:8px;">
        <?php echo htmlspecialchars($type_label); ?> 회원으로 가입이 완료되었습니다.
    </p>
    <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:32px;">
        아이디: <strong style="color:var(--text-main);"><?php echo htmlspecialchars($_SESSION['s_mem_id']); ?></strong>
    </p>
    <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
        <a href="<?php echo $path_prefix; ?>index.php" class="btn btn-primary">홈으로 이동</a>
        <a href="<?php echo $path_prefix; ?>calculator/index.php" class="btn btn-outline">실시간 견적 확인하기</a>
    </div>
</div>

<?php
include_once __DIR__ . "/../inc/footer.php";
?>
