<style>
    .auc-form-card { background:#fff; border:1px solid #E2E8F0; border-radius:var(--radius-lg); padding:32px; box-shadow:var(--shadow-md); margin-bottom:48px; }
    .auc-filter { display:flex; gap:8px; justify-content:center; flex-wrap:wrap; margin-bottom:28px; }
    .auc-filter a { font-size:0.88rem; font-weight:700; padding:8px 18px; border-radius:var(--radius-full); background:#F1F5F9; color:#334155; transition:all 0.2s ease; border:1px solid #E2E8F0; text-decoration:none; }
    .auc-filter a.active { background:var(--primary); color:#fff; border-color:var(--primary); box-shadow:0 4px 12px rgba(0,180,216,0.3); }
    .auc-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:24px; }
    .auc-card { background:#fff; border:1px solid #E2E8F0; border-radius:var(--radius-md); overflow:hidden; box-shadow:var(--shadow-sm); transition:transform 0.2s, box-shadow 0.2s; display:flex; flex-direction:column; }
    .auc-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); border-color:#BAE6FD; }
    .auc-card-head { padding:20px; background:#F8FAFC; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center; }
    .auc-card-body { padding:20px; flex:1; display:flex; flex-direction:column; justify-content:space-between; }
    .st-badge { font-size:0.75rem; font-weight:800; padding:4px 10px; border-radius:4px; }
    .st-waiting { background:#E0F2FE; color:#0284C7; }
    .st-bidding { background:#FEF3C7; color:#D97706; }
    .st-matched { background:#DCFCE7; color:#15803D; }

    .cat-tab-btn {
        display:inline-flex; align-items:center; gap:6px; padding:9px 20px; border-radius:30px;
        font-size:0.9rem; font-weight:700; border:1.5px solid var(--border-color); background:#fff; color:var(--text-color);
        transition:all 0.2s ease; cursor:pointer; text-decoration:none;
    }
    .cat-tab-btn:hover { border-color:var(--primary); color:var(--primary-dark); }
    .cat-tab-btn.active { background:var(--primary-dark); color:#fff; border-color:var(--primary-dark); box-shadow:0 4px 12px rgba(0,119,182,0.25); }

    @media (max-width:900px) { .auc-grid { grid-template-columns:repeat(2, 1fr); } }
    @media (max-width:600px) { 
        .auc-grid { grid-template-columns:1fr; }
        .auc-form-card { padding:20px 16px !important; margin-bottom:32px !important; }
        .auc-filter {
            display:flex !important;
            overflow-x:auto !important;
            -webkit-overflow-scrolling:touch;
            justify-content:flex-start !important;
            gap:8px !important;
            padding-bottom:8px !important;
            scrollbar-width:none;
        }
        .auc-filter::-webkit-scrollbar { display:none; }
        .auc-filter a { flex-shrink:0; white-space:nowrap; padding:8px 14px !important; font-size:0.82rem !important; }
        .auc-card-head { padding:14px 16px !important; }
        .auc-card-body { padding:16px 14px !important; }
    }
</style>

<?php
$cat_map = [
    'apt'       => '아파트',
    'building'  => '상가/빌딩',
    'auto'      => '자동차',
    'officetel' => '오피스텔',
    'house'     => '단독주택',
];
$cat_label = ['' => '전체'] + $cat_map;
$cat = isset($_GET['cat']) && isset($cat_map[$_GET['cat']]) ? $_GET['cat'] : '';
$detail_no = isset($_GET['no']) ? (int)$_GET['no'] : 0;
?>

<!-- ------------------------------------------------------------- -->
<!-- 1. 역경매 상세 및 입찰 참여 뷰 (no 파라미터 지정시) -->
<!-- ------------------------------------------------------------- -->
<?php if ($detail_no > 0): 
    $auc_item = sql_one_one('auctions', '*', "and no=" . $detail_no);
    if ($auc_item):
        $existing_bids = sql_one('auction_bids', '*', "and auction_no=" . $detail_no . " order by no desc");
?>
    <div style="margin-bottom:24px;">
        <a href="index.php?cat=<?php echo urlencode($cat); ?>" class="btn btn-outline btn-sm" style="font-weight:700;"><i class="fa-solid fa-arrow-left"></i> 역경매 리스트 목록으로 돌아가기</a>
    </div>

    <div style="background:#fff; border:1px solid #E2E8F0; border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-md); margin-bottom:40px;">
        <div style="background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color:white; padding:32px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <span style="background:#0077B6; color:#fff; font-size:0.8rem; font-weight:800; padding:4px 10px; border-radius:4px;"><?php echo htmlspecialchars($auc_item['space_type']); ?></span>
                <?php
                    $st_cls = 'st-waiting';
                    if ($auc_item['state'] === '입찰중') $st_cls = 'st-bidding';
                    elseif ($auc_item['state'] === '매칭완료') $st_cls = 'st-matched';
                ?>
                <span class="st-badge <?php echo $st_cls; ?>"><?php echo htmlspecialchars($auc_item['state']); ?></span>
            </div>
<?php
    $reg_time = strtotime($auc_item['reg_date']);
    $now = time();

    if ((int)$auc_item['bid_count'] === 0) {
        $elapsed = max(0, $now - $reg_time);
        $days_added = (int)floor($elapsed / 86400) + 1;
        $deadline_time = $reg_time + ($days_added * 86400);
    } else {
        $last_bid = sql_one_one('auction_bids', 'reg_date', "and auction_no=" . (int)$auc_item['no'] . " order by no desc limit 1");
        if ($last_bid && isset($last_bid['reg_date'])) {
            $last_bid_time = strtotime($last_bid['reg_date']);
            $deadline_time = max($reg_time + 86400, $last_bid_time + 86400);
        } else {
            $deadline_time = $reg_time + 86400;
        }
    }

    $time_left_sec = max(0, $deadline_time - $now);
    $hours_left = (int)floor($time_left_sec / 3600);
    $mins_left = (int)floor(($time_left_sec % 3600) / 60);
    $deadline_str = date('Y-m-d H:i', $deadline_time);
?>
            <h1 style="font-size:2rem; font-weight:900; margin-bottom:8px;"><?php echo htmlspecialchars($auc_item['addr']); ?> <?php echo (int)$auc_item['py']; ?>평 썬팅 시공 역경매</h1>
            <p style="opacity:0.85; font-size:0.95rem;"><i class="fa-regular fa-clock"></i> 신청일: <?php echo htmlspecialchars($auc_item['reg_date']); ?> | 현재 입찰 참여: <strong><?php echo (int)$auc_item['bid_count']; ?>건</strong></p>
        </div>

        <div style="background:#FFFBEB; border-bottom:1px solid #FDE68A; padding:12px 32px; font-size:0.88rem; color:#92400E; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <span><i class="fa-solid fa-hourglass-half" style="color:#D97706;"></i> <strong>입찰 마감 기한:</strong> <?php echo $deadline_str; ?> (남은 시간: <?php echo $hours_left; ?>시간 <?php echo $mins_left; ?>분)</span>
            <span style="font-size:0.8rem; color:#B45309;"><i class="fa-solid fa-rotate"></i> 입찰 마감 기한은 1일 기본 적용되며 입찰자가 없을 경우 1일 단위로 자동 연장됩니다.</span>
        </div>

        <div class="mobile-grid-1col" style="padding:32px; display:grid; grid-template-columns:1fr 1fr; gap:32px;">
            <div>
                <h3 style="font-size:1.2rem; font-weight:800; color:var(--secondary); margin-bottom:16px; border-bottom:2px solid #F1F5F9; padding-bottom:8px;">
                    <i class="fa-solid fa-clipboard-list" style="color:var(--primary);"></i> 시공 신청 요약
                </h3>
                <ul style="list-style:none; padding:0; margin:0 0 24px 0; display:flex; flex-direction:column; gap:12px; font-size:0.95rem; color:#334155;">
                    <li><strong>신청자 성함:</strong> <?php echo htmlspecialchars(mb_substr($auc_item['name'], 0, 1) . '*' . mb_substr($auc_item['name'], 2)); ?></li>
                    <li><strong>공간 구분:</strong> <?php echo htmlspecialchars($auc_item['space_type']); ?> (<?php echo (int)$auc_item['py']; ?>평)</li>
                    <li><strong>시공 위치:</strong> <?php echo htmlspecialchars($auc_item['addr']); ?></li>
                    <li><strong>희망 예상 예산:</strong> <span style="font-size:1.2rem; font-weight:900; color:var(--primary-dark);"><?php echo number_format($auc_item['desired_price']); ?>원</span></li>
                    <li><strong>고객 요청사항:</strong> <?php echo htmlspecialchars($auc_item['memo'] ? $auc_item['memo'] : '요청사항 없음'); ?></li>
                </ul>

                <h3 style="font-size:1.2rem; font-weight:800; color:var(--secondary); margin-bottom:16px; border-bottom:2px solid #F1F5F9; padding-bottom:8px;">
                    <i class="fa-solid fa-handshake-angle" style="color:var(--primary);"></i> 검증 마스터 입찰 제안 내역 (<?php echo count($existing_bids); ?>건)
                </h3>
                <?php if (empty($existing_bids)): ?>
                    <div style="padding:20px; text-align:center; background:#F8FAFC; border-radius:8px; border:1px solid #E2E8F0; color:var(--text-muted); font-size:0.9rem;">
                        아직 참여한 입찰 제안이 없습니다. 첫 입찰을 기다립니다!
                    </div>
                <?php else: foreach ($existing_bids as $b):
                    $is_my_bid = false;
                    if (!empty($_SESSION['s_mem_no']) && (int)$b['mem_no'] === (int)$_SESSION['s_mem_no']) {
                        $is_my_bid = true;
                    } elseif (!empty($_SESSION['s_mem_hphone']) && $b['bidder_hphone'] === $_SESSION['s_mem_hphone']) {
                        $is_my_bid = true;
                    }
                ?>
                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:16px; margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <div>
                                <span style="background:#0077B6; color:#fff; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:4px; margin-right:6px;"><?php echo htmlspecialchars($b['bidder_type']); ?></span>
                                <strong style="font-size:0.95rem; color:#0F172A;"><?php echo htmlspecialchars($b['bidder_name']); ?></strong>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-size:0.82rem; font-weight:700; color:#64748B;"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($b['reg_date']); ?></span>
                                <?php if ($is_my_bid && $now <= $deadline_time): ?>
                                    <button type="button" class="btn btn-outline btn-sm" style="padding:2px 8px; font-size:0.75rem; color:#EF4444; border-color:#FCA5A5;" onclick="cancelBid(<?php echo (int)$b['no']; ?>, <?php echo (int)$auc_item['no']; ?>)">
                                        <i class="fa-solid fa-xmark"></i> 취소
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p style="font-size:0.85rem; color:#475569; margin:6px 0 0 0;">
                            소요시간: <strong><?php echo htmlspecialchars($b['work_duration']); ?></strong> | <?php echo htmlspecialchars($b['memo']); ?>
                        </p>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- 프리랜서 / 기업대리점 역경매 입찰 참여 폼 -->
            <div>
                <div style="background:#F0F9FF; border:1.5px solid #BAE6FD; border-radius:var(--radius-md); padding:24px;">
                    <h3 style="font-size:1.25rem; font-weight:900; color:var(--secondary); margin-bottom:6px;">
                        <i class="fa-solid fa-gavel" style="color:var(--primary);"></i> 마스터 역경매 입찰 참여
                    </h3>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:14px;">
                        시공 마스터 전용 입찰 참여 폼입니다. (1회 입찰 가능, 마감 전까지 취소 가능)
                    </p>

                    <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:8px; padding:10px 12px; margin-bottom:16px; font-size:0.8rem; color:#92400E; line-height:1.5;">
                        <strong><i class="fa-solid fa-triangle-exclamation"></i> 입찰 규칙:</strong><br>
                        • <strong>1차 진행 (24시간 이내)</strong>: <u>대리점(구독사업자)</u>만 입찰 진행 가능<br>
                        • <strong>2차 진행 (1일 경과 유찰 시)</strong>: <u>수요고객 제외 모든 회원</u> 1회 입찰 가능<br>
                        • <strong>취소 기능</strong>: 입찰 마감 전까지 취소 버튼으로 취소 가능
                    </div>

<?php
$my_bidder_type = '프리랜서(개인)';
$is_type_locked = false;
$is_logged_in   = !empty($_SESSION['s_mem_id']);
$is_customer    = false;

if ($is_logged_in) {
    $is_type_locked = true;
    if (!empty($_SESSION['s_mem_no'])) {
        $user_type = check_and_update_partner_status($_SESSION['s_mem_no']);
    } else {
        $user_type = isset($_SESSION['s_mem_type']) ? $_SESSION['s_mem_type'] : '';
    }

    if ($user_type === 'customer' || $user_type === '수요고객') {
        $is_customer = true;
        $my_bidder_type = '수요고객';
    } elseif ($user_type === 'corporate' || $user_type === '기업') {
        $my_bidder_type = '기업(사업자)';
    } elseif ($user_type === 'partner' || $user_type === '대리점') {
        $my_bidder_type = '대리점(구독사업자)';
    } else {
        $my_bidder_type = '프리랜서(개인)';
    }
}
?>
                    <?php if (!$is_logged_in): ?>
                        <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:0.85rem; color:#1E40AF; line-height:1.5;">
                            <strong><i class="fa-solid fa-lock"></i> 로그인 필요:</strong> 입찰 제안 제출은 기본적으로 로그인 후 이용 가능합니다. 
                            <a href="../member/login.php" style="color:#1D4ED8; font-weight:800; text-decoration:underline; margin-left:6px;"><i class="fa-solid fa-right-to-bracket"></i> 로그인하러 가기 &rarr;</a>
                        </div>
                    <?php elseif ($is_customer): ?>
                        <div style="background:#FEF2F2; border:1px solid #FCA5A5; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:0.85rem; color:#991B1B; line-height:1.5;">
                            <strong><i class="fa-solid fa-triangle-exclamation"></i> 입찰 제한 안내:</strong> 수요고객 계정은 입찰 참여 대상이 아닙니다. (시공업체 회원: 프리랜서, 기업, 대리점 전용)
                        </div>
                    <?php endif; ?>

                    <div id="bidAlert" style="display:none; padding:12px; border-radius:6px; font-size:0.88rem; margin-bottom:14px;"></div>

                    <div style="margin-bottom:14px;">
                        <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">
                            입찰자 회원 구분 <span style="color:#EF4444;">*</span>
                            <?php if ($is_type_locked): ?>
                                <span style="font-size:0.75rem; color:#0284C7; font-weight:600; margin-left:6px;">(회원 계정 구분으로 자동 고정)</span>
                            <?php endif; ?>
                        </label>
                        <div class="mobile-grid-1col" style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                            <?php
                            $type_options = array(
                                '수요고객'           => '수요고객',
                                '프리랜서(개인)'     => '프리랜서(개인)',
                                '기업(사업자)'       => '기업(사업자)',
                                '대리점(구독사업자)' => '대리점(구독사업자)'
                            );
                            foreach ($type_options as $t_val => $t_name):
                                $is_selected = ($my_bidder_type === $t_val);
                            ?>
                                <label style="display:flex; align-items:center; gap:6px; background:<?php echo ($is_type_locked && !$is_selected) ? '#F1F5F9' : '#fff'; ?>; border:1px solid #CBD5E1; padding:8px 10px; border-radius:6px; cursor:<?php echo $is_type_locked ? 'not-allowed' : 'pointer'; ?>; font-size:0.85rem;">
                                    <input type="radio" name="bidder_type" value="<?php echo htmlspecialchars($t_val); ?>" <?php echo $is_selected ? 'checked' : ''; ?> <?php echo $is_type_locked ? 'disabled' : ''; ?> style="accent-color:#0077B6;"> <?php echo htmlspecialchars($t_name); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">성함 / 업체명 <span style="color:#EF4444;">*</span></label>
                        <input type="text" id="bidderName" placeholder="예: 홍길동 틴팅프로 / VULUX 강남대리점" style="width:100%; padding:10px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">연락처 <span style="color:#EF4444;">*</span></label>
                        <input type="tel" id="bidderHphone" placeholder="010-0000-0000" style="width:100%; padding:10px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">제안 입찰 금액 (공임 포함) <span style="color:#EF4444;">*</span></label>
                        <input type="number" id="bidPrice" placeholder="예: 350000 (숫자만 입력)" style="width:100%; padding:10px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">예상 시공 소요 시간</label>
                        <input type="text" id="bidWorkDuration" value="1일 소요 (약 3~4시간)" style="width:100%; padding:10px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">제안 메시지 & 필름 사양</label>
                        <textarea id="bidMemo" rows="3" placeholder="예: VULUX 나노 세라믹 70 정품 필름 사용 및 10년 보증서 발급 포함" style="width:100%; padding:10px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem; resize:vertical;"></textarea>
                    </div>

                    <button type="button" class="btn btn-primary" style="width:100%; padding:12px; font-weight:800;" onclick="submitBid(<?php echo (int)$auc_item['no']; ?>)">
                        <i class="fa-solid fa-paper-plane"></i> 입찰 제안 제출하기
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php 
    endif;
endif; 
?>

<!-- ------------------------------------------------------------- -->
<!-- 2. 역경매 위탁 이용 흐름 카드 -->
<!-- ------------------------------------------------------------- -->
<h2 style="font-size:2rem; font-weight:900; text-align:center; margin-bottom:36px; color:var(--secondary);" id="flow">역경매 마켓 이용 흐름</h2>

<div class="mobile-grid-1col" style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:60px;">
    <div style="background:#F0F9FF; border:1px solid #BAE6FD; padding:28px 20px; border-radius:var(--radius-md); text-align:center;">
        <div style="width:42px; height:42px; background:var(--accent); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:1.1rem; margin:0 auto 16px auto; box-shadow:0 4px 10px rgba(255,107,53,0.3);">1</div>
        <h4 style="font-size:1.1rem; font-weight:800; margin-bottom:8px; color:#0F172A;">
            <i class="fa-solid fa-pen-to-square" style="color:var(--primary-dark); margin-right:4px;"></i> 시공 요청 등록
        </h4>
        <p style="font-size:0.88rem; color:var(--text-muted); margin:0;">주소, 평수, 희망 일정을 1분만에 등록합니다.</p>
    </div>

    <div style="background:#F0F9FF; border:1px solid #BAE6FD; padding:28px 20px; border-radius:var(--radius-md); text-align:center;">
        <div style="width:42px; height:42px; background:var(--accent); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:1.1rem; margin:0 auto 16px auto; box-shadow:0 4px 10px rgba(255,107,53,0.3);">2</div>
        <h4 style="font-size:1.1rem; font-weight:800; margin-bottom:8px; color:#0F172A;">
            <i class="fa-solid fa-gavel" style="color:#D97706; margin-right:4px;"></i> 틴팅프로 역경매 입찰
        </h4>
        <p style="font-size:0.88rem; color:var(--text-muted); margin:0;">전국 검증 틴팅프로들이 공임 견적을 제안합니다.</p>
    </div>

    <div style="background:#F0F9FF; border:1px solid #BAE6FD; padding:28px 20px; border-radius:var(--radius-md); text-align:center;">
        <div style="width:42px; height:42px; background:var(--accent); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:1.1rem; margin:0 auto 16px auto; box-shadow:0 4px 10px rgba(255,107,53,0.3);">3</div>
        <h4 style="font-size:1.1rem; font-weight:800; margin-bottom:8px; color:#0F172A;">
            <i class="fa-solid fa-handshake-angle" style="color:#059669; margin-right:4px;"></i> 틴팅프로 선택 &amp; 계약
        </h4>
        <p style="font-size:0.88rem; color:var(--text-muted); margin:0;">평점과 입찰가를 비교하여 확정합니다.</p>
    </div>

    <div style="background:#F0F9FF; border:1.5px solid #BAE6FD; padding:28px 20px; border-radius:var(--radius-md); text-align:center;">
        <div style="width:42px; height:42px; background:var(--accent); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:1.1rem; margin:0 auto 16px auto; box-shadow:0 4px 10px rgba(255,107,53,0.3);">4</div>
        <h4 style="font-size:1.1rem; font-weight:800; margin-bottom:8px; color:#0F172A;">
            <i class="fa-solid fa-certificate" style="color:#0077B6; margin-right:4px;"></i> 시공 &amp; 10년 보증
        </h4>
        <p style="font-size:0.88rem; color:var(--text-muted); margin:0;">완벽 시공 후 모바일 정품 보증서 발급</p>
    </div>
</div>

<!-- ------------------------------------------------------------- -->
<!-- 3. #apply 역경매 무료 신청 폼 카드 -->
<!-- ------------------------------------------------------------- -->
<div class="auc-form-card" id="apply">
    <h3 style="font-size:1.5rem; font-weight:900; color:var(--secondary); margin-bottom:8px; text-align:center;">
        <i class="fa-solid fa-pen-to-square" style="color:var(--primary);"></i> 실시간 역경매 시공 무료 신청하기
    </h3>
    <p style="text-align:center; color:var(--text-muted); margin-bottom:28px; font-size:0.95rem;">
        1분 만에 시공 요청을 등록하시면 검증된 틴팅 틴팅프로들이 최저가 견적을 제안합니다.
    </p>

    <div id="applyAlert" style="display:none; padding:14px; border-radius:8px; font-size:0.9rem; margin-bottom:20px; text-align:center;"></div>

    <form id="aucApplyForm" style="max-width:800px; margin:0 auto;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
            <div>
                <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">신청자 성함 <span style="color:#EF4444;">*</span></label>
                <input type="text" id="app_name" placeholder="홍길동" style="width:100%; padding:12px; border:1px solid #CBD5E1; border-radius:8px;">
            </div>
            <div>
                <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">연락처 <span style="color:#EF4444;">*</span></label>
                <input type="tel" id="app_hphone" placeholder="010-0000-0000" style="width:100%; padding:12px; border:1px solid #CBD5E1; border-radius:8px;">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:16px;">
            <div>
                <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">공간 구분 <span style="color:#EF4444;">*</span></label>
                <select id="app_space_type" style="width:100%; padding:12px; border:1px solid #CBD5E1; border-radius:8px; background:#fff;">
                    <option value="아파트">아파트</option>
                    <option value="상가/빌딩">상가/빌딩</option>
                    <option value="자동차">자동차</option>
                    <option value="오피스텔">오피스텔</option>
                    <option value="단독주택">단독주택</option>
                </select>
            </div>
            <div>
                <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">시공 평수 (평)</label>
                <input type="number" id="app_py" value="33" style="width:100%; padding:12px; border:1px solid #CBD5E1; border-radius:8px;">
            </div>
            <div>
                <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">희망 예산 (원)</label>
                <input type="number" id="app_desired_price" placeholder="예: 450000" style="width:100%; padding:12px; border:1px solid #CBD5E1; border-radius:8px;">
            </div>
        </div>

        <div style="margin-bottom:16px;">
            <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">시공 위치 주소 <span style="color:#EF4444;">*</span></label>
            <div style="display:flex; gap:8px; margin-bottom:8px;">
                <input type="text" id="app_zipcode" placeholder="우편번호" readonly style="width:140px; padding:12px; border:1px solid #CBD5E1; border-radius:8px; background:#F1F5F9;">
                <button type="button" onclick="execAppPostcode()" class="btn btn-outline" style="padding:0 18px; font-size:0.9rem; font-weight:800; white-space:nowrap;">
                    <i class="fa-solid fa-magnifying-glass"></i> 주소 검색
                </button>
            </div>
            <input type="text" id="app_addr1" placeholder="우편번호 검색 시 자동 입력됩니다." readonly style="width:100%; padding:12px; border:1px solid #CBD5E1; border-radius:8px; background:#F1F5F9; margin-bottom:8px;">
            <input type="text" id="app_addr2" placeholder="상세주소 및 아파트 동·호수 입력" style="width:100%; padding:12px; border:1px solid #CBD5E1; border-radius:8px;">
            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:4px;">로그인 회원은 등록된 주소가 기본으로 채워집니다. 시공지가 다르면 "주소 검색"으로 변경해 주세요.</div>
        </div>

        <div style="margin-bottom:24px;">
            <label style="font-size:0.85rem; font-weight:800; display:block; margin-bottom:6px; color:#0F172A;">시공 요청사항 및 희망 일정</label>
            <textarea id="app_memo" rows="3" placeholder="희망 시공 일자나 추가 요청사항을 작성해 주세요." style="width:100%; padding:12px; border:1px solid #CBD5E1; border-radius:8px; resize:vertical;"></textarea>
        </div>

        <button type="button" class="btn btn-accent" style="width:100%; padding:14px; font-weight:900; font-size:1.05rem;" onclick="submitAuctionApply()">
            <i class="fa-solid fa-paper-plane"></i> 역경매 무료 신청 접수하기
        </button>
    </form>
</div>

<!-- ------------------------------------------------------------- -->
<!-- 4. 동일 조건 역경매 신청 리스트 & 페이징 -->
<!-- ------------------------------------------------------------- -->
<h2 style="font-size:2rem; font-weight:900; text-align:center; margin-bottom:12px; color:var(--secondary);" id="live">진행중인 역경매 현황</h2>
<p style="text-align:center; color:var(--text-muted); margin-bottom:28px;">실시간으로 진행중인 역경매 신청 목록입니다. 원하는 현장에 틴팅프로로서 입찰에 참여해 보세요.</p>

<?php
// ai_category 중분류 (depth=2) 시공관련 하위 카테고리 로딩
$sub_categories = array();
$res_sub = mysqli_query($conn, "SELECT * FROM ai_category WHERE depth=2 AND use_yn=1 ORDER BY sort_order ASC, idx ASC");
if ($res_sub && mysqli_num_rows($res_sub) > 0) {
    while ($r = mysqli_fetch_assoc($res_sub)) {
        $sub_categories[] = $r;
    }
}

// -------------------------------------------------------------
// 역경매 DB 주소 기반 시/도 (1차) > 시/군/구 (2차) 그룹핑 및 표준 맵 병합
// -------------------------------------------------------------
$default_location_map = [
    '서울특별시' => ['강남구','강동구','강북구','강서구','관악구','광진구','구로구','금천구','노원구','도봉구','동대문구','동작구','마포구','서대문구','서초구','성동구','성북구','송파구','양천구','영등포구','용산구','은평구','종로구','중구','중랑구'],
    '경기도'     => ['수원시','성남시','고양시','용인시','부천시','안산시','남양주시','안양시','화성시','평택시','의정부시','파주시','시흥시','김포시','광명시','광주시','군포시','이천시','오산시','하남시','양주시','구리시','안성시','포천시','의왕시','여주시','양평군','동두천시','가평군','과천시','연천군'],
    '인천광역시' => ['중구','동구','미추홀구','연수구','남동구','부평구','계양구','서구','강화군','옹진군'],
    '부산광역시' => ['중구','서구','동구','영도구','부산진구','동래구','남구','북구','해운대구','사하구','금정구','강서구','연제구','수영구','사상구','기장군'],
    '대구광역시' => ['중구','동구','서구','남구','북구','수성구','달서구','달성군','군위군'],
    '광주광역시' => ['동구','서구','남구','북구','광산구'],
    '대전광역시' => ['동구','중구','서구','유성구','대덕구'],
    '울산광역시' => ['중구','남구','동구','북구','울주군'],
    '세종특별자치시' => ['세종시'],
    '강원특별자치도' => ['춘천시','원주시','강릉시','동해시','태백시','속초시','삼척시','홍천군','횡성군','영월군','평창군','정선군','철원군','화천군','양구군','인제군','고성군','양양군'],
    '충청북도'   => ['청주시','충주시','제천시','보은군','옥천군','영동군','증평군','진천군','괴산군','음성군','단양군'],
    '충청남도'   => ['천안시','공주시','보령시','아산시','서산시','논산시','계룡시','당진시','금산군','부여군','서천군','청양군','홍성군','예산군','태안군'],
    '전라북도'   => ['전주시','군산시','익산시','정읍시','남원시','김제시','완주군','진안군','무주군','장수군','임실군','순창군','고창군','부안군'],
    '전라남도'   => ['목포시','여수시','순천시','나주시','광양시','담양군','곡성군','구례군','고흥군','보성군','화순군','장흥군','강진군','해남군','영암군','무안군','함평군','영광군','장성군','완도군','진도군','신안군'],
    '경상북도'   => ['포항시','경주시','김천시','안동시','구미시','영주시','영천시','상주시','문경시','경산시','군위군','의성군','청송군','영양군','영덕군','청도군','고령군','성주군','칠곡군','예천군','봉화군','울진군','울릉군'],
    '경상남도'   => ['창원시','진주시','통영시','사천시','김해시','밀양시','거제시','양산시','의령군','함안군','창녕군','고성군','남해군','하동군','산청군','함양군','거창군','합천군'],
    '제주특별자치도' => ['제주시','서귀포시']
];

// DB auctions 테이블에서 주소 group 파싱
$db_location_map = [];
$res_addr = @mysqli_query($conn, "SELECT addr FROM auctions WHERE addr IS NOT NULL AND addr != ''");
if ($res_addr && mysqli_num_rows($res_addr) > 0) {
    while ($r_addr = mysqli_fetch_assoc($res_addr)) {
        $parts = preg_split('/\s+/', trim($r_addr['addr']));
        if (count($parts) >= 1 && !empty($parts[0])) {
            $s_name = $parts[0];
            $g_name = isset($parts[1]) ? $parts[1] : '';
            if (!isset($db_location_map[$s_name])) {
                $db_location_map[$s_name] = [];
            }
            if ($g_name !== '' && !in_array($g_name, $db_location_map[$s_name])) {
                $db_location_map[$s_name][] = $g_name;
            }
        }
    }
}

// 기본 행정구역과 DB 주소 그룹 병합
$location_map = $default_location_map;
foreach ($db_location_map as $ds => $d_guguns) {
    if (!isset($location_map[$ds])) {
        $location_map[$ds] = $d_guguns;
    } else {
        foreach ($d_guguns as $dg) {
            if (!in_array($dg, $location_map[$ds])) {
                $location_map[$ds][] = $dg;
            }
        }
    }
}

$selected_sido  = isset($_GET['sido']) ? trim($_GET['sido']) : '';
$selected_gugun = isset($_GET['gugun']) ? trim($_GET['gugun']) : '';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 6;

$where = "and (state='입찰대기' or state='입찰중' or state='매칭완료')";
if ($cat !== '') {
    $target_space = isset($cat_map[$cat]) ? $cat_map[$cat] : $cat;
    $where .= " and (space_type='" . mysqli_real_escape_string($conn, $target_space) . "' or space_type LIKE '%" . mysqli_real_escape_string($conn, $target_space) . "%')";
}
if ($selected_sido !== '') {
    $where .= " and addr LIKE '%" . mysqli_real_escape_string($conn, $selected_sido) . "%'";
}
if ($selected_gugun !== '') {
    $where .= " and addr LIKE '%" . mysqli_real_escape_string($conn, $selected_gugun) . "%'";
}

$total_cnt = sql_cnt('auctions', $where);
$total_pages = max(1, (int)ceil($total_cnt / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$auctions_list = sql_one('auctions', '*', $where . " order by no desc limit $offset, $limit");
?>

<!-- 시/도별 (1차) > 시/군/구별 (2차) 주소 그룹핑 연동 검색 필터 -->
<form method="get" action="index.php#live" style="background:#fff; border:1px solid #CBD5E1; border-radius:14px; padding:18px 24px; margin-bottom:28px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; box-shadow:0 4px 15px rgba(0,0,0,0.04);">
    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; flex:1;">
        <span style="font-weight:900; font-size:0.95rem; color:#0F172A; display:flex; align-items:center; gap:6px;">
            <i class="fa-solid fa-location-dot" style="color:#0077B6;"></i> 지역 필터 (1차/2차):
        </span>

        <!-- 1차 시/도 Select Box -->
        <select name="sido" id="sidoSelect" onchange="updateGugunOptions()" style="padding:10px 16px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem; font-weight:700; background:#fff; color:#0F172A; min-width:160px; cursor:pointer;">
            <option value="">전체 시/도 (1차)</option>
            <?php foreach (array_keys($location_map) as $s_item): ?>
                <option value="<?php echo htmlspecialchars($s_item); ?>" <?php echo $selected_sido === $s_item ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($s_item); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- 2차 시/군/구 Select Box -->
        <select name="gugun" id="gugunSelect" style="padding:10px 16px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem; font-weight:700; background:#fff; color:#0F172A; min-width:160px; cursor:pointer;">
            <option value="">전체 시/군/구 (2차)</option>
        </select>

        <input type="hidden" name="cat" value="<?php echo htmlspecialchars($cat); ?>">

        <button type="submit" class="btn btn-primary" style="padding:10px 22px; font-weight:800; font-size:0.9rem;">
            <i class="fa-solid fa-magnifying-glass"></i> 조회
        </button>
    </div>

    <?php if ($selected_sido !== '' || $selected_gugun !== ''): ?>
        <a href="index.php?cat=<?php echo urlencode($cat); ?>#live" class="btn btn-outline btn-sm" style="color:#EF4444; border-color:#FCA5A5; font-weight:700;">
            <i class="fa-solid fa-rotate-left"></i> 지역 필터 초기화
        </a>
    <?php endif; ?>
</form>

<script>
var locationData = <?php echo json_encode($location_map, JSON_UNESCAPED_UNICODE); ?>;
var currentSido = <?php echo json_encode($selected_sido, JSON_UNESCAPED_UNICODE); ?>;
var currentGugun = <?php echo json_encode($selected_gugun, JSON_UNESCAPED_UNICODE); ?>;

function updateGugunOptions() {
    var sidoSel = document.getElementById('sidoSelect');
    var gugunSel = document.getElementById('gugunSelect');
    var chosenSido = sidoSel.value;

    gugunSel.innerHTML = '<option value="">전체 시/군/구 (2차)</option>';

    if (chosenSido && locationData[chosenSido]) {
        var list = locationData[chosenSido];
        for (var i = 0; i < list.length; i++) {
            var opt = document.createElement('option');
            opt.value = list[i];
            opt.textContent = list[i];
            if (list[i] === currentGugun && chosenSido === currentSido) {
                opt.selected = true;
            }
            gugunSel.appendChild(opt);
        }
    }
}

// 페이지 로드시 2차 구/군 옵션 동기화
document.addEventListener('DOMContentLoaded', function() {
    updateGugunOptions();
});
</script>

<div class="auc-filter">
    <a href="index.php?cat=&sido=<?php echo urlencode($selected_sido); ?>&gugun=<?php echo urlencode($selected_gugun); ?>#live" class="<?php echo $cat === '' ? 'active' : ''; ?>"><i class="fa-solid fa-boxes-stacked"></i> 전체 시공 구분</a>
    <?php foreach ($sub_categories as $sc): 
        $sc_name = $sc['cat_name'];
        $sc_key = '';
        if (mb_strpos($sc_name, '아파트') !== false) $sc_key = 'apt';
        elseif (mb_strpos($sc_name, '빌딩') !== false || mb_strpos($sc_name, '건물') !== false) $sc_key = 'building';
        elseif (mb_strpos($sc_name, '자동차') !== false || mb_strpos($sc_name, '차량') !== false) $sc_key = 'auto';
        elseif (mb_strpos($sc_name, '오피스텔') !== false) $sc_key = 'officetel';
        elseif (mb_strpos($sc_name, '주택') !== false) $sc_key = 'house';

        $is_act = ($cat === $sc_key || $cat === $sc_name || (isset($cat_map[$cat]) && $cat_map[$cat] === $sc_name));
        $icon_cls = 'fa-tag';
        if (mb_strpos($sc_name, '아파트') !== false) $icon_cls = 'fa-building-user';
        elseif (mb_strpos($sc_name, '빌딩') !== false || mb_strpos($sc_name, '건물') !== false) $icon_cls = 'fa-city';
        elseif (mb_strpos($sc_name, '자동차') !== false || mb_strpos($sc_name, '차량') !== false) $icon_cls = 'fa-car';
        elseif (mb_strpos($sc_name, 'DIY') !== false || mb_strpos($sc_name, '자가') !== false) $icon_cls = 'fa-wrench';
    ?>
        <a href="index.php?cat=<?php echo urlencode($sc_key ?: $sc_name); ?>&sido=<?php echo urlencode($selected_sido); ?>&gugun=<?php echo urlencode($selected_gugun); ?>#live" class="<?php echo $is_act ? 'active' : ''; ?>">
            <i class="fa-solid <?php echo $icon_cls; ?>"></i> <?php echo htmlspecialchars($sc_name); ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="auc-grid">
    <?php if (empty($auctions_list)): ?>
        <div style="grid-column:1/-1; text-align:center; padding:60px 20px; background:#fff; border-radius:12px; border:1px solid #E2E8F0; color:var(--text-muted);">
            <i class="fa-solid fa-folder-open" style="font-size:2.5rem; color:#CBD5E1; margin-bottom:12px; display:block;"></i>
            등록된 역경매 신청 내역이 없습니다.
        </div>
    <?php else: foreach ($auctions_list as $a): 
        $st_cls = 'st-waiting';
        if ($a['state'] === '입찰중') $st_cls = 'st-bidding';
        elseif ($a['state'] === '매칭완료') $st_cls = 'st-matched';

        // 주소 축약
        $addr_parts = preg_split('/\s+/', trim($a['addr']));
        $short_addr = implode(' ', array_slice($addr_parts, 0, 2));
    ?>
        <div class="auc-card" onclick="location.href='index.php?cat=<?php echo urlencode($cat); ?>&no=<?php echo (int)$a['no']; ?>'" style="cursor:pointer;">
            <div class="auc-card-head">
                <span style="font-size:0.85rem; font-weight:800; color:var(--primary-dark);"><?php echo htmlspecialchars($a['space_type']); ?> (<?php echo (int)$a['py']; ?>평)</span>
                <span class="st-badge <?php echo $st_cls; ?>"><?php echo htmlspecialchars($a['state']); ?></span>
            </div>
            <div class="auc-card-body">
                <div>
                    <h4 style="font-size:1.1rem; font-weight:800; color:#0F172A; margin-bottom:8px;">
                        <?php echo htmlspecialchars($short_addr); ?> 역경매
                    </h4>
                    <p style="font-size:0.85rem; color:#64748B; margin-bottom:14px; min-height:36px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                        <?php echo htmlspecialchars($a['memo'] ? $a['memo'] : '희망예산과 조건에 맞춘 최적의 틴팅프로 입찰을 모집합니다.'); ?>
                    </p>
                </div>
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-top:1px dashed #E2E8F0; padding-top:12px;">
                        <span style="font-size:0.85rem; color:var(--text-muted);">희망 예산</span>
                        <strong style="font-size:1.15rem; color:var(--primary-dark); font-weight:900;"><?php echo number_format($a['desired_price']); ?>원</strong>
                    </div>
                    <a href="index.php?cat=<?php echo urlencode($cat); ?>&no=<?php echo (int)$a['no']; ?>" class="btn btn-primary btn-sm" style="width:100%; text-align:center; font-weight:800; padding:9px;">
                        <i class="fa-solid fa-gavel"></i> 상세보기 & 입찰 참여 (<?php echo (int)$a['bid_count']; ?>건)
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<!-- 페이징 컨트롤 -->
<?php if ($total_pages > 1): ?>
<div style="display:flex; justify-content:center; align-items:center; gap:8px; margin-top:40px; margin-bottom:20px;">
    <?php if ($page > 1): ?>
        <a href="index.php?cat=<?php echo urlencode($cat); ?>&page=<?php echo $page - 1; ?>#apply" class="cat-tab-btn" style="padding:7px 14px; font-size:0.85rem;"><i class="fa-solid fa-chevron-left"></i> 이전</a>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <a href="index.php?cat=<?php echo urlencode($cat); ?>&page=<?php echo $p; ?>#apply" class="cat-tab-btn <?php echo $p === $page ? 'active' : ''; ?>" style="padding:7px 14px; font-size:0.85rem; min-width:36px; text-align:center; text-decoration:none;">
            <?php echo $p; ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="index.php?cat=<?php echo urlencode($cat); ?>&page=<?php echo $page + 1; ?>#apply" class="cat-tab-btn" style="padding:7px 14px; font-size:0.85rem;">다음 <i class="fa-solid fa-chevron-right"></i></a>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
function showAlert(elId, ok, msg) {
    const el = document.getElementById(elId);
    if (!el) return;
    el.style.display = 'block';
    el.style.background = ok ? '#ECFDF5' : '#FEF2F2';
    el.style.border = '1px solid ' + (ok ? '#A7F3D0' : '#FECACA');
    el.style.color = ok ? '#047857' : '#B91C1C';
    el.innerText = msg;
}

function submitAuctionApply() {
    const name = document.getElementById('app_name').value.trim();
    const hphone = document.getElementById('app_hphone').value.trim();
    const spaceType = document.getElementById('app_space_type').value;
    const py = document.getElementById('app_py').value;
    const price = document.getElementById('app_desired_price').value;
    const zipcode = document.getElementById('app_zipcode').value.trim();
    const addr1 = document.getElementById('app_addr1').value.trim();
    const addr2 = document.getElementById('app_addr2').value.trim();
    const addr = (addr1 ? '[' + zipcode + '] ' + addr1 : '') + (addr2 ? ' ' + addr2 : '');
    const memo = document.getElementById('app_memo').value.trim();

    if (!name || !hphone) {
        showAlert('applyAlert', false, '신청자 성함과 연락처를 입력해 주세요.');
        return;
    }
    if (!addr1) {
        showAlert('applyAlert', false, '시공 장소 주소를 검색해 입력해 주세요.');
        return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('hphone', hphone);
    formData.append('space_type', spaceType);
    formData.append('py', py);
    formData.append('desired_price', price);
    formData.append('addr', addr);
    formData.append('memo', memo);

    fetch('apply_proc.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showAlert('applyAlert', true, data.msg);
            setTimeout(() => { location.reload(); }, 1200);
        } else {
            showAlert('applyAlert', false, data.msg || '신청 처리 중 오류가 발생했습니다.');
        }
    })
    .catch(err => {
        showAlert('applyAlert', false, '네트워크 오류가 발생했습니다.');
    });
}

function submitBid(auctionNo) {
    const isLoggedIn = <?php echo !empty($_SESSION['s_mem_id']) ? 'true' : 'false'; ?>;
    const isCustomer = <?php echo ($is_customer ?? false) ? 'true' : 'false'; ?>;

    if (!isLoggedIn) {
        if (confirm('입찰 제안 제출은 로그인 후 이용하실 수 있습니다.\n로그인 페이지로 이동하시겠습니까?')) {
            location.href = '../member/login.php';
        }
        return;
    }

    if (isCustomer) {
        alert('입찰 제안은 수요고객을 제외한 시공업체(프리랜서, 기업, 대리점) 회원만 참여 가능합니다.');
        return;
    }

    const bidderTypeRadio = document.querySelector('input[name="bidder_type"]:checked') || document.querySelector('input[name="bidder_type"]');
    const bidderType = bidderTypeRadio ? bidderTypeRadio.value : '프리랜서(개인)';

    if (bidderType === '수요고객') {
        alert('입찰 제안은 수요고객을 제외한 시공업체(프리랜서, 기업, 대리점) 회원만 참여 가능합니다.');
        return;
    }

    const bidderName = document.getElementById('bidderName').value.trim();
    const bidderHphone = document.getElementById('bidderHphone').value.trim();
    const bidPrice = document.getElementById('bidPrice').value;
    const workDuration = document.getElementById('bidWorkDuration').value.trim();
    const memo = document.getElementById('bidMemo').value.trim();

    if (!bidderName || !bidderHphone) {
        alert('입찰자 성함/업체명과 연락처를 입력해 주세요.');
        return;
    }
    if (!bidPrice || parseInt(bidPrice) <= 0) {
        alert('입찰 제안 금액을 정확히 입력해 주세요.');
        return;
    }

    const formData = new FormData();
    formData.append('auction_no', auctionNo);
    formData.append('bidder_type', bidderType);
    formData.append('bidder_name', bidderName);
    formData.append('bidder_hphone', bidderHphone);
    formData.append('bid_price', bidPrice);
    formData.append('work_duration', workDuration);
    formData.append('memo', memo);

    fetch('bid_proc.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            alert(data.msg || '역경매 입찰 제안이 정상 등록되었습니다!');
            location.reload();
        } else {
            alert(data.msg || '입찰 처리 중 오류가 발생했습니다.');
            if (data.msg && data.msg.indexOf('로그인') !== -1) {
                location.href = '../member/login.php';
            }
        }
    })
    .catch(err => {
        alert('네트워크 오류가 발생했습니다.');
    });
}

function cancelBid(bidNo, auctionNo) {
    if (!confirm('정말로 이 입찰 제안을 취소하시겠습니까?')) {
        return;
    }

    const formData = new FormData();
    formData.append('bid_no', bidNo);
    formData.append('auction_no', auctionNo);

    fetch('bid_cancel_proc.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            alert(data.msg || '입찰 제안이 성공적으로 취소되었습니다.');
            location.reload();
        } else {
            alert(data.msg || '취소 처리 중 오류가 발생했습니다.');
        }
    })
    .catch(err => {
        alert('네트워크 오류가 발생했습니다.');
    });
}

function execAppPostcode() {
    new daum.Postcode({
        oncomplete: function (data) {
            var addr = '';
            var extraAddr = '';

            if (data.userSelectedType === 'R') {
                addr = data.roadAddress;
            } else {
                addr = data.jibunAddress;
            }

            if (data.userSelectedType === 'R') {
                if (data.bname !== '' && /[동|로|가]$/g.test(data.bname)) {
                    extraAddr += data.bname;
                }
                if (data.buildingName !== '' && data.apartment === 'Y') {
                    extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                if (extraAddr !== '') {
                    extraAddr = ' (' + extraAddr + ')';
                }
            }

            document.getElementById('app_zipcode').value = data.zonecode;
            document.getElementById('app_addr1').value = addr + extraAddr;
            document.getElementById('app_addr2').focus();
        }
    }).open();
}

document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($_SESSION['s_mem_name'])): ?>
        var aN = document.getElementById('app_name'); if(aN) aN.value = <?php echo json_encode($_SESSION['s_mem_name']); ?>;
        var bN = document.getElementById('bidderName'); if(bN) bN.value = <?php echo json_encode($_SESSION['s_mem_name']); ?>;
    <?php endif; ?>
    <?php if (!empty($_SESSION['s_mem_hphone'])): ?>
        var aH = document.getElementById('app_hphone'); if(aH) aH.value = <?php echo json_encode($_SESSION['s_mem_hphone']); ?>;
        var bH = document.getElementById('bidderHphone'); if(bH) bH.value = <?php echo json_encode($_SESSION['s_mem_hphone']); ?>;
    <?php endif; ?>
    <?php if (!empty($_SESSION['s_mem_zipcode'])): ?>
        var aZ = document.getElementById('app_zipcode'); if(aZ) aZ.value = <?php echo json_encode($_SESSION['s_mem_zipcode']); ?>;
    <?php endif; ?>
    <?php if (!empty($_SESSION['s_mem_addr1'])): ?>
        var aA1 = document.getElementById('app_addr1'); if(aA1) aA1.value = <?php echo json_encode($_SESSION['s_mem_addr1']); ?>;
    <?php endif; ?>
    <?php if (!empty($_SESSION['s_mem_addr2'])): ?>
        var aA2 = document.getElementById('app_addr2'); if(aA2) aA2.value = <?php echo json_encode($_SESSION['s_mem_addr2']); ?>;
    <?php endif; ?>
});
</script>
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
