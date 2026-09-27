<?php
$page_title = "커뮤니티 관리";
$active_page = "community";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$board_rows = sql_one('board_config', 'brd_id, brd_name', 'order by sort_order asc, brd_id asc');
$board_label = [];
foreach ($board_rows as $b) $board_label[$b['brd_id']] = $b['brd_name'];
if (empty($board_label)) $board_label = ['notice' => '공지사항', 'news' => '뉴스', 'faq' => 'FAQ'];

$valid_boards = array_keys($board_label);
$filter = isset($_GET['board']) && in_array($_GET['board'], $valid_boards) ? $_GET['board'] : $valid_boards[0];

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$filtered_total = sql_cnt('community_posts', "and board_type='" . mysqli_real_escape_string($conn, $filter) . "'");
$total_pages = max(1, (int)ceil($filtered_total / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$posts = sql_one('community_posts', '*', "and board_type='" . mysqli_real_escape_string($conn, $filter) . "' order by no desc limit $offset, $limit");

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div class="adm-section">
    <div class="adm-section-head">
        <h3><i class="fa-solid fa-comments"></i> 커뮤니티 게시물</h3>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <a href="config_list.php" class="adm-btn adm-btn-outline" style="padding:6px 12px; font-size:0.82rem;"><i class="fa-solid fa-gears"></i> 게시판 설정</a>
            <select onchange="location.href='?board='+encodeURIComponent(this.value)" style="padding:7px 12px; border:1px solid var(--adm-border); border-radius:6px; font-size:0.82rem; font-weight:700; color:var(--adm-text); background:#fff;">
                <?php foreach ($board_label as $key => $label): ?>
                    <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $filter === $key ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?> (<?php echo htmlspecialchars($key); ?>)</option>
                <?php endforeach; ?>
            </select>
            <button type="button" onclick="copySelectedPosts()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#0284C7; color:#0284C7;"><i class="fa-solid fa-copy"></i> 선택 게시물 복사</button>
            <button type="button" onclick="deleteSelectedPosts()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#EF4444; color:#EF4444;"><i class="fa-solid fa-trash"></i> 선택 게시물 삭제</button>
            <a href="write.php?board=<?php echo urlencode($filter); ?>" class="adm-btn" style="padding:7px 14px; font-size:0.85rem; background:#059669;"><i class="fa-solid fa-pen-to-square"></i> 글쓰기</a>
        </div>
    </div>

    <form id="bulkForm" method="post" action="proc.php">
        <input type="hidden" name="mode" id="bulk_mode" value="copy_bulk">
        <input type="hidden" name="board" value="<?php echo htmlspecialchars($filter); ?>">

        <div style="overflow-x:auto;">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;"><input type="checkbox" id="chk_all" onclick="toggleCheckAll(this)" style="cursor:pointer;"></th>
                        <th style="width:60px; text-align:center;">번호</th>
                        <th>구분</th>
                        <th>제목</th>
                        <?php if ($filter === 'faq'): ?><th>답변</th><?php endif; ?>
                        <th style="text-align:center;">상태</th>
                        <th>등록일</th>
                        <th style="width:130px; text-align:center;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr class="empty-row"><td colspan="<?php echo $filter === 'faq' ? 8 : 7; ?>">등록된 게시물이 없습니다.</td></tr>
                    <?php else: foreach ($posts as $p): ?>
                        <tr>
                            <td style="text-align:center;"><input type="checkbox" name="chk_no[]" value="<?php echo $p['no']; ?>" class="chk-item" style="cursor:pointer;"></td>
                            <td style="text-align:center; font-weight:600; color:var(--adm-muted);"><?php echo $p['no']; ?></td>
                            <td><span class="adm-badge type-customer"><?php echo htmlspecialchars($p['badge']); ?></span></td>
                            <td style="font-weight:700; color:var(--adm-text);"><?php echo htmlspecialchars($p['title']); ?></td>
                            <?php if ($filter === 'faq'): ?><td style="color:var(--adm-muted); font-size:0.82rem;"><?php echo htmlspecialchars($p['answer']); ?></td><?php endif; ?>
                            <td style="text-align:center;">
                                <select class="state-select state-<?php echo $p['state']; ?>" data-no="<?php echo $p['no']; ?>" onchange="updatePostState(this)">
                                    <option value="1" <?php echo $p['state'] == 1 ? 'selected' : ''; ?>>게시중</option>
                                    <option value="0" <?php echo $p['state'] == 0 ? 'selected' : ''; ?>>숨김</option>
                                </select>
                            </td>
                            <td style="font-size:0.82rem; color:var(--adm-muted);"><?php echo htmlspecialchars($p['reg_date']); ?></td>
                            <td style="text-align:center; white-space:nowrap;">
                                <a href="write.php?no=<?php echo $p['no']; ?>" class="adm-btn adm-btn-outline" style="padding:4px 9px; font-size:0.78rem;"><i class="fa-solid fa-pen-to-square"></i> 수정</a>
                                <a href="proc.php?mode=delete&no=<?php echo $p['no']; ?>&board=<?php echo urlencode($filter); ?>" onclick="return confirm('이 게시물을 삭제하시겠습니까?');" class="adm-btn" style="padding:4px 9px; font-size:0.78rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 삭제</a>
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

    function copySelectedPosts() {
        const checked = document.querySelectorAll('.chk-item:checked');
        if (checked.length === 0) {
            alert('복사할 게시물을 하나 이상 선택해 주세요.');
            return;
        }
        if (confirm('선택한 ' + checked.length + '개 게시물을 복사하시겠습니까?')) {
            document.getElementById('bulk_mode').value = 'copy_bulk';
            document.getElementById('bulkForm').submit();
        }
    }

    function deleteSelectedPosts() {
        const checked = document.querySelectorAll('.chk-item:checked');
        if (checked.length === 0) {
            alert('삭제할 게시물을 하나 이상 선택해 주세요.');
            return;
        }
        if (confirm('선택한 ' + checked.length + '개 게시물을 정말로 삭제하시겠습니까?')) {
            document.getElementById('bulk_mode').value = 'delete_bulk';
            document.getElementById('bulkForm').submit();
        }
    }

    function updatePostState(sel) {
        const no = sel.dataset.no;
        const state = sel.value;
        sel.disabled = true;
        fetch('update_state.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'no=' + encodeURIComponent(no) + '&state=' + encodeURIComponent(state)
        })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                sel.className = 'state-select state-' + state;
                admToast(d.ok ? '상태가 변경되었습니다.' : (d.msg || '변경에 실패했습니다.'));
            })
            .catch(function () { admToast('변경 중 오류가 발생했습니다.'); })
            .finally(function () { sel.disabled = false; });
    }
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
