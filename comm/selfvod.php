<?php
$page_title = "DIY 셀프 시공방법 영상 | TINTING PRO 커뮤니티";
$active_menu = "community";
$active_tab = "selfvod";
$path_prefix = "../";

include_once __DIR__ . "/../inc/dbconn.php";
include_once __DIR__ . "/../inc/head.php";
include_once __DIR__ . "/../inc/header.php";

// 기본 샘플 VOD 목록 (DB 게시글이 없거나 적을 때 풍부한 보락/미디어 가이드 제공)
$sample_vods = [
    [
        "id" => 101,
        "title" => "[DIY 셀프시공] 아파트 베란다 창문 단열필름 10분 완벽 부착 노하우",
        "cat_name" => "자가설치 가이드",
        "badge_bg" => "#ECFDF5",
        "badge_color" => "#059669",
        "date" => "2026.09.25",
        "views" => 3420,
        "duration" => "06:45",
        "video_url" => "https://www.youtube.com/embed/dQw4w9WgXcQ", // 가상 또는 샘플 엠베드
        "thumb" => "https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=800&q=80",
        "summary" => "초보자도 실패 없는 세제 퐁퐁수 비율과 헤라 스크래퍼를 활용한 공기 방울 및 수분 제거 핵심 꿀팁을 전수해 드립니다."
    ],
    [
        "id" => 102,
        "title" => "[필름 재단 팁] 유리에 꼭 맞게 깔끔하게 이중재단하는 정밀 기법",
        "cat_name" => "재단 노하우",
        "badge_bg" => "#E0F7FA",
        "badge_color" => "#0077B6",
        "date" => "2026.09.20",
        "views" => 2850,
        "duration" => "04:30",
        "video_url" => "https://www.youtube.com/embed/dQw4w9WgXcQ",
        "thumb" => "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80",
        "summary" => "유리 실리콘 마감 부위 간섭 없이 칼날 각도 45도를 유지하여 깔끔한 마감선을 만드는 틴팅 전문가의 재단법."
    ],
    [
        "id" => 103,
        "title" => "[자동차 틴팅] 측면 유리 자가 시공 시 필름 찝힘 방지 가이드",
        "cat_name" => "차량용 시공",
        "badge_bg" => "#FEF3C7",
        "badge_color" => "#D97706",
        "date" => "2026.09.18",
        "views" => 4190,
        "duration" => "08:15",
        "video_url" => "https://www.youtube.com/embed/dQw4w9WgXcQ",
        "thumb" => "https://images.unsplash.com/photo-1617814076367-b759c7d7e738?auto=format&fit=crop&w=800&q=80",
        "summary" => "도어 트림 분리 없이 펠트 몰딩 사이로 정밀하게 필름을 밀어넣고 쉐이빙 마감하는 단계별 영상 가이드."
    ],
    [
        "id" => 104,
        "title" => "[필수 도구 안내] 셀프 시공을 위한 DIY 전용 키트 200% 활용법",
        "cat_name" => "도구 활용법",
        "badge_bg" => "#F3E8FF",
        "badge_color" => "#7C3AED",
        "date" => "2026.09.12",
        "views" => 1980,
        "duration" => "05:10",
        "video_url" => "https://www.youtube.com/embed/dQw4w9WgXcQ",
        "thumb" => "https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80",
        "summary" => "우레탄 스퀴지, 수분제거용 하드헤라, 몰딩 삽입기 등 TINTING PRO DIY 도구 세트 사용법 대공개."
    ],
    [
        "id" => 105,
        "title" => "[건물 외부 시선차단] 단방향 미러 필름 야간/주간 시야 비교 및 부착 영상",
        "cat_name" => "건물 시공",
        "badge_bg" => "#FFEDD5",
        "badge_color" => "#EA580C",
        "date" => "2026.09.05",
        "views" => 5120,
        "duration" => "07:50",
        "video_url" => "https://www.youtube.com/embed/dQw4w9WgXcQ",
        "thumb" => "https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80",
        "summary" => "외부 시선은 완벽히 반사 차단하고 내부에서는 또렷한 뷰를 유지하는 스퍼터 미러 필름 셀프 설치 영상."
    ],
    [
        "id" => 106,
        "title" => "[시공 Q&A] 시공 후 잔여 기포나 물꽃 현상 빠른 해결 노하우",
        "cat_name" => "문제 해결",
        "badge_bg" => "#E0F2FE",
        "badge_color" => "#0284C7",
        "date" => "2026.09.01",
        "views" => 3670,
        "duration" => "03:45",
        "video_url" => "https://www.youtube.com/embed/dQw4w9WgXcQ",
        "thumb" => "https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80",
        "summary" => "시공 직후 남아있는 미세 수분 방울의 건조 기간과 히팅건/드라이어를 활용한 안전한 마무리 요령."
    ]
];

