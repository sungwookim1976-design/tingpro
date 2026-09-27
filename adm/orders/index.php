<?php
$page_title = "주문관리";
$active_page = "orders";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$allowed_states = ['주문접수', '결제확인중', '배송준비', '배송중', '배송완료', '취소'];

$state_filter = isset($_GET['state']) && in_array($_GET['state'], $allowed_states) ? $_GET['state'] : '';
$sk           = trim((isset($_GET['sk']) ? $_GET['sk'] : ''));

$where = " where 1=1";
if ($state_filter !== '') {
    $where .= " and state='" . mysqli_real_escape_string($conn, $state_filter) . "'";
}
if ($sk !== '') {
    $sk_esc = mysqli_real_escape_string($conn, $sk);
    $where .= " and (name like '%$sk_esc%' or hphone like '%$sk_esc%' or product_name like '%$sk_esc%')";
}

// 통계 수치
$total_cnt     = sql_cnt('orders', '');
$receipt_cnt   = sql_cnt('orders', "and state='주문접수'");
$shipping_cnt  = sql_cnt('orders', "and state='배송중'");
$completed_cnt = sql_cnt('orders', "and state='배송완료'");

// 총 주문 결제 금액 합계
$sum_res = mysqli_query($conn, "SELECT SUM(total_price) as sum_price FROM orders WHERE state <> '취소'");
$sum_row = mysqli_fetch_assoc($sum_res);
$total_price_sum = intval((isset($sum_row['sum_price']) ? $sum_row['sum_price'] : 0));

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$filtered_total = sql_cnt('orders', str_replace(' where 1=1', '', $where));
$total_pages = max(1, (int)ceil($filtered_total / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$orders = sql_one('orders', '*', str_replace(' where 1=1', '', $where) . " order by no desc limit $offset, $limit");

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div class="adm-stat-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-cart-shopping"></i> 전체 주문</div>
        <div class="val"><?php echo number_format($total_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-clock" style="color:#0077B6;"></i> 주문 접수</div>
        <div class="val" style="color:#0077B6;"><?php echo number_format($receipt_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-truck-fast" style="color:#D97706;"></i> 배송 중</div>
        <div class="val" style="color:#D97706;"><?php echo number_format($shipping_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-circle-check" style="color:#059669;"></i> 배송 완료 (누적 금액)</div>
        <div class="val" style="color:#059669; font-size:1.5rem;"><?php echo number_format($total_price_sum); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">원</span></div>
    </div>
</div>

<div class="adm-section">
    <div class="adm-section-head" style="flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <h3><i class="fa-solid fa-cart-shopping"></i> DIY 주문 목록 (<?php echo number_format($filtered_total); ?>건)</h3>
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <a href="?state=&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '' ? '#0077B6' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '' ? '#fff' : '#334155'; ?>;">전체</a>
                <?php foreach ($allowed_states as $st): ?>
                    <a href="?state=<?php echo urlencode($st); ?>&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === $st ? '#0077B6' : '#F1F5F9'; ?>; color:<?php echo $state_filter === $st ? '#fff' : '#334155'; ?>;"><?php echo $st; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:10px;">
            <form method="get" style="display:flex; gap:6px; align-items:center;">
                <input type="hidden" name="state" value="<?php echo htmlspecialchars($state_filter); ?>">
                <input type="text" name="sk" value="<?php echo htmlspecialchars($sk); ?>" placeholder="주문자/연락처/상품명" style="padding:6px 12px; border:1px solid var(--adm-border); border-radius:6px; font-size:0.85rem; outline:none; width:190px;">
                <button type="submit" class="adm-btn" style="padding:6px 12px; font-size:0.82rem;"><i class="fa-solid fa-magnifying-glass"></i> 검색</button>
            </form>
            <button type="button" onclick="copySelectedOrders()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#0284C7; color:#0284C7;"><i class="fa-solid fa-copy"></i> 선택 주문 복사</button>
            <button type="button" onclick="deleteSelectedOrders()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#EF4444; color:#EF4444;"><i class="fa-solid fa-trash"></i> 선택 주문 삭제</button>
            <a href="write.php" class="adm-btn" style="padding:7px 14px; font-size:0.85rem; background:#059669;"><i class="fa-solid fa-plus"></i> 주문 신규 등록</a>
        </div>
    </div>

    <form id="bulkForm" method="post" action="proc.php">
        <input type="hidden" name="mode" id="bulk_mode" value="copy_bulk">

        <div style="overflow-x:auto;">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;"><input type="checkbox" id="chk_all" onclick="toggleCheckAll(this)" style="cursor:pointer;"></th>
                        <th style="width:60px; text-align:center;">번호</th>
                        <th>주문자</th>
                        <th>연락처</th>
                        <th>상품명</th>
                        <th style="text-align:center;">수량</th>
                        <th>결제금액</th>
                        <th>배송지</th>
                        <th style="text-align:center;">상태</th>
                        <th style="text-align:center;">댓글수</th>
                        <th>주문일</th>
                        <th style="width:130px; text-align:center;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr class="empty-row"><td colspan="12">주문 내역이 없습니다.</td></tr>
                    <?php else: foreach ($orders as $o):
                        $cmt_cnt = sql_cnt('order_comments', "and order_no=" . (int)$o['no']);
                    ?>
                        <tr>
                            <td style="text-align:center;"><input type="checkbox" name="chk_no[]" value="<?php echo $o['no']; ?>" class="chk-item" style="cursor:pointer;"></td>
                            <td style="text-align:center; font-weight:600; color:var(--adm-muted);"><?php echo $o['no']; ?></td>
                            <td><strong style="color:var(--adm-text);"><?php echo htmlspecialchars($o['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($o['hphone']); ?></td>
                            <td><strong style="color:var(--adm-primary);"><?php echo htmlspecialchars($o['product_name']); ?></strong></td>
                            <td style="text-align:center; font-weight:600;"><?php echo (int)$o['qty']; ?></td>
                            <td><strong style="color:var(--adm-primary);"><?php echo number_format($o['total_price']); ?>원</strong></td>
                            <td style="max-width:220px; font-size:0.82rem; color:var(--adm-muted); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?php echo htmlspecialchars(trim(($o['zipcode'] ? '[' . $o['zipcode'] . '] ' : '') . $o['addr1'] . ' ' . $o['addr2'])); ?>">
                                <?php echo htmlspecialchars(trim(($o['zipcode'] ? '[' . $o['zipcode'] . '] ' : '') . $o['addr1'] . ' ' . $o['addr2'])); ?>
                            </td>
                            <td style="text-align:center;">
                                <select onchange="changeOrderState(<?php echo $o['no']; ?>, this.value)" class="state-select state-<?php echo $o['state']; ?>">
                                    <?php foreach ($allowed_states as $st): ?>
                                        <option value="<?php echo $st; ?>" <?php echo $o['state'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="text-align:center;">
                                <a href="write.php?no=<?php echo $o['no']; ?>#comments" class="adm-badge" style="background:#EEF2FF; color:#4F46E5; font-weight:800; cursor:pointer;" title="댓글 관리로 이동">
                                    <i class="fa-solid fa-comments"></i> <?php echo $cmt_cnt; ?>개
                                </a>
                            </td>
                            <td style="font-size:0.82rem; color:var(--adm-muted);"><?php echo htmlspecialchars($o['reg_date']); ?></td>
                            <td style="text-align:center; white-space:nowrap;">
                                <a href="write.php?no=<?php echo $o['no']; ?>" class="adm-btn adm-btn-outline" style="padding:4px 9px; font-size:0.78rem;"><i class="fa-solid fa-pen-to-square"></i> 수정</a>
                                <a href="proc.php?mode=delete&no=<?php echo $o['no']; ?>" onclick="return confirm('이 주문 내역을 삭제하시겠습니까?');" class="adm-btn" style="padding:4px 9px; font-size:0.78rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 삭제</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </form>
    <?php render_adm_pagination($page, $total_pages, $_GET); ?>
</div>

<script>
function toggleCheckAll(master) {
    const items = document.querySelectorAll('.chk-item');
    items.forEach(el => el.checked = master.checked);
}

function copySelectedOrders() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('복사할 주문을 하나 이상 선택해 주세요.');
        return;
    }
    if (confirm('선택한 ' + checked.length + '개 주문을 복사하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'copy_bulk';
        document.getElementById('bulkForm').submit();
    }
}

function deleteSelectedOrders() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('삭제할 주문을 하나 이상 선택해 주세요.');
        return;
    }
    if (confirm('선택한 ' + checked.length + '개 주문을 정말로 삭제하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'delete_bulk';
        document.getElementById('bulkForm').submit();
    }
}

function changeOrderState(no, state) {
    fetch('update_state.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'no=' + no + '&state=' + encodeURIComponent(state)
    })
    .then(res => res.json())
    .then(data => {
        if(data.ok || data.success) {
            location.reload();
        } else {
            alert('상태 변경 중 오류가 발생했습니다.');
        }
    })
    .catch(err => {
        location.reload();
    });
}
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
