<?php
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$no = isset($_GET['no']) ? (int)$_GET['no'] : 0;
$row = null;
$edit_files = [];
if ($no) {
    $row = sql_one_one('community_posts', '*', "and no=" . $no);
    if (!$row) {
        header("Location: index.php");
        exit;
    }
    $edit_files = sql_one('community_post_file', '*', "and post_no=" . $no . " order by idx asc");
}

$boards = sql_one('board_config', '*', 'order by sort_order asc, brd_id asc');
if (empty($boards)) {
    $boards = [
        ['brd_id' => 'notice', 'brd_name' => '공지사항', 'file_cnt' => 3, 'file_size' => 5120],
        ['brd_id' => 'news',   'brd_name' => '뉴스',     'file_cnt' => 3, 'file_size' => 5120],
        ['brd_id' => 'faq',    'brd_name' => 'FAQ',      'file_cnt' => 3, 'file_size' => 5120],
    ];
}
$default_board = isset($_GET['board']) ? $_GET['board'] : ($boards[0]['brd_id']);
$cur_board = $row ? $row['board_type'] : $default_board;

$cfg = null;
foreach ($boards as $b) {
    if ($b['brd_id'] === $cur_board) { $cfg = $b; break; }
}
$file_cnt = $cfg ? (int)$cfg['file_cnt'] : 3;
$file_size_kb = $cfg ? (int)$cfg['file_size'] : 5120;

$page_title = $no ? "게시물 수정" : "게시물 작성";
$active_page = "community";
$err_msg = isset($_GET['err']) ? $_GET['err'] : '';

include_once __DIR__ . "/../inc/adm_head.php";
?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">

<?php if ($err_msg): ?>
    <div style="background:#FEF2F2; border:1px solid #FECACA; color:#B91C1C; padding:12px 16px; border-radius:8px; font-size:0.9rem; margin-bottom:18px;">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($err_msg); ?>
    </div>
<?php endif; ?>

