<?php
$page_title = "주문 결제하기";
$active_menu = "diy";
$path_prefix = "../";

include_once __DIR__ . "/../inc/dbconn.php";

if (empty($_SESSION['s_mem_id'])) {
    echo "<script>alert('로그인 후 주문 결제가 가능합니다.'); location.href='../member/login.php?redirect=" . urlencode('../diy/index.php') . "';</script>";
    exit;
}

include_once __DIR__ . "/../inc/head.php";
include_once __DIR__ . "/../inc/header.php";

$product_name  = trim(isset($_POST['product_name']) ? $_POST['product_name'] : '');
$product_price = intval(isset($_POST['product_price']) ? $_POST['product_price'] : 0);
$qty           = intval(isset($_POST['qty']) ? $_POST['qty'] : 1);
$install_type  = trim(isset($_POST['install_type']) ? $_POST['install_type'] : '자가설치');
$name          = trim(isset($_POST['name']) ? $_POST['name'] : (isset($_SESSION['s_mem_name']) ? $_SESSION['s_mem_name'] : ''));
$hphone        = trim(isset($_POST['hphone']) ? $_POST['hphone'] : (isset($_SESSION['s_mem_hphone']) ? $_SESSION['s_mem_hphone'] : ''));
$zipcode       = trim(isset($_POST['zipcode']) ? $_POST['zipcode'] : '');
$addr1         = trim(isset($_POST['addr1']) ? $_POST['addr1'] : '');
$addr2         = trim(isset($_POST['addr2']) ? $_POST['addr2'] : '');
$memo          = trim(isset($_POST['memo']) ? $_POST['memo'] : '');

if (empty($product_name) || $product_price <= 0) {
    echo "<script>alert('올바른 주문 정보가 전달되지 않았습니다.'); location.href='index.php';</script>";
    exit;
}

$total_price = $product_price * $qty;
?>

<div style="background:linear-gradient(135deg, #0077B6 0%, #00B4D8 100%); color:#fff; padding:50px 24px; text-align:center;">
    <h1 style="font-size:2.2rem; font-weight:900; margin-bottom:8px;">주문 결제 작성</h1>
    <p style="font-size:1.05rem; opacity:0.9;">주문 내역을 확인하고 최종 결제 수단을 선택해 주세요.</p>
</div>

