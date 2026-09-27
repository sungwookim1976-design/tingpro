<?php
$page_title = "커뮤니티 상세보기 | 틴팅 마스터 VULUX";
$active_menu = "community";
$path_prefix = "../";

include_once __DIR__ . "/../inc/dbconn.php";

$post_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$cat     = isset($_GET['cat']) ? trim($_GET['cat']) : '';

$board_style = [
    'notice'  => ['badge_bg' => '#E0F7FA', 'badge_color' => '#0077B6', 'cat_name' => '공지사항', 'author' => '틴팅프로 관리자'],
    'news'    => ['badge_bg' => '#ECFDF5', 'badge_color' => '#059669', 'cat_name' => '뉴스',     'author' => '홍보팀'],
    'faq'     => ['badge_bg' => '#FFEDD5', 'badge_color' => '#EA580C', 'cat_name' => 'FAQ',      'author' => '고객지원팀'],
    'consult' => ['badge_bg' => '#E0F2FE', 'badge_color' => '#0284C7', 'cat_name' => '상담문의',  'author' => '고객상담팀'],
];

// "고객후기"는 관리자 게시판이 없어 기존 정적 데이터 유지
$reviews_static = [
    5 => [
        "title" => "[강남 자이아파트 34평] 남향 베란다 뷰럭스 스퍼터 Dual 99 시공 후기",
        "cat_name" => "고객후기", "badge_bg" => "#FEF3C7", "badge_color" => "#D97706",
        "date" => "2026.09.10 16:45", "author" => "김*우 고객님", "views" => 1652,
        "img" => "../imgs/product_diy_kit.png",
        "content" => "저희 집이 남향이라 여름에 거실로 들어오는 눈부심과 열기가 너무 심해서 에어컨을 켜도 실내가 금방 시원해지지 않았습니다.<br><br>
        틴팅 마스터 사이트에서 실시간 견적을 뽑고 마스터분 방문 실측 후 VULUX Sputter Dual 99로 시공받았는데 정말 신세계네요!<br><br>
        바깥 조망은 훨씬 선명해지고 외부 시선 차단 효과도 확실합니다. 마스터분께서 먼지 한 톨 없이 너무 꼼꼼하게 시공해주셔서 정말 만족스럽습니다.",
        "files" => [],
    ],
];

$post = null;
if ($cat === 'reviews') {
    $post = isset($reviews_static[$post_id]) ? $reviews_static[$post_id] : reset($reviews_static);
} else {
    $row = sql_one_one('community_posts', '*', "and no=" . $post_id . " and state=1");
    if ($row) {
        sql_up('community_posts', 'views=views+1', "and no=" . $post_id);
        $style = isset($board_style[$row['board_type']]) ? $board_style[$row['board_type']] : ['badge_bg' => '#F1F5F9', 'badge_color' => '#334155', 'cat_name' => $row['board_type'], 'author' => '관리자'];
        $files = sql_one('community_post_file', '*', "and post_no=" . $post_id . " order by idx asc");
        $post = [
            "title" => $row['title'],
            "cat_name" => $style['cat_name'],
            "badge_bg" => $style['badge_bg'],
            "badge_color" => $style['badge_color'],
            "date" => date('Y.m.d H:i', strtotime($row['reg_date'])),
            "author" => $style['author'],
            "views" => (int)$row['views'] + 1,
            "img" => "",
            "content" => ($row['board_type'] === 'faq' && $row['answer'] !== '')
                ? '<p style="font-weight:700; color:#0077B6; margin-bottom:16px;"><i class="fa-solid fa-circle-check"></i> ' . htmlspecialchars($row['answer']) . '</p>' . $row['content']
                : $row['content'],
            "files" => $files,
            "brd_id" => $row['board_type'],
        ];
    }
}

if (!$post) {
    $post = reset($reviews_static);
}

include_once __DIR__ . "/../inc/head.php";
include_once __DIR__ . "/../inc/header.php";
?>

<div style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:24px;">
    <div style="max-width:1000px; margin:0 auto; font-size:0.9rem; color:#64748B;">
        <a href="../index.php" style="color:#64748B; text-decoration:none;">홈</a> &gt;
        <a href="index.php" style="color:#64748B; text-decoration:none;">커뮤니티</a> &gt;
        <span style="color:#0F172A; font-weight:700;"><?php echo $post['cat_name']; ?></span>
    </div>
</div>

<div class="container" style="max-width:1000px; margin:0 auto; padding:60px 24px;">

    <div style="background:white; border:1px solid #E2E8F0; border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-md);">

        <div style="padding:36px 40px; border-bottom:1px solid #E2E8F0; background:#FAFDFE;">
            <div style="display:inline-block; background:<?php echo $post['badge_bg']; ?>; color:<?php echo $post['badge_color']; ?>; font-size:0.82rem; font-weight:800; padding:6px 14px; border-radius:6px; margin-bottom:14px;">
                <?php echo $post['cat_name']; ?>
            </div>
            <h1 style="font-size:2.1rem; font-weight:900; color:#0F172A; line-height:1.3; margin-bottom:18px;">
                <?php echo htmlspecialchars($post['title']); ?>
            </h1>
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; color:#64748B; font-size:0.9rem; border-top:1px solid #E2E8F0; padding-top:16px;">
                <div style="display:flex; gap:20px; align-items:center;">
                    <span><i class="fa-solid fa-user" style="color:#0077B6;"></i> <strong><?php echo htmlspecialchars($post['author']); ?></strong></span>
                    <span><i class="fa-regular fa-clock"></i> <?php echo $post['date']; ?></span>
                </div>
                <div>
                    <span><i class="fa-regular fa-eye"></i> 조회수 <strong><?php echo number_format($post['views']); ?></strong></span>
                </div>
            </div>
        </div>

        <div style="padding:40px; color:#334155; font-size:1.1rem; line-height:1.8;">
            <?php if (!empty($post['img'])): ?>
                <div style="text-align:center; margin-bottom:32px; background:#F8FAFC; padding:20px; border-radius:12px; border:1px solid #E2E8F0;">
                    <img src="<?php echo $post['img']; ?>" alt="게시글 이미지" style="max-width:100%; max-height:450px; border-radius:8px; box-shadow:0 8px 20px rgba(0,0,0,0.08);">
                </div>
            <?php endif; ?>

            <div>
                <?php echo $post['content']; ?>
            </div>

            <?php if (!empty($post['files'])): ?>
            <div style="margin-top:32px; padding:20px; background:#F8FAFC; border-radius:12px; border:1px solid #E2E8F0;">
                <p style="font-weight:800; color:#0F172A; margin-bottom:10px; font-size:0.95rem;"><i class="fa-solid fa-paperclip"></i> 첨부파일</p>
                <?php foreach ($post['files'] as $f): ?>
                    <div style="margin-bottom:6px;">
                        <a href="../uploads/<?php echo htmlspecialchars($post['brd_id']); ?>/<?php echo htmlspecialchars($f['save_name']); ?>" download="<?php echo htmlspecialchars($f['ori_name']); ?>" style="color:#0077B6; font-weight:600; font-size:0.92rem; text-decoration:none;">
                            <i class="fa-solid fa-download"></i> <?php echo htmlspecialchars($f['ori_name']); ?> (<?php echo round($f['file_size'] / 1024, 1); ?>KB)
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div style="padding:24px 40px; background:#F8FAFC; border-top:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <a href="index.php" class="btn btn-outline" style="padding:12px 24px; font-weight:700; background:white;">
                <i class="fa-solid fa-arrow-left"></i> 목록으로 돌아가기
            </a>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . "/../inc/footer.php";
?>