// DB community_posts (board_type='selfvod') 데이터 조회
$db_vods = [];
if (isset($conn) && $conn) {
    $rows = sql_one('community_posts', '*', "and board_type='selfvod' and state=1 order by no desc");
    foreach ($rows as $r) {
        $thumb = "https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=800&q=80";
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $r['content'], $m)) {
            $thumb = $m[1];
        }
        $summary = trim(strip_tags($r['content']));
        if (mb_strlen($summary) > 80) $summary = mb_substr($summary, 0, 80) . '...';

        // 유튜브 URL 파싱 -> embed URL로 변환
        $raw_yt = !empty($r['youtube_url']) ? trim($r['youtube_url']) : "https://www.youtube.com/embed/dQw4w9WgXcQ";
        $yt_url = $raw_yt;
        if (strpos($raw_yt, 'watch?v=') !== false) {
            $yt_url = preg_replace('/.*watch\?v=([a-zA-Z0-9_-]+).*/', 'https://www.youtube.com/embed/$1', $raw_yt);
        } elseif (strpos($raw_yt, 'youtu.be/') !== false) {
            $yt_url = preg_replace('/.*youtu\.be\/([a-zA-Z0-9_-]+).*/', 'https://www.youtube.com/embed/$1', $raw_yt);
        }

        $db_vods[] = [
            "id" => (int)$r['no'],
            "title" => $r['title'],
            "cat_name" => !empty($r['badge']) ? $r['badge'] : "DIY 셀프시공",
            "badge_bg" => "#ECFDF5",
            "badge_color" => "#059669",
            "date" => date('Y.m.d', strtotime($r['reg_date'])),
            "views" => (int)$r['views'],
            "duration" => "VOD",
            "video_url" => $yt_url,
            "thumb" => $thumb,
            "summary" => $summary,
            "is_db" => true
        ];
    }
}

// DB 데이터가 등록되어 있는 경우 DB 데이터를 최우선 리스팅
$all_vods = !empty($db_vods) ? $db_vods : $sample_vods;

// 검색어 필터링
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
if (!empty($search_query)) {
    $all_vods = array_filter($all_vods, function($item) use ($search_query) {
        return (mb_strpos($item['title'], $search_query) !== false || mb_strpos($item['summary'], $search_query) !== false);
    });
}
?>

<!-- Hero Banner -->
<div class="sub-hero hero-mobile-pad" style="background:linear-gradient(135deg, #059669 0%, #10B981 100%); padding:60px 24px; text-align:center; color:white;">
    <span style="background:rgba(255,255,255,0.2); font-size:0.85rem; padding:4px 14px; border-radius:20px; font-weight:700; letter-spacing:1px; text-transform:uppercase;">VULUX DIY SELF VOD</span>
    <h1 style="font-size:2.5rem; font-weight:900; margin:12px 0 8px 0;">DIY 셀프 시공방법 영상</h1>
    <p style="font-size:1.1rem; opacity:0.9; max-width:640px; margin:0 auto;">초보자도 따라 할 수 있는 윈도우 필름 셀프 시공 노하우와 전문가 재단 꿀팁을 생생한 영상으로 확인하세요.</p>
</div>

