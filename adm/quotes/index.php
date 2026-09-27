<?php
$page_title = "견적관리";
$active_page = "quotes";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$allowed_states = ['신규', '상담중', '완료'];

$state_filter = isset($_GET['state']) && in_array($_GET['state'], $allowed_states) ? $_GET['state'] : '';
$sk           = trim((isset($_GET['sk']) ? $_GET['sk'] : ''));

$where = " where 1=1";
if ($state_filter !== '') {
    $where .= " and state='" . mysqli_real_escape_string($conn, $state_filter) . "'";
}
if ($sk !== '') {
    $sk_esc = mysqli_real_escape_string($conn, $sk);
    $where .= " and (name like '%$sk_esc%' or hphone like '%$sk_esc%' or space_type like '%$sk_esc%' or film_name like '%$sk_esc%')";
}

// 통계 수치
$total_cnt     = sql_cnt('quotes', '');
$new_cnt       = sql_cnt('quotes', "and state='신규'");
$consulting_cnt= sql_cnt('quotes', "and state='상담중'");
$completed_cnt = sql_cnt('quotes', "and state='완료'");

// 총 예상 견적 금액 합계
$sum_res = mysqli_query($conn, "SELECT SUM(total_price) as sum_price FROM quotes");
$sum_row = mysqli_fetch_assoc($sum_res);
$total_price_sum = intval((isset($sum_row['sum_price']) ? $sum_row['sum_price'] : 0));

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$filtered_total = sql_cnt('quotes', str_replace(' where 1=1', '', $where));
$total_pages = max(1, (int)ceil($filtered_total / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$quotes = sql_one('quotes', '*', str_replace(' where 1=1', '', $where) . " order by no desc limit $offset, $limit");

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div class="adm-stat-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-file-invoice"></i> 전체 견적 신청</div>
        <div class="val"><?php echo number_format($total_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-envelope-open-text" style="color:#0077B6;"></i> 신규 신청</div>
        <div class="val" style="color:#0077B6;"><?php echo number_format($new_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-comments" style="color:#D97706;"></i> 상담 진행중</div>
        <div class="val" style="color:#D97706;"><?php echo number_format($consulting_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-circle-check" style="color:#059669;"></i> 상담 완료 (총 예상 견적)</div>
        <div class="val" style="color:#059669; font-size:1.5rem;"><?php echo number_format($total_price_sum); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">원</span></div>
    </div>
</div>

<div class="adm-section">
    <div class="adm-section-head" style="flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <h3><i class="fa-solid fa-file-invoice"></i> 견적 신청 목록 (<?php echo number_format($filtered_total); ?>건)</h3>
            <div style="display:flex; gap:6px;">
                <a href="?state=&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '' ? '#0077B6' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '' ? '#fff' : '#334155'; ?>;">전체</a>
                <a href="?state=신규&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '신규' ? '#0077B6' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '신규' ? '#fff' : '#334155'; ?>;">신규</a>
                <a href="?state=상담중&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '상담중' ? '#D97706' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '상담중' ? '#fff' : '#334155'; ?>;">상담중</a>
                <a href="?state=완료&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '완료' ? '#059669' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '완료' ? '#fff' : '#334155'; ?>;">완료</a>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:10px;">
            <form method="get" style="display:flex; gap:6px; align-items:center;">
                <input type="hidden" name="state" value="<?php echo htmlspecialchars($state_filter); ?>">
                <input type="text" name="sk" value="<?php echo htmlspecialchars($sk); ?>" placeholder="신청자/연락처/필름명" style="padding:6px 12px; border:1px solid var(--adm-border); border-radius:6px; font-size:0.85rem; outline:none; width:190px;">
                <button type="submit" class="adm-btn" style="padding:6px 12px; font-size:0.82rem;"><i class="fa-solid fa-magnifying-glass"></i> 검색</button>
            </form>
            <button type="button" onclick="copySelectedQuotes()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#0284C7; color:#0284C7;"><i class="fa-solid fa-copy"></i> 선택 견적 복사</button>
            <button type="button" onclick="deleteSelectedQuotes()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#EF4444; color:#EF4444;"><i class="fa-solid fa-trash"></i> 선택 견적 삭제</button>
            <a href="write.php" class="adm-btn" style="padding:7px 14px; font-size:0.85rem; background:#059669;"><i class="fa-solid fa-plus"></i> 견적 신규 등록</a>
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
                        <th>신청자</th>
                        <th>연락처</th>
                        <th>공간구분</th>
                        <th style="text-align:center;">평수</th>
                        <th>필름 종류</th>
                        <th>예상금액</th>
                        <th>시공희망일</th>
                        <th style="text-align:center;">상태</th>
                        <th>신청일</th>
                        <th style="width:130px; text-align:center;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quotes)): ?>
                        <tr class="empty-row"><td colspan="12">견적 신청 내역이 없습니다.</td></tr>
                    <?php else: foreach ($quotes as $q): ?>
                        <tr>
                            <td style="text-align:center;"><input type="checkbox" name="chk_no[]" value="<?php echo $q['no']; ?>" class="chk-item" style="cursor:pointer;"></td>
                            <td style="text-align:center; font-weight:600; color:var(--adm-muted);"><?php echo $q['no']; ?></td>
                            <td><strong style="color:var(--adm-text);"><?php echo htmlspecialchars($q['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($q['hphone']); ?></td>
                            <td><span class="adm-badge type-customer"><?php echo htmlspecialchars($q['space_type']); ?></span></td>
                            <td style="text-align:center; font-weight:600;"><?php echo (int)$q['py']; ?>평</td>
                            <td><strong style="color:var(--adm-primary);"><?php echo htmlspecialchars($q['film_name']); ?></strong></td>
                            <td><strong style="color:var(--adm-primary);"><?php echo number_format($q['total_price']); ?>원</strong></td>
                            <td style="font-size:0.82rem; color:var(--adm-muted);"><?php echo $q['request_date'] ? htmlspecialchars($q['request_date']) : '-'; ?></td>
                            <td style="text-align:center;">
                                <select onchange="changeQuoteState(<?php echo $q['no']; ?>, this.value)" class="state-select state-<?php echo $q['state']; ?>">
                                    <?php foreach ($allowed_states as $st): ?>
                                        <option value="<?php echo $st; ?>" <?php echo $q['state'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="font-size:0.82rem; color:var(--adm-muted);"><?php echo htmlspecialchars($q['reg_date']); ?></td>
                            <td style="text-align:center; white-space:nowrap;">
                                <a href="write.php?no=<?php echo $q['no']; ?>" class="adm-btn adm-btn-outline" style="padding:4px 9px; font-size:0.78rem;"><i class="fa-solid fa-pen-to-square"></i> 수정</a>
                                <a href="proc.php?mode=delete&no=<?php echo $q['no']; ?>" onclick="return confirm('이 견적 신청건을 삭제하시겠습니까?');" class="adm-btn" style="padding:4px 9px; font-size:0.78rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 삭제</a>
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

function copySelectedQuotes() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('복사할 견적을 하나 이상 선택해 주세요.');
        return;
    }
    if (confirm('선택한 ' + checked.length + '개 견적을 복사하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'copy_bulk';
        document.getElementById('bulkForm').submit();
    }
}

function deleteSelectedQuotes() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('삭제할 견적을 하나 이상 선택해 주세요.');
        return;
    }
    if (confirm('선택한 ' + checked.length + '개 견적을 정말로 삭제하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'delete_bulk';
        document.getElementById('bulkForm').submit();
    }
}

function changeQuoteState(no, state) {
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
