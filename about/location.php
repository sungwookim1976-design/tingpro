<?php
$page_title = "오시는 길 | 회사소개 | 틴팅 마스터 VULUX";
$active_menu = "about";
$path_prefix = "../";

include_once __DIR__ . "/../inc/head.php";
include_once __DIR__ . "/../inc/header.php";
?>

<div style="background:linear-gradient(135deg, #0077B6 0%, #00B4D8 100%); padding:60px 24px; text-align:center; color:white;">
    <span style="background:rgba(255,255,255,0.2); font-size:0.85rem; padding:4px 14px; border-radius:20px; font-weight:700; letter-spacing:1px; text-transform:uppercase;">VULUX LOCATION</span>
    <h1 style="font-size:2.5rem; font-weight:900; margin:12px 0 8px 0;">오시는 길 (대리점 &amp; 서비스센터)</h1>
    <p style="font-size:1.1rem; opacity:0.9; max-width:600px; margin:0 auto;">틴팅 마스터 VULUX 대리점 및 전국 지역별 서비스센터 위치를 안내해 드립니다.</p>
</div>

<div class="container" style="max-width:1280px; margin:0 auto; padding:60px 24px;">
    
    <div style="display:grid; grid-template-columns:1fr 400px; gap:36px; margin-bottom:48px; align-items:start;">
        
        <div style="background:white; border:1px solid #CBD5E1; border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-md); position:relative;">
            <div style="height:460px; background:#E2E8F0 url('https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=1200&q=80') no-repeat center / cover; position:relative;">
                <div style="position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(15, 23, 42, 0.35);"></div>
                
                <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:rgba(255,255,255,0.96); padding:20px 28px; border-radius:16px; box-shadow:0 15px 35px rgba(0,0,0,0.25); text-align:center; border:2px solid #0077B6;">
                    <div style="width:48px; height:48px; background:#0077B6; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 12px auto; font-size:1.4rem; box-shadow:0 4px 12px rgba(0,119,182,0.4);">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <strong style="font-size:1.2rem; color:#0F172A; display:block;">틴팅 마스터 VULUX 대리점</strong>
                    <span style="font-size:0.88rem; color:#475569; display:block; margin-top:4px;">서울특별시 서초구 마방로4길 16-17 (양재동)</span>
                    <a href="https://map.kakao.com" target="_blank" class="btn btn-primary btn-sm" style="margin-top:12px; display:inline-block;">
                        <i class="fa-solid fa-map-location-dot"></i> 카카오맵 지도 보기
                    </a>
                </div>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:20px;">
            <div style="background:white; border:1px solid #E2E8F0; border-radius:var(--radius-lg); padding:28px; box-shadow:var(--shadow-sm);">
                <h3 style="font-size:1.3rem; font-weight:900; color:#0F172A; margin-bottom:16px; display:flex; align-items:center; gap:10px;">
                    <i class="fa-solid fa-building" style="color:#0077B6;"></i> 대리점 안내
                </h3>
                <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:12px; font-size:0.95rem; color:#334155;">
                    <li><strong>주소:</strong> 서울특별시 서초구 마방로4길 16-17 (양재동)</li>
                    <li><strong>대표전화:</strong> <span style="color:#0077B6; font-weight:800;">1544-0000</span></li>
                    <li><strong>이메일:</strong> support@vulux-tinting.co.kr</li>
                    <li><strong>운영시간:</strong> 평일 09:00 ~ 18:00 (주말·공휴일 휴무)</li>
                </ul>
            </div>

            <div style="background:white; border:1px solid #E2E8F0; border-radius:var(--radius-lg); padding:28px; box-shadow:var(--shadow-sm);">
                <h3 style="font-size:1.3rem; font-weight:900; color:#0F172A; margin-bottom:16px; display:flex; align-items:center; gap:10px;">
                    <i class="fa-solid fa-subway" style="color:#0077B6;"></i> 교통편 안내
                </h3>
                <div style="display:flex; flex-direction:column; gap:14px; font-size:0.92rem;">
                    <div>
                        <strong style="color:#0077B6; display:block; margin-bottom:2px;"><i class="fa-solid fa-train"></i> 지하철 이용시</strong>
                        <span style="color:#475569;">양재시민의숲역(신분당선) 1번 출구 도보 8분 또는 양재역(3호선/신분당선) 8번 출구</span>
                    </div>
                    <div>
                        <strong style="color:#059669; display:block; margin-bottom:2px;"><i class="fa-solid fa-bus"></i> 버스 이용시</strong>
                        <span style="color:#475569;">양재동꽃시장·선바위 방면 (간선 400, 421, 440, 지선 4432, 4435)</span>
                    </div>
                    <div>
                        <strong style="color:#D97706; display:block; margin-bottom:2px;"><i class="fa-solid fa-square-parking"></i> 주차 안내</strong>
                        <span style="color:#475569;">대리점 건물 내 전용 주차장 (방문 고객 무료 주차)</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<?php
include_once __DIR__ . "/../inc/footer.php";
?>
