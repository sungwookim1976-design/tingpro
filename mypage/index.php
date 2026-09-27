<?php
$page_title = "마이페이지 대시보드";
$active_menu = "mypage";
$path_prefix = "../";

include_once __DIR__ . "/../inc/dbconn.php";

if (empty($_SESSION['s_mem_id'])) {
    header("Location: ../member/login.php?redirect=" . urlencode('../mypage/index.php'));
    exit;
}

$mem_id    = $_SESSION['s_mem_id'];
$mem_no    = intval((isset($_SESSION['s_mem_no']) ? $_SESSION['s_mem_no'] : 0));
$mem_name  = (isset($_SESSION['s_mem_name']) ? $_SESSION['s_mem_name'] : '');
$mem_hphone= (isset($_SESSION['s_mem_hphone']) ? $_SESSION['s_mem_hphone'] : '');
$mem_email = (isset($_SESSION['s_mem_email']) ? $_SESSION['s_mem_email'] : $mem_id);

if ($mem_no > 0) {
    check_and_update_partner_status($mem_no);
}

// DB에서 회원 상세 정보 조회
$member = sql_one_one('members', '*', "and (uid='" . mysqli_real_escape_string($conn, $mem_id) . "' or email='" . mysqli_real_escape_string($conn, $mem_email) . "'" . ($mem_no > 0 ? " or no=" . $mem_no : "") . ")");

if (!$member) {
    $member = [
        'no' => $mem_no,
        'uid' => $mem_id,
        'email' => $mem_email,
        'name' => $mem_name,
        'hphone' => $mem_hphone,
        'mem_type' => (isset($_SESSION['s_mem_type']) ? $_SESSION['s_mem_type'] : 'customer'),
        'zipcode' => '',
        'addr1' => '',
        'addr2' => '',
        'reg_date' => date('Y-m-d H:i:s')
    ];
}

$type_labels = [
    'customer'  => '수요고객 회원',
    'freelance' => '프리랜서 파트너',
    'corporate' => '기업 회원',
    'partner'   => '대리점 파트너'
];
$mem_type_title = isset($type_labels[$member['mem_type']]) ? $type_labels[$member['mem_type']] : '일반 회원';

// 회원 정보 기반 검색 조건 (이름, 연락처, 회원번호)
$conds = [];
if (!empty($member['name'])) {
    $conds[] = "name='" . mysqli_real_escape_string($conn, $member['name']) . "'";
}
if (!empty($member['hphone'])) {
    $conds[] = "hphone='" . mysqli_real_escape_string($conn, $member['hphone']) . "'";
}
if (!empty($member['no'])) {
    $conds[] = "mem_no=" . intval($member['no']);
}

$where_user = !empty($conds) ? "and (" . implode(" or ", $conds) . ")" : "and 0";

// 1. 내 DIY 주문 내역
$my_orders = sql_one('orders', '*', $where_user . ' order by no desc limit 50');
$ord_cnt = is_array($my_orders) ? count($my_orders) : 0;

// 2. 내 실시간 견적 신청 내역
$my_quotes = sql_one('quotes', '*', $where_user . ' order by no desc limit 50');
$quo_cnt = is_array($my_quotes) ? count($my_quotes) : 0;

// 3. 내 역경매 신청 내역
$my_auctions = sql_one('auctions', '*', $where_user . ' order by no desc limit 50');
$auc_cnt = is_array($my_auctions) ? count($my_auctions) : 0;

include_once __DIR__ . "/../inc/head.php";
include_once __DIR__ . "/../inc/header.php";
?>

