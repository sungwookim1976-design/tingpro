<?php
$page_title = "카테고리관리";
$active_page = "settings";
$active_sub = "category";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

// corea26 admin/category와 동일 스키마로 테이블 없으면 생성
$que = "CREATE TABLE IF NOT EXISTS `ai_category` (
  `idx`          int(11)      NOT NULL AUTO_INCREMENT,
  `parent_idx`   int(11)      NOT NULL DEFAULT '0',
  `depth`        tinyint(1)   NOT NULL DEFAULT '1',
  `cat_name`     varchar(100) NOT NULL,
  `apply_target` varchar(20)  NOT NULL DEFAULT '',
  `sort_order`   tinyint(3)   NOT NULL DEFAULT '1',
  `use_yn`       tinyint(1)   NOT NULL DEFAULT '1',
  `reg_dt`       datetime     NOT NULL,
  PRIMARY KEY (`idx`),
  KEY `idx_parent` (`parent_idx`),
  KEY `idx_depth`  (`depth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
mysqli_query($conn, $que);

$que = "SELECT * FROM ai_category WHERE depth=1 ORDER BY sort_order ASC, idx ASC";
$res_large = mysqli_query($conn, $que);
$large_list = array();
if ($res_large) {
    while ($r = mysqli_fetch_assoc($res_large)) $large_list[] = $r;
}

$target_label = array('member' => '회원', 'board' => '게시판', 'content' => '콘텐츠');
$target_cls   = array('member' => 'type-customer', 'board' => 'type-partner', 'content' => 'type-freelance');

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div class="adm-section" style="margin-bottom:16px;">
    <div class="adm-section-head">
        <h3><i class="fa-solid fa-folder-tree"></i> 카테고리관리</h3>
    </div>
    <p style="padding:14px 22px 18px; font-size:0.85rem; color:var(--adm-muted);">
        <strong style="color:var(--adm-text);">대분류</strong>에 적용대상(회원/게시판/콘텐츠)을 지정하고,
        <strong style="color:var(--adm-text);">중분류 &rsaquo; 소분류</strong> 순으로 구성합니다.
    </p>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; align-items:start;">

    <!-- 대분류 -->
    <div class="cat-panel">
        <div class="cat-panel-head" style="background:var(--adm-sidebar);">
            <span><i class="fa-solid fa-square"></i> 대분류</span>
            <button class="btn-add" id="btn-add-1">+ 추가</button>
        </div>
        <ul class="cat-list" id="list-1">
        <?php if (empty($large_list)): ?>
            <li class="empty-row">대분류를 추가해주세요.</li>
        <?php else: foreach ($large_list as $item): ?>
            <li class="cat-item <?php echo $item['use_yn'] ? '' : 'item-off'; ?>"
                data-idx="<?php echo $item['idx']; ?>"
                data-depth="1">
                <div style="flex:1; min-width:0; overflow:hidden;">
                    <?php if ($item['apply_target'] && isset($target_label[$item['apply_target']])): ?>
                    <span class="adm-badge <?php echo $target_cls[$item['apply_target']]; ?>">
                        <?php echo $target_label[$item['apply_target']]; ?>
                    </span>
                    <?php endif; ?>
                    <span class="item-name"><?php echo htmlspecialchars($item['cat_name']); ?></span>
                </div>
                <span class="item-order"><?php echo $item['sort_order']; ?></span>
                <div class="item-btns">
                    <button class="btn-edit-sm btn-edit" data-idx="<?php echo $item['idx']; ?>">수정</button>
                    <button class="btn-del-sm  btn-del"  data-idx="<?php echo $item['idx']; ?>">삭제</button>
                </div>
            </li>
        <?php endforeach; endif; ?>
        </ul>
    </div>

    <!-- 중분류 -->
    <div class="cat-panel">
        <div class="cat-panel-head" style="background:var(--adm-primary);">
            <span><i class="fa-solid fa-square"></i> 중분류</span>
            <button class="btn-add" id="btn-add-2" style="display:none;">+ 추가</button>
        </div>
        <ul class="cat-list" id="list-2">
            <li class="empty-row">&#8592; 대분류를 선택하세요.</li>
        </ul>
    </div>

    <!-- 소분류 -->
    <div class="cat-panel">
        <div class="cat-panel-head" style="background:var(--adm-primary-light);">
            <span><i class="fa-solid fa-square"></i> 소분류</span>
            <button class="btn-add" id="btn-add-3" style="display:none;">+ 추가</button>
        </div>
        <ul class="cat-list" id="list-3">
            <li class="empty-row">&#8592; 중분류를 선택하세요.</li>
        </ul>
    </div>
</div>

<!-- 모달 -->
<div id="modal-overlay" style="display:none; position:fixed; top:0;left:0;right:0;bottom:0; background:rgba(15,23,42,0.6); z-index:2000; align-items:center; justify-content:center;">
<div id="modal-box" style="background:#fff; border-radius:14px; width:420px; max-width:95%; padding:30px 32px; position:relative; box-shadow:0 20px 60px rgba(0,0,0,0.25);">
    <h3 id="modal-title" style="font-size:1.1rem; font-weight:800; color:var(--adm-text); margin-bottom:22px;"></h3>

    <div class="adm-form-row">
        <label>카테고리명 <span style="color:#EF4444;">*</span></label>
        <input type="text" id="m_cat_name" placeholder="카테고리명 입력" autocomplete="off">
    </div>
    <div id="row-apply" class="adm-form-row" style="display:none;">
        <label>적용대상 <span style="color:#EF4444;">*</span></label>
        <select id="m_apply_target">
            <option value="member">&#128101; 회원</option>
            <option value="board">&#128196; 게시판</option>
            <option value="content">&#127909; 콘텐츠</option>
        </select>
        <p style="font-size:0.78rem; color:var(--adm-muted); margin-top:5px;">이 대분류가 적용되는 영역을 지정합니다.</p>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <div class="adm-form-row">
            <label>정렬 순서</label>
            <input type="number" id="m_sort_order" value="1" min="1">
        </div>
        <div class="adm-form-row">
            <label>사용 여부</label>
            <select id="m_use_yn">
                <option value="1">사용</option>
                <option value="0">중지</option>
            </select>
        </div>
    </div>
    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
        <button class="adm-btn adm-btn-outline" id="btn-modal-cancel">취소</button>
        <button class="adm-btn" id="btn-modal-save">저장</button>
    </div>
    <button id="btn-modal-close" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94A3B8;">&#215;</button>
</div>
</div>

<style>
.cat-panel{background:var(--adm-card);border-radius:14px;border:1px solid var(--adm-border);overflow:hidden;min-height:460px;}
.cat-panel-head{color:#fff;padding:13px 16px;display:flex;justify-content:space-between;align-items:center;font-size:0.9rem;font-weight:700;}
.cat-panel-head i{font-size:0.55rem;}
.btn-add{background:#fff;color:var(--adm-text);border:none;padding:5px 13px;border-radius:5px;font-size:0.78rem;font-weight:800;cursor:pointer;}
.btn-add:hover{opacity:0.85;}
.cat-list{list-style:none;padding:6px 0;margin:0;}
.cat-item{padding:10px 14px;cursor:pointer;border-bottom:1px solid #F1F5F9;display:flex;align-items:center;gap:6px;transition:background 0.12s;}
.cat-item:hover{background:#F8FAFC;}
.cat-item.active{background:#EFF6FF;border-left:3px solid var(--adm-primary);}
.cat-item.item-off .item-name{color:#CBD5E1;text-decoration:line-through;}
.item-name{font-size:0.9rem;color:var(--adm-text);}
.item-order{font-size:0.72rem;color:var(--adm-muted);background:#F1F5F9;padding:1px 6px;border-radius:3px;white-space:nowrap;flex-shrink:0;}
.item-btns{display:flex;gap:4px;opacity:0;transition:opacity 0.12s;flex-shrink:0;}
.cat-item:hover .item-btns{opacity:1;}
.btn-edit-sm{padding:3px 8px;background:#E0F7FA;color:var(--adm-primary);border:none;border-radius:3px;font-size:0.75rem;cursor:pointer;font-weight:700;}
.btn-del-sm{padding:3px 8px;background:#FEE2E2;color:#B91C1C;border:none;border-radius:3px;font-size:0.75rem;cursor:pointer;font-weight:700;}
</style>

<div class="adm-section" style="margin-top:24px;">
    <div class="adm-section-head">
        <h3><i class="fa-solid fa-diagram-project"></i> 전체 카테고리 구조</h3>
        <button class="adm-btn adm-btn-outline" onclick="reloadTree()" style="padding:6px 14px; font-size:0.8rem;"><i class="fa-solid fa-arrows-rotate"></i> 새로고침</button>
    </div>
    <div id="tree-wrap" style="min-height:60px;">
        <p style="text-align:center; padding:24px; color:var(--adm-muted);">로딩 중...</p>
    </div>
</div>

<script>
// ── 데이터스토어 (idx -> 카테고리 객체) ──
var catStore = {};
var selIdx   = {1: 0, 2: 0};
var modalState = {mode:'insert', depth:1, idx:0, parentIdx:0};

var depthNames   = {1:'대분류', 2:'중분류', 3:'소분류'};
var targetLabel  = {member:'회원', board:'게시판', content:'콘텐츠'};
var targetCls    = {member:'type-customer', board:'type-partner', content:'type-freelance'};

<?php foreach ($large_list as $item): ?>
catStore[<?php echo $item['idx']; ?>] = <?php echo json_encode($item); ?>;
<?php endforeach; ?>

document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('list-1').addEventListener('click', function (e) {
        var li = e.target.closest('.cat-item');
        if (li && !e.target.closest('.item-btns')) {
            document.querySelectorAll('#list-1 .cat-item').forEach(function (x) { x.classList.remove('active'); });
            li.classList.add('active');
            var idx = parseInt(li.dataset.idx);
            selIdx[1] = idx;
            selIdx[2] = 0;
            document.getElementById('list-3').innerHTML = '<li class="empty-row">&#8592; 중분류를 선택하세요.</li>';
            document.getElementById('btn-add-3').style.display = 'none';
            loadChildren(idx, 2);
            return;
        }
        var editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            var idx2 = parseInt(editBtn.dataset.idx);
            var d = catStore[idx2];
            if (d) openModal('update', 1, idx2, d.parent_idx, d);
            return;
        }
        var delBtn = e.target.closest('.btn-del');
        if (delBtn) {
            var idx3 = parseInt(delBtn.dataset.idx);
            var d3 = catStore[idx3];
            deleteItem(idx3, d3 ? d3.cat_name : '', 1);
        }
    });

    ['list-2', 'list-3'].forEach(function (listId) {
        document.getElementById(listId).addEventListener('click', function (e) {
            var editBtn = e.target.closest('.btn-edit');
            if (editBtn) {
                var idx = parseInt(editBtn.dataset.idx);
                var d = catStore[idx];
                if (d) openModal('update', parseInt(d.depth), idx, parseInt(d.parent_idx), d);
                return;
            }
            var delBtn = e.target.closest('.btn-del');
            if (delBtn) {
                var idx2 = parseInt(delBtn.dataset.idx);
                var d2 = catStore[idx2];
                deleteItem(idx2, d2 ? d2.cat_name : '', d2 ? parseInt(d2.depth) : (listId === 'list-2' ? 2 : 3));
                return;
            }
            var li = e.target.closest('.cat-item');
            if (li && listId === 'list-2') {
                document.querySelectorAll('#list-2 .cat-item').forEach(function (x) { x.classList.remove('active'); });
                li.classList.add('active');
                var idx3 = parseInt(li.dataset.idx);
                selIdx[2] = idx3;
                loadChildren(idx3, 3);
            }
        });
    });

    document.getElementById('btn-add-1').addEventListener('click', function () { openModal('insert', 1, 0, 0, null); });
    document.getElementById('btn-add-2').addEventListener('click', function () { openModal('insert', 2, 0, selIdx[1], null); });
    document.getElementById('btn-add-3').addEventListener('click', function () { openModal('insert', 3, 0, selIdx[2], null); });

    document.getElementById('btn-modal-cancel').addEventListener('click', closeModal);
    document.getElementById('btn-modal-close').addEventListener('click', closeModal);
    document.getElementById('modal-overlay').addEventListener('click', function (e) {
        if (e.target.id === 'modal-overlay') closeModal();
    });

    document.getElementById('btn-modal-save').addEventListener('click', saveCategory);
    document.getElementById('m_cat_name').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') saveCategory();
    });

    loadChildren(0, 1);
    reloadTree();
});

function reloadTree() {
    var wrap = document.getElementById('tree-wrap');
    wrap.innerHTML = '<p style="text-align:center; padding:24px; color:var(--adm-muted);">갱신 중...</p>';
    fetch('tree.php')
        .then(function (r) { return r.text(); })
        .then(function (html) { wrap.innerHTML = html; })
        .catch(function () { wrap.innerHTML = '<p style="text-align:center; padding:24px; color:var(--adm-muted);">불러오기 실패</p>'; });
}

function loadChildren(parentIdx, depth) {
    fetch('proc.php?mode=get_children&parent_idx=' + parentIdx)
        .then(function (r) { return r.json(); })
        .then(function (res) {
            var list = document.getElementById('list-' + depth);
            var addBtn = document.getElementById('btn-add-' + depth);
            list.innerHTML = '';

            if (!res.list || res.list.length === 0) {
                list.innerHTML = '<li class="empty-row">항목이 없습니다. 추가해주세요.</li>';
            } else {
                res.list.forEach(function (item) {
                    catStore[item.idx] = item;
                    list.appendChild(makeItem(item, depth));
                });
            }
            addBtn.style.display = '';
        })
        .catch(function () { admToast('목록을 불러오는 중 오류가 발생했습니다.'); });
}

function makeItem(item, depth) {
    var badge = '';
    if (depth === 1 && item.apply_target && targetLabel[item.apply_target]) {
        badge = '<span class="adm-badge ' + targetCls[item.apply_target] + '">' + targetLabel[item.apply_target] + '</span>';
    }
    var li = document.createElement('li');
    li.className = 'cat-item' + (item.use_yn == 0 ? ' item-off' : '');
    li.dataset.idx = item.idx;
    li.dataset.depth = depth;

    var nameDiv = document.createElement('div');
    nameDiv.style.cssText = 'flex:1; min-width:0; overflow:hidden;';
    var nameSpan = document.createElement('span');
    nameSpan.className = 'item-name';
    nameSpan.textContent = item.cat_name;
    nameDiv.innerHTML = badge;
    nameDiv.appendChild(nameSpan);
    li.appendChild(nameDiv);

    var order = document.createElement('span');
    order.className = 'item-order';
    order.textContent = item.sort_order;
    li.appendChild(order);

    var btns = document.createElement('div');
    btns.className = 'item-btns';
    btns.innerHTML =
        '<button class="btn-edit-sm btn-edit" data-idx="' + item.idx + '">수정</button>' +
        '<button class="btn-del-sm btn-del" data-idx="' + item.idx + '">삭제</button>';
    li.appendChild(btns);

    return li;
}

function openModal(mode, depth, idx, parentIdx, data) {
    modalState = {mode: mode, depth: depth, idx: idx, parentIdx: parentIdx};

    var isEdit = (mode === 'update');
    document.getElementById('modal-title').textContent = (isEdit ? '수정: ' : '추가: ') + depthNames[depth];
    document.getElementById('m_cat_name').value = isEdit && data ? data.cat_name : '';
    document.getElementById('m_sort_order').value = isEdit && data ? data.sort_order : 1;
    document.getElementById('m_use_yn').value = isEdit && data ? data.use_yn : 1;

    if (depth === 1) {
        document.getElementById('row-apply').style.display = '';
        document.getElementById('m_apply_target').value = isEdit && data ? (data.apply_target || 'member') : 'member';
    } else {
        document.getElementById('row-apply').style.display = 'none';
    }

    document.getElementById('modal-overlay').style.display = 'flex';
    setTimeout(function () { document.getElementById('m_cat_name').focus(); }, 80);
}

function closeModal() {
    document.getElementById('modal-overlay').style.display = 'none';
    document.getElementById('m_cat_name').value = '';
}

function saveCategory() {
    var catName = document.getElementById('m_cat_name').value.trim();
    if (!catName) { admToast('카테고리명을 입력해 주세요.'); document.getElementById('m_cat_name').focus(); return; }

    var body = new URLSearchParams({
        mode: modalState.mode,
        idx: modalState.idx,
        depth: modalState.depth,
        parent_idx: modalState.parentIdx,
        cat_name: catName,
        apply_target: modalState.depth === 1 ? document.getElementById('m_apply_target').value : '',
        sort_order: document.getElementById('m_sort_order').value,
        use_yn: document.getElementById('m_use_yn').value
    });

    var btn = document.getElementById('btn-modal-save');
    btn.disabled = true;

    fetch('proc.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            admToast(res.msg);
            if (res.result === 'ok') {
                closeModal();
                var d = modalState.depth;
                var p = modalState.parentIdx;
                var reloadParent = (modalState.mode === 'insert') ? p : selIdx[d - 1];

                if (d === 1) {
                    location.reload();
                } else {
                    loadChildren(reloadParent, d);
                    reloadTree();
                }
            }
        })
        .catch(function () { admToast('서버 오류가 발생했습니다.'); })
        .finally(function () { btn.disabled = false; });
}

function deleteItem(idx, name, depth) {
    if (!confirm('[' + name + '] 을 삭제하시겠습니까?\n하위 항목이 있으면 삭제되지 않습니다.')) return;

    fetch('proc.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'mode=delete&idx=' + idx })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            admToast(res.msg);
            if (res.result === 'ok') {
                delete catStore[idx];
                if (depth === 1) {
                    location.reload();
                } else {
                    var el = document.querySelector('[data-idx="' + idx + '"][data-depth="' + depth + '"]');
                    if (el) el.remove();
                    reloadTree();
                }
            }
        })
        .catch(function () { admToast('삭제 중 오류가 발생했습니다.'); });
}
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
