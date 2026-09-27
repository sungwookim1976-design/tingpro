<?php
$page_title = "관리자관리";
$active_page = "admins";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$grade_label = [
    'super'   => '최고관리자',
    'manager' => '운영관리자',
    'staff'   => '일반직원'
];

$grade_filter = isset($_GET['grade']) && in_array($_GET['grade'], ['super', 'manager', 'staff']) ? $_GET['grade'] : '';
$state_filter = isset($_GET['state']) && $_GET['state'] !== '' ? intval($_GET['state']) : '';
$sk = trim((isset($_GET['sk']) ? $_GET['sk'] : ''));

$where = " where 1=1";
if ($grade_filter !== '') {
    $where .= " and grade='" . mysqli_real_escape_string($conn, $grade_filter) . "'";
}
if ($state_filter !== '') {
    $where .= " and state=" . intval($state_filter);
}
if ($sk !== '') {
    $sk_esc = mysqli_real_escape_string($conn, $sk);
    $where .= " and (uid like '%$sk_esc%' or name like '%$sk_esc%')";
}

// 통계 수치
$total_cnt   = sql_cnt('admins', '');
$super_cnt   = sql_cnt('admins', "and grade='super'");
$manager_cnt = sql_cnt('admins', "and grade='manager'");
$staff_cnt   = sql_cnt('admins', "and grade='staff'");

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$filtered_total = sql_cnt('admins', str_replace(' where 1=1', '', $where));
$total_pages = max(1, (int)ceil($filtered_total / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$admins = sql_one('admins', 'no, uid, name, grade, state, last_login, reg_date', str_replace(' where 1=1', '', $where) . " order by no desc limit $offset, $limit");

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div class="adm-stat-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-user-shield"></i> 전체 관리자</div>
        <div class="val"><?php echo number_format($total_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">명</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-crown" style="color:#8B5CF6;"></i> 최고 관리자</div>
        <div class="val" style="color:#7C3AED;"><?php echo number_format($super_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">명</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-user-gear" style="color:#0284C7;"></i> 운영 관리자</div>
        <div class="val" style="color:#0284C7;"><?php echo number_format($manager_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">명</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-user" style="color:#64748B;"></i> 일반 직원</div>
        <div class="val" style="color:#475569;"><?php echo number_format($staff_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">명</span></div>
    </div>
</div>

<div class="adm-section">
    <div class="adm-section-head" style="flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <h3><i class="fa-solid fa-user-shield"></i> 관리자 계정 목록 (<?php echo number_format($filtered_total); ?>명)</h3>
            <div style="display:flex; gap:6px;">
                <a href="?grade=&state=<?php echo $state_filter; ?>&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $grade_filter === '' ? '#0077B6' : '#F1F5F9'; ?>; color:<?php echo $grade_filter === '' ? '#fff' : '#334155'; ?>;">전체 등급</a>
                <a href="?grade=super&state=<?php echo $state_filter; ?>&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $grade_filter === 'super' ? '#7C3AED' : '#F1F5F9'; ?>; color:<?php echo $grade_filter === 'super' ? '#fff' : '#334155'; ?>;">최고관리자</a>
                <a href="?grade=manager&state=<?php echo $state_filter; ?>&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $grade_filter === 'manager' ? '#0284C7' : '#F1F5F9'; ?>; color:<?php echo $grade_filter === 'manager' ? '#fff' : '#334155'; ?>;">운영관리자</a>
                <a href="?grade=staff&state=<?php echo $state_filter; ?>&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $grade_filter === 'staff' ? '#475569' : '#F1F5F9'; ?>; color:<?php echo $grade_filter === 'staff' ? '#fff' : '#334155'; ?>;">일반직원</a>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:10px;">
            <form method="get" style="display:flex; gap:6px; align-items:center;">
                <input type="hidden" name="grade" value="<?php echo htmlspecialchars($grade_filter); ?>">
                <input type="hidden" name="state" value="<?php echo htmlspecialchars($state_filter); ?>">
                <input type="text" name="sk" value="<?php echo htmlspecialchars($sk); ?>" placeholder="아이디 / 이름 검색" style="padding:6px 12px; border:1px solid var(--adm-border); border-radius:6px; font-size:0.85rem; outline:none; width:180px;">
                <button type="submit" class="adm-btn" style="padding:6px 12px; font-size:0.82rem;"><i class="fa-solid fa-magnifying-glass"></i> 검색</button>
            </form>
            <a href="write.php" class="adm-btn" style="padding:7px 14px; font-size:0.85rem; background:#059669;"><i class="fa-solid fa-plus"></i> 신규 관리자 등록</a>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="adm-table">
            <thead>
                <tr>
                    <th style="width:60px; text-align:center;">번호</th>
                    <th>아이디</th>
                    <th>이름</th>
                    <th>권한 등급</th>
                    <th style="text-align:center;">상태</th>
                    <th>최근 로그인</th>
                    <th>등록일</th>
                    <th style="width:140px; text-align:center;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($admins)): ?>
                    <tr class="empty-row"><td colspan="8">등록된 관리자가 없습니다.</td></tr>
                <?php else: foreach ($admins as $adm): ?>
                    <tr>
                        <td style="text-align:center; font-weight:600; color:var(--adm-muted);"><?php echo $adm['no']; ?></td>
                        <td><strong style="color:var(--adm-primary);"><?php echo htmlspecialchars($adm['uid']); ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($adm['name']); ?></strong></td>
                        <td>
                            <?php
                            if ($adm['grade'] === 'super') {
                                echo '<span class="adm-badge" style="background:#F3E8FF; color:#7C3AED;"><i class="fa-solid fa-crown"></i> 최고관리자</span>';
                            } elseif ($adm['grade'] === 'manager') {
                                echo '<span class="adm-badge" style="background:#E0F2FE; color:#0369A1;"><i class="fa-solid fa-user-gear"></i> 운영관리자</span>';
                            } else {
                                echo '<span class="adm-badge" style="background:#F1F5F9; color:#475569;"><i class="fa-solid fa-user"></i> 일반직원</span>';
                            }
                            ?>
                        </td>
                        <td style="text-align:center;">
                            <select onchange="changeAdminState(<?php echo $adm['no']; ?>, this.value)" class="state-select state-<?php echo $adm['state']; ?>">
                                <option value="1" <?php echo $adm['state'] == 1 ? 'selected' : ''; ?>>정상</option>
                                <option value="0" <?php echo $adm['state'] == 0 ? 'selected' : ''; ?>>정지</option>
                            </select>
                        </td>
                        <td style="font-size:0.82rem; color:var(--adm-muted);"><?php echo $adm['last_login'] ? htmlspecialchars($adm['last_login']) : '-'; ?></td>
                        <td style="font-size:0.82rem; color:var(--adm-muted);"><?php echo htmlspecialchars($adm['reg_date']); ?></td>
                        <td style="text-align:center; white-space:nowrap;">
                            <a href="edit.php?no=<?php echo $adm['no']; ?>" class="adm-btn adm-btn-outline" style="padding:4px 9px; font-size:0.78rem;"><i class="fa-solid fa-pen-to-square"></i> 수정</a>
                            <?php if ($adm['uid'] !== 'admin'): ?>
                                <a href="proc.php?mode=delete&no=<?php echo $adm['no']; ?>" onclick="return confirm('이 관리자 계정을 삭제하시겠습니까?');" class="adm-btn" style="padding:4px 9px; font-size:0.78rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 삭제</a>
                            <?php else: ?>
                                <span style="font-size:0.75rem; color:#94A3B8; margin-left:4px;">(기본 계정)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php render_adm_pagination($page, $total_pages, $_GET); ?>
</div>

<script>
function changeAdminState(no, state) {
    fetch('proc.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'mode=change_state&no=' + no + '&state=' + state
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