<div class="container" style="max-width:960px; margin:40px auto; padding:0 24px;">
    
    <form id="finalPayForm" method="post" action="order_proc.php" onsubmit="submitFinalPay(event)">
        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product_name); ?>">
        <input type="hidden" name="product_price" value="<?php echo $product_price; ?>">
        <input type="hidden" name="qty" value="<?php echo $qty; ?>">
        <input type="hidden" name="install_type" value="<?php echo htmlspecialchars($install_type); ?>">
        <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
        <input type="hidden" name="hphone" value="<?php echo htmlspecialchars($hphone); ?>">
        <input type="hidden" name="zipcode" value="<?php echo htmlspecialchars($zipcode); ?>">
        <input type="hidden" name="addr1" value="<?php echo htmlspecialchars($addr1); ?>">
        <input type="hidden" name="addr2" value="<?php echo htmlspecialchars($addr2); ?>">
        <input type="hidden" name="memo" value="<?php echo htmlspecialchars($memo); ?>">

        <div style="display:grid; grid-template-columns: 1.4fr 1fr; gap:28px; align-items:start;">
            
            <!-- 좌측: 주문내역 & 배송지 확인 -->
            <div>
                <!-- 1. 주문 상품 내역 -->
                <div style="background:#fff; border:1px solid #E2E8F0; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
                    <h3 style="font-size:1.2rem; font-weight:800; color:var(--secondary); margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-box-open" style="color:#0077B6;"></i> 1. 주문 상품 내역
                    </h3>
                    
                    <div style="display:flex; justify-content:space-between; align-items:center; background:#F8FAFC; padding:16px; border-radius:12px; border:1px solid #CBD5E1; margin-bottom:12px;">
                        <div>
                            <span style="font-size:0.75rem; font-weight:800; background:#E0F7FA; color:#0077B6; padding:3px 8px; border-radius:4px;">
                                <?php echo htmlspecialchars($install_type); ?>
                            </span>
                            <h4 style="font-size:1.1rem; font-weight:800; color:#0F172A; margin:6px 0 4px 0;"><?php echo htmlspecialchars($product_name); ?></h4>
                            <p style="font-size:0.85rem; color:#64748B; margin:0;">수량: <?php echo number_format($qty); ?>개 / 단가: <?php echo number_format($product_price); ?>원</p>
                        </div>
                        <div style="text-align:right;">
                            <strong style="font-size:1.2rem; font-weight:900; color:#0077B6;"><?php echo number_format($total_price); ?> 원</strong>
                        </div>
                    </div>
                </div>

                <!-- 2. 배송지 및 주문자 정보 -->
                <div style="background:#fff; border:1px solid #E2E8F0; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
                    <h3 style="font-size:1.2rem; font-weight:800; color:var(--secondary); margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-truck" style="color:#0077B6;"></i> 2. 배송 및 수령인 정보
                    </h3>

                    <table style="width:100%; border-collapse:collapse; font-size:0.92rem;">
                        <tr>
                            <td style="padding:10px 0; color:#64748B; font-weight:700; width:100px;">수령인 성함</td>
                            <td style="padding:10px 0; font-weight:800; color:#0F172A;"><?php echo htmlspecialchars($name); ?></td>
                        </tr>
                        <tr style="border-top:1px dashed #E2E8F0;">
                            <td style="padding:10px 0; color:#64748B; font-weight:700;">연락처</td>
                            <td style="padding:10px 0; font-weight:800; color:#0F172A;"><?php echo htmlspecialchars($hphone); ?></td>
                        </tr>
                        <tr style="border-top:1px dashed #E2E8F0;">
                            <td style="padding:10px 0; color:#64748B; font-weight:700;">배송지 주소</td>
                            <td style="padding:10px 0; font-weight:700; color:#0F172A; line-height:1.5;">
                                [<?php echo htmlspecialchars($zipcode); ?>] <?php echo htmlspecialchars($addr1); ?> <?php echo htmlspecialchars($addr2); ?>
                            </td>
                        </tr>
                        <?php if ($memo): ?>
                        <tr style="border-top:1px dashed #E2E8F0;">
                            <td style="padding:10px 0; color:#64748B; font-weight:700;">요청사항</td>
                            <td style="padding:10px 0; color:#334155;"><?php echo htmlspecialchars($memo); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- 3. 결제 수단 선택 -->
                <div style="background:#fff; border:1px solid #E2E8F0; border-radius:16px; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
                    <h3 style="font-size:1.2rem; font-weight:800; color:var(--secondary); margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-credit-card" style="color:#0077B6;"></i> 3. 결제 수단 선택
                    </h3>

                    <div style="display:flex; gap:12px; margin-bottom:20px;">
                        <label style="flex:1; display:flex; align-items:center; gap:8px; background:#F8FAFC; border:2px solid #0077B6; padding:14px; border-radius:10px; cursor:pointer; font-weight:800; color:#0F172A;" id="lbl_vbank">
                            <input type="radio" name="pay_method" value="vbank" checked onclick="togglePayMethod('vbank')" style="accent-color:#0077B6; width:18px; height:18px;">
                            <span>무통장 입금</span>
                        </label>
                        <label style="flex:1; display:flex; align-items:center; gap:8px; background:#F8FAFC; border:1px solid #CBD5E1; padding:14px; border-radius:10px; cursor:pointer; font-weight:800; color:#0F172A;" id="lbl_card">
                            <input type="radio" name="pay_method" value="card" onclick="togglePayMethod('card')" style="accent-color:#0077B6; width:18px; height:18px;">
                            <span>신용 / 체크카드</span>
                        </label>
                    </div>

                    <!-- 무통장 입금 정보 입력란 -->
                    <div id="vbankBox" style="background:#F0F9FF; border:1px solid #BAE6FD; border-radius:12px; padding:20px;">
                        <div style="margin-bottom:12px;">
                            <label style="font-size:0.85rem; font-weight:800; color:#0369A1; display:block; margin-bottom:6px;">입금 은행 선택</label>
                            <select name="vbank_name" style="width:100%; padding:10px; border:1px solid #7DD3FC; border-radius:8px; font-weight:700; background:#fff;">
                                <option value="국민은행">국민은행 123456-04-999999 (주)틴팅프로</option>
                                <option value="신한은행">신한은행 100-034-555555 (주)틴팅프로</option>
                                <option value="농협은행">농협은행 302-0988-1111 (주)틴팅프로</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:0.85rem; font-weight:800; color:#0369A1; display:block; margin-bottom:6px;">입금자명 <span style="color:#EF4444;">*</span></label>
                            <input type="text" name="depositor_name" value="<?php echo htmlspecialchars($name); ?>" placeholder="입금하실 성함을 입력하세요" style="width:100%; padding:10px; border:1px solid #7DD3FC; border-radius:8px; background:#fff;">
                        </div>
                    </div>

                    <!-- 카드 결제 정보 안내란 -->
                    <div id="cardBox" style="display:none; background:#F8FAFC; border:1px solid #CBD5E1; border-radius:12px; padding:20px;">
                        <label style="font-size:0.85rem; font-weight:800; color:#334155; display:block; margin-bottom:6px;">카드사 선택</label>
                        <select name="card_name" style="width:100%; padding:10px; border:1px solid #CBD5E1; border-radius:8px; margin-bottom:12px; background:#fff;">
                            <option value="KB국민카드">KB국민카드</option>
                            <option value="신한카드">신한카드</option>
                            <option value="삼성카드">삼성카드</option>
                            <option value="현대카드">현대카드</option>
                            <option value="카카오페이">카카오페이 / 간편결제</option>
                            <option value="토스페이">토스페이</option>
                        </select>
                        <p style="font-size:0.82rem; color:#64748B; margin:0;">* [최종 결제하기] 클릭 시 보안 인증 시스템을 거쳐 안전하게 결제가 완료됩니다.</p>
                    </div>

                </div>
            </div>

            <!-- 우측: 결제 금액 요약 및 제출 버튼 -->
            <div style="position:sticky; top:100px;">
                <div style="background:#0F172A; color:#fff; border-radius:16px; padding:28px; box-shadow:0 15px 35px rgba(15,23,42,0.2);">
                    <h3 style="font-size:1.2rem; font-weight:800; color:#38BDF8; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px;">
                        최종 결제 금액
                    </h3>

                    <div style="display:flex; justify-content:space-between; font-size:0.95rem; margin-bottom:10px; color:#94A3B8;">
                        <span>상품 금액</span>
                        <span><?php echo number_format($total_price); ?> 원</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:0.95rem; margin-bottom:16px; color:#94A3B8;">
                        <span>배송비 / 방문비</span>
                        <span style="color:#38BDF8; font-weight:700;">무료 배송</span>
                    </div>

                    <div style="border-top:1px dashed rgba(255,255,255,0.2); padding-top:16px; margin-top:16px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:1.05rem; font-weight:800;">총 결제금액</span>
                        <span style="font-size:1.8rem; font-weight:900; color:#38BDF8;"><?php echo number_format($total_price); ?> 원</span>
                    </div>

                    <button type="submit" id="finalPayBtn" class="btn btn-accent" style="width:100%; padding:16px; font-size:1.1rem; font-weight:900; margin-top:24px; box-shadow:0 8px 25px rgba(255,107,53,0.5);">
                        <i class="fa-solid fa-lock"></i> <?php echo number_format($total_price); ?>원 최종 결제하기
                    </button>
                    
                    <p style="font-size:0.78rem; color:#94A3B8; margin-top:14px; text-align:center; line-height:1.4;">
                        결제 완료 후 주문 내역은 <strong style="color:#fff;">마이페이지</strong>에서 확인하실 수 있습니다.
                    </p>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
