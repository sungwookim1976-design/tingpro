<?php
include_once __DIR__ . "/../inc/dbconn.php";

if (!empty($_SESSION['s_adm_id'])) {
    header("Location: index.php");
    exit;
}

$err_msg = isset($_GET['err']) ? $_GET['err'] : '';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 로그인 | 틴팅 마스터</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', 'Pretendard', sans-serif; }
        body {
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(135deg, #0F172A 0%, #1E293B 60%, #0F172A 100%);
        }
        .adm-login-card {
            width:100%;
            max-width:380px;
            background:#fff;
            border-radius:16px;
            padding:40px 36px;
            box-shadow:0 24px 60px rgba(0,0,0,0.35);
        }
        .adm-login-logo {
            display:flex;
            align-items:center;
            gap:10px;
            justify-content:center;
            margin-bottom:8px;
        }
        .adm-login-logo i { font-size:1.6rem; color:#00B4D8; }
        .adm-login-logo span { font-size:1.3rem; font-weight:900; color:#0F172A; }
        .adm-login-sub { text-align:center; color:#64748B; font-size:0.85rem; margin-bottom:28px; }
        .adm-input-box { margin-bottom:16px; }
        .adm-input-box label { display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:6px; }
        .adm-input-box input {
            width:100%; padding:12px 14px; border:1px solid #CBD5E1; border-radius:8px;
            font-size:0.95rem; outline:none; transition:border-color 0.2s;
        }
        .adm-input-box input:focus { border-color:#00B4D8; }
        .adm-login-btn {
            width:100%; padding:13px; border:none; border-radius:8px; cursor:pointer;
            background:linear-gradient(135deg, #0077B6 0%, #00B4D8 100%); color:#fff;
            font-size:1rem; font-weight:800; margin-top:6px;
        }
        .adm-login-btn:hover { opacity:0.92; }
        .adm-error {
            background:#FEF2F2; border:1px solid #FECACA; color:#B91C1C;
            padding:10px 14px; border-radius:8px; font-size:0.85rem; margin-bottom:18px;
        }
        .adm-back-link { display:block; text-align:center; margin-top:20px; font-size:0.82rem; color:#94A3B8; text-decoration:none; }
        .adm-back-link:hover { color:#00B4D8; }
    </style>
</head>
<body>
    <div class="adm-login-card">
        <div class="adm-login-logo"><i class="fa-solid fa-shield-halved"></i><span>ADMIN</span></div>
        <p class="adm-login-sub">틴팅 마스터 관리자 페이지</p>

        <?php if ($err_msg): ?>
            <div class="adm-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($err_msg); ?></div>
        <?php endif; ?>

        <form method="post" action="login_proc.php">
            <div class="adm-input-box">
                <label>아이디</label>
                <input type="text" name="uid" required autofocus placeholder="관리자 아이디" value="<?php echo isset($_GET['uid']) ? htmlspecialchars($_GET['uid']) : ''; ?>">
            </div>
            <div class="adm-input-box">
                <label>비밀번호</label>
                <input type="password" name="passwd" required placeholder="비밀번호">
            </div>
            <button type="submit" class="adm-login-btn"><i class="fa-solid fa-right-to-bracket"></i> 로그인</button>
        </form>
        <a href="../index.php" class="adm-back-link"><i class="fa-solid fa-arrow-left"></i> 사이트로 돌아가기</a>
    </div>
</body>
</html>
