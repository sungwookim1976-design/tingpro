<?php
$page_title = "관리자 신규 등록";
$active_page = "admins";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";
include_once __DIR__ . "/../inc/adm_head.php";
?>

<div style="margin-bottom:20px;">
    <a href="index.php" class="adm-btn adm-btn-outline" style="padding:6px 14px; font-size:0.85rem;"><i class="fa-solid fa-arrow-left"></i> 목록으로 돌아가기</a>
</div>

<div class="adm-form-box" style="max-width:100%;">
    <h3 style="margin-bottom:20px; font-size:1.1rem; font-weight:800; border-bottom:1px solid var(--adm-border); padding-bottom:12px;">
        <i class="fa-solid fa-user-plus" style="color:var(--adm-primary);"></i> 신규 관리자 등록
    </h3>
    <form action="proc.php" method="post" onsubmit="return validateAdminForm(this);">
        <input type="hidden" name="mode" value="insert">

        <div class="adm-form-row">
            <label for="uid">아이디 <span style="color:#EF4444;">*</span></label>
            <input type="text" id="uid" name="uid" placeholder="영문, 숫자 4~20자" required autofocus>
        </div>

        <div class="adm-form-row">
            <label for="passwd">비밀번호 <span style="color:#EF4444;">*</span></label>
            <input type="password" id="passwd" name="passwd" placeholder="4자 이상 입력" required>
        </div>

        <div class="adm-form-row">
            <label for="name">이름 (담당자명) <span style="color:#EF4444;">*</span></label>
            <input type="text" id="name" name="name" placeholder="관리자 이름 입력" required>
        </div>

        <div class="adm-form-row">
            <label for="grade">권한 등급 <span style="color:#EF4444;">*</span></label>
            <select id="grade" name="grade" required>
                <option value="staff">일반직원 (Staff)</option>
                <option value="manager">운영관리자 (Manager)</option>
                <option value="super">최고관리자 (Super)</option>
            </select>
        </div>

        <div class="adm-form-row">
            <label for="state">계정 상태</label>
            <select id="state" name="state">
                <option value="1">정상 (사용가능)</option>
                <option value="0">정지 (접속불가)</option>
            </select>
        </div>

        <div style="display:flex; gap:10px; margin-top:28px;">
            <button type="submit" class="adm-btn" style="flex:1; justify-content:center; padding:12px; font-size:0.95rem;"><i class="fa-solid fa-check"></i> 관리자 등록 완료</button>
            <a href="index.php" class="adm-btn adm-btn-outline" style="padding:12px 20px; font-size:0.95rem;">취소</a>
        </div>
    </form>
</div>

<script>
function validateAdminForm(f) {
    if (!/^[a-zA-Z0-9]{4,20}$/.test(f.uid.value)) {
        alert('아이디는 영문/숫자 4~20자로 입력해 주세요.');
        f.uid.focus();
        return false;
    }
    if (f.passwd.value.length < 4) {
        alert('비밀번호는 4자 이상 입력해 주세요.');
        f.passwd.focus();
        return false;
    }
    if (!f.name.value.trim()) {
        alert('이름을 입력해 주세요.');
        f.name.focus();
        return false;
    }
    return true;
}
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
