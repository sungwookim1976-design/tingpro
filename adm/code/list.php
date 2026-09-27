<?php
$page_title = "코드관리";
$active_page = "settings";
$active_sub = "code";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

// corea26 admin/code와 동일 스키마로 테이블 없으면 생성
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS `tb_code` (
      `idx`        INT          NOT NULL AUTO_INCREMENT,
      `group_sort` INT          NOT NULL DEFAULT 1       COMMENT '그룹순서',
      `group_name` VARCHAR(100) NOT NULL                 COMMENT '그룹관리명',
      `code_sort`  INT          NOT NULL DEFAULT 1       COMMENT '코드순서',
      `code_name`  VARCHAR(100) NOT NULL                 COMMENT '코드명',
      `code_value` VARCHAR(100) NOT NULL                 COMMENT '코드값',
      `reg_date`   DATETIME     NOT NULL DEFAULT NOW()   COMMENT '등록일',
      PRIMARY KEY (`idx`),
      KEY `idx_group` (`group_sort`, `group_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

/* ── AJAX 요청 처리 ── */
if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    header('Content-Type: application/json; charset=utf-8');

    if (empty($_SESSION['s_adm_id'])) {
        echo json_encode(array('result' => 'fail', 'msg' => '로그인이 필요합니다.'), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $mode = isset($_POST['mode']) ? $_POST['mode'] : '';

    if ($mode === 'save') {
        $idx        = isset($_POST['idx'])        ? (int)$_POST['idx']        : 0;
        $group_sort = isset($_POST['group_sort']) ? (int)$_POST['group_sort'] : 1;
        $code_sort  = isset($_POST['code_sort'])  ? (int)$_POST['code_sort']  : 1;
        $group_name = mysqli_real_escape_string($conn, trim(isset($_POST['group_name']) ? $_POST['group_name'] : ''));
        $code_name  = mysqli_real_escape_string($conn, trim(isset($_POST['code_name'])  ? $_POST['code_name']  : ''));
        $code_value = mysqli_real_escape_string($conn, trim(isset($_POST['code_value']) ? $_POST['code_value'] : ''));

        if ($group_name === '' || $code_name === '' || $code_value === '') {
            echo json_encode(array('result' => 'fail', 'msg' => '필수 항목을 입력해주세요.'));
            exit;
        }

        if ($idx > 0) {
            mysqli_query($conn, "UPDATE tb_code SET
                group_sort='$group_sort', group_name='$group_name',
                code_sort='$code_sort', code_name='$code_name', code_value='$code_value'
                WHERE idx=$idx");
        } else {
            mysqli_query($conn, "INSERT INTO tb_code (group_sort,group_name,code_sort,code_name,code_value)
                VALUES ('$group_sort','$group_name','$code_sort','$code_name','$code_value')");
        }
        echo json_encode(array('result' => 'ok'));
        exit;
    }

    if ($mode === 'delete') {
        $idx = isset($_POST['idx']) ? (int)$_POST['idx'] : 0;
        mysqli_query($conn, "DELETE FROM tb_code WHERE idx=$idx");
        echo json_encode(array('result' => 'ok', 'msg' => '삭제되었습니다.'));
        exit;
    }

    if ($mode === 'list') {
        $filter_group = mysqli_real_escape_string($conn, trim(isset($_POST['filter_group']) ? $_POST['filter_group'] : ''));
        $search_kw    = mysqli_real_escape_string($conn, trim(isset($_POST['search_group']) ? $_POST['search_group'] : ''));
        $conds = array();
        if ($filter_group !== '') $conds[] = "group_name='$filter_group'";
        if ($search_kw    !== '') $conds[] = "(code_name LIKE '%$search_kw%' OR code_value LIKE '%$search_kw%')";
        $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';
        $res   = mysqli_query($conn, "SELECT * FROM tb_code $where ORDER BY group_sort ASC, group_name ASC, code_sort ASC, idx ASC");
        $rows  = array();
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        echo json_encode(array('result' => 'ok', 'list' => $rows));
        exit;
    }

    echo json_encode(array('result' => 'fail', 'msg' => 'unknown mode'));
    exit;
}

/* ── 그룹관리명 목록 (selectbox용) ── */
$group_names = array();
$rg = mysqli_query($conn, "SELECT DISTINCT group_name FROM tb_code ORDER BY group_sort ASC, group_name ASC");
if ($rg) while ($g = mysqli_fetch_assoc($rg)) $group_names[] = $g['group_name'];

/* ── 전체 목록 → 그룹별로 구조화 ── */
$list = array();
$res  = mysqli_query($conn, "SELECT * FROM tb_code ORDER BY group_sort ASC, group_name ASC, code_sort ASC, idx ASC");
while ($r = mysqli_fetch_assoc($res)) $list[] = $r;

$groups = array();
foreach ($list as $row) {
    $gn = $row['group_name'];
    if (!isset($groups[$gn])) {
        $groups[$gn] = array('sort' => (int)$row['group_sort'], 'items' => array());
    }
    $groups[$gn]['items'][] = $row;
}

function makeOptions($selected = 1) {
    $html = '';
    for ($i = 1; $i <= 100; $i++) {
        $sel = ($i == $selected) ? ' selected' : '';
        $html .= '<option value="'.$i.'"'.$sel.'>'.$i.'</option>';
    }
    return $html;
}

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div class="adm-section" style="margin-bottom:16px;">
    <div class="adm-section-head">
        <h3><i class="fa-solid fa-code"></i> 코드관리</h3>
    </div>
    <p style="padding:14px 22px 18px; font-size:0.85rem; color:var(--adm-muted);">
        그룹 단위로 공통 코드를 관리합니다. 등록된 코드는 시스템 전반에서 참조됩니다.
    </p>
</div>

<!-- 툴바 -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <select id="filterGroup" onchange="doSearch()" class="adm-form-row-select" style="padding:9px 14px; border:1px solid var(--adm-border); border-radius:8px; font-size:0.9rem; min-width:160px; background:#fff;">
            <option value="">전체 그룹</option>
            <?php foreach ($group_names as $gn): ?>
            <option value="<?php echo htmlspecialchars($gn, ENT_QUOTES); ?>">
                <?php echo htmlspecialchars($gn); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <input type="text" id="searchInput" placeholder="코드명 또는 코드값 검색"
               style="padding:9px 14px; border:1px solid var(--adm-border); border-radius:8px; font-size:0.9rem; width:220px;">
        <button onclick="doSearch()" class="adm-btn" style="padding:9px 18px;">검색</button>
        <button onclick="clearSearch()" class="adm-btn adm-btn-outline" style="padding:9px 14px;">초기화</button>
    </div>
    <button onclick="openModal()" class="adm-btn"><i class="fa-solid fa-plus"></i> 코드 등록</button>
</div>

<!-- 트리 -->
<div id="codeTree">
<?php if (empty($groups)): ?>
<div class="tree-empty">등록된 코드가 없습니다.</div>
<?php else: foreach ($groups as $gn => $gdata): ?>

<div class="tree-group" id="grp-<?= md5($gn) ?>">
    <div class="tree-group-header" onclick="toggleGroup('<?= md5($gn) ?>')">
        <span class="tree-toggle" id="tgl-<?= md5($gn) ?>">▼</span>
        <span class="adm-badge type-customer" style="margin:0 8px;"><?= $gdata['sort'] ?></span>
        <span class="tree-group-name"><?= htmlspecialchars($gn) ?></span>
        <span class="tree-count"><?= count($gdata['items']) ?>건</span>
        <button class="adm-btn" style="padding:5px 12px; font-size:0.78rem;" onclick="event.stopPropagation(); openModalForGroup('<?= htmlspecialchars($gn, ENT_QUOTES) ?>', <?= $gdata['sort'] ?>)">
            + 코드 추가
        </button>
    </div>
    <div class="tree-group-body" id="body-<?= md5($gn) ?>">
        <table class="adm-table tree-table">
            <thead>
                <tr>
                    <th style="width:60px; text-align:center;">코드순서</th>
                    <th>코드명</th>
                    <th style="width:180px;">코드값</th>
                    <th style="width:110px; text-align:center;">관리</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($gdata['items'] as $row):
                $csort = isset($row['code_sort']) ? $row['code_sort'] : 1; ?>
            <tr data-idx="<?= $row['idx'] ?>"
                data-gsort="<?= $row['group_sort'] ?>"
                data-csort="<?= $csort ?>"
                data-group="<?= htmlspecialchars($row['group_name'], ENT_QUOTES) ?>"
                data-name="<?= htmlspecialchars($row['code_name'],  ENT_QUOTES) ?>"
                data-value="<?= htmlspecialchars($row['code_value'], ENT_QUOTES) ?>">
                <td style="text-align:center;"><span class="adm-badge type-customer"><?= $csort ?></span></td>
                <td class="tree-code-name"><?= htmlspecialchars($row['code_name']) ?></td>
                <td><code class="code-chip"><?= htmlspecialchars($row['code_value']) ?></code></td>
                <td style="text-align:center; white-space:nowrap;">
                    <button class="btn-edit-sm" onclick="editRow(this)">수정</button>
                    <button class="btn-del-sm"  onclick="delRow(this)">삭제</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endforeach; endif; ?>
</div>

<!-- ── 등록/수정 모달 ── -->
<div id="modalOverlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:2000; align-items:center; justify-content:center;">
<div style="background:#fff; border-radius:14px; width:480px; max-width:95%; padding:32px; position:relative; box-shadow:0 20px 60px rgba(0,0,0,0.25);">
    <h3 id="modalTitle" style="font-size:1.1rem; font-weight:800; color:var(--adm-text); margin-bottom:24px;"></h3>
    <input type="hidden" id="mIdx" value="0">

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div class="adm-form-row">
            <label>그룹순서</label>
            <select id="mGSort"><?= makeOptions(1) ?></select>
        </div>
        <div class="adm-form-row">
            <label>코드순서</label>
            <select id="mCSort"><?= makeOptions(1) ?></select>
        </div>
    </div>
    <div class="adm-form-row">
        <label>그룹관리명 <span style="color:#EF4444;">*</span></label>
        <input type="text" id="mGroup" placeholder="예: 강의유형" autocomplete="off">
    </div>
    <div class="adm-form-row">
        <label>코드명 <span style="color:#EF4444;">*</span></label>
        <input type="text" id="mName" placeholder="예: 온라인 강의" autocomplete="off">
    </div>
    <div class="adm-form-row">
        <label>코드값 <span style="color:#EF4444;">*</span></label>
        <input type="text" id="mValue" placeholder="예: online" autocomplete="off">
    </div>

    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:22px;">
        <button class="adm-btn adm-btn-outline" onclick="closeModal()">취소</button>
        <button class="adm-btn" id="btnSave" onclick="saveCode()">저장</button>
    </div>
    <button onclick="closeModal()" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:1.4rem;cursor:pointer;color:#94A3B8;">&#215;</button>
</div>
</div>

<style>
.code-chip { background:#F1F5F9; border:1px solid var(--adm-border); border-radius:4px; padding:2px 8px; font-size:0.85rem; }

#codeTree { display:flex; flex-direction:column; gap:10px; }
.tree-empty { text-align:center; padding:60px; color:var(--adm-muted); font-size:0.95rem; background:var(--adm-card); border-radius:14px; border:1px solid var(--adm-border); }

.tree-group { background:var(--adm-card); border-radius:14px; border:1px solid var(--adm-border); overflow:hidden; }
.tree-group-header { display:flex; align-items:center; gap:0; padding:13px 18px; cursor:pointer; background:#F8FAFC; border-bottom:1px solid var(--adm-border); transition:background 0.15s; user-select:none; }
.tree-group-header:hover { background:#EFF6FF; }
.tree-toggle { font-size:0.7rem; color:var(--adm-muted); width:18px; transition:transform 0.2s; display:inline-block; }
.tree-toggle.collapsed { transform:rotate(-90deg); }
.tree-group-name { font-size:0.97rem; font-weight:800; color:var(--adm-text); flex:1; }
.tree-count { font-size:0.78rem; color:var(--adm-muted); font-weight:600; background:#F1F5F9; padding:2px 8px; border-radius:10px; margin-right:10px; }

.tree-group-body { padding:0; }
.tree-group-body.hidden { display:none; }
.tree-table .tree-code-name { font-weight:700; color:var(--adm-text); padding-left:28px; }
.tree-table .tree-code-name::before { content:'└'; color:#CBD5E1; margin-right:8px; font-size:0.9rem; }

.btn-edit-sm { padding:4px 10px; background:#E0F7FA; color:var(--adm-primary); border:none; border-radius:4px; font-size:0.78rem; cursor:pointer; font-weight:700; }
.btn-edit-sm:hover { background:var(--adm-primary); color:#fff; }
.btn-del-sm  { padding:4px 10px; background:#FEE2E2; color:#B91C1C; border:none; border-radius:4px; font-size:0.78rem; cursor:pointer; font-weight:700; }
.btn-del-sm:hover  { background:#B91C1C; color:#fff; }
</style>

<script>
var PROC = 'list.php';

function toggleGroup(hash) {
    var body = document.getElementById('body-' + hash);
    var tgl  = document.getElementById('tgl-'  + hash);
    body.classList.toggle('hidden');
    tgl.classList.toggle('collapsed');
}

function setSelect(id, val) {
    var sel = document.getElementById(id);
    for (var i = 0; i < sel.options.length; i++) {
        sel.options[i].selected = (parseInt(sel.options[i].value) === parseInt(val));
    }
}

function openModal(data) {
    document.getElementById('modalTitle').textContent = data ? '코드 수정' : '코드 등록';
    document.getElementById('mIdx').value = data ? data.idx : 0;
    setSelect('mGSort', data ? data.gsort : 1);
    setSelect('mCSort', data ? data.csort : 1);
    document.getElementById('mGroup').value = data ? data.group : '';
    document.getElementById('mName').value = data ? data.name : '';
    document.getElementById('mValue').value = data ? data.value : '';
    document.getElementById('mGroup').readOnly = false;
    document.getElementById('modalOverlay').style.display = 'flex';
    setTimeout(function () { document.getElementById('mGroup').focus(); }, 80);
}

function openModalForGroup(groupName, groupSort) {
    document.getElementById('modalTitle').textContent = '코드 추가 — ' + groupName;
    document.getElementById('mIdx').value = 0;
    setSelect('mGSort', groupSort || 1);
    setSelect('mCSort', 1);
    document.getElementById('mGroup').value = groupName;
    document.getElementById('mGroup').readOnly = true;
    document.getElementById('mName').value = '';
    document.getElementById('mValue').value = '';
    document.getElementById('modalOverlay').style.display = 'flex';
    setTimeout(function () { document.getElementById('mName').focus(); }, 80);
}

function closeModal() {
    document.getElementById('mGroup').readOnly = false;
    document.getElementById('modalOverlay').style.display = 'none';
}

function editRow(btn) {
    var tr = btn.closest('tr');
    openModal({
        idx:   tr.dataset.idx,
        gsort: tr.dataset.gsort,
        csort: tr.dataset.csort,
        group: tr.dataset.group,
        name:  tr.dataset.name,
        value: tr.dataset.value
    });
}

function delRow(btn) {
    var tr   = btn.closest('tr');
    var name = tr.dataset.name;
    if (!confirm('[' + name + '] 코드를 삭제하시겠습니까?')) return;
    fetch(PROC, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'ajax=1&mode=delete&idx=' + tr.dataset.idx })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.result === 'ok') { tr.remove(); } else { admToast(res.msg); }
        })
        .catch(function () { admToast('서버 오류가 발생했습니다.'); });
}

function saveCode() {
    var group = document.getElementById('mGroup').value.trim();
    var name  = document.getElementById('mName').value.trim();
    var value = document.getElementById('mValue').value.trim();
    if (!group || !name || !value) { admToast('필수 항목을 모두 입력해주세요.'); return; }

    var btn = document.getElementById('btnSave');
    btn.disabled = true;

    var body = new URLSearchParams({
        ajax: '1', mode: 'save',
        idx:        document.getElementById('mIdx').value,
        group_sort: document.getElementById('mGSort').value,
        code_sort:  document.getElementById('mCSort').value,
        group_name: group,
        code_name:  name,
        code_value: value
    });

    fetch(PROC, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.result === 'ok') { closeModal(); location.reload(); }
            else { admToast(res.msg); }
        })
        .catch(function () { admToast('서버 오류가 발생했습니다.'); })
        .finally(function () { btn.disabled = false; });
}

function doSearch() {
    var kw    = document.getElementById('searchInput').value.trim();
    var group = document.getElementById('filterGroup').value;
    if (!kw && !group) { location.reload(); return; }

    fetch(PROC, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'ajax=1&mode=list&filter_group=' + encodeURIComponent(group) + '&search_group=' + encodeURIComponent(kw) })
        .then(function (r) { return r.json(); })
        .then(function (res) { renderTree(res.list || [], '#codeTree'); });
}

function clearSearch() {
    document.getElementById('filterGroup').value = '';
    document.getElementById('searchInput').value = '';
    location.reload();
}

function renderTree(list, targetSel) {
    var wrap = document.querySelector(targetSel);
    wrap.innerHTML = '';
    if (!list.length) {
        wrap.innerHTML = '<div class="tree-empty">검색 결과가 없습니다.</div>';
        return;
    }

    var groups = {};
    var order  = [];
    list.forEach(function (r) {
        var gn = r.group_name;
        if (!groups[gn]) { groups[gn] = { sort: r.group_sort, items: [] }; order.push(gn); }
        groups[gn].items.push(r);
    });

    order.forEach(function (gn) {
        var gdata = groups[gn];
        var hash  = md5Lite(gn);
        var rows  = '';
        gdata.items.forEach(function (r) {
            var cs = r.code_sort || 1;
            rows += '<tr data-idx="'+r.idx+'" data-gsort="'+r.group_sort+'" data-csort="'+cs+'"' +
                    ' data-group="'+escHtml(r.group_name)+'" data-name="'+escHtml(r.code_name)+'" data-value="'+escHtml(r.code_value)+'">' +
                    '<td style="text-align:center;"><span class="adm-badge type-customer">'+cs+'</span></td>' +
                    '<td class="tree-code-name">'+escHtml(r.code_name)+'</td>' +
                    '<td><code class="code-chip">'+escHtml(r.code_value)+'</code></td>' +
                    '<td style="text-align:center; white-space:nowrap;">' +
                      '<button class="btn-edit-sm" onclick="editRow(this)">수정</button> ' +
                      '<button class="btn-del-sm" onclick="delRow(this)">삭제</button>' +
                    '</td></tr>';
        });
        var groupHtml =
            '<div class="tree-group" id="grp-'+hash+'">' +
            '<div class="tree-group-header" onclick="toggleGroup(\''+hash+'\')">'+
              '<span class="tree-toggle" id="tgl-'+hash+'">▼</span>'+
              '<span class="adm-badge type-customer" style="margin:0 8px;">'+gdata.sort+'</span>'+
              '<span class="tree-group-name">'+escHtml(gn)+'</span>'+
              '<span class="tree-count">'+gdata.items.length+'건</span>'+
              '<button class="adm-btn" style="padding:5px 12px; font-size:0.78rem;" onclick="event.stopPropagation();openModalForGroup(\''+escQ(gn)+'\','+gdata.sort+')">+ 코드 추가</button>'+
            '</div>'+
            '<div class="tree-group-body" id="body-'+hash+'">'+
              '<table class="adm-table tree-table"><thead><tr>'+
              '<th style="width:60px; text-align:center;">코드순서</th><th>코드명</th>'+
              '<th style="width:180px;">코드값</th><th style="width:110px; text-align:center;">관리</th>'+
              '</tr></thead><tbody>'+rows+'</tbody></table>'+
            '</div></div>';
        wrap.insertAdjacentHTML('beforeend', groupHtml);
    });
}

function md5Lite(str) {
    var h = 0;
    for (var i = 0; i < str.length; i++) h = ((h << 5) - h + str.charCodeAt(i)) | 0;
    return 'g' + Math.abs(h).toString(16);
}
function escHtml(s) {
    var d = document.createElement('div');
    d.textContent = String(s);
    return d.innerHTML;
}
function escQ(s) {
    return String(s).replace(/'/g, "\\'");
}

document.getElementById('modalOverlay').addEventListener('click', function (e) {
    if (e.target.id === 'modalOverlay') closeModal();
});
['mGroup', 'mName', 'mValue'].forEach(function (id) {
    document.getElementById(id).addEventListener('keydown', function (e) {
        if (e.key === 'Enter') saveCode();
    });
});
document.getElementById('searchInput').addEventListener('keydown', function (e) { if (e.key === 'Enter') doSearch(); });
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
