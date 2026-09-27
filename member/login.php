<?php
$page_title = "로그인";
$active_menu = "";
$path_prefix = "../";

include_once __DIR__ . "/../inc/dbconn.php";

if (!empty($_SESSION['s_mem_id'])) {
    header("Location: " . $path_prefix . "index.php");
    exit;
}

$err_msg = isset($_GET['err']) ? $_GET['err'] : '';
$redirect_to = isset($_GET['redirect']) ? $_GET['redirect'] : '';

include_once __DIR__ . "/../inc/head.php";
include_once __DIR__ . "/../inc/header.php";
?>

<style>
    .login-hero { background:linear-gradient(135deg, #E0F2FE 0%, #F0F9FF 100%); padding:60px 24px; text-align:center; border-bottom:1px solid #E0F2FE; }
    .login-hero h1 { font-size:2.2rem; font-weight:900; color:var(--secondary); margin-bottom:10px; }
    .login-hero p { font-size:1.05rem; color:var(--text-muted); }
    .login-wrap { max-width:440px; margin:0 auto; padding:56px 24px 90px; }
    .login-card { background:#fff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:36px; box-shadow:var(--shadow-sm); }
    .input-box { margin-bottom:18px; }
    .input-box label { display:block; font-size:0.85rem; font-weight:700; color:var(--text-main); margin-bottom:6px; }
    .input-box input { width:100%; padding:12px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.95rem; outline:none; transition:border-color 0.2s; }
    .input-box input:focus { border-color:var(--primary); }
    .login-error { background:#FEF2F2; border:1px solid #FECACA; color:#B91C1C; padding:12px 16px; border-radius:var(--radius-md); font-size:0.9rem; margin-bottom:20px; }
</style>

<div class="login-hero">
    <h1>로그인</h1>
    <p>이메일과 비밀번호로 로그인해 주세요.</p>
</div>

<div class="login-wrap">
    <?php if ($err_msg): ?>
        <div class="login-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($err_msg); ?></div>
    <?php endif; ?>

    <div class="login-card">
        <form method="post" action="login_proc.php">
            <?php if ($redirect_to): ?>
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_to); ?>">
            <?php endif; ?>
            <div class="input-box">
                <label>이메일 <span style="color:#EF4444;">*</span></label>
                <input type="email" name="email" required autofocus placeholder="example@email.com" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
            </div>
            <div class="input-box">
                <label>비밀번호 <span style="color:#EF4444;">*</span></label>
                <input type="password" name="passwd" required placeholder="비밀번호">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; padding:15px; font-size:1rem;">
                <i class="fa-solid fa-right-to-bracket"></i> 로그인
            </button>
        </form>
        <p style="text-align:center; margin-top:20px; font-size:0.9rem; color:var(--text-muted);">
            아직 회원이 아니신가요? <a href="join.php" style="color:var(--primary-dark); font-weight:700;">회원가입</a>
        </p>
    </div>
</div>

<?php
include_once __DIR__ . "/../inc/footer.php";
?>