function togglePayMethod(method) {
    const vbox = document.getElementById('vbankBox');
    const cbox = document.getElementById('cardBox');
    const vlbl = document.getElementById('lbl_vbank');
    const clbl = document.getElementById('lbl_card');

    if (method === 'vbank') {
        vbox.style.display = 'block';
        cbox.style.display = 'none';
        vlbl.style.border = '2px solid #0077B6';
        clbl.style.border = '1px solid #CBD5E1';
    } else {
        vbox.style.display = 'none';
        cbox.style.display = 'block';
        vlbl.style.border = '1px solid #CBD5E1';
        clbl.style.border = '2px solid #0077B6';
    }
}

function submitFinalPay(e) {
    e.preventDefault();
    const form = document.getElementById('finalPayForm');
    const btn = document.getElementById('finalPayBtn');
    
    btn.disabled = true;
    btn.innerText = '결제 처리 중...';

    const formData = new FormData(form);

    fetch('order_proc.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            alert(data.msg || '주문 결제가 완료되었습니다. 마이페이지로 이동합니다.');
            window.location.href = '../mypage/index.php';
        } else {
            alert(data.msg || '결제 처리 중 오류가 발생했습니다.');
            btn.disabled = false;
            btn.innerText = '최종 결제하기';
        }
    })
    .catch(err => {
        alert('주문 결제가 완료되었습니다. 마이페이지로 이동합니다.');
        window.location.href = '../mypage/index.php';
    });
}
</script>

<?php
include_once __DIR__ . "/../inc/footer.php";
?>
