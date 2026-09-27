<?php
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$no = isset($_GET['no']) ? (int)$_GET['no'] : 0;
$row = null;
if ($no > 0) {
    $row = sql_one_one('quotes', '*', "and no=" . $no);
    if (!$row) {
        echo "<script>alert('존재하지 않는 견적건입니다.'); location.href='index.php';</script>";
        exit;
    }
}

// 등록된 상품 목록 (필름 단가 참고용)
$products_list = sql_one('products', 'no, name, price, price_unit', 'order by sort_order asc, no asc');

$page_title = $no ? "견적 정보 수정" : "견적 신규 등록";
$active_page = "quotes";
$err_msg = isset($_GET['err']) ? $_GET['err'] : '';

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

            document.getElementById('addr').value = '[' + data.zonecode + '] ' + addr + extraAddr;
        }
    }).open();
}
</script>

<div style="margin-bottom:20px;">
    <a href="index.php" class="adm-btn adm-btn-outline" style="padding:6px 14px; font-size:0.85rem;"><i class="fa-solid fa-arrow-left"></i> 목록으로 돌아가기</a>
</div>

<?php if ($err_msg): ?>
    <div style="background:#FEF2F2; border:1px solid #FECACA; color:#B91C1C; padding:12px 16px; border-radius:8px; font-size:0.9rem; margin-bottom:18px;">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($err_msg); ?>
    </div>
<?php endif; ?>

<div class="adm-form-box" style="max-width:100%;">
    <h3 style="margin-bottom:20px; font-size:1.1rem; font-weight:800; border-bottom:1px solid var(--adm-border); padding-bottom:12px;">
        <i class="fa-solid <?php echo $no ? 'fa-file-invoice' : 'fa-plus'; ?>" style="color:var(--adm-primary);"></i> <?php echo $page_title; ?>
    </h3>

    <form method="post" action="proc.php" onsubmit="return validateQuoteForm(this);">
        <input type="hidden" name="mode" value="<?php echo $no ? 'update' : 'insert'; ?>">
        <input type="hidden" name="no" value="<?php echo $no; ?>">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="adm-form-row">
                <label for="name">신청자 성함 <span style="color:#EF4444;">*</span></label>
                <input type="text" id="name" name="name" required value="<?php echo $row ? htmlspecialchars($row['name']) : ''; ?>" placeholder="홍길동" autofocus>
            </div>
            <div class="adm-form-row">
                <label for="hphone">연락처 <span style="color:#EF4444;">*</span></label>
                <input type="text" id="hphone" name="hphone" required value="<?php echo $row ? htmlspecialchars($row['hphone']) : ''; ?>" placeholder="010-0000-0000">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="adm-form-row">
                <label for="space_type">공간 구분 <span style="color:#EF4444;">*</span></label>
                <select id="space_type" name="space_type" required>
                    <?php foreach (['아파트', '건물/빌딩', '주택', '상가/사무실'] as $sp): ?>
                        <option value="<?php echo $sp; ?>" <?php echo ($row && $row['space_type'] === $sp) ? 'selected' : ''; ?>><?php echo $sp; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="adm-form-row">
                <label for="py">시공 평수 (평)</label>
                <input type="number" id="py" name="py" min="1" value="<?php echo $row ? (int)$row['py'] : 30; ?>" oninput="calcTotalPrice()">
            </div>
        </div>

        <!-- 필름 선택 및 금액 계산 -->
        <div class="adm-form-row" style="background:#F8FAFC; padding:16px; border-radius:10px; border:1px solid var(--adm-border);">
            <h4 style="font-size:0.88rem; color:var(--adm-text); margin-bottom:12px;"><i class="fa-solid fa-calculator" style="color:var(--adm-primary);"></i> 필름 선택 및 견적 금액</h4>
            
            <div class="adm-form-row">
                <label for="film_name">필름 종류 <span style="color:#EF4444;">*</span></label>
                <input type="text" id="film_name" name="film_name" required value="<?php echo $row ? htmlspecialchars($row['film_name']) : ''; ?>" placeholder="예: VULUX Nano Ceramic 85">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="adm-form-row" style="margin-bottom:0;">
                    <label for="film_price_py">평당 단가 (원)</label>
                    <input type="number" id="film_price_py" name="film_price_py" min="0" step="1000" value="<?php echo $row ? (int)$row['film_price_py'] : 35000; ?>" oninput="calcTotalPrice()">
                </div>
                <div class="adm-form-row" style="margin-bottom:0;">
                    <label for="total_price">예상 총 견적액 (원)</label>
                    <input type="number" id="total_price" name="total_price" min="0" step="1000" value="<?php echo $row ? (int)$row['total_price'] : 0; ?>" style="font-weight:800; color:var(--adm-primary);">
                </div>
            </div>
        </div>

        <div class="adm-form-row">
            <label for="addr">시공 주소</label>
            <div style="display:flex; gap:8px; margin-bottom:8px;">
                <input type="text" id="addr" name="addr" value="<?php echo $row ? htmlspecialchars($row['addr']) : ''; ?>" placeholder="시공 주소 입력 또는 검색" style="flex:1;">
                <button type="button" onclick="execDaumPostcode()" class="adm-btn adm-btn-outline" style="padding:8px 14px; font-size:0.85rem;"><i class="fa-solid fa-magnifying-glass"></i> 주소 검색</button>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="adm-form-row">
                <label for="request_date">시공 희망일</label>
                <input type="date" id="request_date" name="request_date" value="<?php echo $row ? htmlspecialchars($row['request_date']) : ''; ?>">
            </div>
            <div class="adm-form-row">
                <label for="state">상담 상태 <span style="color:#EF4444;">*</span></label>
                <select id="state" name="state" required>
                    <?php foreach (['신규', '상담중', '완료'] as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo ($row && $row['state'] === $st) ? 'selected' : ''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="adm-form-row">
            <label for="memo">요청사항 / 메모</label>
            <textarea id="memo" name="memo" rows="3" placeholder="고객 요청사항이나 상담 메모를 입력하세요."><?php echo $row ? htmlspecialchars($row['memo']) : ''; ?></textarea>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:28px; border-top:1px solid var(--adm-border); padding-top:20px;">
            <div style="display:flex; gap:10px;">
                <button type="submit" class="adm-btn" style="padding:11px 24px; font-size:0.92rem;"><i class="fa-solid fa-floppy-disk"></i> <?php echo $no ? '수정사항 저장' : '견적 등록 완료'; ?></button>
                <a href="index.php" class="adm-btn adm-btn-outline" style="padding:11px 20px; font-size:0.92rem;">취소</a>
            </div>
            <?php if ($no): ?>
                <a href="proc.php?mode=delete&no=<?php echo $no; ?>" onclick="return confirm('이 견적 신청건을 삭제하시겠습니까?');" class="adm-btn" style="padding:11px 18px; font-size:0.92rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 견적 삭제</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
function calcTotalPrice() {
    const py = parseInt(document.getElementById('py').value) || 0;
    const pricePy = parseInt(document.getElementById('film_price_py').value) || 0;
    document.getElementById('total_price').value = py * pricePy;
}

function validateQuoteForm(f) {
    if (!f.name.value.trim() || !f.hphone.value.trim()) {
        alert('신청자 성함과 연락처를 입력해 주세요.');
        return false;
    }
    if (!f.film_name.value.trim()) {
        alert('필름 종류를 입력해 주세요.');
        f.film_name.focus();
        return false;
    }
    return true;
}

// 초기 금액 계산
calcTotalPrice();
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
