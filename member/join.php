<?php
$page_title = "회원가입";
$active_menu = "";
$path_prefix = "../";

include_once __DIR__ . "/../inc/dbconn.php";

// 이미 로그인된 경우 마이페이지 대신 홈으로 안내
if (!empty($_SESSION['s_mem_id'])) {
    header("Location: " . $path_prefix . "index.php");
    exit;
}

$err_msg = isset($_GET['err']) ? $_GET['err'] : '';
$default_type = isset($_GET['type']) && in_array($_GET['type'], ['customer', 'freelance', 'partner']) ? $_GET['type'] : 'customer';

include_once __DIR__ . "/../inc/head.php";
?>
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js">
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

                document.getElementById('join_zipcode').value = data.zonecode;
                document.getElementById('join_addr1').value = addr + extraAddr;
                document.getElementById('join_addr2').focus();
            }
        }).open();
    }
</script>
<?php
include_once __DIR__ . "/../inc/header.php";
?>

<style>
    .join-hero { background:linear-gradient(135deg, #E0F2FE 0%, #F0F9FF 100%); padding:60px 24px; text-align:center; border-bottom:1px solid #E0F2FE; }
    .join-hero h1 { font-size:2.2rem; font-weight:900; color:var(--secondary); margin-bottom:10px; }
    .join-hero p { font-size:1.05rem; color:var(--text-muted); }

    .join-wrap { max-width:680px; margin:0 auto; padding:56px 24px 90px; }

    .join-type-tabs { display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; margin-bottom:32px; }
    .join-type-tab { border:2px solid var(--border-color); background:#fff; border-radius:var(--radius-md); padding:18px 10px; text-align:center; cursor:pointer; transition:all 0.2s ease; }
    .join-type-tab i { font-size:1.5rem; color:var(--text-muted); margin-bottom:8px; display:block; }
    .join-type-tab .t { font-weight:800; font-size:0.95rem; color:var(--text-main); }
    .join-type-tab .s { font-size:0.75rem; color:var(--text-muted); margin-top:4px; }
    .join-type-tab.active { border-color:var(--primary); background:var(--primary-light); }
    .join-type-tab.active i, .join-type-tab.active .t { color:var(--primary-dark); }

    .join-card { background:#fff; border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:36px; box-shadow:var(--shadow-sm); }
    .input-box { margin-bottom:18px; }
    .input-box label { display:block; font-size:0.85rem; font-weight:700; color:var(--text-main); margin-bottom:6px; }
    .input-box label .req { color:#EF4444; margin-left:2px; }
    .input-box input, .input-box select { width:100%; padding:12px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.95rem; outline:none; transition:border-color 0.2s; }
    .input-box input:focus, .input-box select:focus { border-color:var(--primary); }
    .input-box .hint { font-size:0.78rem; color:var(--text-muted); margin-top:4px; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .join-type-fields { display:none; padding-top:6px; margin-top:6px; border-top:1px dashed var(--border-color); }
    .join-type-fields.active { display:block; }
    .agree-box { display:flex; align-items:flex-start; gap:10px; font-size:0.85rem; color:var(--text-muted); margin:20px 0; padding:14px; background:#F8FAFC; border-radius:var(--radius-md); }
    .agree-box input { margin-top:3px; }
    .join-error { background:#FEF2F2; border:1px solid #FECACA; color:#B91C1C; padding:12px 16px; border-radius:var(--radius-md); font-size:0.9rem; margin-bottom:20px; }

    @media (max-width:640px) {
        .form-row { grid-template-columns:1fr; }
        .join-card { padding:24px; }
        .join-type-tab .s { display:none; }
    }
</style>

<div class="join-hero">
    <h1>회원가입</h1>
    <p>틴팅 마스터에서 수요고객 · 프리랜서 마스터 · 기업/대리점 회원으로 함께하세요.</p>
</div>

<div class="join-wrap">
    <?php if ($err_msg): ?>
        <div class="join-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($err_msg); ?></div>
    <?php endif; ?>

    <div class="join-type-tabs">
        <div class="join-type-tab" data-type="customer" onclick="selectMemType('customer')">
            <i class="fa-solid fa-user"></i>
            <div class="t">수요 고객</div>
            <div class="s">베란다/건물 시공 요청</div>
        </div>
        <div class="join-type-tab" data-type="freelance" onclick="selectMemType('freelance')">
            <i class="fa-solid fa-id-card"></i>
            <div class="t">프리랜서 마스터</div>
            <div class="s">시공 기술자 회원</div>
        </div>
        <div class="join-type-tab" data-type="partner" onclick="selectMemType('partner')">
            <i class="fa-solid fa-building"></i>
            <div class="t">기업 · 대리점</div>
            <div class="s">법인/대리점 제휴</div>
        </div>
    </div>

    <div class="join-card">
        <form method="post" action="join_proc.php" id="joinForm">
            <input type="hidden" name="mem_type" id="memType" value="<?php echo htmlspecialchars($default_type); ?>">

            <div class="input-box">
                <label>아이디 (이메일 주소) <span class="req">*</span></label>
                <input type="email" name="email" required placeholder="example@email.com (로그인 아이디로 사용)" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : (isset($_GET['uid']) ? htmlspecialchars($_GET['uid']) : ''); ?>">
                <div class="hint">서비스 로그인 시 사용할 이메일 주소를 입력해 주세요.</div>
            </div>

            <div class="form-row">
                <div class="input-box">
                    <label>비밀번호 <span class="req">*</span></label>
                    <input type="password" name="passwd" required minlength="6" placeholder="6자 이상">
                </div>
                <div class="input-box">
                    <label>비밀번호 확인 <span class="req">*</span></label>
                    <input type="password" name="passwd2" required minlength="6" placeholder="비밀번호 재입력">
                </div>
            </div>

            <div class="form-row">
                <div class="input-box">
                    <label>이름 / 담당자명 <span class="req">*</span></label>
                    <input type="text" name="name" required placeholder="홍길동">
                </div>
                <div class="input-box">
                    <label>연락처 <span class="req">*</span></label>
                    <input type="tel" name="hphone" required placeholder="010-0000-0000" pattern="[0-9\-]{9,14}">
                </div>
            </div>

            <!-- 수요 고객 전용 (다음 우편번호 API 연동) -->
            <div class="join-type-fields" id="fields-customer">
                <div class="input-box">
                    <label>우편번호 / 주소 검색</label>
                    <div style="display:flex; gap:8px;">
                        <input type="text" name="zipcode" id="join_zipcode" placeholder="우편번호" readonly style="width:140px; background:#F1F5F9;">
                        <button type="button" onclick="execDaumPostcode()" class="btn btn-outline" style="padding:10px 18px; font-size:0.9rem; font-weight:800; background:white; border-color:var(--primary-dark); color:var(--primary-dark); white-space:nowrap;">
                            <i class="fa-solid fa-magnifying-glass"></i> 우편번호 검색
                        </button>
                    </div>
                </div>
                <div class="input-box">
                    <label>기본 주소</label>
                    <input type="text" name="addr1" id="join_addr1" placeholder="우편번호 검색 시 자동 입력됩니다." readonly style="background:#F1F5F9;">
                </div>
                <div class="input-box">
                    <label>상세주소</label>
                    <input type="text" name="addr2" id="join_addr2" placeholder="상세주소 및 아파트 동·호수 입력">
                </div>
            </div>

            <!-- 프리랜서 마스터 전용 -->
            <div class="join-type-fields" id="fields-freelance">
                <div class="form-row">
                    <div class="input-box">
                        <label>주요 활동지역</label>
                        <input type="text" name="region" placeholder="예: 서울/경기">
                    </div>
                    <div class="input-box">
                        <label>시공 경력</label>
                        <input type="text" name="career" placeholder="예: 8년">
                    </div>
                </div>
            </div>

            <!-- 기업/대리점 전용 -->
            <div class="join-type-fields" id="fields-partner">
                <div class="input-box">
                    <label>상호 / 업체명</label>
                    <input type="text" name="biz_name" placeholder="예: (주)틴팅파트너스">
                </div>
                <div class="input-box">
                    <label>사업자등록번호</label>
                    <input type="text" name="biz_no" placeholder="000-00-00000">
                </div>
            </div>

            <label class="agree-box">
                <input type="checkbox" name="agree_privacy" required value="1">
                <span>개인정보 수집 및 이용에 동의합니다. 가입 시 입력한 정보는 견적 상담 및 회원 서비스 제공 목적으로만 사용됩니다. <span class="req">(필수)</span></span>
            </label>

            <button type="submit" class="btn btn-primary" style="width:100%; padding:15px; font-size:1rem;">
                <i class="fa-solid fa-user-plus"></i> 회원가입 완료하기
            </button>
        </form>
        <p style="text-align:center; margin-top:20px; font-size:0.9rem; color:var(--text-muted);">
            이미 회원이신가요? <a href="login.php" style="color:var(--primary-dark); font-weight:700;">로그인</a>
        </p>
    </div>
</div>

<script>
    function selectMemType(type) {
        document.getElementById('memType').value = type;
        document.querySelectorAll('.join-type-tab').forEach(function (el) {
            el.classList.toggle('active', el.dataset.type === type);
        });
        document.querySelectorAll('.join-type-fields').forEach(function (el) {
            el.classList.remove('active');
        });
        var target = document.getElementById('fields-' + type);
        if (target) target.classList.add('active');
    }

    document.getElementById('joinForm').addEventListener('submit', function (e) {
        var pw = this.passwd.value;
        var pw2 = this.passwd2.value;
        if (pw !== pw2) {
            e.preventDefault();
            alert('비밀번호가 일치하지 않습니다.');
            this.passwd2.focus();
        }
    });

    selectMemType('<?php echo htmlspecialchars($default_type); ?>');

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

                document.getElementById('join_zipcode').value = data.zonecode;
                document.getElementById('join_addr1').value = addr + extraAddr;
                document.getElementById('join_addr2').focus();
            }
        }).open();
    }
</script>

<?php
include_once __DIR__ . "/../inc/footer.php";
?>
