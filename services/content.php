<style>
    .film-gallery { display:grid; grid-template-columns:repeat(2, 1fr); gap:32px; margin-bottom:60px; }
    .film-gallery-card { background:#fff; border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-sm); transition:all 0.3s ease; }
    .film-gallery-card:hover { box-shadow:var(--shadow-md); transform:translateY(-4px); }
    .film-gallery-img { position:relative; height:240px; background:#0F172A; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .film-gallery-img img { width:100%; height:100%; object-fit:cover; transition:transform 0.5s; }
    .film-gallery-card:hover .film-gallery-img img { transform:scale(1.05); }
    .film-gallery-badge { position:absolute; top:16px; left:16px; background:var(--secondary); color:#fff; padding:5px 14px; border-radius:var(--radius-full); font-size:0.8rem; font-weight:700; }
    .film-gallery-body { padding:28px; }
    .film-gallery-title { font-size:1.35rem; font-weight:800; color:var(--secondary); margin-bottom:8px; }
    .film-gallery-desc { font-size:0.9rem; color:var(--text-muted); margin-bottom:18px; line-height:1.6; }
    .film-gallery-specs { display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; background:#F8FAFC; padding:14px; border-radius:var(--radius-md); margin-bottom:18px; font-size:0.85rem; }
    .film-gallery-specs .lbl { color:var(--text-muted); display:block; }
    .film-gallery-specs .val { font-weight:800; color:var(--primary-dark); }

    @media (max-width:768px) {
        .film-gallery { grid-template-columns:1fr; }
    }
</style>

<h2 style="font-size:2rem; font-weight:900; text-align:center; margin-bottom:16px; color:var(--secondary);">용도별 추천 필름 제품 라인업</h2>
<p style="text-align:center; color:var(--text-muted); margin-bottom:48px;">공간의 특성(아파트 베란다, 건물 오피스, 매장 1층)에 따라 맞춤 선택이 가능합니다.</p>

<?php
$lineup_products = sql_one('products', '*', "and show_lineup=1 and state=1 order by sort_order asc, no asc");
?>

<div class="film-gallery" id="sputter">
<?php if (empty($lineup_products)): ?>
    <p style="grid-column:1 / -1; text-align:center; color:var(--text-muted); padding:40px;">등록된 제품 라인업이 없습니다.</p>
<?php else: foreach ($lineup_products as $prod): ?>
    <div class="film-gallery-card">
        <div class="film-gallery-img">
            <img src="../<?php echo htmlspecialchars($prod['thumb']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" onerror="this.src='../imgs/building_office.png'">
            <?php if ($prod['badge']): ?>
                <span class="film-gallery-badge" style="background:<?php echo htmlspecialchars($prod['badge_color'] ?: 'var(--secondary)'); ?>;"><?php echo htmlspecialchars($prod['badge']); ?></span>
            <?php endif; ?>
        </div>
        <div class="film-gallery-body">
            <h3 class="film-gallery-title"><?php echo htmlspecialchars($prod['name']); ?></h3>
            <p class="film-gallery-desc"><?php echo htmlspecialchars($prod['desc']); ?></p>
            <div class="film-gallery-specs">
                <div><span class="lbl">자외선 차단율</span><span class="val"><?php echo htmlspecialchars($prod['uv_rate'] ?: '-'); ?></span></div>
                <div><span class="lbl">열차단율(IR)</span><span class="val"><?php echo htmlspecialchars($prod['ir_rate'] ?: '-'); ?></span></div>
                <div><span class="lbl">보증기간</span><span class="val"><?php echo htmlspecialchars($prod['warranty'] ?: '-'); ?></span></div>
                <div><span class="lbl">권장 장소</span><span class="val"><?php echo htmlspecialchars($prod['recommend_place'] ?: '-'); ?></span></div>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="../calculator/index.php?pid=<?php echo (int)$prod['no']; ?>" class="btn btn-primary" style="flex:1; text-align:center; text-decoration:none;">
                    <i class="fa-solid fa-calculator"></i> 견적보기
                </a>
                <a href="https://youtu.be/eGtSTR-DdZs" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="flex:1; border-color:#EF4444; color:#EF4444; font-weight:700;"><i class="fa-brands fa-youtube" style="color:#EF4444;"></i> 동영상 보기</a>
            </div>
        </div>
    </div>
<?php endforeach; endif; ?>
</div>

<!-- 실시간 견적 모달 (#sputter) -->
<div class="modal-overlay" id="sputterCalcModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div class="modal-card" style="background:#fff; border-radius:16px; width:100%; max-width:540px; padding:32px; box-shadow:0 20px 40px rgba(0,0,0,0.25); position:relative;">
        <button class="modal-close" onclick="closeSputterCalcModal()" style="position:absolute; top:18px; right:18px; background:none; border:none; font-size:1.5rem; color:#64748B; cursor:pointer;">&times;</button>
        <div style="text-align:center; margin-bottom:20px;">
            <span style="background:#E0F2FE; color:#0284C7; font-size:0.78rem; font-weight:800; padding:4px 12px; border-radius:20px; text-transform:uppercase;">Realtime Quick Calculator</span>
            <h3 style="font-size:1.5rem; font-weight:900; color:var(--secondary); margin-top:8px;" id="sputterCalcFilmName">상품 견적계산기</h3>
            <p style="font-size:0.88rem; color:var(--text-muted);" id="sputterCalcFilmSpec">평수에 따른 실시간 예상 시공 금액을 확인하세요.</p>
        </div>

        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; padding:20px; margin-bottom:20px;">
            <div style="margin-bottom:16px;">
                <label style="font-size:0.88rem; font-weight:800; color:var(--secondary); display:block; margin-bottom:6px;">시공 공간 선택</label>
                <select id="sputterSpaceType" onchange="calcSputterTotal()" style="width:100%; padding:12px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.95rem;">
                    <option value="1.0">아파트 · 주택 베란다</option>
                    <option value="1.15">건물 · 상가 · 사무실 (+15%)</option>
                </select>
            </div>

            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <label style="font-size:0.88rem; font-weight:800; color:var(--secondary);">공급/전용 평수</label>
                    <div style="font-size:1.15rem; font-weight:900; color:#0077B6; background:#F0F9FF; padding:3px 12px; border-radius:16px; border:1px solid #BAE6FD;">
                        <span id="sputterPyText">32</span> <span style="font-size:0.8rem; font-weight:700; color:var(--text-muted);">평</span>
                    </div>
                </div>
                <input type="range" id="sputterPyRange" value="32" min="10" max="100" step="1" oninput="updateSputterPy(this.value)" style="width:100%; height:8px; accent-color:#0077B6; cursor:pointer;">
                <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:var(--text-muted); font-weight:700; margin-top:6px;">
                    <span onclick="updateSputterPy(10)" style="cursor:pointer;">10평</span>
                    <span onclick="updateSputterPy(25)" style="cursor:pointer; color:#0077B6;">25평</span>
                    <span onclick="updateSputterPy(33)" style="cursor:pointer; color:#0077B6;">33평</span>
                    <span onclick="updateSputterPy(50)" style="cursor:pointer; color:#0077B6;">50평</span>
                    <span onclick="updateSputterPy(100)" style="cursor:pointer;">100평</span>
                </div>
            </div>
        </div>

        <div style="background:linear-gradient(135deg, #0077B6 0%, #00B4D8 100%); color:white; border-radius:12px; padding:20px; text-align:center; margin-bottom:20px;">
            <div style="font-size:0.85rem; opacity:0.9;">예상 시공 비용</div>
            <div id="sputterTotalPrice" style="font-size:2.2rem; font-weight:900; margin:6px 0;">1,120,000 원</div>
            <div style="font-size:0.78rem; opacity:0.85;">* 부가세 별도 / 전문 마스터 방문 포함</div>
        </div>

        <div style="display:flex; gap:10px;">
            <a href="#" id="sputterGoCalcBtn" class="btn btn-accent" style="flex:1; text-align:center; padding:12px; font-weight:800;">
                <i class="fa-solid fa-calculator"></i> 무료 방문실측 신청하기
            </a>
            <button type="button" class="btn btn-outline" onclick="closeSputterCalcModal()" style="padding:12px 18px;">닫기</button>
        </div>
    </div>
</div>

<script>
    let sputterCurrentPricePy = 35000;

    function openSputterCalcModal(prodNo, prodName, price, spec) {
        sputterCurrentPricePy = price || 35000;
        document.getElementById('sputterCalcFilmName').innerText = prodName + ' 실시간 견적';
        document.getElementById('sputterCalcFilmSpec').innerText = spec ? spec : '평수에 따른 실시간 예상 시공 금액을 확인하세요.';
        document.getElementById('sputterGoCalcBtn').href = '../calculator/index.php?pid=' + prodNo;
        document.getElementById('sputterPyRange').value = 32;
        updateSputterPy(32);
        document.getElementById('sputterCalcModal').style.display = 'flex';
    }

    function closeSputterCalcModal() {
        document.getElementById('sputterCalcModal').style.display = 'none';
    }

    function updateSputterPy(val) {
        document.getElementById('sputterPyText').innerText = val;
        document.getElementById('sputterPyRange').value = val;
        calcSputterTotal();
    }

    function calcSputterTotal() {
        let py = parseInt(document.getElementById('sputterPyRange').value) || 32;
        let mult = parseFloat(document.getElementById('sputterSpaceType').value) || 1.0;
        let total = Math.round((py * sputterCurrentPricePy * mult) / 10000) * 10000;
        document.getElementById('sputterTotalPrice').innerText = total.toLocaleString() + ' 원';
    }
</script>

<!-- VULUX Promotional Video Section (#video) -->
<section id="video" style="padding-top:40px; margin-top:60px; border-top:1px solid #E2E8F0;">
    <div style="text-align:center; max-width:760px; margin:0 auto 36px auto;">
        <span style="background:#FEF3C7; color:#D97706; font-size:0.8rem; font-weight:800; padding:4px 12px; border-radius:var(--radius-full); text-transform:uppercase; letter-spacing:1px;">
            <i class="fa-solid fa-circle-play"></i> Official Brand Video
        </span>
        <h2 style="font-size:2.2rem; font-weight:900; color:var(--secondary); margin-top:12px;">VULUX 프리미엄 필름 공식 홍보영상</h2>
        <p style="color:var(--text-muted); font-size:1rem; margin-top:8px;">
            최첨단 스퍼터링 공법으로 완성된 VULUX 필름의 강력한 열차단 시연 및 실제 아파트·건물 시공 현장을 영상으로 직접 확인해 보세요.
        </p>
    </div>

    <!-- Video Grid / Container -->
    <div style="background:#0F172A; border-radius:var(--radius-lg); padding:36px; color:white; box-shadow:0 20px 40px rgba(0,0,0,0.2); display:grid; grid-template-columns:1.2fr 0.8fr; gap:36px; align-items:center;">
        <div style="position:relative; border-radius:14px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.4); background:#1E293B; aspect-ratio:16/9; display:flex; align-items:center; justify-content:center;" id="servicesVideoPlayerBox">
            <img src="../imgs/hero_balcony.png" alt="VULUX 시공 비디오 썸네일" style="width:100%; height:100%; object-fit:cover; opacity:0.85;">
            <button type="button" onclick="playInplaceServicesVideo()" style="position:absolute; inset:0; background:rgba(0,0,0,0.35); border:none; display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer; color:white; width:100%; text-align:center;">
                <div style="width:72px; height:72px; background:rgba(239, 68, 68, 0.95); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; box-shadow:0 0 25px rgba(239, 68, 68, 0.6); transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-play" style="margin-left:3px;"></i>
                </div>
                <span style="margin-top:14px; font-weight:800; font-size:1.05rem; letter-spacing:0.5px;">VULUX 열차단 성능 시연 영상 재생하기 (클릭)</span>
            </button>
        </div>
        <script>
            function playInplaceServicesVideo() {
                const box = document.getElementById('servicesVideoPlayerBox');
                if (box) {
                    box.innerHTML = '<iframe src="https://www.youtube.com/embed/eGtSTR-DdZs?autoplay=1&rel=0" title="VULUX 홍보영상" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="width:100%; height:100%; border:0; border-radius:14px;"></iframe>';
                }
            }
        </script>

        <div>
            <span style="color:#38BDF8; font-weight:800; font-size:0.85rem; text-transform:uppercase;">Key Features Highlight</span>
            <h3 style="font-size:1.6rem; font-weight:900; margin:10px 0 16px 0; color:white; line-height:1.3;">
                빛과 열을 지혜롭게 제어하는<br><span style="color:#38BDF8;">VULUX 기술의 혁신</span>
            </h3>
            <ul style="display:flex; flex-direction:column; gap:14px; font-size:0.95rem; color:#CBD5E1;">
                <li style="display:flex; align-items:flex-start; gap:10px;">
                    <i class="fa-solid fa-circle-check" style="color:#38BDF8; margin-top:3px;"></i>
                    <span><strong>유해 자외선 99.9% 차단:</strong> 피부 노화 및 내부 인테리어 가구 변색 원천 방지</span>
                </li>
                <li style="display:flex; align-items:flex-start; gap:10px;">
                    <i class="fa-solid fa-circle-check" style="color:#38BDF8; margin-top:3px;"></i>
                    <span><strong>최대 95% 적외선(IR) 열차단:</strong> 여름철 태양열 유입 차단으로 실내 온도 3~5도 감소</span>
                </li>
                <li style="display:flex; align-items:flex-start; gap:10px;">
                    <i class="fa-solid fa-circle-check" style="color:#38BDF8; margin-top:3px;"></i>
                    <span><strong>초고투명 선명 시인성:</strong> 탁 트인 조망은 유지하면서 야간 사생활 시선 완벽 보호</span>
                </li>
            </ul>
        </div>
    </div>
</section>
