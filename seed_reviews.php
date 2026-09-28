<?php
include_once __DIR__ . "/inc/dbconn.php";

// 1. case_comments 테이블 존재 확인 및 기본 데이터 시딩
$chk_tbl = @mysqli_query($conn, "SHOW TABLES LIKE 'case_comments'");
if (!$chk_tbl || mysqli_num_rows($chk_tbl) == 0) {
    @mysqli_query($conn, "CREATE TABLE case_comments (
        no INT AUTO_INCREMENT PRIMARY KEY,
        case_no INT DEFAULT 0,
        tbl_type VARCHAR(50) DEFAULT 'quote',
        writer_name VARCHAR(100) NOT NULL,
        rating INT DEFAULT 5,
        content TEXT NOT NULL,
        reg_date DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Created table case_comments.\n";
}

$reviews_10 = [
    [
        "writer" => "강남 자이아파트 김*우 님",
        "space"  => "아파트",
        "rating" => 5,
        "content" => "남향 아파트라 여름에 햇빛과 열기가 어마어마했는데 VULUX Sputter Dual 99 필름 시공 후 에어컨 효율이 급상승했습니다! 실내 온도가 체감 4도 이상 낮아졌어요."
    ],
    [
        "writer" => "판교 테크노밸리 이*성 대표님",
        "space"  => "상가/빌딩",
        "rating" => 5,
        "content" => "사무실 전면 통유리창 보안 및 시선 차단 필름 시공을 받았는데, 외부에서는 반사되어 완벽 차단되고 내부에서는 조망이 선명하여 직원 만족도가 매우 높습니다."
    ],
    [
        "writer" => "송파 아시아선수촌 박*혜 님",
        "space"  => "아파트",
        "rating" => 5,
        "content" => "아파트 1층이라 사생활 보호 미러 필름 시공을 받았는데, 커튼이나 블라인드 없이 하루 종일 밝은 채광을 누릴 수 있어 너무 행복합니다."
    ],
    [
        "writer" => "마포 자이 3차 최*영 님",
        "space"  => "오피스텔",
        "rating" => 5,
        "content" => "역경매 마켓을 통해서 여러 틴팅프로 마스터분들의 견적을 비교하고 최저가로 시공받았습니다. 시공 속도도 빠르고 마무리 청소까지 깨끗하게 해주셨어요."
    ],
    [
        "writer" => "분당 정자동 파크뷰 윤*호 님",
        "space"  => "단독주택",
        "rating" => 5,
        "content" => "주택 창문 자외선 차단 필름 시공 후 가구 탈색 걱정도 사라지고, 자외선 99.9% 차단 효과를 피부로 느낍니다. 마스터님 친절한 설명 감사드립니다."
    ],
    [
        "writer" => "용인 수지 동천 래미안 정*희 님",
        "space"  => "아파트",
        "rating" => 5,
        "content" => "겨울철 단열필름 기능 덕분에 창문 난방열 손실이 줄어들어 관리비 절감 효과를 톡톡히 보고 있습니다. TINTING PRO 강력 추천합니다."
    ],
    [
        "writer" => "인천 송도 더샵 한*수 님",
        "space"  => "아파트",
        "rating" => 5,
        "content" => "바다 전망 유리창 눈부심이 심했는데 시선차단 겸 열차단 필름 시공 후 눈이 너무 편안해졌습니다. 조망권이 훨씬 맑고 선명해졌어요."
    ],
    [
        "writer" => "수원 광교 힐스테이트 임*진 님",
        "space"  => "아파트",
        "rating" => 5,
        "content" => "DIY 키트로 직접 셀프 시공해보려다 방문 시공 마스터님께 맡겼는데, 먼지 하나 없이 깨끗한 마감에 감탄했습니다. 과연 전문가의 기술은 다릅니다."
    ],
    [
        "writer" => "하남 미사 강변 오피스텔 조*아 님",
        "space"  => "오피스텔",
        "rating" => 5,
        "content" => "상담 신청 당일 마스터님이 직접 방문 실측해 주시고 친절하게 필름 종류별 차이점을 설명해 주셔서 믿고 진행했습니다. 대만족입니다."
    ],
    [
        "writer" => "일산 위시티 자이 강*훈 님",
        "space"  => "아파트",
        "rating" => 5,
        "content" => "10년 품질보증서도 모바일로 깔끔하게 발행되고, 시공 품질이 정말 훌륭합니다. 주변 지인분들에게도 주저 없이 추천하고 있습니다."
    ]
];

// 기존 데이터가 적은 경우 10개 시딩
foreach ($reviews_10 as $idx => $r) {
    $w_esc = mysqli_real_escape_string($conn, $r['writer']);
    $c_esc = mysqli_real_escape_string($conn, $r['content']);
    $st    = (int)$r['rating'];
    $sp    = mysqli_real_escape_string($conn, $r['space']);

    // case_comments 테이블에 저장
    sql_in('case_comments', "case_no=" . ($idx + 1) . ", tbl_type='quote', writer_name='$w_esc', rating=$st, content='$c_esc', reg_date=NOW()");
    
    // community_posts 테이블에도 board_type='reviews'로 저장
    $title_esc = mysqli_real_escape_string($conn, "[{$r['space']}] {$r['writer']} 시공 만족 후기");
    sql_in('community_posts', "board_type='reviews', badge='고객후기', title='$title_esc', content='$c_esc', views=" . rand(1200, 3500) . ", state=1, reg_date=NOW()");

    echo "Inserted Review #" . ($idx + 1) . ": {$r['writer']}\n";
}

echo "Successfully seeded 10 customer reviews into DB!\n";
?>
