<?php
$page_title = "관리자 정보 수정";
$active_page = "admins";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$no = intval((isset($_GET['no']) ? $_GET['no'] : 0));
if ($no <= 0) {
    echo "<script>alert('올바르지 않은 접근입니다.'); location.href='index.php';</script>";
    exit;
}

$admin = sql_one_one('admins', '*', "and no=" . $no);
if (!$admin) {
    echo "<script>alert('존재하지 않는 관리자 계정입니다.'); location.href='index.php';</script>";
    exit;
}

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div style="margin-bottom:20px;">
    <a href="index.php" class="adm-btn adm-btn-outline" style="padding:6px 14px; font-size:0.85rem;"><i class="fa-solid fa-arrow-left"></i> 목록으로 돌아가기</a>
</div>

<div class="adm-form-box" style="max-width:100%;">
    <h3 style="margin-bottom:20px; font-size:1.1rem; font-weight:800; border-bottom:1px solid var(--adm-border); padding-bottom:12px;">
        <i class="fa-solid fa-user-pen" style="color:var(--adm-primary);"></i> 관리자 정보 수정
    </h3>
    <form action="proc.php" method="post" onsubmit="return validateEditForm(this);">
        <input type="hidden" name="mode" value="update">
        <input type="hidden" name="no" value="<?php echo $admin['no']; ?>">

        <div class="adm-form-row">
            <label for="uid">아이디</label>
            <input type="text" id="uid" value="<?php echo htmlspecialchars($admin['uid']); ?>" disabled style="background:#F8FAFC; color:#64748B; cursor:not-allowed;">
        </div>

        <div class="adm-form-row">
            <label for="name">이름 (담당자명) <span style="color:#EF4444;">*</span></label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
        </div>

        <div class="adm-form-row">
            <label for="passwd">비밀번호 변경 <span style="font-size:0.75rem; color:var(--adm-muted); font-weight:normal;">(변경시에만 입력)</span></label>
            <input type="password" id="passwd" name="passwd" placeholder="변경할 경우에만 4자 이상 입력">
        </div>

        <div class="adm-form-row">
            <label for="grade">권한 등급 <span style="color:#EF4444;">*</span></label>
            <select id="grade" name="grade" required>
                <option value="staff" <?php echo $admin['grade'] === 'staff' ? 'selected' : ''; ?>>일반직원 (Staff)</option>
                <option value="manager" <?php echo $admin['grade'] === 'manager' ? 'selected' : ''; ?>>운영관리자 (Manager)</option>
                <option value="super" <?php echo $admin['grade'] === 'super' ? 'selected' : ''; ?>>최고관리자 (Super)</option>
            </select>
        </div>

        <div class="adm-form-row">
            <label for="state">계정 상태</label>
            <select id="state" name="state">
                <option value="1" <?php echo $admin['state'] == 1 ? 'selected' : ''; ?>>정상 (사용가능)</option>
                <option value="0" <?php echo $admin['state'] == 0 ? 'selected' : ''; ?>>정지 (접속불가)</option>
            </select>
        </div>

        <div style="font-size:0.8rem; color:var(--adm-muted); margin-bottom:16px;">
            가입일: <?php echo htmlspecialchars($admin['reg_date']); ?> | 
            최근 로그인: <?php echo $admin['last_login'] ? htmlspecialchars($admin['last_login']) : '기록 없음'; ?>
        </div>

        <div style="display:flex; gap:10px; margin-top:24px;">
            <button type="submit" class="adm-btn" style="flex:1; justify-content:center; padding:12px; font-size:0.95rem;"><i class="fa-solid fa-floppy-disk"></i> 수정사항 저장</button>
            <a href="index.php" class="adm-btn adm-btn-outline" style="padding:12px 20px; font-size:0.95rem;">취소</a>
        </div>
    </form>
</div>

<script>
function validateEditForm(f) {
    if (!f.name.value.trim()) {
        alert('이름을 입력해 주세요.');
        f.name.focus();
        return false;
    }
    if (f.passwd.value && f.passwd.value.length < 4) {
        alert('비밀번호 변경 시 4자 이상 입력해 주세요.');
        f.passwd.focus();
        return false;
    }
    return true;
}
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
