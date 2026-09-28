<?php
if (session_status() === PHP_SESSION_NONE) {
    $script_path = str_replace('\\', '/', (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : ''));
    $dir_path    = str_replace('\\', '/', __DIR__);
    $req_uri     = str_replace('\\', '/', (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''));
    if (strpos($script_path, '/adm/') !== false || strpos($req_uri, '/adm/') !== false || strpos($dir_path, '/adm') !== false) {
        session_name('APT_ADMIN_SESS');
    } else {
        session_name('APT_FRONT_SESS');
    }
    session_start();
}
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', __DIR__ . '/..');
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | 틴팅 마스터 VULUX' : '틴팅 마스터 | 아파트 베란다 & 건물 썬팅 전문 플랫폼'; ?></title>
    
    <!-- Pretendard WOFF2 Font Application -->
    <style>
        @font-face {
            font-family: 'Pretendard';
            font-weight: 400;
            font-style: normal;
            font-display: swap;
            src: url('https://fastly.jsdelivr.net/gh/Project-Noonnu/noonfonts_2107@1.1/Pretendard-Regular.woff2') format('woff2');
        }
        @font-face {
            font-family: 'Pretendard';
            font-weight: 600;
            font-style: normal;
            font-display: swap;
            src: url('https://fastly.jsdelivr.net/gh/Project-Noonnu/noonfonts_2107@1.1/Pretendard-SemiBold.woff2') format('woff2');
        }
        @font-face {
            font-family: 'Pretendard';
            font-weight: 700;
            font-style: normal;
            font-display: swap;
            src: url('https://fastly.jsdelivr.net/gh/Project-Noonnu/noonfonts_2107@1.1/Pretendard-Bold.woff2') format('woff2');
        }
        @font-face {
            font-family: 'Pretendard';
            font-weight: 900;
            font-style: normal;
            font-display: swap;
            src: url('https://fastly.jsdelivr.net/gh/Project-Noonnu/noonfonts_2107@1.1/Pretendard-Black.woff2') format('woff2');
        }

        :root {
            --primary: #00B4D8;
            --primary-dark: #0077B6;
            --primary-light: #E0F7FA;
            --secondary: #0F172A;
            --accent: #FF6B35;
            --accent-hover: #E85D04;
            --bg-dark: #F0F9FF;
            --card-bg-dark: #FFFFFF;
            --text-main: #1E293B;
            --text-muted: #475569;
            --bg-light: #F8FAFC;
            --border-color: #E2E8F0;
            --glass-bg: rgba(255, 255, 255, 0.95);
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 12px 28px -6px rgba(0, 180, 216, 0.12);
            --shadow-lg: 0 24px 40px -10px rgba(0, 180, 216, 0.2);
            --radius-md: 14px;
            --radius-lg: 24px;
            --radius-full: 9999px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Pretendard', sans-serif; -webkit-font-smoothing: antialiased; }
        html { scroll-behavior: smooth; }
        body { background-color: #FFFFFF; color: var(--text-main); line-height: 1.6; overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        /* Top Banner Bar */
        .top-banner { background: linear-gradient(90deg, #0077B6 0%, #00B4D8 50%, #0096C7 100%); color: #ffffff; padding: 9px 16px; font-size: 0.875rem; text-align: center; display: flex; justify-content: center; align-items: center; gap: 12px; font-weight: 600; }
        .top-banner .badge { background: var(--accent); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 800; }

        /* Header Navigation (Full Width 100%) */
        .header { position: sticky; top: 0; z-index: 1000; background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-bottom: 1px solid var(--border-color); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .header.scrolled { padding-top: 0; padding-bottom: 0; background: rgba(255, 255, 255, 0.98); box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.1); }
        
        .header-container { width: 100%; max-width: 100%; margin: 0 auto; padding: 14px 40px; display: flex; justify-content: space-between; align-items: center; transition: padding 0.3s ease; }
        .header.scrolled .header-container { padding: 10px 40px; }

        .logo-area { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
        .logo-img { height: 48px; max-width: 200px; object-fit: contain; display: block; border-radius: 12px; transition: all 0.3s ease; }
        .header.scrolled .logo-img { height: 40px; border-radius: 10px; }

        /* Modern Premium NEXFIL-style Text Logo */
        .text-logo-brand, .nexfil-logo-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            user-select: none;
            flex-shrink: 0;
            transition: transform 0.25s ease;
        }
        .text-logo-brand:hover, .nexfil-logo-link:hover {
            transform: scale(1.02);
        }
        .nexfil-logo-svg {
            height: 38px;
            width: auto;
            max-width: 250px;
            transition: height 0.3s ease;
        }
        .header.scrolled .nexfil-logo-svg {
            height: 32px;
        }
        .text-logo-icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: linear-gradient(135deg, #0077B6 0%, #00B4D8 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            box-shadow: 0 4px 14px rgba(0, 180, 216, 0.35);
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        .header.scrolled .text-logo-icon {
            width: 34px;
            height: 34px;
            font-size: 1.05rem;
            border-radius: 9px;
        }
        .text-logo-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.1;
        }
        .text-logo-main {
            font-size: 1.35rem;
            font-weight: 900;
            letter-spacing: -0.8px;
            color: #0F172A;
            display: flex;
            align-items: center;
            gap: 2px;
            transition: font-size 0.3s ease;
        }
        .text-logo-main .accent {
            background: linear-gradient(135deg, #0077B6 0%, #00B4D8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 900;
        }
        .text-logo-sub {
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #0077B6;
            text-transform: uppercase;
            margin-top: 2px;
            transition: font-size 0.3s ease;
        }
        .header.scrolled .text-logo-main {
            font-size: 1.2rem;
        }
        .header.scrolled .text-logo-sub {
            font-size: 0.62rem;
        }

        .gnb { display: flex; flex: 1; justify-content: center; gap: 40px; align-items: center; padding: 0 32px; }
        .gnb li { position: relative; }
        .gnb li a { font-size: 0.95rem; font-weight: 700; color: #334155; transition: all 0.25s ease; position: relative; padding: 8px 4px; display: inline-flex; align-items: center; gap: 6px; }
        .gnb li a:hover, .gnb li a.active { color: var(--primary-dark); }
        .gnb li a::after { content: ''; position: absolute; bottom: 0; left: 50%; width: 0; height: 3px; background: linear-gradient(90deg, var(--primary) 0%, #38BDF8 100%); border-radius: 3px; transition: all 0.3s ease; transform: translateX(-50%); }
        .gnb li a:hover::after, .gnb li a.active::after { width: 100%; }

        .gnb-badge { font-size: 0.65rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; line-height: 1; display: inline-block; }
        .gnb-badge.hot { background: #EF4444; color: white; animation: gnbPulse 1.8s infinite; }
        .gnb-badge.new { background: #10B981; color: white; }
        @keyframes gnbPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }

        .header-actions { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; font-size: 0.95rem; font-weight: 700; border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s ease; border: none; outline: none; }
        .btn-outline { background: transparent; border: 1.5px solid var(--primary-dark); color: var(--primary-dark); }
        .btn-outline:hover { background: var(--primary-light); }
        .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; box-shadow: 0 4px 12px rgba(0, 180, 216, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0, 180, 216, 0.4); }
        .btn-accent { background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%); color: white; box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3); }
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; }

        .phone-call-btn { display: flex; align-items: center; gap: 8px; background: #F1F5F9; padding: 8px 16px; border-radius: var(--radius-full); font-weight: 800; color: var(--secondary); font-size: 0.95rem; }
        .phone-call-btn i { color: var(--primary-dark); }
        .menu-toggle { display: none; font-size: 1.5rem; background: none; border: none; color: var(--text-main); cursor: pointer; }

        /* Off-Canvas Mobile Drawer */
        .mobile-drawer-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(6px); z-index: 2000; opacity: 0; visibility: hidden; transition: all 0.3s ease; }
        .mobile-drawer-overlay.open { opacity: 1; visibility: visible; }
        .mobile-drawer { position: fixed; top: 0; right: -320px; width: 300px; height: 100%; background: #FFFFFF; z-index: 2001; box-shadow: -10px 0 30px rgba(0,0,0,0.2); transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; justify-content: space-between; padding: 24px; overflow-y: auto; }
        .mobile-drawer.open { right: 0; }
        .drawer-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; }
        .drawer-close-btn { background: none; border: none; font-size: 1.8rem; color: var(--text-main); cursor: pointer; }
        .drawer-gnb { margin: 24px 0; display: flex; flex-direction: column; gap: 16px; }
        .drawer-gnb > li > a { font-size: 1.1rem; font-weight: 700; color: var(--text-main); display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px dashed #F1F5F9; }
        .drawer-actions { display: flex; flex-direction: column; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px; }

        /* Footer */
        .footer { background: #0F172A; color: #94A3B8; padding: 60px 24px 30px 24px; font-size: 0.9rem; border-top: 1px solid #1E293B; }
        .footer-container { max-width: 1280px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 40px; margin-bottom: 40px; }
        .footer-brand h4 { color: white; font-size: 1.4rem; font-weight: 900; margin-bottom: 12px; }
        .footer-links h5 { color: white; font-size: 1rem; margin-bottom: 16px; font-weight: 700; }
        .footer-links ul li { margin-bottom: 8px; }
        .footer-bottom { max-width: 1280px; margin: 0 auto; border-top: 1px solid #1E293B; padding-top: 24px; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; }

        /* Floating CTA */
        .floating-cta-bar { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border-top: 1px solid rgba(255, 255, 255, 0.1); padding: 12px 20px; z-index: 999; display: flex; justify-content: space-around; align-items: center; }
        .floating-cta-bar .cta-item { display: flex; flex-direction: column; align-items: center; color: white; font-size: 0.75rem; gap: 4px; font-weight: 600; }
        .floating-cta-bar .cta-item i { font-size: 1.2rem; color: #38BDF8; }
        .floating-cta-bar .cta-btn-main { background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%); color: white; padding: 10px 24px; border-radius: var(--radius-full); font-weight: 800; font-size: 0.9rem; border: none; cursor: pointer; }

        /* Modal Styles */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); z-index: 2000; display: none; align-items: center; justify-content: center; padding: 20px; }
        .modal-card { background: white; width: 100%; max-width: 500px; border-radius: var(--radius-lg); padding: 36px; position: relative; box-shadow: var(--shadow-lg); }
        .modal-close { position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.3rem; color: var(--text-muted); cursor: pointer; }

        /* Responsive Utilities & Mobile Breakpoints */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 1rem; }
        .horizontal-scroll-tab { display: flex; flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 8px; gap: 8px; scrollbar-width: none; }
        .horizontal-scroll-tab::-webkit-scrollbar { display: none; }

        @media (max-width: 1024px) {
            .gnb { display: none !important; }
            .menu-toggle { display: flex !important; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 10px; background: #F1F5F9; color: var(--secondary); font-size: 1.25rem; border: 1px solid var(--border-color); }
            .footer-container { grid-template-columns: 1fr 1fr; gap: 30px; }
        }
        @media (max-width: 768px) {
            .top-banner { font-size: 0.78rem; padding: 7px 12px; gap: 6px; }
            .header-container { padding: 10px 14px; }
            .logo-img { height: 36px; max-width: 140px; }
            .header-actions .btn-outline, 
            .header-actions .btn-primary { display: none !important; }
            .header-actions { gap: 8px; }
            .phone-call-btn { padding: 8px 10px; font-size: 0.85rem; border-radius: 8px; }
            .phone-call-btn span { display: none; }
            .footer-container { grid-template-columns: 1fr; gap: 24px; }
            .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
            .modal-card { padding: 24px 18px; max-width: 92vw; margin: 10px; }
            .mobile-grid-1col { grid-template-columns: 1fr !important; gap: 16px !important; }
            .floating-widgets { bottom: 74px !important; right: 12px !important; }
            .floating-widgets a, .floating-widgets div { width: 42px !important; height: 42px !important; font-size: 1.05rem !important; }

            /* Sub Hero & General Mobile Container Layout */
            .sub-hero, .hero-mobile-pad { padding: 36px 16px !important; }
            .sub-hero h1, .hero-mobile-pad h1 { font-size: 1.65rem !important; line-height: 1.3 !important; margin-bottom: 8px !important; }
            .sub-hero p, .hero-mobile-pad p { font-size: 0.95rem !important; }
            .container, .container-mobile-pad { padding: 24px 16px !important; }

            /* Calculator Card & Selected Product Mobile Styles */
            .calc-card-box { padding: 20px 16px !important; border-radius: 16px !important; }
            .selected-prod-box { flex-direction: column !important; text-align: center; padding: 16px !important; }
            .selected-prod-box img { margin: 0 auto; }
            .mobile-price-display { font-size: 2.1rem !important; word-break: break-all; }
            .mobile-btn-full { width: 100% !important; padding: 14px 16px !important; font-size: 0.95rem !important; justify-content: center; }
            .master-hero-box { padding: 28px 20px !important; gap: 24px !important; }
            .master-hero-box h2 { font-size: 1.65rem !important; }
            
            /* Horizontal Scroll Tabs */
            .mobile-tab-scroll, .horizontal-scroll-tab { display: flex !important; overflow-x: auto !important; -webkit-overflow-scrolling: touch; justify-content: flex-start !important; gap: 8px !important; padding-bottom: 8px !important; width: 100% !important; scrollbar-width: none; }
            .mobile-tab-scroll::-webkit-scrollbar, .horizontal-scroll-tab::-webkit-scrollbar { display: none; }
            .mobile-tab-scroll .btn, .horizontal-scroll-tab .btn, .horizontal-scroll-tab .gallery-cat-btn { flex-shrink: 0 !important; padding: 8px 14px !important; font-size: 0.85rem !important; white-space: nowrap !important; }

            /* Gallery Detail Mobile Styles */
            .case-detail-header { padding: 20px 16px !important; }
            .case-detail-header h1 { font-size: 1.45rem !important; line-height: 1.35 !important; }
            .case-detail-body { padding: 20px 16px !important; gap: 20px !important; }
            .case-comment-box { padding: 20px 16px !important; }

            /* Community Board Mobile Card View */
            .comm-filter-bar { flex-direction: column; align-items: stretch !important; gap: 12px !important; }
            .comm-search-box { width: 100% !important; }
            .comm-search-box input { flex: 1 !important; width: auto !important; }
            .comm-table thead, .main-auc-table thead { display: none !important; }
            .comm-table, .comm-table tbody, .comm-table tr, .comm-table td,
            .main-auc-table, .main-auc-table tbody, .main-auc-table tr, .main-auc-table td { display: block !important; width: 100% !important; box-sizing: border-box; }
            .comm-table tr, .main-auc-table tr { padding: 14px 12px !important; border-bottom: 1px solid #E2E8F0 !important; background: #fff !important; }
            .comm-table td, .main-auc-table td { padding: 2px 0 !important; border: none !important; text-align: left !important; }
            .comm-table td.col-num, .comm-table td.col-author, .comm-table td.col-date, .comm-table td.col-views,
            .main-auc-table .desktop-only-td { display: none !important; }
            .main-auc-table .mobile-auc-bottom { display: flex !important; }
            .main-auc-table { min-width: 100% !important; }
            .comm-table td.col-cat { margin-bottom: 6px !important; }
            .comm-table td.col-title a { font-size: 1rem !important; line-height: 1.4 !important; }
            .mobile-post-meta { display: flex !important; flex-wrap: wrap; gap: 12px; font-size: 0.8rem; color: #64748B; margin-top: 8px; }

            /* Community Detail Page Mobile */
            .comm-detail-header { padding: 20px 16px !important; }
            .comm-detail-header h1 { font-size: 1.4rem !important; }
            .comm-detail-content { padding: 20px 16px !important; font-size: 1rem !important; }
        }
        @media (max-width: 480px) {
            .top-banner span:nth-child(2) { display: inline-block; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: middle; }
            .mobile-drawer { width: min(300px, 85vw); }
            input[type="text"], input[type="tel"], input[type="email"], input[type="date"], select, textarea { font-size: 16px !important; }
        }
    
        /* Mobile Drawer Accordion Sub-Menu Styling */
        .drawer-gnb { margin: 16px 0; display: flex; flex-direction: column; gap: 4px; }
        .drawer-gnb li { border-bottom: 1px solid #F1F5F9; list-style: none; }
        .drawer-menu-item, .drawer-single-item {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 6px;
            cursor: pointer;
            user-select: none;
            transition: background 0.2s, color 0.2s;
            text-decoration: none;
        }
        .drawer-menu-item:hover, .drawer-single-item:hover {
            background: #F8FAFC;
            color: var(--primary-dark);
        }
        .drawer-menu-item.active {
            color: var(--primary-dark);
            background: #F0F9FF;
            border-radius: 8px;
        }
        .drawer-arrow {
            font-size: 0.85rem;
            color: #94A3B8;
            transition: transform 0.25s ease;
        }
        .drawer-menu-item.active .drawer-arrow {
            color: var(--primary-dark);
        }
        .drawer-sub-menu {
            display: none;
            background: #F0F9FF;
            border-radius: 12px;
            padding: 6px 12px;
            margin: 4px 0 10px 0;
            list-style: none;
            border: 1px solid #BAE6FD;
        }
        .drawer-sub-menu li {
            border-bottom: 1px dashed #CBD5E1;
        }
        .drawer-sub-menu li:last-child {
            border-bottom: none;
        }
        .drawer-sub-menu li a {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 10px !important;
            padding: 10px 8px !important;
            font-size: 0.92rem;
            font-weight: 600;
            color: #334155;
            text-decoration: none;
            transition: color 0.2s;
            border-bottom: none !important;
        }
        .drawer-sub-menu li a:hover, .drawer-sub-menu li a.active {
            color: var(--primary-dark);
            font-weight: 800;
        }
        @media (min-width: 769px) {
            .main-auc-table .mobile-only-badge { display: none !important; }
        }

        /* GNB Dropdown Submenu Styling */
        .gnb li { position: relative; }
        .gnb li .sub-menu {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(12px);
            background: #FFFFFF;
            border: 1px solid #BAE6FD;
            border-radius: 14px;
            padding: 10px 0;
            min-width: 160px;
            box-shadow: 0 15px 35px rgba(0, 180, 216, 0.15);
            opacity: 0;
            visibility: hidden;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1100;
        }
        .gnb li:hover .sub-menu, .gnb li.open .sub-menu {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }
        .gnb li .sub-menu li { display: block; width: 100%; text-align: left; }
        .gnb li .sub-menu li a {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 6px;
            padding: 9px 18px;
            font-size: 0.88rem;
            font-weight: 700;
            color: #334155;
            text-align: left;
            transition: background 0.2s, color 0.2s;
            white-space: nowrap;
        }
        .gnb li .sub-menu li a:hover {
            background: #F0F9FF;
            color: #0077B6;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
