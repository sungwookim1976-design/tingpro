<?php
$page_title = "커뮤니티 | 틴팅 마스터 VULUX";
$active_menu = "community";
$path_prefix = "../";

$active_tab = isset($_GET['tab']) && in_array($_GET['tab'], ['notice', 'news', 'faq', 'reviews']) ? $_GET['tab'] : 'all';

include_once __DIR__ . "/../inc/dbconn.php";
include_once __DIR__ . "/../inc/head.php";
include_once __DIR__ . "/../inc/header.php";

// board_type별 고정 스타일/작성자 라벨 (community_posts에는 작성자 컬럼이 없어 board 단위로 대체)
$board_style = [
    'notice'  => ['badge_bg' => '#E0F7FA', 'badge_color' => '#0077B6', 'cat_name' => '공지사항', 'author' => '틴팅프로 관리자'],
    'news'    => ['badge_bg' => '#ECFDF5', 'badge_color' => '#059669', 'cat_name' => '뉴스',     'author' => '홍보팀'],
    'faq'     => ['badge_bg' => '#FFEDD5', 'badge_color' => '#EA580C', 'cat_name' => 'FAQ',      'author' => '고객지원팀'],
    'consult' => ['badge_bg' => '#E0F2FE', 'badge_color' => '#0284C7', 'cat_name' => '상담문의',  'author' => '고객상담팀'],
];

// "고객후기"는 관리자에서 관리하는 게시판이 아니라 기존 정적 데이터를 그대로 유지
$reviews_static = [
    [
        "id" => 5, "cat" => "reviews", "cat_name" => "고객후기",
        "badge_bg" => "#FEF3C7", "badge_color" => "#D97706",
        "title" => "[강남 자이아파트 34평] 남향 베란다 뷰럭스 스퍼터 Dual 99 시공 후기",
        "date" => "2026.09.10", "author" => "김*우 고객님", "views" => 1650, "has_img" => true,
        "summary" => "여름에 햇빛이 너무 뜨거워서 고민하다 시공했는데 체감 온도 차이가 어마어마합니다! 강력 추천합니다."
    ],
];

function comm_summary($row) {
    $text = ($row['board_type'] === 'faq' && $row['answer'] !== '') ? $row['answer'] : trim(strip_tags($row['content']));
    $text = preg_replace('/\s+/', ' ', $text);
    if (mb_strlen($text) > 80) $text = mb_substr($text, 0, 80) . '...';
    return $text;
}

$filtered_posts = [];

if ($active_tab === 'reviews') {
    $filtered_posts = $reviews_static;
} else {
    $board_types = $active_tab === 'all' ? array_keys($board_style) : [$active_tab];
    $in_list = implode(',', array_map(function ($t) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $t) . "'";
    }, $board_types));

    $rows = sql_one('community_posts', '*', "and board_type in ($in_list) and state=1 order by no desc");
    foreach ($rows as $r) {
        $style = $board_style[$r['board_type']];
        $filtered_posts[] = [
            "id" => (int)$r['no'],
            "cat" => $r['board_type'],
            "cat_name" => $style['cat_name'],
            "badge_bg" => $style['badge_bg'],
            "badge_color" => $style['badge_color'],
            "title" => $r['title'],
            "date" => date('Y.m.d', strtotime($r['reg_date'])),
            "author" => $style['author'],
            "views" => (int)$r['views'],
            "has_img" => strpos($r['content'], '<img') !== false,
            "summary" => comm_summary($r),
        ];
    }

    if ($active_tab === 'all') {
        $filtered_posts = array_merge($filtered_posts, $reviews_static);
        usort($filtered_posts, function ($a, $b) { return strcmp($b['date'], $a['date']); });
    }
}
?>

<div style="background:linear-gradient(135deg, #0077B6 0%, #00B4D8 100%); padding:60px 24px; text-align:center; color:white;">
    <span style="background:rgba(255,255,255,0.2); font-size:0.85rem; padding:4px 14px; border-radius:20px; font-weight:700; letter-spacing:1px; text-transform:uppercase;">VULUX Community</span>
    <h1 style="font-size:2.5rem; font-weight:900; margin:12px 0 8px 0;">틴팅 마스터 커뮤니티</h1>
    <p style="font-size:1.1rem; opacity:0.9; max-width:600px; margin:0 auto;">공지사항, 브랜드 소식, 자주 묻는 질문 및 생생한 고객 후기를 한눈에 확인하세요.</p>
</div>

