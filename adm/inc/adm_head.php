<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | 관리자' : '관리자'; ?> - 틴팅 마스터</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --adm-bg:#F1F5F9; --adm-sidebar:#0F172A; --adm-primary:#0077B6; --adm-primary-light:#00B4D8;
            --adm-text:#1E293B; --adm-muted:#64748B; --adm-border:#E2E8F0; --adm-card:#FFFFFF;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI','Pretendard',sans-serif; }
        body { background:var(--adm-bg); color:var(--adm-text); }
        a { text-decoration:none; color:inherit; }

        .adm-layout { display:flex; min-height:100vh; }

        .adm-sidebar {
            width:240px; flex-shrink:0; background:var(--adm-sidebar); color:#E2E8F0;
            display:flex; flex-direction:column; position:sticky; top:0; height:100vh;
        }
        .adm-sidebar-logo { padding:22px 20px; display:flex; align-items:center; gap:10px; border-bottom:1px solid rgba(255,255,255,0.08); }
        .adm-sidebar-logo i { color:var(--adm-primary-light); font-size:1.3rem; }
        .adm-sidebar-logo span { font-weight:900; font-size:1.05rem; color:#fff; }
        .adm-nav { flex:1; padding:16px 12px; }
        .adm-nav a {
            display:flex; align-items:center; gap:10px; padding:11px 14px; border-radius:8px;
            font-size:0.9rem; font-weight:600; color:#CBD5E1; margin-bottom:4px; transition:all 0.15s;
        }
        .adm-nav a i { width:18px; text-align:center; color:#64748B; }
        .adm-nav a:hover { background:rgba(255,255,255,0.06); color:#fff; }
        .adm-nav a.active { background:var(--adm-primary); color:#fff; }
        .adm-nav a.active i { color:#fff; }
        .adm-sub-nav { margin-bottom:4px; }
        .adm-sub-nav a { padding:8px 14px 8px 42px; font-size:0.82rem; font-weight:600; }
        .adm-sub-nav a::before { content:"-"; margin-right:6px; color:#475569; }
        .adm-sub-nav a.active::before { color:#fff; }
        .adm-sub-nav a.sub-item { padding:6px 14px 6px 52px; font-size:0.8rem; color:#94A3B8; }
        .adm-sub-nav a.sub-item::before { content:"•"; margin-right:6px; color:#64748B; }
        .adm-sub-nav a.sub-item:hover { color:#fff; background:rgba(255,255,255,0.06); }
        .adm-sub-nav a.sub-item.active { color:#38BDF8; font-weight:700; background:rgba(56,189,248,0.12); }
        .adm-sub-nav a.sub-item.active::before { color:#38BDF8; }
        .adm-sidebar-foot { padding:16px 20px; border-top:1px solid rgba(255,255,255,0.08); font-size:0.78rem; color:#64748B; }

        .adm-main { flex:1; min-width:0; display:flex; flex-direction:column; }
        .adm-topbar {
            height:64px; background:#fff; border-bottom:1px solid var(--adm-border);
            display:flex; align-items:center; justify-content:space-between; padding:0 28px; flex-shrink:0;
        }
        .adm-topbar h1 { font-size:1.15rem; font-weight:800; }
        .adm-topbar-user { display:flex; align-items:center; gap:14px; font-size:0.88rem; color:var(--adm-muted); }
        .adm-topbar-user .grade-badge { background:var(--adm-primary-light); color:#fff; font-size:0.7rem; font-weight:800; padding:3px 9px; border-radius:20px; }
        .adm-topbar-user a { color:var(--adm-primary); font-weight:700; }

        .adm-content { padding:28px; flex:1; }

        .adm-stat-grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:20px; margin-bottom:32px; }
        .adm-stat-card { background:var(--adm-card); border:1px solid var(--adm-border); border-radius:14px; padding:22px 24px; }
        .adm-stat-card .lbl { font-size:0.85rem; color:var(--adm-muted); font-weight:700; margin-bottom:8px; display:flex; align-items:center; gap:8px; }
        .adm-stat-card .val { font-size:2rem; font-weight:900; color:var(--adm-text); }
        .adm-stat-card .lbl i { color:var(--adm-primary-light); }

        .adm-section { background:var(--adm-card); border:1px solid var(--adm-border); border-radius:14px; margin-bottom:24px; overflow:hidden; }
        .adm-section-head { padding:18px 22px; border-bottom:1px solid var(--adm-border); display:flex; justify-content:space-between; align-items:center; }
        .adm-section-head h3 { font-size:1rem; font-weight:800; }
        .adm-table { width:100%; border-collapse:collapse; font-size:0.85rem; }
        .adm-table th { background:#F8FAFC; text-align:left; padding:11px 16px; font-weight:700; color:var(--adm-muted); border-bottom:1px solid var(--adm-border); white-space:nowrap; }
        .adm-table td { padding:11px 16px; border-bottom:1px solid #F1F5F9; color:var(--adm-text); }
        .adm-table tr:last-child td { border-bottom:none; }
        .adm-table .empty-row td { text-align:center; color:var(--adm-muted); padding:24px; }

        .adm-badge { display:inline-block; white-space:nowrap; font-size:0.72rem; font-weight:800; padding:3px 8px; border-radius:5px; line-height:1.3; vertical-align:middle; text-align:center; }
        .adm-badge.type-customer { background:#E0F7FA; color:#0077B6; }
        .adm-badge.type-freelance { background:#FEF3C7; color:#D97706; }
        .adm-badge.type-corporate { background:#EEF2FF; color:#4F46E5; }
        .adm-badge.type-partner { background:#ECFDF5; color:#059669; }
        .adm-badge.state-신규, .adm-badge.state-주문접수, .adm-badge.state-입찰대기,
        .state-select.state-신규, .state-select.state-주문접수, .state-select.state-입찰대기 { background:#E0F7FA; color:#0077B6; }
        .adm-badge.state-상담중, .adm-badge.state-결제확인중, .adm-badge.state-배송준비, .adm-badge.state-배송중, .adm-badge.state-입찰중,
        .state-select.state-상담중, .state-select.state-결제확인중, .state-select.state-배송준비, .state-select.state-배송중, .state-select.state-입찰중 { background:#FEF3C7; color:#D97706; }
        .adm-badge.state-완료, .adm-badge.state-배송완료, .adm-badge.state-매칭완료,
        .state-select.state-완료, .state-select.state-배송완료, .state-select.state-매칭완료,
        .state-select.state-1 { background:#ECFDF5; color:#059669; }
        .adm-badge.state-취소, .state-select.state-취소, .state-select.state-0 { background:#FEE2E2; color:#B91C1C; }

        .state-select { border:none; border-radius:5px; font-size:0.78rem; font-weight:800; padding:5px 8px; cursor:pointer; outline:none; }
        .adm-btn { display:inline-flex; align-items:center; gap:6px; padding:9px 18px; border-radius:8px; font-size:0.85rem; font-weight:700; border:none; cursor:pointer; background:var(--adm-primary); color:#fff; }
        .adm-btn:hover { opacity:0.9; }
        .adm-btn-outline { background:#fff; border:1.5px solid var(--adm-border); color:var(--adm-text); }
        .adm-form-box { background:var(--adm-card); border:1px solid var(--adm-border); border-radius:14px; padding:28px; width:100%; }
        .adm-form-row { margin-bottom:16px; }
        .adm-form-row label { display:block; font-size:0.85rem; font-weight:700; color:var(--adm-text); margin-bottom:6px; }
        .adm-form-row input, .adm-form-row select, .adm-form-row textarea {
            width:100%; padding:10px 12px; border:1px solid var(--adm-border); border-radius:8px; font-size:0.92rem; outline:none;
        }
        .adm-form-row input:focus, .adm-form-row select:focus, .adm-form-row textarea:focus { border-color:var(--adm-primary-light); }
        .adm-toast { position:fixed; top:20px; right:20px; background:#0F172A; color:#fff; padding:12px 20px; border-radius:8px; font-size:0.85rem; font-weight:600; z-index:9999; opacity:0; transform:translateY(-10px); transition:all 0.25s; pointer-events:none; }
        .adm-toast.show { opacity:1; transform:translateY(0); }

        @media (max-width:1100px) {
            .adm-stat-grid { grid-template-columns:repeat(2, 1fr); }
        }
        @media (max-width:800px) {
            .adm-sidebar { display:none; }
            .adm-content { padding:16px; }
        }
    </style>
</head>
<body>
<div class="adm-layout">
    <aside class="adm-sidebar">
        <div class="adm-sidebar-logo">
            <svg viewBox="0 0 250 42" height="28" style="display:block; overflow:visible;">
                <defs>
                    <linearGradient id="nexfilSlashGradAdm" x1="0%" y1="100%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#DC2626" />
                        <stop offset="35%" stop-color="#EA580C" />
                        <stop offset="70%" stop-color="#F59E0B" />
                        <stop offset="100%" stop-color="#FDE047" />
                    </linearGradient>
                </defs>
                <text x="0" y="33" font-family="'Pretendard', 'Montserrat', 'Arial Black', sans-serif" font-weight="900" font-style="italic" font-size="33" fill="#EF4444" letter-spacing="-0.8">TINTING PRO<tspan fill="#EF4444">.</tspan></text>
                <polygon points="112,41 124,41 168,0 156,0" fill="url(#nexfilSlashGradAdm)" />
            </svg>
        </div>
        <?php $ap = isset($adm_path_prefix) ? $adm_path_prefix : ''; $active = (isset($active_page) ? $active_page : ''); ?>
        <nav class="adm-nav">
            <a href="<?php echo $ap; ?>index.php" class="<?php echo $active === 'dashboard' ? 'active' : ''; ?>"><i class="fa-solid fa-gauge"></i> 대시보드</a>
            <a href="<?php echo $ap; ?>admins/index.php" class="<?php echo $active === 'admins' ? 'active' : ''; ?>"><i class="fa-solid fa-user-shield"></i> 관리자관리</a>
            <a href="<?php echo $ap; ?>members/index.php" class="<?php echo $active === 'members' ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i> 회원관리</a>
            <a href="<?php echo $ap; ?>products/index.php" class="<?php echo $active === 'products' ? 'active' : ''; ?>"><i class="fa-solid fa-box-open"></i> 상품관리</a>
            <a href="<?php echo $ap; ?>orders/index.php" class="<?php echo $active === 'orders' ? 'active' : ''; ?>"><i class="fa-solid fa-cart-shopping"></i> 주문관리</a>
            <a href="<?php echo $ap; ?>quotes/index.php" class="<?php echo $active === 'quotes' ? 'active' : ''; ?>"><i class="fa-solid fa-file-invoice"></i> 견적관리</a>
            <?php if ($active === 'quotes'): $active_sub = (isset($active_sub) ? $active_sub : 'list'); ?>
            <div class="adm-sub-nav">
                <a href="<?php echo $ap; ?>quotes/index.php" class="<?php echo $active_sub === 'list' ? 'active' : ''; ?>">견적목록</a>
                <a href="<?php echo $ap; ?>quotes/quick_consult.php" class="<?php echo $active_sub === 'quick_consult' ? 'active' : ''; ?>">빠른 시공 상담 신청서</a>
                <a href="<?php echo $ap; ?>quotes/cases.php" class="<?php echo $active_sub === 'cases' ? 'active' : ''; ?>">시공사례 이미지 등록</a>
            </div>
            <?php endif; ?>
            <a href="<?php echo $ap; ?>auction/index.php" class="<?php echo $active === 'auction' ? 'active' : ''; ?>"><i class="fa-solid fa-gavel"></i> 역경매관리</a>
            <?php if ($active === 'auction'): $active_sub = (isset($active_sub) ? $active_sub : 'list'); ?>
            <div class="adm-sub-nav">
                <a href="<?php echo $ap; ?>auction/index.php" class="<?php echo $active_sub === 'list' ? 'active' : ''; ?>">역경매목록</a>
                <a href="<?php echo $ap; ?>auction/cases.php" class="<?php echo $active_sub === 'cases' ? 'active' : ''; ?>">시공사례 이미지 등록</a>
            </div>
            <?php endif; ?>
            <a href="<?php echo $ap; ?>community/index.php" class="<?php echo $active === 'community' ? 'active' : ''; ?>"><i class="fa-solid fa-comments"></i> 커뮤니티</a>
            <?php if ($active === 'community'): 
                $active_sub = (isset($active_sub) ? $active_sub : 'list');
                $curr_board = isset($_GET['board']) ? $_GET['board'] : (isset($filter) ? $filter : (isset($cur_board) ? $cur_board : ''));
                $lnb_boards = function_exists('sql_one') ? sql_one('board_config', 'brd_id, brd_name', 'order by sort_order asc, brd_id asc') : [];
            ?>
            <div class="adm-sub-nav">
                <a href="<?php echo $ap; ?>community/index.php" class="<?php echo ($active_sub === 'list' && empty($curr_board)) ? 'active' : ''; ?>">게시물목록</a>
                <?php if (!empty($lnb_boards)): ?>
                    <?php foreach ($lnb_boards as $lb): 
                        $lb_id = $lb['brd_id'];
                        $lb_name = $lb['brd_name'];
                        $is_b_active = ($active_sub === 'list' && $curr_board === $lb_id);
                    ?>
                        <a href="<?php echo $ap; ?>community/index.php?board=<?php echo urlencode($lb_id); ?>" class="sub-item <?php echo $is_b_active ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($lb_name); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="<?php echo $ap; ?>community/config_list.php" class="<?php echo $active_sub === 'config' ? 'active' : ''; ?>">게시판 환경설정</a>
            </div>
            <?php endif; ?>
            <a href="<?php echo $ap; ?>category/list.php" class="<?php echo $active === 'settings' ? 'active' : ''; ?>"><i class="fa-solid fa-gears"></i> 설정</a>
            <?php if ($active === 'settings'): $active_sub = (isset($active_sub) ? $active_sub : 'category'); ?>
            <div class="adm-sub-nav">
                <a href="<?php echo $ap; ?>category/list.php" class="<?php echo $active_sub === 'category' ? 'active' : ''; ?>">카테고리관리</a>
                <a href="<?php echo $ap; ?>code/list.php" class="<?php echo $active_sub === 'code' ? 'active' : ''; ?>">코드관리</a>
            </div>
            <?php endif; ?>
            <a href="<?php echo (isset($site_path_prefix) ? $site_path_prefix : '../') . 'index.php'; ?>" target="_blank" style="margin-top:10px; border-top:1px solid rgba(255,255,255,0.08); padding-top:16px;"><i class="fa-solid fa-arrow-up-right-from-square"></i> 사이트 바로가기</a>
        </nav>
        <div class="adm-sidebar-foot">© 2026 TINTING MASTER Admin</div>
    </aside>

    <div class="adm-main">
        <div class="adm-topbar">
            <h1><?php echo isset($page_title) ? htmlspecialchars($page_title) : '관리자'; ?></h1>
            <div class="adm-topbar-user">
                <span class="grade-badge"><?php echo htmlspecialchars((isset($_SESSION['s_adm_grade']) ? $_SESSION['s_adm_grade'] : '')); ?></span>
                <span><i class="fa-solid fa-circle-user"></i> <?php echo htmlspecialchars((isset($_SESSION['s_adm_name']) ? $_SESSION['s_adm_name'] : '')); ?>님</span>
                <a href="<?php echo $ap; ?>logout.php">로그아웃</a>
            </div>
        </div>
        <div class="adm-content">
