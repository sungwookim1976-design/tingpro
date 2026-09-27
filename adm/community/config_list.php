<?php
$page_title = "게시판 설정";
$active_page = "community";
$active_sub = "config";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$filtered_total = sql_cnt('board_config', '');
$total_pages = max(1, (int)ceil($filtered_total / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$boards = sql_one('board_config', '*', "order by sort_order asc, brd_id asc limit $offset, $limit");

// 게시판별 글수 (community_posts.board_type 기준)
$post_cnt = [];
$res = mysqli_query($conn, "SELECT board_type, COUNT(*) cnt FROM community_posts GROUP BY board_type");
if ($res) while ($r = mysqli_fetch_assoc($res)) $post_cnt[$r['board_type']] = (int)$r['cnt'];

$type_label = [1 => '공지형', 2 => '갤러리형', 3 => '일반형'];
$write_label = [0 => '관리자만', 1 => '회원', 2 => '누구나'];

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
    <a href="index.php" style="font-size:0.85rem; color:var(--adm-primary); font-weight:700;"><i class="fa-solid fa-arrow-left"></i> 게시물 목록으로</a>
    <button class="adm-btn" onclick="openModal('add', null)"><i class="fa-solid fa-plus"></i> 게시판 추가</button>
</div>

<div class="adm-section">
    <div class="adm-section-head">
        <h3><i class="fa-solid fa-gears"></i> 게시판 환경설정</h3>
    </div>
    <div style="overflow-x:auto;">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>순서</th><th>게시판ID</th><th>게시판명</th><th>종류</th><th>댓글</th><th>작성권한</th><th>첨부</th><th>목록수</th><th>상태</th><th>글수</th><th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($boards)): ?>
                    <tr class="empty-row"><td colspan="11">게시판이 없습니다. 추가해 주세요.</td></tr>
                <?php else: foreach ($boards as $b): ?>
                    <tr>
                        <td><?php echo $b['sort_order']; ?></td>
                        <td><code style="background:#F1F5F9; padding:2px 6px; border-radius:4px; font-size:0.8rem;"><?php echo htmlspecialchars($b['brd_id']); ?></code></td>
                        <td style="font-weight:700;"><?php echo htmlspecialchars($b['brd_name']); ?></td>
                        <td><?php echo isset($type_label[$b['brd_type']]) ? $type_label[$b['brd_type']] : $b['brd_type']; ?></td>
                        <td style="text-align:center;"><?php echo $b['use_comment'] ? '<i class="fa-solid fa-check" style="color:#059669;"></i>' : '<i class="fa-solid fa-xmark" style="color:#CBD5E1;"></i>'; ?></td>
                        <td><?php echo isset($write_label[$b['use_write']]) ? $write_label[$b['use_write']] : $b['use_write']; ?></td>
                        <td><?php echo (int)$b['file_cnt']; ?>개</td>
                        <td><?php echo (int)$b['list_cnt']; ?>개</td>
                        <td><span class="adm-badge <?php echo $b['use_yn'] ? 'type-partner' : 'type-freelance'; ?>"><?php echo $b['use_yn'] ? '사용' : '중지'; ?></span></td>
                        <td>
                            <?php $cnt = isset($post_cnt[$b['brd_id']]) ? $post_cnt[$b['brd_id']] : 0; ?>
                            <?php if ($cnt > 0): ?>
                                <a href="index.php?board=<?php echo urlencode($b['brd_id']); ?>" style="color:var(--adm-primary); font-weight:700;"><?php echo number_format($cnt); ?></a>
                            <?php else: ?>
                                <span style="color:#CBD5E1;">0</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <button class="adm-btn adm-btn-outline" style="padding:5px 10px; font-size:0.78rem;" onclick='openModal("edit", <?php echo json_encode($b, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>수정</button>
                            <button class="adm-btn adm-btn-outline" style="padding:5px 10px; font-size:0.78rem; color:#B91C1C; border-color:#FECACA;" onclick="deleteBoard('<?php echo htmlspecialchars($b['brd_id']); ?>', '<?php echo htmlspecialchars($b['brd_name'], ENT_QUOTES); ?>')">삭제</button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php render_adm_pagination($page, $total_pages, $_GET); ?>
</div>

<!-- 모달 -->
<div id="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; width:520px; max-width:92%; padding:30px; position:relative;">
        <button onclick="closeModal()" style="position:absolute; top:16px; right:16px; background:none; border:none; font-size:1.3rem; cursor:pointer; color:#94A3B8;">&times;</button>
        <h3 id="modal-title" style="font-size:1.15rem; font-weight:800; color:var(--adm-text); margin-bottom:22px;">게시판 추가</h3>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
            <div class="adm-form-row">
                <label>게시판 ID <span style="color:#EF4444;">*</span> <small style="color:#94A3B8; font-weight:400;">(영소문자+숫자)</small></label>
                <input type="text" id="m_brd_id" placeholder="예: notice">
            </div>
            <div class="adm-form-row">
                <label>게시판명 <span style="color:#EF4444;">*</span></label>
                <input type="text" id="m_brd_name" placeholder="예: 공지사항">
            </div>
            <div class="adm-form-row">
                <label>게시판 종류</label>
                <select id="m_brd_type">
                    <option value="1">공지형</option>
                    <option value="2">갤러리형</option>
                    <option value="3">일반형</option>
                </select>
            </div>
            <div class="adm-form-row">
                <label>작성 권한</label>
                <select id="m_use_write">
                    <option value="0">관리자만</option>
                    <option value="1">회원</option>
                    <option value="2">누구나</option>
                </select>
            </div>
            <div class="adm-form-row">
                <label>댓글 사용</label>
                <select id="m_use_comment">
                    <option value="1">사용</option>
                    <option value="0">미사용</option>
                </select>
            </div>
            <div class="adm-form-row">
                <label>첨부파일 개수</label>
                <select id="m_file_cnt">
                    <option value="0">첨부불가</option>
                    <option value="1">1개</option>
                    <option value="2">2개</option>
                    <option value="3">3개</option>
                </select>
            </div>
            <div class="adm-form-row">
                <label>파일 최대크기 (KB)</label>
                <input type="number" id="m_file_size" value="5120">
            </div>
            <div class="adm-form-row">
                <label>목록 게시글 수</label>
                <select id="m_list_cnt">
                    <option value="10">10개</option>
                    <option value="15">15개</option>
                    <option value="20">20개</option>
                </select>
            </div>
            <div class="adm-form-row">
                <label>정렬 순서</label>
                <input type="number" id="m_sort_order" value="1">
            </div>
            <div class="adm-form-row">
                <label>사용 여부</label>
                <select id="m_use_yn">
                    <option value="1">사용</option>
                    <option value="0">중지</option>
                </select>
            </div>
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:22px;">
            <button class="adm-btn adm-btn-outline" onclick="closeModal()">취소</button>
            <button class="adm-btn" onclick="saveBoard()">저장</button>
        </div>
    </div>
</div>

<script>
    var modalMode = 'add';

    function openModal(mode, data) {
        modalMode = mode;
        document.getElementById('modal-overlay').style.display = 'flex';

        if (mode === 'add') {
            document.getElementById('modal-title').textContent = '게시판 추가';
            document.getElementById('m_brd_id').value = '';
            document.getElementById('m_brd_id').readOnly = false;
            document.getElementById('m_brd_name').value = '';
            document.getElementById('m_brd_type').value = '1';
            document.getElementById('m_use_write').value = '0';
            document.getElementById('m_use_comment').value = '0';
            document.getElementById('m_file_cnt').value = '0';
            document.getElementById('m_file_size').value = '5120';
            document.getElementById('m_list_cnt').value = '15';
            document.getElementById('m_sort_order').value = '1';
            document.getElementById('m_use_yn').value = '1';
        } else {
            document.getElementById('modal-title').textContent = '게시판 수정';
            document.getElementById('m_brd_id').value = data.brd_id;
            document.getElementById('m_brd_id').readOnly = true;
            document.getElementById('m_brd_name').value = data.brd_name;
            document.getElementById('m_brd_type').value = data.brd_type;
            document.getElementById('m_use_write').value = data.use_write;
            document.getElementById('m_use_comment').value = data.use_comment;
            document.getElementById('m_file_cnt').value = data.file_cnt;
            document.getElementById('m_file_size').value = data.file_size;
            document.getElementById('m_list_cnt').value = data.list_cnt;
            document.getElementById('m_sort_order').value = data.sort_order;
            document.getElementById('m_use_yn').value = data.use_yn;
        }
    }

    function closeModal() {
        document.getElementById('modal-overlay').style.display = 'none';
    }

    function saveBoard() {
        var brd_id = document.getElementById('m_brd_id').value.trim();
        var brd_name = document.getElementById('m_brd_name').value.trim();
        if (!brd_id) { admToast('게시판 ID를 입력해 주세요.'); return; }
        if (!brd_name) { admToast('게시판명을 입력해 주세요.'); return; }

        var body = new URLSearchParams({
            mode: modalMode === 'add' ? 'insert' : 'update',
            brd_id: brd_id,
            brd_name: brd_name,
            brd_type: document.getElementById('m_brd_type').value,
            use_comment: document.getElementById('m_use_comment').value,
            use_write: document.getElementById('m_use_write').value,
            file_cnt: document.getElementById('m_file_cnt').value,
            file_size: document.getElementById('m_file_size').value,
            list_cnt: document.getElementById('m_list_cnt').value,
            sort_order: document.getElementById('m_sort_order').value,
            use_yn: document.getElementById('m_use_yn').value
        });

        fetch('config_proc.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                admToast(res.msg);
                if (res.result === 'ok') { closeModal(); location.reload(); }
            })
            .catch(function () { admToast('오류가 발생했습니다.'); });
    }

    function deleteBoard(brd_id, brd_name) {
        if (!confirm('[' + brd_name + '] 게시판을 삭제하시겠습니까?')) return;
        fetch('config_proc.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'mode=delete&brd_id=' + encodeURIComponent(brd_id) })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                admToast(res.msg);
                if (res.result === 'ok') location.reload();
            })
            .catch(function () { admToast('오류가 발생했습니다.'); });
    }
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
