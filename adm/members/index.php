<?php
$page_title = "회원관리";
$active_page = "members";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

// 기업고객 중 VULUX 상품 구매자에 대한 대리점 자격 일괄 동기화
sync_all_partner_statuses();

$type_label = [
    'customer'  => '수요고객',
    'freelance' => '프리랜서',
    'corporate' => '기업',
    'partner'   => '대리점'
];

$type_filter  = isset($_GET['type']) && in_array($_GET['type'], ['customer', 'freelance', 'corporate', 'partner']) ? $_GET['type'] : '';
$state_filter = isset($_GET['state']) && $_GET['state'] !== '' ? intval($_GET['state']) : '';
$sk           = trim((isset($_GET['sk']) ? $_GET['sk'] : ''));

$where = " where 1=1";
if ($type_filter !== '') {
    $where .= " and mem_type='" . mysqli_real_escape_string($conn, $type_filter) . "'";
}
if ($state_filter !== '') {
    $where .= " and mem_state=" . intval($state_filter);
}
if ($sk !== '') {
    $sk_esc = mysqli_real_escape_string($conn, $sk);
    $where .= " and (uid like '%$sk_esc%' or email like '%$sk_esc%' or name like '%$sk_esc%' or hphone like '%$sk_esc%')";
}

// 통계 수치
$total_cnt     = sql_cnt('members', '');
$customer_cnt  = sql_cnt('members', "and mem_type='customer'");
$freelance_cnt = sql_cnt('members', "and mem_type='freelance'");
$corporate_cnt = sql_cnt('members', "and mem_type='corporate'");
$partner_cnt   = sql_cnt('members', "and mem_type='partner'");

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$filtered_total = sql_cnt('members', str_replace(' where 1=1', '', $where));
$total_pages = max(1, (int)ceil($filtered_total / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$members = sql_one('members', 'no, mem_type, uid, name, hphone, email, zipcode, addr1, addr2, mem_state, reg_date', str_replace(' where 1=1', '', $where) . " order by no desc limit $offset, $limit");

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div class="adm-stat-grid" style="grid-template-columns: repeat(5, 1fr);">
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-users"></i> 전체 회원</div>
        <div class="val"><?php echo number_format($total_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">명</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-user" style="color:#0077B6;"></i> 수요고객</div>
        <div class="val" style="color:#0077B6;"><?php echo number_format($customer_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">명</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-user-gear" style="color:#D97706;"></i> 프리랜서</div>
        <div class="val" style="color:#D97706;"><?php echo number_format($freelance_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">명</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-building" style="color:#4F46E5;"></i> 기업</div>
        <div class="val" style="color:#4F46E5;"><?php echo number_format($corporate_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">명</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-store" style="color:#059669;"></i> 대리점</div>
        <div class="val" style="color:#059669;"><?php echo number_format($partner_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">명</span></div>
    </div>
</div>

<div class="adm-section">
    <div class="adm-section-head" style="flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <h3><i class="fa-solid fa-users"></i> 회원 목록 (<?php echo number_format($filtered_total); ?>명)</h3>
            <div style="display:flex; gap:6px;">
                <a href="?type=&state=<?php echo $state_filter; ?>&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $type_filter === '' ? '#0077B6' : '#F1F5F9'; ?>; color:<?php echo $type_filter === '' ? '#fff' : '#334155'; ?>;">전체</a>
                <a href="?type=customer&state=<?php echo $state_filter; ?>&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $type_filter === 'customer' ? '#0077B6' : '#F1F5F9'; ?>; color:<?php echo $type_filter === 'customer' ? '#fff' : '#334155'; ?>;">수요고객</a>
                <a href="?type=freelance&state=<?php echo $state_filter; ?>&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $type_filter === 'freelance' ? '#D97706' : '#F1F5F9'; ?>; color:<?php echo $type_filter === 'freelance' ? '#fff' : '#334155'; ?>;">프리랜서</a>
                <a href="?type=corporate&state=<?php echo $state_filter; ?>&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $type_filter === 'corporate' ? '#4F46E5' : '#F1F5F9'; ?>; color:<?php echo $type_filter === 'corporate' ? '#fff' : '#334155'; ?>;">기업</a>
                <a href="?type=partner&state=<?php echo $state_filter; ?>&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $type_filter === 'partner' ? '#059669' : '#F1F5F9'; ?>; color:<?php echo $type_filter === 'partner' ? '#fff' : '#334155'; ?>;">대리점</a>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:10px;">
            <form method="get" style="display:flex; gap:6px; align-items:center;">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($type_filter); ?>">
                <input type="hidden" name="state" value="<?php echo htmlspecialchars($state_filter); ?>">
                <input type="text" name="sk" value="<?php echo htmlspecialchars($sk); ?>" placeholder="아이디(이메일)/이름/연락처" style="padding:6px 12px; border:1px solid var(--adm-border); border-radius:6px; font-size:0.85rem; outline:none; width:200px;">
                <button type="submit" class="adm-btn" style="padding:6px 12px; font-size:0.82rem;"><i class="fa-solid fa-magnifying-glass"></i> 검색</button>
            </form>
            <button type="button" onclick="copySelectedMembers()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#0284C7; color:#0284C7;"><i class="fa-solid fa-copy"></i> 선택 회원 복사</button>
            <button type="button" onclick="deleteSelectedMembers()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#EF4444; color:#EF4444;"><i class="fa-solid fa-trash"></i> 선택 회원 삭제</button>
            <a href="write.php" class="adm-btn" style="padding:7px 14px; font-size:0.85rem; background:#059669;"><i class="fa-solid fa-user-plus"></i> 회원 신규 등록</a>
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
                        <th>유형</th>
                        <th>아이디(이메일)</th>
                        <th>이름</th>
                        <th>연락처</th>
                        <th>주소</th>
                        <th style="text-align:center;">상태</th>
                        <th>가입일</th>
                        <th style="width:130px; text-align:center;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($members)): ?>
                        <tr class="empty-row"><td colspan="10">해당 조건의 회원이 없습니다.</td></tr>
                    <?php else: foreach ($members as $m): ?>
                        <tr>
                            <td style="text-align:center;"><input type="checkbox" name="chk_no[]" value="<?php echo $m['no']; ?>" class="chk-item" style="cursor:pointer;"></td>
                            <td style="text-align:center; font-weight:600; color:var(--adm-muted);"><?php echo $m['no']; ?></td>
                            <td><span class="adm-badge type-<?php echo $m['mem_type']; ?>"><?php echo isset($type_label[$m['mem_type']]) ? $type_label[$m['mem_type']] : $m['mem_type']; ?></span></td>
                            <td><strong style="color:var(--adm-primary);"><?php echo htmlspecialchars($m['email'] ? $m['email'] : $m['uid']); ?></strong></td>
                            <td><strong><?php echo htmlspecialchars($m['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($m['hphone']); ?></td>
                            <td style="font-size:0.82rem; color:var(--adm-muted); max-width:220px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?php echo htmlspecialchars($m['addr1'] . ' ' . $m['addr2']); ?>">
                                <?php echo htmlspecialchars($m['addr1'] ? $m['addr1'] : '-'); ?>
                            </td>
                            <td style="text-align:center;">
                                <select onchange="changeMemberState(<?php echo $m['no']; ?>, this.value)" class="state-select state-<?php echo $m['mem_state']; ?>">
                                    <option value="1" <?php echo $m['mem_state'] == 1 ? 'selected' : ''; ?>>정상</option>
                                    <option value="0" <?php echo $m['mem_state'] == 0 ? 'selected' : ''; ?>>정지</option>
                                </select>
                            </td>
                            <td style="font-size:0.82rem; color:var(--adm-muted);"><?php echo htmlspecialchars($m['reg_date']); ?></td>
                            <td style="text-align:center; white-space:nowrap;">
                                <a href="edit.php?no=<?php echo $m['no']; ?>" class="adm-btn adm-btn-outline" style="padding:4px 9px; font-size:0.78rem;"><i class="fa-solid fa-pen-to-square"></i> 수정</a>
                                <a href="proc.php?mode=delete&no=<?php echo $m['no']; ?>" onclick="return confirm('이 회원을 정말로 삭제하시겠습니까?');" class="adm-btn" style="padding:4px 9px; font-size:0.78rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 삭제</a>
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

function copySelectedMembers() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('복사할 회원을 하나 이상 선택해 주세요.');
        return;
    }
    if (confirm('선택한 ' + checked.length + '명의 회원을 복사하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'copy_bulk';
        document.getElementById('bulkForm').submit();
    }
}

function deleteSelectedMembers() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('삭제할 회원을 하나 이상 선택해 주세요.');
        return;
    }
    if (confirm('선택한 ' + checked.length + '명의 회원을 정말로 삭제하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'delete_bulk';
        document.getElementById('bulkForm').submit();
    }
}

function changeMemberState(no, state) {
    fetch('proc.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'mode=change_state&no=' + no + '&mem_state=' + state
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
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
