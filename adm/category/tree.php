<?php
include_once __DIR__ . "/../../inc/dbconn.php";

if (empty($_SESSION['s_adm_id'])) {
    echo '<p style="text-align:center;padding:24px;color:var(--adm-muted);">로그인이 필요합니다.</p>';
    exit;
}

$all_cats = array();
$que = "SELECT * FROM ai_category ORDER BY depth ASC, sort_order ASC, idx ASC";
$res_all = mysqli_query($conn, $que);
if ($res_all) {
    while ($row = mysqli_fetch_assoc($res_all)) $all_cats[$row['idx']] = $row;
}

$tree = array();
foreach ($all_cats as $cat) {
    if ($cat['depth'] == 1) $tree[$cat['idx']] = array('info' => $cat, 'children' => array());
}
foreach ($all_cats as $cat) {
    if ($cat['depth'] == 2 && isset($tree[$cat['parent_idx']])) {
        $tree[$cat['parent_idx']]['children'][$cat['idx']] = array('info' => $cat, 'children' => array());
    }
}
foreach ($all_cats as $cat) {
    if ($cat['depth'] == 3) {
        foreach ($tree as $d1_idx => &$d1) {
            if (isset($d1['children'][$cat['parent_idx']])) {
                $d1['children'][$cat['parent_idx']]['children'][] = $cat;
                break;
            }
        }
        unset($d1);
    }
}

$target_label = array('member' => '회원', 'board' => '게시판', 'content' => '콘텐츠');

if (empty($tree)) {
    echo '<p style="text-align:center;padding:24px;color:var(--adm-muted);">카테고리가 없습니다.</p>';
    exit;
}
?>
<table class="adm-table">
    <thead>
        <tr>
            <th>카테고리 구조</th>
            <th style="width:100px; text-align:center; white-space:nowrap;">구분</th>
            <th style="width:100px; text-align:center; white-space:nowrap;">적용대상</th>
            <th style="width:60px; text-align:center;">순서</th>
            <th style="width:60px; text-align:center;">상태</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($tree as $d1): $i1 = $d1['info']; ?>
        <tr style="background:#F8FAFC;">
            <td style="font-weight:800; color:var(--adm-text);">&#9646; <?php echo htmlspecialchars($i1['cat_name']); ?></td>
            <td style="text-align:center;"><span class="adm-badge" style="background:var(--adm-sidebar); color:#fff; display:inline-block; white-space:nowrap;">대분류</span></td>
            <td style="text-align:center;">
                <?php if ($i1['apply_target'] && isset($target_label[$i1['apply_target']])): ?>
                    <span class="adm-badge type-customer" style="display:inline-block; white-space:nowrap;"><?php echo $target_label[$i1['apply_target']]; ?></span>
                <?php else: ?><span style="color:#CBD5E1;">-</span><?php endif; ?>
            </td>
            <td style="text-align:center; color:var(--adm-muted); font-size:0.82rem;"><?php echo $i1['sort_order']; ?></td>
            <td style="text-align:center; font-size:0.75rem; font-weight:700; color:<?php echo $i1['use_yn'] ? '#059669' : '#CBD5E1'; ?>;"><?php echo $i1['use_yn'] ? '사용' : '중지'; ?></td>
        </tr>
        <?php if (empty($d1['children'])): ?>
            <tr><td colspan="5" style="padding-left:38px; color:#CBD5E1; font-size:0.82rem;">&#8627; 중분류 없음</td></tr>
        <?php else: foreach ($d1['children'] as $d2): $i2 = $d2['info']; ?>
            <tr>
                <td style="padding-left:38px; font-weight:700; color:var(--adm-text);">&#8627; <?php echo htmlspecialchars($i2['cat_name']); ?></td>
                <td style="text-align:center;"><span class="adm-badge" style="background:var(--adm-primary); color:#fff; display:inline-block; white-space:nowrap;">중분류</span></td>
                <td style="text-align:center; color:#CBD5E1;">-</td>
                <td style="text-align:center; color:var(--adm-muted); font-size:0.82rem;"><?php echo $i2['sort_order']; ?></td>
                <td style="text-align:center; font-size:0.75rem; font-weight:700; color:<?php echo $i2['use_yn'] ? '#059669' : '#CBD5E1'; ?>;"><?php echo $i2['use_yn'] ? '사용' : '중지'; ?></td>
            </tr>
            <?php if (empty($d2['children'])): ?>
                <tr><td colspan="5" style="padding-left:60px; color:#E2E8F0; font-size:0.8rem;">&#8627; 소분류 없음</td></tr>
            <?php else: foreach ($d2['children'] as $d3): ?>
                <tr>
                    <td style="padding-left:60px; color:var(--adm-text);">&#8627; <?php echo htmlspecialchars($d3['cat_name']); ?></td>
                    <td style="text-align:center;"><span class="adm-badge" style="background:var(--adm-primary-light); color:#fff; display:inline-block; white-space:nowrap;">소분류</span></td>
                    <td style="text-align:center; color:#CBD5E1;">-</td>
                    <td style="text-align:center; color:var(--adm-muted); font-size:0.82rem;"><?php echo $d3['sort_order']; ?></td>
                    <td style="text-align:center; font-size:0.75rem; font-weight:700; color:<?php echo $d3['use_yn'] ? '#059669' : '#CBD5E1'; ?>;"><?php echo $d3['use_yn'] ? '사용' : '중지'; ?></td>
                </tr>
            <?php endforeach; endif; ?>
        <?php endforeach; endif; ?>
    <?php endforeach; ?>
    </tbody>
</table>
<?php $total = count($all_cats); ?>
<div style="padding:10px 16px; background:#F8FAFC; border-top:1px solid var(--adm-border); font-size:0.8rem; color:var(--adm-muted); text-align:right;">
    총 <?php echo $total; ?>개 카테고리
</div>
