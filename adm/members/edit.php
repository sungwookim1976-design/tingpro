<?php
$page_title = "회원 정보 수정";
$active_page = "members";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$no = intval((isset($_GET['no']) ? $_GET['no'] : 0));
if ($no <= 0) {
    echo "<script>alert('올바르지 않은 접근입니다.'); location.href='index.php';</script>";
    exit;
}

$m = sql_one_one('members', '*', "and no=" . $no);
if (!$m) {
    echo "<script>alert('존재하지 않는 회원입니다.'); location.href='index.php';</script>";
    exit;
}

include_once __DIR__ . "/../inc/adm_head.php";
?>

<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
function execDaumPostcode() {
    new daum.Postcode({
        oncomplete: function(data) {
            var addr = '';
            var extraAddr = '';

            if (data.userSelectedType === 'R') {
                addr = data.roadAddress;
            } else {
                addr = data.jibunAddress;
            }

            if (data.userSelectedType === 'R') {
                if (data.bname !== '' && /[동|로|가]$/g.test(data.bname)) {
                    extraAddr += data.bname;
                }
                if (data.buildingName !== '' && data.apartment === 'Y') {
                    extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                if (extraAddr !== '') {
                    extraAddr = ' (' + extraAddr + ')';
                }
            }

            document.getElementById('zipcode').value = data.zonecode;
            document.getElementById('addr1').value = addr + extraAddr;
            document.getElementById('addr2').focus();
        }
    }).open();
}
</script>

<div style="margin-bottom:20px;">
    <a href="index.php" class="adm-btn adm-btn-outline" style="padding:6px 14px; font-size:0.85rem;"><i class="fa-solid fa-arrow-left"></i> 목록으로 돌아가기</a>
</div>

<div class="adm-form-box" style="max-width:100%;">
    <h3 style="margin-bottom:20px; font-size:1.1rem; font-weight:800; border-bottom:1px solid var(--adm-border); padding-bottom:12px;">
        <i class="fa-solid fa-user-pen" style="color:var(--adm-primary);"></i> 회원 정보 수정
    </h3>
    <form action="proc.php" method="post" onsubmit="return validateEditForm(this);">
        <input type="hidden" name="mode" value="update">
        <input type="hidden" name="no" value="<?php echo $m['no']; ?>">

        <div class="adm-form-row">
            <label for="mem_type">회원 유형 <span style="color:#EF4444;">*</span></label>
            <select id="mem_type" name="mem_type" onchange="toggleMemTypeFields(this.value)" required>
                <option value="customer" <?php echo ($m['mem_type'] === 'customer' || $m['mem_type'] === 'cust' || $m['mem_type'] === '수요고객') ? 'selected' : ''; ?>>수요고객</option>
                <option value="freelance" <?php echo ($m['mem_type'] === 'freelance' || $m['mem_type'] === 'free' || $m['mem_type'] === '프리랜서') ? 'selected' : ''; ?>>프리랜서</option>
                <option value="corporate" <?php echo ($m['mem_type'] === 'corporate' || $m['mem_type'] === 'corp' || $m['mem_type'] === '기업') ? 'selected' : ''; ?>>기업</option>
                <option value="partner" <?php echo ($m['mem_type'] === 'partner' || $m['mem_type'] === 'part' || $m['mem_type'] === '대리점') ? 'selected' : ''; ?>>대리점</option>
            </select>
        </div>

        <div class="adm-form-row">
            <label for="email">아이디 (이메일 주소) <span style="color:#EF4444;">*</span></label>
            <div style="display:flex; gap:8px;">
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($m['email'] ? $m['email'] : $m['uid']); ?>" required oninput="resetEmailCheck()">
                <button type="button" onclick="checkEmailDupEdit()" class="adm-btn adm-btn-outline" style="padding:8px 14px; font-size:0.85rem; white-space:nowrap; border-color:#0077B6; color:#0077B6;"><i class="fa-solid fa-magnifying-glass"></i> 중복검사</button>
            </div>
            <div id="email_dup_msg" style="font-size:0.8rem; margin-top:4px; font-weight:700;"></div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div class="adm-form-row">
                <label for="name">이름 / 담당자명 <span style="color:#EF4444;">*</span></label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($m['name']); ?>" required>
            </div>
            <div class="adm-form-row">
                <label for="hphone">연락처 <span style="color:#EF4444;">*</span></label>
                <input type="text" id="hphone" name="hphone" value="<?php echo htmlspecialchars($m['hphone']); ?>" required>
            </div>
        </div>

        <div class="adm-form-row">
            <label for="passwd">비밀번호 변경 <span style="font-size:0.75rem; color:var(--adm-muted); font-weight:normal;">(변경시에만 입력)</span></label>
            <input type="password" id="passwd" name="passwd" placeholder="변경할 경우에만 4자 이상 입력">
        </div>

        <div class="adm-form-row">
            <label for="zipcode">우편번호 / 주소 검색</label>
            <div style="display:flex; gap:8px;">
                <input type="text" id="zipcode" name="zipcode" value="<?php echo htmlspecialchars($m['zipcode']); ?>" readonly style="width:140px; background:#F8FAFC;">
                <button type="button" onclick="execDaumPostcode()" class="adm-btn adm-btn-outline" style="padding:8px 14px; font-size:0.85rem;"><i class="fa-solid fa-magnifying-glass"></i> 우편번호 검색</button>
            </div>
        </div>

        <div class="adm-form-row">
            <label for="addr1">주소</label>
            <input type="text" id="addr1" name="addr1" value="<?php echo htmlspecialchars($m['addr1']); ?>" readonly style="background:#F8FAFC; margin-bottom:8px;">
            <input type="text" id="addr2" name="addr2" value="<?php echo htmlspecialchars($m['addr2']); ?>">
        </div>

        <!-- 프리랜서 전용 필드 -->
        <div id="freelance_fields" style="display:<?php echo $m['mem_type'] === 'freelance' ? 'block' : 'none'; ?>; background:#FFFBEB; padding:16px; border-radius:10px; border:1px solid #FCD34D; margin-bottom:16px;">
            <h4 style="font-size:0.88rem; color:#B45309; margin-bottom:12px;"><i class="fa-solid fa-briefcase"></i> 프리랜서 추가 정보</h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="adm-form-row" style="margin-bottom:0;">
                    <label for="region">활동 지역</label>
                    <input type="text" id="region" name="region" value="<?php echo htmlspecialchars($m['region']); ?>">
                </div>
                <div class="adm-form-row" style="margin-bottom:0;">
                    <label for="career">경력 정보</label>
                    <input type="text" id="career" name="career" value="<?php echo htmlspecialchars($m['career']); ?>">
                </div>
            </div>
        </div>

        <!-- 기업/대리점 전용 필드 -->
        <div id="partner_fields" style="display:<?php echo ($m['mem_type'] === 'partner' || $m['mem_type'] === 'corporate') ? 'block' : 'none'; ?>; background:#ECFDF5; padding:16px; border-radius:10px; border:1px solid #6EE7B7; margin-bottom:16px;">
            <h4 style="font-size:0.88rem; color:#047857; margin-bottom:12px;"><i class="fa-solid fa-building"></i> 기업/대리점 사업자 정보</h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="adm-form-row" style="margin-bottom:0;">
                    <label for="biz_name">상호 / 기업명</label>
                    <input type="text" id="biz_name" name="biz_name" value="<?php echo htmlspecialchars($m['biz_name']); ?>">
                </div>
                <div class="adm-form-row" style="margin-bottom:0;">
                    <label for="biz_no">사업자등록번호</label>
                    <input type="text" id="biz_no" name="biz_no" value="<?php echo htmlspecialchars($m['biz_no']); ?>">
                </div>
            </div>
        </div>

        <div class="adm-form-row">
            <label for="mem_state">계정 상태</label>
            <select id="mem_state" name="mem_state">
                <option value="1" <?php echo $m['mem_state'] == 1 ? 'selected' : ''; ?>>정상 (이용 가능)</option>
                <option value="0" <?php echo $m['mem_state'] == 0 ? 'selected' : ''; ?>>정지 (이용 제한)</option>
            </select>
        </div>

        <div style="font-size:0.8rem; color:var(--adm-muted); margin-bottom:16px;">
            가입일: <?php echo htmlspecialchars($m['reg_date']); ?>
        </div>

        <div style="display:flex; gap:10px; margin-top:24px;">
            <button type="submit" class="adm-btn" style="flex:1; justify-content:center; padding:12px; font-size:0.95rem;"><i class="fa-solid fa-floppy-disk"></i> 회원 정보 저장</button>
            <a href="index.php" class="adm-btn adm-btn-outline" style="padding:12px 20px; font-size:0.95rem;">취소</a>
        </div>
    </form>
</div>

<script>
const initialEmail = '<?php echo addslashes($m['email'] ? $m['email'] : $m['uid']); ?>';
const memberNo = <?php echo (int)$m['no']; ?>;
let isEmailChecked = true;

function resetEmailCheck() {
    const emailVal = document.getElementById('email').value.trim();
    const msgEl = document.getElementById('email_dup_msg');
    if (emailVal === initialEmail) {
        isEmailChecked = true;
        msgEl.innerHTML = '';
    } else {
        isEmailChecked = false;
        msgEl.style.color = '#D97706';
        msgEl.innerHTML = '이메일(아이디) 변경됨. 중복검사를 진행해 주세요.';
    }
}

function checkEmailDupEdit() {
    const emailInput = document.getElementById('email');
    const emailVal = emailInput.value.trim();
    const msgEl = document.getElementById('email_dup_msg');

    if (!emailVal || !emailVal.includes('@')) {
        alert('올바른 이메일 주소를 입력해 주세요.');
        emailInput.focus();
        return;
    }

    fetch('../../member/check_email_ajax.php?email=' + encodeURIComponent(emailVal) + '&no=' + memberNo)
        .then(res => res.json())
        .then(data => {
            if (data.exists) {
                isEmailChecked = false;
                msgEl.style.color = '#EF4444';
                msgEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> ' + data.msg;
                alert(data.msg);
            } else {
                isEmailChecked = true;
                msgEl.style.color = '#059669';
                msgEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + data.msg;
                alert(data.msg);
            }
        })
        .catch(err => {
            alert('중복검사 중 오류가 발생했습니다.');
        });
}

function toggleMemTypeFields(val) {
    document.getElementById('freelance_fields').style.display = (val === 'freelance') ? 'block' : 'none';
    document.getElementById('partner_fields').style.display = (val === 'partner' || val === 'corporate') ? 'block' : 'none';
}

function validateEditForm(f) {
    if (!f.email.value.trim() || !f.email.value.includes('@')) {
        alert('올바른 이메일 주소(아이디)를 입력해 주세요.');
        f.email.focus();
        return false;
    }
    if (!isEmailChecked && f.email.value.trim() !== initialEmail) {
        alert('이메일(아이디) 중복검사를 완료해 주세요.');
        f.email.focus();
        return false;
    }
    if (!f.name.value.trim() || !f.hphone.value.trim()) {
        alert('이름과 연락처를 입력해 주세요.');
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
