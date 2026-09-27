<?php
$page_title = "역경매 등록/수정";
$active_page = "auction";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

$no = intval((isset($_GET['no']) ? $_GET['no'] : 0));
$is_edit = ($no > 0);
$auction = [];

if ($is_edit) {
    $auction = sql_one_one('auctions', '*', "and no=" . $no);
    if (!$auction) {
        echo "<script>alert('존재하지 않는 역경매 항목입니다.'); location.href='index.php';</script>";
        exit;
    }
}

$space_types = ['아파트', '상가/빌딩', '오피스텔', '단독주택', '기타'];
$allowed_states = ['입찰대기', '입찰중', '매칭완료', '취소'];

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div class="adm-section" style="max-width:100%;">
    <div class="adm-section-head">
        <h3><i class="fa-solid fa-gavel"></i> <?php echo $is_edit ? '역경매 신청내역 수정' : '역경매 신규 등록'; ?></h3>
        <a href="index.php" class="adm-btn adm-btn-outline" style="padding:5px 12px; font-size:0.82rem;"><i class="fa-solid fa-list"></i> 목록으로</a>
    </div>
    
    <div style="padding:24px;">
        <form method="post" action="proc.php">
            <input type="hidden" name="mode" value="<?php echo $is_edit ? 'update' : 'insert'; ?>">
            <?php if ($is_edit): ?>
                <input type="hidden" name="no" value="<?php echo $auction['no']; ?>">
            <?php endif; ?>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="adm-form-row">
                    <label>신청자 성함 <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars((isset($auction['name']) ? $auction['name'] : '')); ?>" required placeholder="성함 입력">
                </div>
                <div class="adm-form-row">
                    <label>연락처 <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="hphone" value="<?php echo htmlspecialchars((isset($auction['hphone']) ? $auction['hphone'] : '')); ?>" required placeholder="010-0000-0000">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                <div class="adm-form-row">
                    <label>공간 구분 <span style="color:#EF4444;">*</span></label>
                    <select name="space_type" required>
                        <?php foreach ($space_types as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo ((isset($auction['space_type']) ? $auction['space_type'] : '')) === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="adm-form-row">
                    <label>시공 평수 (평)</label>
                    <input type="number" name="py" value="<?php echo (int)((isset($auction['py']) ? $auction['py'] : 30)); ?>" min="1" placeholder="예: 32">
                </div>
                <div class="adm-form-row">
                    <label>진행 상태 <span style="color:#EF4444;">*</span></label>
                    <select name="state" required>
                        <?php foreach ($allowed_states as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo ((isset($auction['state']) ? $auction['state'] : '입찰대기')) === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="adm-form-row">
                    <label>희망 예산 (원)</label>
                    <input type="number" name="desired_price" value="<?php echo (int)((isset($auction['desired_price']) ? $auction['desired_price'] : 1000000)); ?>" step="10000" placeholder="예: 1200000">
                </div>
                <div class="adm-form-row">
                    <label>입찰 수 (건)</label>
                    <input type="number" name="bid_count" value="<?php echo (int)((isset($auction['bid_count']) ? $auction['bid_count'] : 0)); ?>" min="0" placeholder="예: 0">
                </div>
            </div>

            <div class="adm-form-row">
                <label>시공 주소 / 지역 <span style="color:#EF4444;">*</span></label>
                <input type="text" name="addr" value="<?php echo htmlspecialchars((isset($auction['addr']) ? $auction['addr'] : '')); ?>" required placeholder="예: 서울시 강남구 역삼동 00아파트 101동">
            </div>

            <div class="adm-form-row">
                <label>시공 요청사항 / 특이사항</label>
                <textarea name="memo" rows="4" placeholder="고객 요청사항이나 입찰 관련 메모를 입력하세요."><?php echo htmlspecialchars((isset($auction['memo']) ? $auction['memo'] : '')); ?></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                <a href="index.php" class="adm-btn adm-btn-outline">취소</a>
                <button type="submit" class="adm-btn" style="background:#0077B6;"><i class="fa-solid fa-check"></i> <?php echo $is_edit ? '수정 완료' : '등록 저장'; ?></button>
            </div>
        </form>
    </div>
</div>

<?php if ($is_edit):
    $auction_bids = sql_one('auction_bids', '*', "and auction_no=" . $no . " order by bid_price asc, no asc");
    $auction_comments = sql_one('auction_comments', '*', "and auction_no=" . $no . " order by no desc");
    $adm_name = isset($_SESSION['s_adm_name']) ? $_SESSION['s_adm_name'] : '관리자';
    $bidder_types = ['대리점(구독사업자)', '기업(사업자)', '프리랜서(개인)', '수요고객'];
    $bid_states = ['입찰중', '낙찰', '취소'];
?>

<!-- 1. 입찰 내역 관리 (Bids History CRUD) -->
<div class="adm-section" id="bids" style="max-width:100%; margin-top:24px;">
    <div class="adm-section-head">
        <h3><i class="fa-solid fa-gavel" style="color:#D97706;"></i> 입찰 제안 내역 관리 (<?php echo count($auction_bids); ?>건)</h3>
    </div>
    
    <div style="padding:24px;">
        <!-- 신규 입찰제안 등록 폼 -->
        <div style="background:#FFFBEB; border:1px solid #FCD34D; border-radius:10px; padding:18px; margin-bottom:24px;">
            <h4 style="font-size:0.95rem; font-weight:800; color:#B45309; margin-bottom:12px;">
                <i class="fa-solid fa-plus"></i> 신규 입찰 제안 수동 등록
            </h4>
            <form method="post" action="proc.php">
                <input type="hidden" name="mode" value="bid_insert">
                <input type="hidden" name="auction_no" value="<?php echo $no; ?>">

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div class="adm-form-row" style="margin-bottom:0;">
                        <label>입찰자 / 업체명 <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="bidder_name" required placeholder="예: 틴팅프로 강남점">
                    </div>
                    <div class="adm-form-row" style="margin-bottom:0;">
                        <label>회원 구분 <span style="color:#EF4444;">*</span></label>
                        <select name="bidder_type" required>
                            <?php foreach ($bidder_types as $bt): ?>
                                <option value="<?php echo $bt; ?>"><?php echo $bt; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="adm-form-row" style="margin-bottom:0;">
                        <label>연락처</label>
                        <input type="text" name="bidder_hphone" placeholder="010-0000-0000">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div class="adm-form-row" style="margin-bottom:0;">
                        <label>입찰 제안가 (원) <span style="color:#EF4444;">*</span></label>
                        <input type="number" name="bid_price" required min="0" step="10000" placeholder="예: 950000">
                    </div>
                    <div class="adm-form-row" style="margin-bottom:0;">
                        <label>예상 시공기간</label>
                        <input type="text" name="work_duration" value="1일 소요" placeholder="예: 1일 소요">
                    </div>
                    <div class="adm-form-row" style="margin-bottom:0;">
                        <label>입찰 상태</label>
                        <select name="state">
                            <?php foreach ($bid_states as $bs): ?>
                                <option value="<?php echo $bs; ?>"><?php echo $bs; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="adm-form-row" style="margin-bottom:14px;">
                    <label>입찰 제안 메모 / 특이사항</label>
                    <input type="text" name="memo" placeholder="예: VULUX 99 최고급 필름 시공 및 10년 보증서 발급">
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button type="submit" class="adm-btn" style="background:#D97706; padding:8px 20px; font-size:0.88rem;"><i class="fa-solid fa-check"></i> 입찰 제안 등록</button>
                </div>
            </form>
        </div>

        <!-- 입찰 목록 테이블 -->
        <div style="overflow-x:auto;">
            <table class="adm-table" style="font-size:0.83rem;">
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center;">순위</th>
                        <th>입찰자/업체명</th>
                        <th>구분</th>
                        <th>입찰가 (최저가순)</th>
                        <th>시공기간</th>
                        <th>제안메모</th>
                        <th style="text-align:center;">상태</th>
                        <th style="text-align:center;">낙찰 선택</th>
                        <th>입찰일시</th>
                        <th style="width:110px; text-align:center;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($auction_bids)): ?>
                        <tr class="empty-row"><td colspan="10">등록된 입찰 제안이 없습니다.</td></tr>
                    <?php else: foreach ($auction_bids as $idx => $b): ?>
                        <tr style="<?php echo $b['state'] === '낙찰' ? 'background:#F0FDF4;' : ($idx === 0 ? 'background:#FFFBEB;' : ''); ?>">
                            <td style="text-align:center; font-weight:700;">
                                <?php if ($b['state'] === '낙찰'): ?>
                                    <span style="color:#059669;"><i class="fa-solid fa-trophy"></i> 낙찰</span>
                                <?php elseif ($idx === 0): ?>
                                    <span class="adm-badge" style="background:#ECFDF5; color:#059669;">1위</span>
                                <?php else: ?>
                                    <span style="color:var(--adm-muted);"><?php echo $idx + 1; ?>위</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($b['bidder_name']); ?></strong></td>
                            <td><span class="adm-badge" style="background:#FEF3C7; color:#D97706; font-size:0.7rem;"><?php echo htmlspecialchars($b['bidder_type']); ?></span></td>
                            <td><strong style="color:<?php echo $idx === 0 ? '#059669' : 'var(--adm-primary)'; ?>; font-size:0.92rem;"><?php echo number_format($b['bid_price']); ?>원</strong></td>
                            <td><?php echo htmlspecialchars($b['work_duration'] ? $b['work_duration'] : '-'); ?></td>
                            <td style="max-width:180px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?php echo htmlspecialchars($b['memo']); ?>"><?php echo htmlspecialchars($b['memo'] ? $b['memo'] : '-'); ?></td>
                            <td style="text-align:center;">
                                <span class="adm-badge" style="background:<?php echo $b['state'] === '낙찰' ? '#ECFDF5' : ($b['state'] === '취소' ? '#FEE2E2' : '#E0F7FA'); ?>; color:<?php echo $b['state'] === '낙찰' ? '#059669' : ($b['state'] === '취소' ? '#B91C1C' : '#0077B6'); ?>;"><?php echo htmlspecialchars($b['state']); ?></span>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($b['state'] === '낙찰'): ?>
                                    <span class="adm-badge" style="background:#059669; color:#fff; font-weight:800; padding:4px 9px;"><i class="fa-solid fa-crown"></i> 최종 낙찰자</span>
                                <?php else: ?>
                                    <a href="proc.php?mode=select_winner&no=<?php echo $b['no']; ?>&auction_no=<?php echo $no; ?>" onclick="return confirm('<?php echo htmlspecialchars(addslashes($b['bidder_name'])); ?>님 (입찰가: <?php echo number_format($b['bid_price']); ?>원)을 최종 낙찰자로 선정하시겠습니까?\n역경매 상태가 \'매칭완료\'로 변경됩니다.');" class="adm-btn" style="padding:3px 9px; font-size:0.78rem; background:#059669; color:#fff;">
                                        <i class="fa-solid fa-trophy"></i> 낙찰 선택
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--adm-muted); font-size:0.78rem;"><?php echo htmlspecialchars($b['reg_date']); ?></td>
                            <td style="text-align:center; white-space:nowrap;">
                                <button type="button" onclick="toggleEditBid(<?php echo $b['no']; ?>)" class="adm-btn adm-btn-outline" style="padding:2px 7px; font-size:0.75rem;">수정</button>
                                <a href="proc.php?mode=bid_delete&no=<?php echo $b['no']; ?>&auction_no=<?php echo $no; ?>" onclick="return confirm('이 입찰 제안을 삭제하시겠습니까?');" class="adm-btn" style="padding:2px 7px; font-size:0.75rem; background:#EF4444;">삭제</a>
                            </td>
                        </tr>
                        
                        <!-- Inline Edit Form for Bid -->
                        <tr id="bid-edit-row-<?php echo $b['no']; ?>" style="display:none; background:#F8FAFC;">
                            <td colspan="10" style="padding:16px;">
                                <form method="post" action="proc.php">
                                    <input type="hidden" name="mode" value="bid_update">
                                    <input type="hidden" name="no" value="<?php echo $b['no']; ?>">
                                    <input type="hidden" name="auction_no" value="<?php echo $no; ?>">

                                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
                                        <div>
                                            <label style="font-size:0.78rem; font-weight:700;">입찰자명</label>
                                            <input type="text" name="bidder_name" value="<?php echo htmlspecialchars($b['bidder_name']); ?>" required style="width:100%; padding:5px; font-size:0.82rem;">
                                        </div>
                                        <div>
                                            <label style="font-size:0.78rem; font-weight:700;">입찰가(원)</label>
                                            <input type="number" name="bid_price" value="<?php echo (int)$b['bid_price']; ?>" required style="width:100%; padding:5px; font-size:0.82rem;">
                                        </div>
                                        <div>
                                            <label style="font-size:0.78rem; font-weight:700;">시공기간</label>
                                            <input type="text" name="work_duration" value="<?php echo htmlspecialchars($b['work_duration']); ?>" style="width:100%; padding:5px; font-size:0.82rem;">
                                        </div>
                                        <div>
                                            <label style="font-size:0.78rem; font-weight:700;">상태</label>
                                            <select name="state" style="width:100%; padding:5px; font-size:0.82rem;">
                                                <?php foreach ($bid_states as $bs): ?>
                                                    <option value="<?php echo $bs; ?>" <?php echo $b['state'] === $bs ? 'selected' : ''; ?>><?php echo $bs; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div style="margin-bottom:10px;">
                                        <label style="font-size:0.78rem; font-weight:700;">제안 메모</label>
                                        <input type="text" name="memo" value="<?php echo htmlspecialchars($b['memo']); ?>" style="width:100%; padding:5px; font-size:0.82rem;">
                                    </div>
                                    <div style="display:flex; justify-content:flex-end; gap:6px;">
                                        <button type="button" onclick="toggleEditBid(<?php echo $b['no']; ?>)" class="adm-btn adm-btn-outline" style="padding:4px 10px; font-size:0.78rem;">취소</button>
                                        <button type="submit" class="adm-btn" style="padding:4px 12px; font-size:0.78rem; background:#D97706;">수정 저장</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 2. 댓글 관리 (Auction Comments CRUD) -->
<div class="adm-section" id="comments" style="max-width:100%; margin-top:24px;">
    <div class="adm-section-head">
        <h3><i class="fa-solid fa-comments" style="color:var(--adm-primary);"></i> 역경매 댓글 / 특이사항 메모 (<?php echo count($auction_comments); ?>개)</h3>
    </div>

    <div style="padding:24px;">
        <!-- 신규 댓글 등록 폼 -->
        <form method="post" action="proc.php" style="margin-bottom:24px; background:#F8FAFC; padding:16px; border-radius:10px; border:1px solid var(--adm-border);">
            <input type="hidden" name="mode" value="comment_insert">
            <input type="hidden" name="auction_no" value="<?php echo $no; ?>">
            <div style="display:flex; gap:10px; margin-bottom:8px;">
                <input type="text" name="writer" value="<?php echo htmlspecialchars($adm_name); ?>" required placeholder="작성자 이름" style="width:140px; padding:7px 10px; font-size:0.85rem;">
                <span style="font-size:0.8rem; color:var(--adm-muted); align-self:center;">로그인 관리자명이 기본 세팅됩니다.</span>
            </div>
            <div style="display:flex; gap:10px;">
                <textarea name="content" required rows="2" placeholder="역경매 처리 메모나 특이사항 댓글을 입력하세요." style="flex:1; padding:8px 10px; font-size:0.88rem; outline:none; border:1px solid var(--adm-border); border-radius:6px;"></textarea>
                <button type="submit" class="adm-btn" style="padding:8px 18px; font-size:0.85rem; height:auto; background:var(--adm-primary);"><i class="fa-solid fa-paper-plane"></i> 댓글 등록</button>
            </div>
        </form>

        <!-- 댓글 목록 -->
        <div style="display:flex; flex-direction:column; gap:12px;">
            <?php if (empty($auction_comments)): ?>
                <div style="text-align:center; padding:24px; color:var(--adm-muted); font-size:0.88rem;">등록된 댓글/메모가 없습니다.</div>
            <?php else: foreach ($auction_comments as $cmt): ?>
                <div id="cmt-box-<?php echo $cmt['no']; ?>" style="background:#fff; border:1px solid var(--adm-border); border-radius:8px; padding:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <strong style="font-size:0.88rem; color:var(--adm-text);"><i class="fa-solid fa-user-gear" style="color:var(--adm-primary);"></i> <?php echo htmlspecialchars($cmt['writer']); ?></strong>
                            <span style="font-size:0.75rem; color:var(--adm-muted);"><?php echo htmlspecialchars($cmt['reg_date']); ?></span>
                        </div>
                        <div style="display:flex; gap:6px;">
                            <button type="button" onclick="toggleEditCmt(<?php echo $cmt['no']; ?>)" class="adm-btn adm-btn-outline" style="padding:2px 8px; font-size:0.75rem;"><i class="fa-solid fa-pen"></i> 수정</button>
                            <a href="proc.php?mode=comment_delete&no=<?php echo $cmt['no']; ?>&auction_no=<?php echo $no; ?>" onclick="return confirm('이 댓글을 삭제하시겠습니까?');" class="adm-btn" style="padding:2px 8px; font-size:0.75rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 삭제</a>
                        </div>
                    </div>
                    
                    <div id="cmt-view-<?php echo $cmt['no']; ?>" style="font-size:0.9rem; color:#334155; white-space:pre-wrap; line-height:1.5;"><?php echo htmlspecialchars($cmt['content']); ?></div>

                    <form id="cmt-edit-<?php echo $cmt['no']; ?>" method="post" action="proc.php" style="display:none; margin-top:8px;">
                        <input type="hidden" name="mode" value="comment_update">
                        <input type="hidden" name="no" value="<?php echo $cmt['no']; ?>">
                        <input type="hidden" name="auction_no" value="<?php echo $no; ?>">
                        <textarea name="content" required rows="2" style="width:100%; padding:8px; font-size:0.88rem; margin-bottom:6px;"><?php echo htmlspecialchars($cmt['content']); ?></textarea>
                        <div style="display:flex; justify-content:flex-end; gap:6px;">
                            <button type="button" onclick="toggleEditCmt(<?php echo $cmt['no']; ?>)" class="adm-btn adm-btn-outline" style="padding:4px 10px; font-size:0.78rem;">취소</button>
                            <button type="submit" class="adm-btn" style="padding:4px 12px; font-size:0.78rem; background:var(--adm-primary);">수정 저장</button>
                        </div>
                    </form>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<script>
function toggleEditBid(no) {
    const row = document.getElementById('bid-edit-row-' + no);
    row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
}

function toggleEditCmt(no) {
    const v = document.getElementById('cmt-view-' + no);
    const e = document.getElementById('cmt-edit-' + no);
    if (e.style.display === 'none') {
        e.style.display = 'block';
        v.style.display = 'none';
    } else {
        e.style.display = 'none';
        v.style.display = 'block';
    }
}
</script>
<?php endif; ?>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
