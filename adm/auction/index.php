<?php
$page_title = "역경매관리";
$active_page = "auction";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$allowed_states = ['입찰대기', '입찰중', '매칭완료', '취소'];

$state_filter = isset($_GET['state']) && in_array($_GET['state'], $allowed_states) ? $_GET['state'] : '';
$sk           = trim((isset($_GET['sk']) ? $_GET['sk'] : ''));

$where = " where 1=1";
if ($state_filter !== '') {
    $where .= " and state='" . mysqli_real_escape_string($conn, $state_filter) . "'";
}
if ($sk !== '') {
    $sk_esc = mysqli_real_escape_string($conn, $sk);
    $where .= " and (name like '%$sk_esc%' or hphone like '%$sk_esc%' or space_type like '%$sk_esc%' or addr like '%$sk_esc%')";
}

// 통계 수치
$total_cnt     = sql_cnt('auctions', '');
$waiting_cnt   = sql_cnt('auctions', "and state='입찰대기'");
$bidding_cnt   = sql_cnt('auctions', "and state='입찰중'");
$matched_cnt   = sql_cnt('auctions', "and state='매칭완료'");

// 총 희망 예산 합계
$sum_res = mysqli_query($conn, "SELECT SUM(desired_price) as sum_price FROM auctions WHERE state <> '취소'");
$sum_row = mysqli_fetch_assoc($sum_res);
$total_price_sum = intval((isset($sum_row['sum_price']) ? $sum_row['sum_price'] : 0));

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$filtered_total = sql_cnt('auctions', str_replace(' where 1=1', '', $where));
$total_pages = max(1, (int)ceil($filtered_total / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$auctions = sql_one('auctions', '*', str_replace(' where 1=1', '', $where) . " order by no desc limit $offset, $limit");

$auc_bids_map = [];
if (!empty($auctions)) {
    $a_ids = array_map(function($a) { return (int)$a['no']; }, $auctions);
    $ids_in = implode(',', $a_ids);
    $bids_res = mysqli_query($conn, "SELECT * FROM auction_bids WHERE auction_no IN ($ids_in) ORDER BY bid_price ASC, no ASC");
    if ($bids_res) {
        while ($b_row = mysqli_fetch_assoc($bids_res)) {
            $auc_bids_map[$b_row['auction_no']][] = $b_row;
        }
    }
}

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div class="adm-stat-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-gavel"></i> 전체 역경매</div>
        <div class="val"><?php echo number_format($total_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-clock" style="color:#0077B6;"></i> 입찰 대기</div>
        <div class="val" style="color:#0077B6;"><?php echo number_format($waiting_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-handshake" style="color:#D97706;"></i> 입찰 진행중</div>
        <div class="val" style="color:#D97706;"><?php echo number_format($bidding_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-circle-check" style="color:#059669;"></i> 매칭 완료 (총 희망예산)</div>
        <div class="val" style="color:#059669; font-size:1.5rem;"><?php echo number_format($total_price_sum); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">원</span></div>
    </div>
</div>

<div class="adm-section">
    <div class="adm-section-head" style="flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <h3><i class="fa-solid fa-gavel"></i> 역경매 신청 목록 (<?php echo number_format($filtered_total); ?>건)</h3>
            <div style="display:flex; gap:6px;">
                <a href="?state=&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '' ? '#0077B6' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '' ? '#fff' : '#334155'; ?>;">전체</a>
                <a href="?state=입찰대기&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '입찰대기' ? '#0077B6' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '입찰대기' ? '#fff' : '#334155'; ?>;">입찰대기</a>
                <a href="?state=입찰중&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '입찰중' ? '#D97706' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '입찰중' ? '#fff' : '#334155'; ?>;">입찰중</a>
                <a href="?state=매칭완료&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '매칭완료' ? '#059669' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '매칭완료' ? '#fff' : '#334155'; ?>;">매칭완료</a>
                <a href="?state=취소&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '취소' ? '#EF4444' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '취소' ? '#fff' : '#334155'; ?>;">취소</a>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <form method="get" style="display:flex; gap:6px; align-items:center;">
                <input type="hidden" name="state" value="<?php echo htmlspecialchars($state_filter); ?>">
                <input type="text" name="sk" value="<?php echo htmlspecialchars($sk); ?>" placeholder="신청자/연락처/주소" style="padding:6px 12px; border:1px solid var(--adm-border); border-radius:6px; font-size:0.85rem; outline:none; width:190px;">
                <button type="submit" class="adm-btn" style="padding:6px 12px; font-size:0.82rem;"><i class="fa-solid fa-magnifying-glass"></i> 검색</button>
            </form>
            <button type="button" onclick="copySelectedAuctions()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#0284C7; color:#0284C7;"><i class="fa-solid fa-copy"></i> 선택 역경매 복사</button>
            <button type="button" onclick="deleteSelectedAuctions()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#EF4444; color:#EF4444;"><i class="fa-solid fa-trash"></i> 선택 역경매 삭제</button>
            <a href="write.php" class="adm-btn" style="padding:7px 14px; font-size:0.85rem; background:#059669;"><i class="fa-solid fa-plus"></i> 역경매 신규 등록</a>
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
                        <th>시공주소</th>
                        <th>희망예산</th>
                        <th style="text-align:center;">입찰수</th>
                        <th style="text-align:center;">댓글수</th>
                        <th style="text-align:center;">상태</th>
                        <th>신청일</th>
                        <th style="width:130px; text-align:center;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($auctions)): ?>
                        <tr class="empty-row"><td colspan="13">역경매 신청 내역이 없습니다.</td></tr>
                    <?php else: foreach ($auctions as $a):
                        $a_bids_list  = isset($auc_bids_map[$a['no']]) ? $auc_bids_map[$a['no']] : [];
                        $real_bid_cnt = count($a_bids_list);
                        $cmt_cnt      = sql_cnt('auction_comments', "and auction_no=" . (int)$a['no']);
                    ?>
                        <tr>
                            <td style="text-align:center;"><input type="checkbox" name="chk_no[]" value="<?php echo $a['no']; ?>" class="chk-item" style="cursor:pointer;"></td>
                            <td style="text-align:center; font-weight:600; color:var(--adm-muted);"><?php echo $a['no']; ?></td>
                            <td><strong style="color:var(--adm-text);"><?php echo htmlspecialchars($a['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($a['hphone']); ?></td>
                            <td><span class="adm-badge type-customer"><?php echo htmlspecialchars($a['space_type']); ?></span></td>
                            <td style="text-align:center; font-weight:600;"><?php echo (int)$a['py']; ?>평</td>
                            <td style="max-width:200px; font-size:0.82rem; color:var(--adm-muted); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?php echo htmlspecialchars($a['addr']); ?>">
                                <?php echo htmlspecialchars($a['addr']); ?>
                            </td>
                            <td><strong style="color:var(--adm-primary);"><?php echo number_format($a['desired_price']); ?>원</strong></td>
                            <td style="text-align:center;">
                                <button type="button" onclick="openBidsModal(<?php echo $a['no']; ?>)" class="adm-badge" style="background:#FEF3C7; color:#D97706; font-weight:800; border:none; cursor:pointer; padding:5px 9px;" title="입찰 제안 목록 (최저가순 노출)">
                                    <i class="fa-solid fa-gavel"></i> <?php echo $real_bid_cnt; ?>건
                                </button>
                            </td>
                            <td style="text-align:center;">
                                <a href="write.php?no=<?php echo $a['no']; ?>#comments" class="adm-badge" style="background:#EEF2FF; color:#4F46E5; font-weight:800; cursor:pointer;" title="댓글 관리로 이동">
                                    <i class="fa-solid fa-comments"></i> <?php echo $cmt_cnt; ?>개
                                </a>
                            </td>
                            <td style="text-align:center;">
                                <select onchange="changeAuctionState(<?php echo $a['no']; ?>, this.value)" class="state-select state-<?php echo $a['state']; ?>">
                                    <?php foreach ($allowed_states as $st): ?>
                                        <option value="<?php echo $st; ?>" <?php echo $a['state'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="font-size:0.82rem; color:var(--adm-muted);"><?php echo htmlspecialchars($a['reg_date']); ?></td>
                            <td style="text-align:center; white-space:nowrap;">
                                <a href="write.php?no=<?php echo $a['no']; ?>" class="adm-btn adm-btn-outline" style="padding:4px 9px; font-size:0.78rem;"><i class="fa-solid fa-pen-to-square"></i> 수정</a>
                                <a href="proc.php?mode=delete&no=<?php echo $a['no']; ?>" onclick="return confirm('이 역경매 신청건을 삭제하시겠습니까?');" class="adm-btn" style="padding:4px 9px; font-size:0.78rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 삭제</a>
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

function copySelectedAuctions() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('복사할 역경매 항목을 하나 이상 선택해 주세요.');
        return;
    }
    if (confirm('선택한 ' + checked.length + '개 역경매 항목을 복사하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'copy_bulk';
        document.getElementById('bulkForm').submit();
    }
}

function deleteSelectedAuctions() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('삭제할 역경매 항목을 하나 이상 선택해 주세요.');
        return;
    }
    if (confirm('선택한 ' + checked.length + '개 역경매 항목을 정말로 삭제하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'delete_bulk';
        document.getElementById('bulkForm').submit();
    }
}

function changeAuctionState(no, state) {
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

<!-- 입찰제안 목록 팝업 모달 (최저가 순 노출 & 낙찰자 선택) -->
<div id="bidsModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.75); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:14px; max-width:920px; width:100%; max-height:85vh; overflow-y:auto; padding:24px; box-shadow:0 25px 60px rgba(0,0,0,0.35); position:relative;">
        <button type="button" onclick="closeBidsModal()" style="position:absolute; top:16px; right:20px; background:none; border:none; font-size:1.6rem; cursor:pointer; color:#64748B;">&times;</button>
        <div id="bidsModalContent"></div>
    </div>
</div>

<script>
    const auctionsData = <?php echo json_encode(array_column($auctions, null, 'no')); ?>;
    const bidsMapData = <?php echo json_encode($auc_bids_map); ?>;

    function openBidsModal(aucNo) {
        const item = auctionsData[aucNo];
        const bids = bidsMapData[aucNo] || [];

        let rowsHtml = '';
        if (bids.length === 0) {
            rowsHtml = '<tr><td colspan="9" style="text-align:center; padding:32px; color:#64748B; font-size:0.9rem;">등록된 입찰 제안 내역이 없습니다.</td></tr>';
        } else {
            bids.forEach((b, idx) => {
                let rankBadge = '';
                if (b.state === '낙찰') {
                    rankBadge = '<span class="adm-badge" style="background:#059669; color:#fff; font-weight:800;"><i class="fa-solid fa-crown"></i> 최종 낙찰</span>';
                } else if (idx === 0) {
                    rankBadge = '<span class="adm-badge" style="background:#ECFDF5; color:#059669; font-weight:900;"><i class="fa-solid fa-trophy"></i> 1위 (최저가)</span>';
                } else if (idx === 1) {
                    rankBadge = '<span class="adm-badge" style="background:#FEF3C7; color:#D97706; font-weight:800;">2위</span>';
                } else if (idx === 2) {
                    rankBadge = '<span class="adm-badge" style="background:#E0F7FA; color:#0077B6; font-weight:800;">3위</span>';
                } else {
                    rankBadge = `<span style="font-size:0.8rem; color:#64748B; font-weight:700;">${idx + 1}위</span>`;
                }

                const priceStr = new Intl.NumberFormat().format(b.bid_price);
                const selectWinnerBtn = (b.state === '낙찰') 
                    ? '<span class="adm-badge" style="background:#059669; color:#fff; font-weight:800; padding:4px 8px;"><i class="fa-solid fa-check"></i> 낙찰됨</span>'
                    : `<a href="proc.php?mode=select_winner&no=${b.no}&auction_no=${aucNo}" onclick="return confirm('${escapeHtml(b.bidder_name)}님(제안가: ${priceStr}원)을 최종 낙찰자로 선택하시겠습니까?\\n역경매 상태가 \'매칭완료\'로 변경됩니다.');" class="adm-btn" style="padding:3px 9px; font-size:0.78rem; background:#059669; color:#fff;"><i class="fa-solid fa-trophy"></i> 낙찰 선택</a>`;

                rowsHtml += `
                    <tr style="${b.state === '낙찰' ? 'background:#F0FDF4;' : (idx === 0 ? 'background:#FFFBEB;' : '')}">
                        <td style="text-align:center;">${rankBadge}</td>
                        <td><strong>${escapeHtml(b.bidder_name)}</strong></td>
                        <td><span class="adm-badge type-customer" style="font-size:0.72rem;">${escapeHtml(b.bidder_type)}</span></td>
                        <td><strong style="color:${idx === 0 ? '#059669' : '#0077B6'}; font-size:0.95rem;">${priceStr}원</strong></td>
                        <td>${escapeHtml(b.work_duration || '-')}</td>
                        <td style="max-width:180px; font-size:0.82rem; color:#475569;" title="${escapeHtml(b.memo)}">${escapeHtml(b.memo || '-')}</td>
                        <td style="text-align:center;"><span class="adm-badge state-${b.state}">${escapeHtml(b.state)}</span></td>
                        <td style="text-align:center;">${selectWinnerBtn}</td>
                        <td style="font-size:0.78rem; color:#94A3B8;">${b.reg_date ? b.reg_date.substring(0, 16) : ''}</td>
                    </tr>
                `;
            });
        }

        const modalHtml = `
            <div style="border-bottom:1px solid #E2E8F0; padding-bottom:16px; margin-bottom:20px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                    <span class="adm-badge" style="background:#0077B6; color:#fff; font-weight:800;">No.${aucNo}</span>
                    <span class="adm-badge type-customer">${item ? escapeHtml(item.space_type) : ''} ${item ? item.py : ''}평</span>
                    <span style="font-size:0.85rem; color:#64748B;">희망예산: <strong>${item ? new Intl.NumberFormat().format(item.desired_price) : 0}원</strong></span>
                </div>
                <h3 style="font-size:1.25rem; font-weight:900; color:#0F172A; margin:0;">
                    <i class="fa-solid fa-gavel" style="color:#D97706;"></i> ${item ? escapeHtml(item.name) : ''} 고객님 역경매 입찰 제안 목록 (총 ${bids.length}건 / 최저가순 정렬)
                </h3>
            </div>

            <div style="overflow-x:auto; margin-bottom:20px;">
                <table class="adm-table" style="font-size:0.85rem;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="text-align:center; width:110px;">순위 (최저가)</th>
                            <th>입찰자/업체명</th>
                            <th>구분</th>
                            <th>입찰 제안가</th>
                            <th>시공기간</th>
                            <th>제안 메모</th>
                            <th style="text-align:center;">상태</th>
                            <th style="text-align:center;">낙찰 선택</th>
                            <th>입찰일시</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #E2E8F0; padding-top:16px;">
                <span style="font-size:0.82rem; color:#64748B;"><i class="fa-solid fa-circle-info" style="color:#0077B6;"></i> 가장 저렴한 입찰 제안가가 1위(최상위)로 노출되며, '낙찰 선택' 클릭 시 최종 낙찰 결정 및 상태가 '매칭완료'로 변경됩니다.</span>
                <div style="display:flex; gap:8px;">
                    <a href="write.php?no=${aucNo}#bids" class="adm-btn" style="background:#D97706; padding:8px 16px; font-size:0.85rem;"><i class="fa-solid fa-plus"></i> 제안 추가 / 입찰 관리</a>
                    <button type="button" onclick="closeBidsModal()" class="adm-btn adm-btn-outline" style="padding:8px 16px; font-size:0.85rem;">닫기</button>
                </div>
            </div>
        `;

        document.getElementById('bidsModalContent').innerHTML = modalHtml;
        document.getElementById('bidsModal').style.display = 'flex';
    }

    function closeBidsModal() {
        document.getElementById('bidsModal').style.display = 'none';
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
