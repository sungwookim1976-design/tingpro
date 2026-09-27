<?php
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$page_title = "견적 시공사례 이미지 등록";
$active_page = "quotes";
$active_sub = "cases";

$no = isset($_GET['no']) ? (int)$_GET['no'] : 0;

// 목록 조회
$where = "and 1=1";
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($search_q !== '') {
    $esc_q = mysqli_real_escape_string($conn, $search_q);
    $where .= " and (name like '%$esc_q%' or addr like '%$esc_q%' or film_name like '%$esc_q%')";
}

$total_cnt = sql_cnt('quotes', $where);
$quotes_list = sql_one('quotes', '*', $where . " order by no desc");

// 선택된 견적건
$selected_quote = null;
if ($no > 0) {
    $selected_quote = sql_one_one('quotes', '*', "and no=" . $no);
} elseif (!empty($quotes_list)) {
    $selected_quote = $quotes_list[0];
    $no = (int)$selected_quote['no'];
}

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <div>
        <h2 style="font-size:1.3rem; font-weight:800; color:var(--adm-text);">
            <i class="fa-solid fa-camera" style="color:var(--adm-primary);"></i> 견적 시공사례 첨부파일 이미지 등록 (최대 5장)
        </h2>
        <p style="font-size:0.88rem; color:var(--adm-muted); margin-top:4px;">
            견적 신청건별로 실제 시공 현장의 첨부 이미지 5장을 등록할 수 있습니다.
        </p>
    </div>
    <a href="index.php" class="adm-btn adm-btn-outline"><i class="fa-solid fa-list"></i> 견적 목록으로</a>
</div>