<div class="adm-form-box" style="max-width:100%;">
    <form id="write-form" method="post" action="write_proc.php" enctype="multipart/form-data">
        <input type="hidden" name="no" value="<?php echo $no; ?>">

        <div class="adm-form-row">
            <label>게시판 *</label>
            <select name="board_type" id="boardType" onchange="toggleAnswerField()" <?php echo $no ? 'disabled' : ''; ?>>
                <?php foreach ($boards as $b): ?>
                    <option value="<?php echo htmlspecialchars($b['brd_id']); ?>" <?php echo $cur_board === $b['brd_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['brd_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($no): ?><input type="hidden" name="board_type" value="<?php echo htmlspecialchars($cur_board); ?>"><p style="font-size:0.78rem; color:var(--adm-muted); margin-top:5px;">수정 시에는 게시판을 변경할 수 없습니다.</p><?php endif; ?>
        </div>

        <div class="adm-form-row">
            <label>배지 (목록에 표시되는 라벨)</label>
            <input type="text" name="badge" value="<?php echo $row ? htmlspecialchars($row['badge']) : '공지'; ?>" placeholder="예: 공지 / 안내 / 소식 / Q">
        </div>

        <div class="adm-form-row">
            <label>제목 *</label>
            <input type="text" name="title" required value="<?php echo $row ? htmlspecialchars($row['title']) : ''; ?>" placeholder="게시물 제목을 입력하세요">
        </div>

        <div class="adm-form-row" id="answerRow">
            <label>답변 요약 (FAQ 전용)</label>
            <input type="text" name="answer" value="<?php echo $row ? htmlspecialchars($row['answer']) : ''; ?>" placeholder="예: 30평형 기준 2~3시간">
        </div>

        <div class="adm-form-row">
            <label>내용</label>
            <textarea id="content" name="content"><?php echo $row ? htmlspecialchars($row['content']) : ''; ?></textarea>
        </div>

        <?php if ($file_cnt > 0): ?>
        <div class="adm-form-row">
            <label>첨부파일 (최대 <?php echo $file_cnt; ?>개, 1개당 <?php echo number_format($file_size_kb); ?>KB)</label>

            <?php if ($no && count($edit_files) > 0): ?>
            <div style="margin-bottom:12px; padding:12px; background:#F8FAFC; border-radius:8px;">
                <p style="font-size:0.8rem; color:var(--adm-muted); margin-bottom:8px;">기존 첨부파일</p>
                <?php foreach ($edit_files as $ef): ?>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                    <span style="font-size:0.85rem;"><i class="fa-solid fa-paperclip"></i> <?php echo htmlspecialchars($ef['ori_name']); ?> (<?php echo round($ef['file_size'] / 1024, 1); ?>KB)</span>
                    <button type="button" class="adm-btn adm-btn-outline" style="padding:3px 10px; font-size:0.78rem; color:#B91C1C; border-color:#FECACA;" onclick="delFile(<?php echo $ef['idx']; ?>, this)">삭제</button>
                    <input type="hidden" name="del_file[]" id="del_file_<?php echo $ef['idx']; ?>" value="">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php
            $remain = $file_cnt - count($edit_files);
            if ($remain < 0) $remain = 0;
            $show_cnt = $no ? $remain : $file_cnt;
            for ($i = 0; $i < $show_cnt; $i++):
            ?>
            <div style="margin-bottom:10px;">
                <input type="file" name="file_<?php echo $i; ?>" accept=".jpg,.jpeg,.png,.gif,.pdf,.zip,.xlsx,.docx,.hwp" style="width:100%; padding:8px; border:1px dashed var(--adm-border); border-radius:8px; font-size:0.85rem;">
            </div>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <div class="adm-form-row">
            <label>상태</label>
            <select name="state">
                <option value="1" <?php echo (!$row || $row['state'] == 1) ? 'selected' : ''; ?>>게시중</option>
                <option value="0" <?php echo ($row && $row['state'] == 0) ? 'selected' : ''; ?>>숨김</option>
            </select>
        </div>

        <div style="display:flex; gap:10px; margin-top:24px;">
            <button type="button" class="adm-btn" onclick="submitForm()"><i class="fa-solid fa-floppy-disk"></i> 저장</button>
            <a href="index.php" class="adm-btn adm-btn-outline">취소</a>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>
<script>
    $(function () {
        $('#content').summernote({
            lang: 'ko-KR',
            height: 400,
            toolbar: [
                ['style',  ['style']],
                ['font',   ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                ['color',  ['color']],
                ['para',   ['ul', 'ol', 'paragraph']],
                ['table',  ['table']],
                ['insert', ['link', 'picture']],
                ['view',   ['fullscreen', 'codeview']]
            ],
            callbacks: {
                onImageUpload: function (files) {
                    var reader = new FileReader();
                    reader.onloadend = function () {
                        var img = $('<img>').attr('src', reader.result).css('max-width', '100%');
                        $('#content').summernote('insertNode', img[0]);
                    };
                    reader.readAsDataURL(files[0]);
                }
            }
        });
    });

    function toggleAnswerField() {
        var sel = document.getElementById('boardType');
        document.getElementById('answerRow').style.display = sel.value === 'faq' ? 'block' : 'none';
    }
    toggleAnswerField();

    function submitForm() {
        var title = document.querySelector('input[name=title]').value.trim();
        if (!title) { admToast('제목을 입력해 주세요.'); return; }
        $('textarea[name=content]').val($('#content').summernote('code'));
        document.getElementById('write-form').submit();
    }

    function delFile(fileIdx, btn) {
        if (!confirm('이 첨부파일을 삭제하시겠습니까?')) return;
        document.getElementById('del_file_' + fileIdx).value = fileIdx;
        btn.closest('div').style.opacity = '0.4';
        btn.disabled = true;
        btn.textContent = '삭제예정';
    }
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