<div class="container container-mobile-pad" style="max-width:1280px; margin:0 auto; padding:60px 24px;">

    <!-- Category Filter Tabs -->
    <div class="comm-filter-bar" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:36px; border-bottom:2px solid #E2E8F0; padding-bottom:16px;">
        <div class="mobile-tab-scroll" style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="index.php?tab=all" class="btn btn-outline" style="padding:10px 20px;">
                <i class="fa-solid fa-list-ul"></i> 전체보기
            </a>
            <a href="index.php?tab=notice" class="btn btn-outline" style="padding:10px 20px;">
                <i class="fa-solid fa-bullhorn"></i> 공지사항
            </a>
            <a href="index.php?tab=news" class="btn btn-outline" style="padding:10px 20px;">
                <i class="fa-solid fa-newspaper"></i> 뉴스
            </a>
            <a href="index.php?tab=faq" class="btn btn-outline" style="padding:10px 20px;">
                <i class="fa-solid fa-circle-question"></i> FAQ
            </a>
            <a href="../reviews/index.php" class="btn btn-outline" style="padding:10px 20px;">
                <i class="fa-solid fa-star"></i> 고객후기
            </a>
            <a href="selfvod.php" class="btn btn-primary" style="padding:10px 20px; background:#059669; border-color:#059669;">
                <i class="fa-solid fa-circle-play"></i> DIY 셀프 시공 영상
            </a>
        </div>

        <form method="get" action="selfvod.php" class="comm-search-box" style="display:flex; align-items:center; gap:8px;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="영상 제목/내용 검색..." style="padding:10px 16px; border:1px solid #CBD5E1; border-radius:var(--radius-md); font-size:0.9rem; width:230px;">
            <button type="submit" class="btn btn-primary" style="padding:10px 16px; background:#059669; border-color:#059669;"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>

    <!-- Gallery Grid Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h2 style="font-size:1.3rem; font-weight:800; color:#0F172A; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-film" style="color:#059669;"></i> 셀프시공 영상 목록 
            <span style="font-size:0.9rem; font-weight:600; color:#64748B;">(총 <?php echo count($all_vods); ?>건)</span>
        </h2>
    </div>

    <!-- Gallery VOD Grid Layout -->
    <div class="selfvod-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(350px, 1fr)); gap:28px;">
        <?php if (empty($all_vods)): ?>
            <div style="grid-column:1 / -1; padding:60px; text-align:center; background:white; border-radius:16px; border:1px solid #E2E8F0; color:#94A3B8;">
                <i class="fa-solid fa-video-slash" style="font-size:3rem; margin-bottom:16px; color:#CBD5E1;"></i>
                <p style="font-size:1.1rem; font-weight:700;">검색된 DIY 시공 영상이 없습니다.</p>
            </div>
        <?php else: ?>
            <?php foreach ($all_vods as $vod): ?>
                <div class="vod-card" style="background:white; border:1px solid #E2E8F0; border-radius:16px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.05); transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display:flex; flex-direction:column;">
                    <!-- Thumbnail Box with Video Overlay -->
                    <div class="vod-thumb-wrapper" onclick="openVodModal('<?php echo htmlspecialchars(addslashes($vod['title'])); ?>', '<?php echo htmlspecialchars($vod['video_url']); ?>')" style="position:relative; aspect-ratio:16/9; overflow:hidden; cursor:pointer; background:#0F172A;">
                        <img src="<?php echo htmlspecialchars($vod['thumb']); ?>" alt="<?php echo htmlspecialchars($vod['title']); ?>" style="width:100%; height:100%; object-fit:cover; transition:transform 0.4s ease;">
                        <div class="vod-overlay" style="position:absolute; inset:0; background:rgba(15,23,42,0.35); display:flex; align-items:center; justify-content:center; transition:background 0.3s ease;">
                            <div class="play-btn" style="width:58px; height:58px; background:rgba(5,150,105,0.9); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:1.4rem; padding-left:4px; box-shadow:0 0 20px rgba(5,150,105,0.6); transition:transform 0.3s ease, background 0.3s ease;">
                                <i class="fa-solid fa-play"></i>
                            </div>
                        </div>
                        <span style="position:absolute; bottom:12px; right:12px; background:rgba(0,0,0,0.75); color:white; font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:4px; backdrop-filter:blur(4px);">
                            <i class="fa-regular fa-clock" style="margin-right:4px;"></i><?php echo $vod['duration']; ?>
                        </span>
                        <span style="position:absolute; top:12px; left:12px; background:<?php echo $vod['badge_bg']; ?>; color:<?php echo $vod['badge_color']; ?>; font-size:0.75rem; font-weight:800; padding:4px 10px; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                            <?php echo $vod['cat_name']; ?>
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div style="padding:22px; display:flex; flex-direction:column; flex:1; justify-content:space-between;">
                        <div>
                            <h3 style="font-size:1.1rem; font-weight:800; color:#0F172A; line-height:1.4; margin-bottom:10px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                <a href="javascript:void(0);" onclick="openVodModal('<?php echo htmlspecialchars(addslashes($vod['title'])); ?>', '<?php echo htmlspecialchars($vod['video_url']); ?>')" style="text-decoration:none; color:inherit;">
                                    <?php echo htmlspecialchars($vod['title']); ?>
                                </a>
                            </h3>
                            <p style="font-size:0.88rem; color:#64748B; line-height:1.5; margin-bottom:16px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                <?php echo htmlspecialchars($vod['summary']); ?>
                            </p>
                        </div>

                        <!-- Card Footer -->
                        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #F1F5F9; padding-top:14px; font-size:0.82rem; color:#94A3B8;">
                            <div style="display:flex; gap:12px; align-items:center;">
                                <span><i class="fa-regular fa-calendar" style="margin-right:4px;"></i><?php echo $vod['date']; ?></span>
                                <span><i class="fa-regular fa-eye" style="margin-right:4px;"></i><?php echo number_format($vod['views']); ?></span>
                            </div>
                            <button onclick="openVodModal('<?php echo htmlspecialchars(addslashes($vod['title'])); ?>', '<?php echo htmlspecialchars($vod['video_url']); ?>')" style="background:#ECFDF5; border:none; color:#059669; font-weight:700; font-size:0.82rem; padding:6px 12px; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                                시공영상 시청 <i class="fa-solid fa-play" style="font-size:0.75rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <div style="display:flex; justify-content:center; align-items:center; gap:8px; margin-top:50px;">
        <button class="btn btn-outline" style="padding:8px 14px;" disabled><i class="fa-solid fa-chevron-left"></i></button>
        <button class="btn btn-primary" style="padding:8px 14px; background:#059669; border-color:#059669;">1</button>
        <button class="btn btn-outline" style="padding:8px 14px;"><i class="fa-solid fa-chevron-right"></i></button>
    </div>

