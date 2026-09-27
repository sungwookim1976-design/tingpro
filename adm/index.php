<?php
$page_title = "대시보드";
$active_page = "dashboard";

include_once __DIR__ . "/../inc/dbconn.php";
include_once __DIR__ . "/inc/auth_check.php";

$cnt_members = sql_cnt('members', '');
$cnt_products = sql_cnt('products', '');
$cnt_orders  = sql_cnt('orders', '');
$cnt_quotes  = sql_cnt('quotes', '');
$cnt_auctions = sql_cnt('auctions', '');
$cnt_community = sql_cnt('community_posts', '');

$recent_quotes = sql_one('quotes', 'no, name, space_type, total_price, state, reg_date', 'order by no desc limit 5');
$recent_orders = sql_one('orders', 'no, name, product_name, total_price, state, reg_date', 'order by no desc limit 5');

include_once __DIR__ . "/inc/adm_head.php";
?>

<div class="adm-stat-grid">
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-users"></i> 총 회원수</div>
        <div class="val"><?php echo number_format($cnt_members); ?></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-box-open"></i> 등록 상품</div>
        <div class="val"><?php echo number_format($cnt_products); ?></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-cart-shopping"></i> DIY 주문</div>
        <div class="val"><?php echo number_format($cnt_orders); ?></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-file-invoice"></i> 견적 신청</div>
        <div class="val"><?php echo number_format($cnt_quotes); ?></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-gavel"></i> 역경매 신청</div>
        <div class="val"><?php echo number_format($cnt_auctions); ?></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-comments"></i> 커뮤니티 게시물</div>
        <div class="val"><?php echo number_format($cnt_community); ?></div>
    </div>
</div>

<div class="adm-section">
    <div class="adm-section-head">
        <h3><i class="fa-solid fa-file-invoice"></i> 최근 견적 신청</h3>
        <a href="quotes/index.php" style="font-size:0.82rem; color:var(--adm-primary); font-weight:700;">전체보기 &rarr;</a>
    </div>
    <div style="overflow-x:auto;">
        <table class="adm-table">
            <thead><tr><th>번호</th><th>신청자</th><th>공간구분</th><th>예상금액</th><th>상태</th><th>신청일</th></tr></thead>
            <tbody>
                <?php if (empty($recent_quotes)): ?>
                    <tr class="empty-row"><td colspan="6">견적 신청 내역이 없습니다.</td></tr>
                <?php else: foreach ($recent_quotes as $q): ?>
                    <tr>
                        <td><?php echo $q['no']; ?></td>
                        <td><?php echo htmlspecialchars($q['name']); ?></td>
                        <td><?php echo htmlspecialchars($q['space_type']); ?></td>
                        <td><?php echo number_format($q['total_price']); ?>원</td>
                        <td><span class="adm-badge state-<?php echo $q['state']; ?>"><?php echo htmlspecialchars($q['state']); ?></span></td>
                        <td><?php echo htmlspecialchars($q['reg_date']); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="adm-section">
    <div class="adm-section-head">
        <h3><i class="fa-solid fa-cart-shopping"></i> 최근 DIY 주문</h3>
        <a href="orders/index.php" style="font-size:0.82rem; color:var(--adm-primary); font-weight:700;">전체보기 &rarr;</a>
    </div>
    <div style="overflow-x:auto;">
        <table class="adm-table">
            <thead><tr><th>번호</th><th>주문자</th><th>상품</th><th>결제금액</th><th>상태</th><th>주문일</th></tr></thead>
            <tbody>
                <?php if (empty($recent_orders)): ?>
                    <tr class="empty-row"><td colspan="6">주문 내역이 없습니다.</td></tr>
                <?php else: foreach ($recent_orders as $o): ?>
                    <tr>
                        <td><?php echo $o['no']; ?></td>
                        <td><?php echo htmlspecialchars($o['name']); ?></td>
                        <td><?php echo htmlspecialchars($o['product_name']); ?></td>
                        <td><?php echo number_format($o['total_price']); ?>원</td>
                        <td><span class="adm-badge state-<?php echo $o['state']; ?>"><?php echo htmlspecialchars($o['state']); ?></span></td>
                        <td><?php echo htmlspecialchars($o['reg_date']); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include_once __DIR__ . "/inc/adm_foot.php";
?>