<style>
    .mp-container { max-width:1200px; margin:0 auto; padding:40px 24px 80px; }
    
    /* 프로필 상단 옥타곤 스타일 헤더 */
    .mp-profile-hero {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
        color: #fff; border-radius: 20px; padding: 36px 40px; margin-bottom: 32px;
        box-shadow: 0 10px 30px rgba(15,23,42,0.15); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 24px;
    }
    .mp-profile-left { display: flex; align-items: center; gap: 24px; }
    .mp-avatar {
        width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #0077B6, #00B4D8);
        display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #fff;
        box-shadow: 0 4px 14px rgba(0,180,216,0.35); flex-shrink: 0;
    }
    .mp-profile-info h2 { font-size: 1.6rem; font-weight: 900; margin-bottom: 6px; display: flex; align-items: center; gap: 10px; }
    .mp-profile-info p { color: #94A3B8; font-size: 0.9rem; line-height: 1.5; }
    .mp-grade-tag { background: #00B4D8; color: #fff; font-size: 0.75rem; font-weight: 800; padding: 4px 12px; border-radius: 20px; vertical-align: middle; }

    /* 대시보드 요약 카드 */
    .mp-stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
    .mp-stat-card {
        background: #fff; border: 1px solid var(--border-color); border-radius: 16px; padding: 24px 28px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03); transition: all 0.25s ease; cursor: pointer;
    }
    .mp-stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); border-color: var(--primary-light); }
    .mp-stat-card .card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; color: var(--text-muted); font-size: 0.9rem; font-weight: 700; }
    .mp-stat-card .card-top i { font-size: 1.4rem; color: var(--primary-dark); }
    .mp-stat-card .card-val { font-size: 2.2rem; font-weight: 900; color: var(--secondary); }

    /* 탭 헤더 */
    .mp-tabs { display: flex; gap: 8px; border-bottom: 2px solid #E2E8F0; margin-bottom: 28px; flex-wrap: wrap; }
    .mp-tab-btn {
        padding: 12px 24px; font-size: 0.95rem; font-weight: 800; color: #64748B; background: none; border: none;
        border-bottom: 3px solid transparent; margin-bottom: -2px; cursor: pointer; transition: all 0.2s;
        display: inline-flex; align-items: center; gap: 8px;
    }
    .mp-tab-btn:hover { color: var(--primary-dark); }
    .mp-tab-btn.active { color: var(--primary-dark); border-bottom-color: var(--primary-dark); }

    .mp-tab-pane { display: none; }
    .mp-tab-pane.active { display: block; }

    /* 데이터 테이블 */
    .mp-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color); }
    .mp-table th { background: #F8FAFC; text-align: left; padding: 14px 18px; font-weight: 800; color: #475569; border-bottom: 1px solid #E2E8F0; white-space: nowrap; }
    .mp-table td { padding: 14px 18px; border-bottom: 1px solid #F1F5F9; color: var(--text-color); }
    .mp-table tr:last-child td { border-bottom: none; }
    .mp-table .empty-td { text-align: center; color: #94A3B8; padding: 48px 20px; }

    /* 상태 뱃지 */
    .mp-badge { display: inline-block; font-size: 0.78rem; font-weight: 800; padding: 4px 10px; border-radius: 6px; white-space: nowrap; }
    .mp-badge.st-cyan { background: #E0F7FA; color: #0077B6; }
    .mp-badge.st-amber { background: #FEF3C7; color: #D97706; }
    .mp-badge.st-emerald { background: #ECFDF5; color: #059669; }
    .mp-badge.st-rose { background: #FEE2E2; color: #B91C1C; }

    /* 프로필 수정 폼 */
    .mp-form-card { background: #fff; border: 1px solid var(--border-color); border-radius: 16px; padding: 36px; max-width: 680px; margin: 0 auto; box-shadow: 0 4px 16px rgba(0,0,0,0.03); }
    .mp-form-group { margin-bottom: 20px; }
    .mp-form-group label { display: block; font-size: 0.9rem; font-weight: 800; color: var(--secondary); margin-bottom: 8px; }
    .mp-form-group input { width: 100%; padding: 12px 14px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.95rem; outline: none; transition: border 0.2s; }
    .mp-form-group input:focus { border-color: var(--primary-light); }

    @media (max-width: 900px) {
        .mp-stat-grid { grid-template-columns: 1fr; }
        .mp-profile-hero { padding: 24px; }
    }
</style>

<div class="mp-container">

    <!-- 프로필 히어로 바 -->
    <div class="mp-profile-hero">
        <div class="mp-profile-left">
            <div class="mp-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="mp-profile-info">
                <h2>
                    <?php echo htmlspecialchars($member['name']); ?> 회원님 
                    <span class="mp-grade-tag"><?php echo htmlspecialchars($mem_type_title); ?></span>
                </h2>
                <p>
                    <i class="fa-solid fa-envelope" style="margin-right:4px;"></i> <?php echo htmlspecialchars($member['email']); ?> &nbsp;|&nbsp;
                    <i class="fa-solid fa-mobile-screen" style="margin-right:4px;"></i> <?php echo htmlspecialchars($member['hphone']); ?>
                </p>
                <p style="margin-top:4px; font-size:0.82rem; color:#64748B;">가입일자: <?php echo htmlspecialchars($member['reg_date']); ?></p>
            </div>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" class="btn btn-outline btn-sm" onclick="switchTab('edit')" style="background:rgba(255,255,255,0.1); color:#fff; border-color:rgba(255,255,255,0.2);">
                <i class="fa-solid fa-pen-to-square"></i> 정보 수정
            </button>
            <a href="../member/logout.php" class="btn btn-outline btn-sm" style="background:rgba(239,68,68,0.15); color:#FCA5A5; border-color:rgba(239,68,68,0.3);">
                <i class="fa-solid fa-right-from-bracket"></i> 로그아웃
            </a>
        </div>
    </div>

    <!-- 대시보드 통계 카운터 카드 -->
    <div class="mp-stat-grid">
        <div class="mp-stat-card" onclick="switchTab('orders')">
            <div class="card-top">
                <span><i class="fa-solid fa-cart-shopping" style="color:#059669;"></i> DIY 필름 주문</span>
                <i class="fa-solid fa-chevron-right" style="font-size:0.8rem;"></i>
            </div>
            <div class="card-val"><?php echo number_format($ord_cnt); ?><span style="font-size:1rem; font-weight:600; color:#64748B; margin-left:4px;">건</span></div>
        </div>

        <div class="mp-stat-card" onclick="switchTab('quotes')">
            <div class="card-top">
                <span><i class="fa-solid fa-calculator" style="color:#0077B6;"></i> 실시간 견적 신청</span>
                <i class="fa-solid fa-chevron-right" style="font-size:0.8rem;"></i>
            </div>
            <div class="card-val"><?php echo number_format($quo_cnt); ?><span style="font-size:1rem; font-weight:600; color:#64748B; margin-left:4px;">건</span></div>
        </div>

        <div class="mp-stat-card" onclick="switchTab('auctions')">
            <div class="card-top">
                <span><i class="fa-solid fa-gavel" style="color:#D97706;"></i> 역경매 마켓 신청</span>
                <i class="fa-solid fa-chevron-right" style="font-size:0.8rem;"></i>
            </div>
            <div class="card-val"><?php echo number_format($auc_cnt); ?><span style="font-size:1rem; font-weight:600; color:#64748B; margin-left:4px;">건</span></div>
        </div>
    </div>

    <!-- 탭 네비게이션 -->
    <div class="mp-tabs">
        <button class="mp-tab-btn active" id="tabBtn_orders" onclick="switchTab('orders')">
            <i class="fa-solid fa-box"></i> DIY 주문 내역 (<?php echo $ord_cnt; ?>)
        </button>
        <button class="mp-tab-btn" id="tabBtn_quotes" onclick="switchTab('quotes')">
            <i class="fa-solid fa-file-invoice"></i> 견적 신청 내역 (<?php echo $quo_cnt; ?>)
        </button>
        <button class="mp-tab-btn" id="tabBtn_auctions" onclick="switchTab('auctions')">
            <i class="fa-solid fa-gavel"></i> 역경매 신청 내역 (<?php echo $auc_cnt; ?>)
        </button>
        <button class="mp-tab-btn" id="tabBtn_edit" onclick="switchTab('edit')">
            <i class="fa-solid fa-user-gear"></i> 회원정보 / 비밀번호 변경
        </button>
    </div>

    <!-- Tab 1: DIY 주문 내역 -->
    <div class="mp-tab-pane active" id="pane_orders">
        <div style="overflow-x:auto;">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th style="width:70px; text-align:center;">번호</th>
                        <th>주문 상품명</th>
                        <th style="text-align:center;">수량</th>
                        <th>결제 예상금액</th>
                        <th>배송지 주소</th>
                        <th style="text-align:center;">진행상태</th>
                        <th>주문일시</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($my_orders)): ?>
                        <tr><td colspan="7" class="empty-td"><i class="fa-solid fa-basket-shopping" style="font-size:2rem; margin-bottom:8px; display:block;"></i>주문 내역이 없습니다. <a href="../diy/index.php" style="color:var(--primary-dark); font-weight:700;">DIY 상품 보러가기 &rarr;</a></td></tr>
                    <?php else: foreach ($my_orders as $o): ?>
                        <?php
                            $st_cls = 'st-cyan';
                            if ($o['state'] === '배송중' || $o['state'] === '배송준비') $st_cls = 'st-amber';
                            elseif ($o['state'] === '배송완료') $st_cls = 'st-emerald';
                            elseif ($o['state'] === '취소') $st_cls = 'st-rose';
                        ?>
                        <tr>
                            <td style="text-align:center; font-weight:700; color:#64748B;"><?php echo $o['no']; ?></td>
                            <td><strong style="color:var(--secondary);"><?php echo htmlspecialchars($o['product_name']); ?></strong></td>
                            <td style="text-align:center; font-weight:700;"><?php echo (int)$o['qty']; ?>개</td>
                            <td><strong style="color:var(--primary-dark);"><?php echo number_format($o['total_price']); ?>원</strong></td>
                            <td style="font-size:0.85rem; color:#64748B; max-width:260px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?php echo htmlspecialchars($o['addr1'] . ' ' . $o['addr2']); ?>">
                                <?php echo htmlspecialchars(trim(($o['zipcode'] ? '[' . $o['zipcode'] . '] ' : '') . $o['addr1'] . ' ' . $o['addr2'])); ?>
                            </td>
                            <td style="text-align:center;"><span class="mp-badge <?php echo $st_cls; ?>"><?php echo htmlspecialchars($o['state']); ?></span></td>
                            <td style="font-size:0.85rem; color:#64748B;"><?php echo htmlspecialchars($o['reg_date']); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 2: 실시간 견적 신청 내역 -->
    <div class="mp-tab-pane" id="pane_quotes">
        <div style="overflow-x:auto;">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th style="width:70px; text-align:center;">번호</th>
                        <th>공간 구분</th>
                        <th style="text-align:center;">평수</th>
                        <th>선택 필름</th>
                        <th>예상 견적가</th>
                        <th>시공 희망일</th>
                        <th style="text-align:center;">상태</th>
                        <th>신청일시</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($my_quotes)): ?>
                        <tr><td colspan="8" class="empty-td"><i class="fa-solid fa-file-circle-xmark" style="font-size:2rem; margin-bottom:8px; display:block;"></i>신청된 견적 내역이 없습니다. <a href="../calculator/index.php" style="color:var(--primary-dark); font-weight:700;">실시간 견적 내기 &rarr;</a></td></tr>
                    <?php else: foreach ($my_quotes as $q): ?>
                        <?php
                            $st_cls = 'st-cyan';
                            if ($q['state'] === '상담중') $st_cls = 'st-amber';
                            elseif ($q['state'] === '완료') $st_cls = 'st-emerald';
                        ?>
                        <tr>
                            <td style="text-align:center; font-weight:700; color:#64748B;"><?php echo $q['no']; ?></td>
                            <td><span class="mp-badge st-cyan"><?php echo htmlspecialchars($q['space_type']); ?></span></td>
                            <td style="text-align:center; font-weight:700;"><?php echo (int)$q['py']; ?>평</td>
                            <td><strong style="color:var(--secondary);"><?php echo htmlspecialchars($q['film_name']); ?></strong></td>
                            <td><strong style="color:var(--primary-dark);"><?php echo number_format($q['total_price']); ?>원</strong></td>
                            <td style="font-size:0.85rem; color:#64748B;"><?php echo $q['request_date'] ? htmlspecialchars($q['request_date']) : '-'; ?></td>
                            <td style="text-align:center;"><span class="mp-badge <?php echo $st_cls; ?>"><?php echo htmlspecialchars($q['state']); ?></span></td>
                            <td style="font-size:0.85rem; color:#64748B;"><?php echo htmlspecialchars($q['reg_date']); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: 역경매 신청 내역 -->
    <div class="mp-tab-pane" id="pane_auctions">
        <div style="overflow-x:auto;">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th style="width:70px; text-align:center;">번호</th>
                        <th>공간 구분</th>
                        <th style="text-align:center;">평수</th>
                        <th>시공 지역</th>
                        <th>희망 예산</th>
                        <th style="text-align:center;">참여 입찰수</th>
                        <th style="text-align:center;">상태</th>
                        <th>신청일시</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($my_auctions)): ?>
                        <tr><td colspan="8" class="empty-td"><i class="fa-solid fa-gavel" style="font-size:2rem; margin-bottom:8px; display:block;"></i>신청된 역경매가 없습니다. <a href="../process/index.php#apply" style="color:var(--primary-dark); font-weight:700;">역경매 신청하기 &rarr;</a></td></tr>
                    <?php else: foreach ($my_auctions as $a): ?>
                        <?php
                            $st_cls = 'st-cyan';
                            if ($a['state'] === '입찰중') $st_cls = 'st-amber';
                            elseif ($a['state'] === '매칭완료') $st_cls = 'st-emerald';
                            elseif ($a['state'] === '취소') $st_cls = 'st-rose';
                        ?>
                        <tr>
                            <td style="text-align:center; font-weight:700; color:#64748B;"><?php echo $a['no']; ?></td>
                            <td><span class="mp-badge st-cyan"><?php echo htmlspecialchars($a['space_type']); ?></span></td>
                            <td style="text-align:center; font-weight:700;"><?php echo (int)$a['py']; ?>평</td>
                            <td style="font-size:0.85rem; color:#64748B; max-width:240px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?php echo htmlspecialchars($a['addr']); ?>">
                                <?php echo htmlspecialchars($a['addr']); ?>
                            </td>
                            <td><strong style="color:var(--primary-dark);"><?php echo number_format($a['desired_price']); ?>원</strong></td>
                            <td style="text-align:center; font-weight:800; color:#D97706;"><?php echo (int)$a['bid_count']; ?>건</td>
                            <td style="text-align:center;"><span class="mp-badge <?php echo $st_cls; ?>"><?php echo htmlspecialchars($a['state']); ?></span></td>
                            <td style="font-size:0.85rem; color:#64748B;"><?php echo htmlspecialchars($a['reg_date']); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 4: 내 정보 / 비밀번호 수정 -->
    <div class="mp-tab-pane" id="pane_edit">
        <div class="mp-form-card">
            <h3 style="font-size:1.3rem; font-weight:800; color:var(--secondary); margin-bottom:6px;"><i class="fa-solid fa-user-pen"></i> 회원 정보 수정</h3>
            <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:24px;">기본 회원정보 및 비밀번호를 수정할 수 있습니다.</p>

            <div id="editAlert" style="display:none; padding:12px 16px; border-radius:8px; font-size:0.9rem; margin-bottom:20px;"></div>

            <form id="profileForm" onsubmit="event.preventDefault(); submitProfileUpdate();">
                <div class="mp-form-group">
                    <label>아이디 (이메일)</label>
                    <input type="text" value="<?php echo htmlspecialchars($member['email']); ?>" readonly style="background:#F1F5F9; color:#64748B;">
                </div>

                <div class="mp-form-group">
                    <label>성함 <span style="color:#EF4444;">*</span></label>
                    <input type="text" id="editName" value="<?php echo htmlspecialchars($member['name']); ?>" required placeholder="성함 입력">
                </div>

                <div class="mp-form-group">
                    <label>연락처 <span style="color:#EF4444;">*</span></label>
                    <input type="tel" id="editHphone" value="<?php echo htmlspecialchars($member['hphone']); ?>" required placeholder="010-0000-0000">
                </div>

                <div class="mp-form-group">
                    <label>주소</label>
                    <div style="display:flex; gap:8px; margin-bottom:8px;">
                        <input type="text" id="editZipcode" value="<?php echo htmlspecialchars($member['zipcode']); ?>" placeholder="우편번호" readonly style="width:140px; background:#F1F5F9;">
                        <button type="button" onclick="execEditPostcode()" class="btn btn-outline" style="padding:0 18px; font-size:0.9rem; font-weight:800; white-space:nowrap;">
                            <i class="fa-solid fa-magnifying-glass"></i> 주소 검색
                        </button>
                    </div>
                    <input type="text" id="editAddr1" value="<?php echo htmlspecialchars($member['addr1']); ?>" placeholder="우편번호 검색 시 자동 입력됩니다." readonly style="background:#F1F5F9; margin-bottom:8px;">
                    <input type="text" id="editAddr2" value="<?php echo htmlspecialchars($member['addr2']); ?>" placeholder="상세주소 입력">
                </div>

                <hr style="border:none; border-top:1px solid #E2E8F0; margin:24px 0;">

                <div class="mp-form-group">
                    <label>새 비밀번호 (변경 시에만 입력)</label>
                    <input type="password" id="editPasswd" placeholder="비밀번호 변경을 원하시면 4자 이상 입력해 주세요.">
                </div>

                <div style="margin-top:28px; display:flex; justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary" id="editSubmitBtn" style="padding:12px 28px; font-weight:800;">
                        <i class="fa-solid fa-check"></i> 회원 정보 저장
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
    function switchTab(tabName) {
        document.querySelectorAll('.mp-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.mp-tab-pane').forEach(pane => pane.classList.remove('active'));

        const btn = document.getElementById('tabBtn_' + tabName);
        const pane = document.getElementById('pane_' + tabName);

        if (btn) btn.classList.add('active');
        if (pane) pane.classList.add('active');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab') || window.location.hash.replace('#', '');
        if (tabParam && ['orders', 'quotes', 'auctions', 'edit'].includes(tabParam)) {
            switchTab(tabParam);
        }
    });

    function execEditPostcode() {
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

                document.getElementById('editZipcode').value = data.zonecode;
                document.getElementById('editAddr1').value = addr + extraAddr;
                document.getElementById('editAddr2').focus();
            }
        }).open();
    }

    function showEditAlert(ok, msg) {
        const el = document.getElementById('editAlert');
        if (!el) return;
        el.style.display = 'block';
        el.style.background = ok ? '#ECFDF5' : '#FEF2F2';
        el.style.border = '1px solid ' + (ok ? '#A7F3D0' : '#FECACA');
        el.style.color = ok ? '#047857' : '#B91C1C';
        el.innerText = msg;
    }

    function submitProfileUpdate() {
        const name = document.getElementById('editName').value.trim();
        const phone = document.getElementById('editHphone').value.trim();
        const zipcode = document.getElementById('editZipcode').value.trim();
        const addr1 = document.getElementById('editAddr1').value.trim();
        const addr2 = document.getElementById('editAddr2').value.trim();
        const passwd = document.getElementById('editPasswd').value;

        if (!name || !phone) {
            showEditAlert(false, '성함과 연락처를 입력해 주세요.');
            return;
        }

        const btn = document.getElementById('editSubmitBtn');
        btn.disabled = true;
        btn.innerText = '저장 중...';

        const body = new URLSearchParams({
            name: name,
            hphone: phone,
            zipcode: zipcode,
            addr1: addr1,
            addr2: addr2,
            passwd: passwd
        });

        fetch('update_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(res => res.json())
            .then(data => {
                showEditAlert(data.ok, data.msg);
                if (data.ok) {
                    document.getElementById('editPasswd').value = '';
                    setTimeout(function () {
                        location.reload();
                    }, 1200);
                }
            })
            .catch(() => {
                showEditAlert(false, '회원정보 수정 중 오류가 발생했습니다.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = '회원 정보 저장';
            });
    }
</script>

<?php
include_once __DIR__ . "/../inc/footer.php";
?>