<div class="container" style="max-width:1280px; margin:0 auto; padding:60px 24px;">

    <!-- Category Filter Tabs -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:32px; border-bottom:2px solid #E2E8F0; padding-bottom:16px;">
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="index.php?tab=all" class="btn <?php echo $active_tab === 'all' ? 'btn-primary' : 'btn-outline'; ?>" style="padding:10px 20px;">
                <i class="fa-solid fa-list-ul"></i> 전체보기
            </a>
            <a href="index.php?tab=notice" class="btn <?php echo $active_tab === 'notice' ? 'btn-primary' : 'btn-outline'; ?>" style="padding:10px 20px;">
                <i class="fa-solid fa-bullhorn"></i> 공지사항
            </a>
            <a href="index.php?tab=news" class="btn <?php echo $active_tab === 'news' ? 'btn-primary' : 'btn-outline'; ?>" style="padding:10px 20px;">
                <i class="fa-solid fa-newspaper"></i> 뉴스
            </a>
            <a href="index.php?tab=faq" class="btn <?php echo $active_tab === 'faq' ? 'btn-primary' : 'btn-outline'; ?>" style="padding:10px 20px;">
                <i class="fa-solid fa-circle-question"></i> FAQ
            </a>
            <a href="index.php?tab=reviews" class="btn <?php echo $active_tab === 'reviews' ? 'btn-primary' : 'btn-outline'; ?>" style="padding:10px 20px;">
                <i class="fa-solid fa-star"></i> 고객후기
            </a>
        </div>

        <div style="display:flex; align-items:center; gap:8px;">
            <input type="text" id="commSearch" placeholder="게시글 검색..." style="padding:10px 16px; border:1px solid #CBD5E1; border-radius:var(--radius-md); font-size:0.9rem; width:220px;">
            <button class="btn btn-primary" style="padding:10px 16px;"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>
    </div>

    <!-- Post Table List -->
    <div style="background:white; border:1px solid #E2E8F0; border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-sm);">
        <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
                <tr style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; color:#475569; font-size:0.9rem; font-weight:800;">
                    <th style="padding:16px 20px; width:70px; text-align:center;">번호</th>
                    <th style="padding:16px 20px; width:110px; text-align:center;">분류</th>
                    <th style="padding:16px 20px;">제목</th>
                    <th style="padding:16px 20px; width:130px; text-align:center;">작성자</th>
                    <th style="padding:16px 20px; width:110px; text-align:center;">작성일</th>
                    <th style="padding:16px 20px; width:80px; text-align:center;">조회수</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($filtered_posts)): ?>
                <tr><td colspan="6" style="padding:40px; text-align:center; color:#94A3B8;">등록된 게시물이 없습니다.</td></tr>
                <?php endif; ?>
                <?php foreach ($filtered_posts as $post): ?>
                <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.2s ease;" onmouseover="this.style.background='#F0F9FF';" onmouseout="this.style.background='white';">
                    <td style="padding:18px 20px; text-align:center; color:#94A3B8; font-weight:600;"><?php echo $post['id']; ?></td>
                    <td style="padding:18px 20px; text-align:center;">
                        <span style="background:<?php echo $post['badge_bg']; ?>; color:<?php echo $post['badge_color']; ?>; font-size:0.75rem; font-weight:800; padding:4px 10px; border-radius:4px;">
                            <?php echo $post['cat_name']; ?>
                        </span>
                    </td>
                    <td style="padding:18px 20px;">
                        <a href="view.php?id=<?php echo $post['id']; ?>&cat=<?php echo urlencode($post['cat']); ?>" style="text-decoration:none; color:#0F172A; font-weight:700; font-size:1.02rem; display:inline-flex; align-items:center; gap:8px;">
                            <?php echo htmlspecialchars($post['title']); ?>
                            <?php if ($post['has_img']): ?>
                                <i class="fa-solid fa-image" style="color:#0077B6; font-size:0.85rem;" title="이미지 첨부"></i>
                            <?php endif; ?>
                        </a>
                        <p style="color:#64748B; font-size:0.85rem; margin-top:4px; font-weight:400;"><?php echo htmlspecialchars($post['summary']); ?></p>
                    </td>
                    <td style="padding:18px 20px; text-align:center; color:#475569; font-size:0.9rem; font-weight:600;"><?php echo htmlspecialchars($post['author']); ?></td>
                    <td style="padding:18px 20px; text-align:center; color:#64748B; font-size:0.85rem;"><?php echo $post['date']; ?></td>
                    <td style="padding:18px 20px; text-align:center; color:#94A3B8; font-size:0.85rem;"><?php echo number_format($post['views']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="display:flex; justify-content:center; align-items:center; gap:8px; margin-top:40px;">
        <button class="btn btn-outline" style="padding:8px 14px;" disabled><i class="fa-solid fa-chevron-left"></i></button>
        <button class="btn btn-primary" style="padding:8px 14px;">1</button>
        <button class="btn btn-outline" style="padding:8px 14px;"><i class="fa-solid fa-chevron-right"></i></button>
    </div>
</div>

<?php
include_once __DIR__ . "/../inc/footer.php";
?>