<div style="display:grid; grid-template-columns:360px 1fr; gap:24px; align-items:start;">

    <!-- 좌측: 견적건 선택 목록 -->
    <div class="adm-section" style="margin-bottom:0;">
        <div class="adm-section-head">
            <h3><i class="fa-solid fa-file-invoice"></i> 견적 목록 (<?php echo number_format($total_cnt); ?>건)</h3>
        </div>
        <div style="padding:12px;">
            <form method="get" action="cases.php" style="margin-bottom:12px; display:flex; gap:6px;">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search_q); ?>" placeholder="성함/주소/필름 검색" style="padding:7px 10px; border:1px solid var(--adm-border); border-radius:6px; font-size:0.85rem; flex:1;">
                <button type="submit" class="adm-btn" style="padding:7px 12px; font-size:0.85rem;"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>

            <div style="max-height:600px; overflow-y:auto; display:flex; flex-direction:column; gap:8px;">
                <?php if (empty($quotes_list)): ?>
                    <div style="text-align:center; padding:30px; color:var(--adm-muted); font-size:0.88rem;">등록된 견적이 없습니다.</div>
                <?php else: foreach ($quotes_list as $q):
                    $is_sel = ($selected_quote && (int)$selected_quote['no'] === (int)$q['no']);
                    $img_cnt = 0;
                    for ($i = 1; $i <= 5; $i++) {
                        if (!empty($q['case_img' . $i])) $img_cnt++;
                    }
                ?>
                    <a href="cases.php?no=<?php echo $q['no']; ?>&q=<?php echo urlencode($search_q); ?>" style="display:block; padding:12px; border-radius:8px; border:1px solid <?php echo $is_sel ? 'var(--adm-primary)' : 'var(--adm-border)'; ?>; background:<?php echo $is_sel ? '#F0F9FF' : '#fff'; ?>; transition:all 0.15s;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <strong style="font-size:0.9rem; color:<?php echo $is_sel ? 'var(--adm-primary)' : 'var(--adm-text)'; ?>;"><?php echo htmlspecialchars($q['name']); ?> 고객님</strong>
                            <span class="adm-badge" style="background:<?php echo $img_cnt > 0 ? '#ECFDF5' : '#F1F5F9'; ?>; color:<?php echo $img_cnt > 0 ? '#059669' : '#64748B'; ?>;">
                                <i class="fa-solid fa-image"></i> <?php echo $img_cnt; ?>/5장
                            </span>
                        </div>
                        <div style="font-size:0.82rem; color:var(--adm-muted); margin-bottom:4px;">
                            <?php echo htmlspecialchars($q['space_type']); ?> <?php echo (int)$q['py']; ?>평 | <?php echo htmlspecialchars($q['film_name']); ?>
                        </div>
                        <div style="font-size:0.78rem; color:#94A3B8; display:flex; justify-content:space-between;">
                            <span><?php echo htmlspecialchars(mb_strimwidth($q['addr'], 0, 24, '...')); ?></span>
                            <span><?php echo substr($q['reg_date'], 0, 10); ?></span>
                        </div>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- 우측: 5장 이미지 등록/수정 폼 -->
    <?php if ($selected_quote): ?>
        <div class="adm-form-box" style="max-width:100%;">
            <div style="background:#F8FAFC; border:1px solid var(--adm-border); border-radius:10px; padding:18px; margin-bottom:24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <h3 style="font-size:1.1rem; font-weight:800; color:var(--adm-text);">
                        [No.<?php echo $selected_quote['no']; ?>] <?php echo htmlspecialchars($selected_quote['name']); ?> 고객님 견적 건
                    </h3>
                    <span class="adm-badge state-<?php echo htmlspecialchars($selected_quote['state']); ?>"><?php echo htmlspecialchars($selected_quote['state']); ?></span>
                </div>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; font-size:0.88rem; color:var(--adm-muted);">
                    <div><strong>공간:</strong> <?php echo htmlspecialchars($selected_quote['space_type']); ?> (<?php echo (int)$selected_quote['py']; ?>평)</div>
                    <div><strong>적용필름:</strong> <?php echo htmlspecialchars($selected_quote['film_name']); ?></div>
                    <div><strong>견적액:</strong> <?php echo number_format($selected_quote['total_price']); ?>원</div>
                    <div style="grid-column:1 / -1;"><strong>시공주소:</strong> <?php echo htmlspecialchars($selected_quote['addr']); ?></div>
                </div>
            </div>

            <form method="post" action="proc.php" enctype="multipart/form-data">
                <input type="hidden" name="mode" value="update_case_imgs">
                <input type="hidden" name="no" value="<?php echo $selected_quote['no']; ?>">
                <input type="hidden" name="redirect_to" value="cases.php?no=<?php echo $selected_quote['no']; ?>&q=<?php echo urlencode($search_q); ?>">

                <h4 style="font-size:0.95rem; font-weight:800; margin-bottom:16px; color:var(--adm-text); border-bottom:2px solid var(--adm-border); padding-bottom:8px;">
                    <i class="fa-solid fa-images" style="color:var(--adm-primary);"></i> 시공사례 첨부 이미지 5장 (대표 썸네일 ~ 상세 이미지)
                </h4>

                <div style="display:flex; flex-direction:column; gap:20px; margin-bottom:24px;">
                    <?php for ($i = 1; $i <= 5; $i++):
                        $img_val = isset($selected_quote['case_img' . $i]) ? $selected_quote['case_img' . $i] : '';
                        $img_src = '';
                        if (!empty($img_val)) {
                            $img_src = (strpos($img_val, 'http') === 0 || strpos($img_val, '/') === 0 || strpos($img_val, '../') === 0) ? $img_val : '../../' . $img_val;
                        }
                    ?>
                        <div style="background:#fff; border:1px solid var(--adm-border); border-radius:10px; padding:16px; display:flex; gap:20px; align-items:center;">
                            <div style="width:120px; height:90px; background:#F1F5F9; border-radius:8px; border:1px dashed #CBD5E1; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                                <?php if ($img_src): ?>
                                    <img src="<?php echo htmlspecialchars($img_src); ?>" alt="이미지 <?php echo $i; ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <span style="font-size:0.75rem; color:#94A3B8; text-align:center;"><i class="fa-solid fa-image" style="font-size:1.5rem; display:block; margin-bottom:4px;"></i>이미지 <?php echo $i; ?></span>
                                <?php endif; ?>
                            </div>

                            <div style="flex:1;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                    <label style="font-size:0.88rem; font-weight:800; color:var(--adm-text);">
                                        <?php echo $i === 1 ? '시공사례 대표 이미지 (이미지 1)' : '시공사례 추가 이미지 ' . $i; ?>
                                    </label>
                                    <?php if ($img_val): ?>
                                        <label style="font-size:0.8rem; color:#EF4444; font-weight:700; cursor:pointer;">
                                            <input type="checkbox" name="del_case_img<?php echo $i; ?>" value="1"> 이미지 삭제
                                        </label>
                                    <?php endif; ?>
                                </div>

                                <div style="display:flex; gap:10px; align-items:center; margin-bottom:6px;">
                                    <input type="file" name="case_img<?php echo $i; ?>_file" accept="image/*" style="font-size:0.82rem;">
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:0.78rem; color:var(--adm-muted); white-space:nowrap;">경로 / URL:</span>
                                    <input type="text" name="case_img<?php echo $i; ?>" value="<?php echo htmlspecialchars($img_val); ?>" placeholder="imgs/case_sample.png 또는 외부 URL" style="padding:6px 10px; font-size:0.82rem;">
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px;">
                    <button type="submit" class="adm-btn" style="padding:12px 28px; font-size:0.95rem; background:var(--adm-primary);">
                        <i class="fa-solid fa-floppy-disk"></i> 시공사례 이미지 5장 저장
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="adm-form-box" style="text-align:center; padding:60px; color:var(--adm-muted);">
            <i class="fa-solid fa-hand-pointer" style="font-size:2.5rem; color:#94A3B8; margin-bottom:12px;"></i>
            <p>좌측 목록에서 이미지 등록을 진행할 견적 건을 선택해 주세요.</p>
        </div>
    <?php endif; ?>
</div>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