</div>

<!-- VOD Video Modal Popup -->
<div id="vodModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.85); backdrop-filter:blur(6px); align-items:center; justify-content:center; padding:20px;">
    <div style="background:#0F172A; width:100%; max-width:900px; border-radius:20px; overflow:hidden; border:1px solid rgba(255,255,255,0.1); box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
        <!-- Modal Header -->
        <div style="padding:20px 24px; background:#1E293B; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #334155;">
            <h3 id="vodModalTitle" style="color:white; font-size:1.15rem; font-weight:800; margin:0; display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-circle-play" style="color:#10B981;"></i> <span>셀프시공 영상 시청</span>
            </h3>
            <button onclick="closeVodModal()" style="background:transparent; border:none; color:#94A3B8; font-size:1.6rem; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <!-- Modal Content / Player -->
        <div style="position:relative; aspect-ratio:16/9; background:black;">
            <iframe id="vodIframe" src="" style="width:100%; height:100%; border:none;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <!-- Modal Footer -->
        <div style="padding:16px 24px; background:#1E293B; text-align:right;">
            <button onclick="closeVodModal()" class="btn btn-outline" style="color:white; border-color:#475569; padding:8px 20px;">닫기</button>
        </div>
    </div>
</div>

<style>
.vod-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 15px 30px rgba(5,150,105,0.15) !important;
    border-color: #A7F3D0 !important;
}
.vod-card:hover .vod-thumb-wrapper img {
    transform: scale(1.05);
}
.vod-card:hover .play-btn {
    transform: scale(1.15);
    background: #059669 !important;
}
@media (max-width: 768px) {
    .selfvod-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
function openVodModal(title, url) {
    if (url.startsWith('view.php')) {
        location.href = url;
        return;
    }
    document.getElementById('vodModalTitle').children[1].innerText = title;
    var embedUrl = url;
    if (embedUrl.indexOf('?') === -1) {
        embedUrl += '?autoplay=1';
    } else {
        embedUrl += '&autoplay=1';
    }
    document.getElementById('vodIframe').src = embedUrl;
    document.getElementById('vodModal').style.display = 'flex';
}

function closeVodModal() {
    document.getElementById('vodIframe').src = '';
    document.getElementById('vodModal').style.display = 'none';
}

// Esc 키로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeVodModal();
});
</script>

<?php
include_once __DIR__ . "/../inc/footer.php";
?>
