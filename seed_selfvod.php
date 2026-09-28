<?php
include_once __DIR__ . "/inc/dbconn.php";

// 1. 컬럼 검사 및 자동 추가
$chk_col = @mysqli_query($conn, "SHOW COLUMNS FROM community_posts LIKE 'youtube_url'");
if ($chk_col && mysqli_num_rows($chk_col) == 0) {
    @mysqli_query($conn, "ALTER TABLE community_posts ADD COLUMN youtube_url VARCHAR(255) NULL");
    echo "Added youtube_url column to community_posts.\n";
}

// 2. board_config에 selfvod 게시판이 없으면 등록
$chk_brd = sql_one_one('board_config', '*', "and brd_id='selfvod'");
if (!$chk_brd) {
    sql_in('board_config', "brd_id='selfvod', brd_name='DIY 셀프 시공 영상', file_cnt=3, file_size=5120, sort_order=5");
    echo "Added selfvod to board_config.\n";
}

// 3. 기존 selfvod 갯수 확인
$cnt = sql_cnt('community_posts', "and board_type='selfvod'");
echo "Existing selfvod count: $cnt\n";

$samples = [
    [
        "title" => "[DIY 셀프시공] 아파트 베란다 창문 단열필름 10분 완벽 부착 노하우",
        "badge" => "자가설치",
        "youtube_url" => "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
        "content" => "<p>초보자도 실패 없는 세제 퐁퐁수 비율과 헤라 스크래퍼를 활용한 공기 방울 및 수분 제거 핵심 꿀팁을 전수해 드립니다.</p><p><img src=\"https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=800&q=80\" alt=\"베란다 단열필름 시공\"></p>",
        "views" => 3420
    ],
    [
        "title" => "[필름 재단 팁] 유리에 딱 맞게 깔끔하게 이중재단하는 정밀 기법",
        "badge" => "재단노하우",
        "youtube_url" => "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
        "content" => "<p>유리 실리콘 마감 부위 간섭 없이 칼날 각도 45도를 유지하여 깔끔한 마감선을 만드는 틴팅 전문가의 재단법.</p><p><img src=\"https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80\" alt=\"필름 정밀 재단\"></p>",
        "views" => 2850
    ],
    [
        "title" => "[자동차 틴팅] 측면 유리 자가 시공 시 필름 찝힘 방지 가이드",
        "badge" => "차량용시공",
        "youtube_url" => "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
        "content" => "<p>도어 트림 분리 없이 펠트 몰딩 사이로 정밀하게 필름을 밀어넣고 쉐이빙 마감하는 단계별 영상 가이드입니다.</p><p><img src=\"https://images.unsplash.com/photo-1617814076367-b759c7d7e738?auto=format&fit=crop&w=800&q=80\" alt=\"자동차 측면 틴팅\"></p>",
        "views" => 4190
    ],
    [
        "title" => "[필수 도구 안내] 셀프 시공을 위한 DIY 전용 키트 200% 활용법",
        "badge" => "도구활용",
        "youtube_url" => "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
        "content" => "<p>우레탄 스퀴지, 수분제거용 하드헤라, 몰딩 삽입기 등 TINTING PRO DIY 전용 도구 세트 사용법 대공개.</p><p><img src=\"https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80\" alt=\"DIY 키트 도구\"></p>",
        "views" => 1980
    ]
];

foreach ($samples as $s) {
    $t_esc = mysqli_real_escape_string($conn, $s['title']);
    $b_esc = mysqli_real_escape_string($conn, $s['badge']);
    $yt_esc = mysqli_real_escape_string($conn, $s['youtube_url']);
    $c_esc = mysqli_real_escape_string($conn, $s['content']);
    $views = (int)$s['views'];

    sql_in('community_posts', "board_type='selfvod', badge='$b_esc', title='$t_esc', youtube_url='$yt_esc', content='$c_esc', views=$views, state=1, reg_date=NOW()");
    echo "Inserted: {$s['title']}\n";
}

echo "Successfully seeded 4 sample VOD posts into DB!\n";
?>
