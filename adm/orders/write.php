<?php
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$no = isset($_GET['no']) ? (int)$_GET['no'] : 0;
$row = null;
if ($no > 0) {
    $row = sql_one_one('orders', '*', "and no=" . $no);
    if (!$row) {
        echo "<script>alert('존재하지 않는 주문건입니다.'); location.href='index.php';</script>";
        exit;
    }
}

// 등록된 상품 목록 가져오기
$products_list = sql_one('products', 'no, name, price, price_unit', 'order by sort_order asc, no asc');

$page_title = $no ? "주문 정보 수정" : "주문 신규 등록";
$active_page = "orders";
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

<?php if ($err_msg): ?>
    <div style="background:#FEF2F2; border:1px solid #FECACA; color:#B91C1C; padding:12px 16px; border-radius:8px; font-size:0.9rem; margin-bottom:18px;">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($err_msg); ?>
    </div>
<?php endif; ?>

<div class="adm-form-box" style="max-width:100%;">
    <h3 style="margin-bottom:20px; font-size:1.1rem; font-weight:800; border-bottom:1px solid var(--adm-border); padding-bottom:12px;">
        <i class="fa-solid <?php echo $no ? 'fa-cart-shopping' : 'fa-plus'; ?>" style="color:var(--adm-primary);"></i> <?php echo $page_title; ?>
    </h3>

    <form method="post" action="proc.php" onsubmit="return validateOrderForm(this);">
        <input type="hidden" name="mode" value="<?php echo $no ? 'update' : 'insert'; ?>">
        <input type="hidden" name="no" value="<?php echo $no; ?>">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="adm-form-row">
                <label for="name">주문자 성함 <span style="color:#EF4444;">*</span></label>
                <input type="text" id="name" name="name" required value="<?php echo $row ? htmlspecialchars($row['name']) : ''; ?>" placeholder="홍길동" autofocus>
            </div>
            <div class="adm-form-row">
                <label for="hphone">연락처 <span style="color:#EF4444;">*</span></label>
                <input type="text" id="hphone" name="hphone" required value="<?php echo $row ? htmlspecialchars($row['hphone']) : ''; ?>" placeholder="010-0000-0000">
            </div>
        </div>

        <!-- 상품 선택 및 결제 정보 -->
        <div class="adm-form-row" style="background:#F8FAFC; padding:16px; border-radius:10px; border:1px solid var(--adm-border);">
            <h4 style="font-size:0.88rem; color:var(--adm-text); margin-bottom:12px;"><i class="fa-solid fa-box" style="color:var(--adm-primary);"></i> 주문 상품 및 결제 금액</h4>
            
            <div class="adm-form-row">
                <label for="select_prod">등록 상품 선택</label>
                <select id="select_prod" onchange="onSelectProduct(this)">
                    <option value="">-- 상품 선택 (또는 직접 입력) --</option>
                    <?php foreach ($products_list as $p): ?>
                        <option value="<?php echo htmlspecialchars($p['name']); ?>" data-price="<?php echo $p['price']; ?>"><?php echo htmlspecialchars($p['name']); ?> (<?php echo number_format($p['price']); ?>원/<?php echo htmlspecialchars($p['price_unit']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="adm-form-row">
                <label for="product_name">상품명 <span style="color:#EF4444;">*</span></label>
                <input type="text" id="product_name" name="product_name" required value="<?php echo $row ? htmlspecialchars($row['product_name']) : ''; ?>" placeholder="상품명 입력">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <div class="adm-form-row" style="margin-bottom:0;">
                    <label for="product_price">상품 단가 (원)</label>
                    <input type="number" id="product_price" name="product_price" min="0" step="100" value="<?php echo $row ? (int)$row['product_price'] : 0; ?>" oninput="calcTotalPrice()">
                </div>
                <div class="adm-form-row" style="margin-bottom:0;">
                    <label for="qty">주문 수량</label>
                    <input type="number" id="qty" name="qty" min="1" value="<?php echo $row ? (int)$row['qty'] : 1; ?>" oninput="calcTotalPrice()">
                </div>
                <div class="adm-form-row" style="margin-bottom:0;">
                    <label for="total_price">총 결제 금액 (원)</label>
                    <input type="number" id="total_price" name="total_price" min="0" step="100" value="<?php echo $row ? (int)$row['total_price'] : 0; ?>" style="font-weight:800; color:var(--adm-primary);">
                </div>
            </div>
        </div>

        <div class="adm-form-row">
            <label for="zipcode">우편번호 / 배송지 검색</label>
            <div style="display:flex; gap:8px;">
                <input type="text" id="zipcode" name="zipcode" value="<?php echo $row ? htmlspecialchars($row['zipcode']) : ''; ?>" placeholder="우편번호" readonly style="width:140px; background:#F8FAFC;">
                <button type="button" onclick="execDaumPostcode()" class="adm-btn adm-btn-outline" style="padding:8px 14px; font-size:0.85rem;"><i class="fa-solid fa-magnifying-glass"></i> 우편번호 검색</button>
            </div>
        </div>

        <div class="adm-form-row">
            <label for="addr1">배송지 주소</label>
            <input type="text" id="addr1" name="addr1" value="<?php echo $row ? htmlspecialchars($row['addr1']) : ''; ?>" placeholder="우편번호 검색 시 자동 입력됩니다." readonly style="background:#F8FAFC; margin-bottom:8px;">
            <input type="text" id="addr2" name="addr2" value="<?php echo $row ? htmlspecialchars($row['addr2']) : ''; ?>" placeholder="상세 주소 및 아파트 동·호수 입력">
        </div>

        <div class="adm-form-row">
            <label for="memo">배송 요청사항 / 메모</label>
            <input type="text" id="memo" name="memo" value="<?php echo $row ? htmlspecialchars($row['memo']) : ''; ?>" placeholder="예: 부재 시 문 앞에 놓아주세요.">
        </div>

        <div class="adm-form-row">
            <label for="state">주문 / 배송 상태 <span style="color:#EF4444;">*</span></label>
            <select id="state" name="state" required>
                <?php foreach (['주문접수', '결제확인중', '배송준비', '배송중', '배송완료', '취소'] as $st): ?>
                    <option value="<?php echo $st; ?>" <?php echo ($row && $row['state'] === $st) ? 'selected' : ''; ?>><?php echo $st; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:28px; border-top:1px solid var(--adm-border); padding-top:20px;">
            <div style="display:flex; gap:10px;">
                <button type="submit" class="adm-btn" style="padding:11px 24px; font-size:0.92rem;"><i class="fa-solid fa-floppy-disk"></i> <?php echo $no ? '수정사항 저장' : '주문 등록 완료'; ?></button>
                <a href="index.php" class="adm-btn adm-btn-outline" style="padding:11px 20px; font-size:0.92rem;">취소</a>
            </div>
            <?php if ($no): ?>
                <a href="proc.php?mode=delete&no=<?php echo $no; ?>" onclick="return confirm('이 주문 내역을 삭제하시겠습니까?');" class="adm-btn" style="padding:11px 18px; font-size:0.92rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 주문 삭제</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ($no > 0):
    $order_comments = sql_one('order_comments', '*', "and order_no=" . $no . " order by no desc");
    $adm_name = isset($_SESSION['s_adm_name']) ? $_SESSION['s_adm_name'] : '관리자';
?>
<div class="adm-form-box" id="comments" style="max-width:100%; margin-top:24px;">
    <h3 style="margin-bottom:16px; font-size:1.05rem; font-weight:800; border-bottom:1px solid var(--adm-border); padding-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
        <span><i class="fa-solid fa-comments" style="color:var(--adm-primary);"></i> 주문 댓글 / 특이사항 메모 (<?php echo count($order_comments); ?>개)</span>
    </h3>

    <!-- 신규 댓글 등록 폼 -->
    <form method="post" action="proc.php" style="margin-bottom:24px; background:#F8FAFC; padding:16px; border-radius:10px; border:1px solid var(--adm-border);">
        <input type="hidden" name="mode" value="comment_insert">
        <input type="hidden" name="order_no" value="<?php echo $no; ?>">
        <div style="display:flex; gap:10px; margin-bottom:8px;">
            <input type="text" name="writer" value="<?php echo htmlspecialchars($adm_name); ?>" required placeholder="작성자 이름" style="width:140px; padding:7px 10px; font-size:0.85rem;">
            <span style="font-size:0.8rem; color:var(--adm-muted); align-self:center;">로그인 관리자명이 기본 세팅됩니다.</span>
        </div>
        <div style="display:flex; gap:10px;">
            <textarea name="content" required rows="2" placeholder="주문 관련 처리 메모나 특이사항 댓글을 입력하세요." style="flex:1; padding:8px 10px; font-size:0.88rem; outline:none; border:1px solid var(--adm-border); border-radius:6px;"></textarea>
            <button type="submit" class="adm-btn" style="padding:8px 18px; font-size:0.85rem; height:auto; background:var(--adm-primary);"><i class="fa-solid fa-paper-plane"></i> 댓글 등록</button>
        </div>
    </form>

    <!-- 댓글 목록 -->
    <div style="display:flex; flex-direction:column; gap:12px;">
        <?php if (empty($order_comments)): ?>
            <div style="text-align:center; padding:24px; color:var(--adm-muted); font-size:0.88rem;">등록된 댓글/메모가 없습니다.</div>
        <?php else: foreach ($order_comments as $cmt): ?>
            <div id="cmt-box-<?php echo $cmt['no']; ?>" style="background:#fff; border:1px solid var(--adm-border); border-radius:8px; padding:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <strong style="font-size:0.88rem; color:var(--adm-text);"><i class="fa-solid fa-user-gear" style="color:var(--adm-primary);"></i> <?php echo htmlspecialchars($cmt['writer']); ?></strong>
                        <span style="font-size:0.75rem; color:var(--adm-muted);"><?php echo htmlspecialchars($cmt['reg_date']); ?></span>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button type="button" onclick="toggleEditCmt(<?php echo $cmt['no']; ?>)" class="adm-btn adm-btn-outline" style="padding:2px 8px; font-size:0.75rem;"><i class="fa-solid fa-pen"></i> 수정</button>
                        <a href="proc.php?mode=comment_delete&no=<?php echo $cmt['no']; ?>&order_no=<?php echo $no; ?>" onclick="return confirm('이 댓글을 삭제하시겠습니까?');" class="adm-btn" style="padding:2px 8px; font-size:0.75rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 삭제</a>
                    </div>
                </div>
                
                <!-- 일반 뷰 -->
                <div id="cmt-view-<?php echo $cmt['no']; ?>" style="font-size:0.9rem; color:#334155; white-space:pre-wrap; line-height:1.5;"><?php echo htmlspecialchars($cmt['content']); ?></div>

                <!-- 수정 폼 -->
                <form id="cmt-edit-<?php echo $cmt['no']; ?>" method="post" action="proc.php" style="display:none; margin-top:8px;">
                    <input type="hidden" name="mode" value="comment_update">
                    <input type="hidden" name="no" value="<?php echo $cmt['no']; ?>">
                    <input type="hidden" name="order_no" value="<?php echo $no; ?>">
                    <textarea name="content" required rows="2" style="width:100%; padding:8px; font-size:0.88rem; margin-bottom:6px;"><?php echo htmlspecialchars($cmt['content']); ?></textarea>
                    <div style="display:flex; justify-content:flex-end; gap:6px;">
                        <button type="button" onclick="toggleEditCmt(<?php echo $cmt['no']; ?>)" class="adm-btn adm-btn-outline" style="padding:4px 10px; font-size:0.78rem;">취소</button>
                        <button type="submit" class="adm-btn" style="padding:4px 12px; font-size:0.78rem; background:var(--adm-primary);">수정 저장</button>
                    </div>
                </form>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<script>
function toggleEditCmt(no) {
    const v = document.getElementById('cmt-view-' + no);
    const e = document.getElementById('cmt-edit-' + no);
    if (e.style.display === 'none') {
        e.style.display = 'block';
        v.style.display = 'none';
    } else {
        e.style.display = 'none';
        v.style.display = 'block';
    }
}
</script>
<?php endif; ?>

<script>
function onSelectProduct(sel) {
    if (sel.value) {
        document.getElementById('product_name').value = sel.value;
        const opt = sel.options[sel.selectedIndex];
        const price = opt.dataset.price ? parseInt(opt.dataset.price) : 0;
        document.getElementById('product_price').value = price;
        calcTotalPrice();
    }
}

function calcTotalPrice() {
    const price = parseInt(document.getElementById('product_price').value) || 0;
    const qty = parseInt(document.getElementById('qty').value) || 1;
    document.getElementById('total_price').value = price * qty;
}

function validateOrderForm(f) {
    if (!f.name.value.trim() || !f.hphone.value.trim()) {
        alert('주문자 성함과 연락처를 입력해 주세요.');
        return false;
    }
    if (!f.product_name.value.trim()) {
        alert('주문 상품명을 입력해 주세요.');
        f.product_name.focus();
        return false;
    }
    return true;
}
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
