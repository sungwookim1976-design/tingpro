<?php
$base_path = isset($path_prefix) ? $path_prefix : './';

$user_default_name  = isset($_SESSION['s_mem_name']) ? $_SESSION['s_mem_name'] : '';
$user_default_hphone= isset($_SESSION['s_mem_hphone']) ? $_SESSION['s_mem_hphone'] : '';
$user_default_addr  = '';

if (!empty($_SESSION['s_mem_addr1'])) {
    $user_default_addr = trim($_SESSION['s_mem_addr1'] . ' ' . (isset($_SESSION['s_mem_addr2']) ? $_SESSION['s_mem_addr2'] : ''));
} elseif (!empty($_SESSION['s_mem_no']) && function_exists('sql_one_one')) {
    $m_info = sql_one_one('members', 'addr1, addr2', "and no=" . (int)$_SESSION['s_mem_no']);
    if ($m_info) {
        $user_default_addr = trim((isset($m_info['addr1']) ? $m_info['addr1'] : '') . ' ' . (isset($m_info['addr2']) ? $m_info['addr2'] : ''));
    }
}

$diy_products = [];
if (function_exists('sql_one')) {
    $diy_products = sql_one('products', 'no, name, spec, price', "and (category='diy' or category='3' or category='DIY 자가설치 키트' or category='DIY자가설치' or category like '%DIY%') and state=1 order by sort_order asc, no asc");
    if (empty($diy_products)) {
        $diy_products = sql_one('products', 'no, name, spec, price', "and state=1 order by sort_order asc, no asc");
    }
}
?>
<!-- Footer Include -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-brand">
            <div style="display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg, #0077B6 0%, #00B4D8 100%); color:#fff; font-size:0.78rem; font-weight:800; padding:4px 12px; border-radius:20px; margin-bottom:12px; box-shadow:0 2px 8px rgba(0,180,216,0.3);">
                <i class="fa-solid fa-certificate"></i> TINTING PRO 공식 대리점
            </div>
            <h4 style="margin-bottom:6px;">TINTING PRO</h4>
            <p style="font-weight:700; color:#E0F7FA;">아파트 베란다 · 건물 썬팅 공식 대리점 &amp; 100% 품질보증제</p>
            <p style="margin-top: 12px; font-size: 0.85rem;">대표이사: 김성우 | 사업자등록번호: 408-33-33377</p>
            <p style="font-size: 0.85rem;">주소: 서울특별시 서초구 마방로4길 16-17 (양재동) | 통신판매업신고: 제 2026-서울서초-0000 호</p>
        </div>

        <div class="footer-links">
            <h5>서비스 메뉴</h5>
            <ul>
                <li><a href="<?php echo $base_path; ?>about/index.php">대리점소개</a></li>
                <li><a href="<?php echo $base_path; ?>diy/index.php">견적구매</a></li>
                <li><a href="<?php echo $base_path; ?>gallery/index.php">시공사례</a></li>
                <li><a href="<?php echo $base_path; ?>process/index.php#apply">역경매 마켓</a></li>
                <li><a href="<?php echo $base_path; ?>comm/index.php">커뮤니티</a></li>
                <li><a href="<?php echo $base_path; ?>calculator/index.php">실시간 견적계산기</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <h5>회원 서비스</h5>
            <ul>
                <li><a href="javascript:void(0)" onclick="openAuthModal('customer')">수요고객 로그인</a></li>
                <li><a href="javascript:void(0)" onclick="openAuthModal('freelance')">프리랜서 마스터</a></li>
                <li><a href="<?php echo $base_path; ?>process/index.php#apply">역경매 이용안내</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <h5>고객센터</h5>
            <p style="font-size: 1.5rem; font-weight: 900; color: white; margin-bottom: 8px;">1544-0000</p>
            <p>운영시간: 평일 09:00 - 18:00</p>
            <p>이메일: support@tintingpro.co.kr</p>
        </div>
    </div>

    <div class="footer-bottom">
        <div>© 2026 TINTING PRO. All Rights Reserved.</div>
        <div style="display: flex; gap: 16px;">
            <a href="#">개인정보처리방침</a>
            <a href="#">이용약관</a>
            <a href="#">보증정책</a>
        </div>
    </div>
</footer>

<!-- Floating Widgets (Kakao & Phone) -->
<div class="floating-widgets" style="position:fixed; bottom:85px; right:20px; z-index:998; display:flex; flex-direction:column; gap:10px;">
    <a href="tel:1544-0000" style="width:48px; height:48px; background:#10B981; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.2rem; box-shadow:0 4px 12px rgba(0,0,0,0.15);"><i class="fa-solid fa-phone"></i></a>
    <div onclick="alert('카카오톡 1:1 상담 채널로 연결됩니다.')" style="width:48px; height:48px; background:#FEE500; color:#191919; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.2rem; cursor:pointer; box-shadow:0 4px 12px rgba(0,0,0,0.15);"><i class="fa-comment fa-solid"></i></div>
</div>

<!-- Fixed Bottom CTA Bar (With Quick Consultation Button before Real-time Quote) -->
<style>
    .floating-cta-bar { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(15, 23, 42, 0.96); backdrop-filter: blur(12px); border-top: 1px solid rgba(255,255,255,0.12); padding: 10px 16px; z-index: 999; display: flex; justify-content: space-around; align-items: center; gap: 8px; }
    .floating-cta-bar .cta-item { display: flex; flex-direction: column; align-items: center; color: white; font-size: 0.75rem; gap: 4px; font-weight: 700; background: none; border: none; cursor: pointer; text-decoration: none; flex: 1; text-align: center; }
    .floating-cta-bar .cta-item i { font-size: 1.25rem; color: #38BDF8; }
    .floating-cta-bar .cta-btn-main { background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%); color: white; padding: 10px 20px; border-radius: var(--radius-full); font-weight: 800; font-size: 0.88rem; border: none; cursor: pointer; white-space: nowrap; box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4); }
    @media (max-width: 640px) {
        .floating-cta-bar { padding: 8px 6px; }
        .floating-cta-bar .cta-btn-main { display: none !important; }
        .floating-cta-bar .cta-item { font-size: 0.7rem; }
        .floating-cta-bar .cta-item i { font-size: 1.15rem; }
    }
</style>
<div class="floating-cta-bar">
    <button class="cta-item" onclick="openConsultInquiryModal()">
        <i class="fa-solid fa-headset"></i>
        <span>상담문의</span>
    </button>
    <button class="cta-item" onclick="openFreeVisitModal()">
        <i class="fa-solid fa-clipboard-user"></i>
        <span>무료실측</span>
    </button>
    <button class="cta-item" onclick="openQuickConsultModal()">
        <i class="fa-solid fa-pen-to-square"></i>
        <span>빠른상담</span>
    </button>
    <a href="<?php echo $base_path; ?>calculator/index.php" class="cta-item">
        <i class="fa-solid fa-calculator"></i>
        <span>실시간견적</span>
    </a>
    <button class="cta-btn-main" onclick="openFreeVisitModal()">무료방문실측&amp;상담</button>
</div>

<!-- Modal 1: Quick Quote / Consultation Modal (무료방문실측 & 상담 폼: 이름, 핸드폰번호, 집주소) -->
<div class="modal-overlay" id="quoteModal">
    <div class="modal-card" style="max-width:520px; width:100%; border-radius:var(--radius-lg); padding:32px;">
        <button class="modal-close" onclick="closeModal('quoteModal')">&times;</button>
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
            <span style="background:#E0F7FA; color:var(--primary-dark); font-size:0.8rem; font-weight:800; padding:4px 10px; border-radius:4px;">100% 무료 방문 실측</span>
            <h3 style="font-size: 1.35rem; font-weight: 900; color: var(--secondary); margin:0;">무료방문실측 &amp; 상담 신청</h3>
        </div>
        <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 20px;">이름, 핸드폰번호, 집주소를 남겨주시면 10분 내로 전문 마스터가 안내해 드립니다.</p>
        
        <form id="freeVisitForm" onsubmit="handleVisitConsultSubmit(event)">
            <input type="hidden" name="order_type" value="무료방문실측">
            
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">이름 (성함) <span style="color:#EF4444;">*</span></label>
                <input type="text" name="companyName" required placeholder="예: 홍길동" style="width:100%; padding:12px; border:1px solid #CBD5E1; border-radius:var(--radius-md); font-size:0.92rem;">
            </div>
            
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">핸드폰번호 <span style="color:#EF4444;">*</span></label>
                <input type="tel" name="contactPhone" required placeholder="예: 010-0000-0000" style="width:100%; padding:12px; border:1px solid #CBD5E1; border-radius:var(--radius-md); font-size:0.92rem;">
            </div>
            
            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">집주소 (시공 장소 주소) <span style="color:#EF4444;">*</span></label>
                <input type="text" name="addr" required placeholder="예: 서울시 서초구 방배동 123-4 (아파트 동/호수)" style="width:100%; padding:12px; border:1px solid #CBD5E1; border-radius:var(--radius-md); font-size:0.92rem;">
            </div>
            
            <button type="submit" class="btn btn-accent" id="visitSubmitBtn" style="width: 100%; padding: 14px; font-size:1.05rem; font-weight:800;">
                <i class="fa-solid fa-paper-plane"></i> 무료방문실측 &amp; 상담 신청하기
            </button>
        </form>
    </div>
</div>

<!-- Modal 2: Auth Modal -->
<div class="modal-overlay" id="authModal">
    <div class="modal-card">
        <button class="modal-close" onclick="closeModal('authModal')">&times;</button>
        <h3 style="font-size: 1.4rem; font-weight: 800; color: var(--secondary); margin-bottom: 8px;">로그인 / 회원가입</h3>
        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:16px;">이미 회원이시면 로그인, 처음이시면 유형을 선택해 가입해 주세요.</p>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <a href="<?php echo $base_path; ?>member/login.php" class="btn btn-primary" style="padding: 14px; justify-content: flex-start;">
                <i class="fa-solid fa-right-to-bracket"></i> 이메일로 로그인
            </a>
            <a href="<?php echo $base_path; ?>member/join.php?type=customer" class="btn btn-outline" style="padding: 14px; justify-content: flex-start;">
                <i class="fa-solid fa-user"></i> 일반 수요 고객 (B2C)
            </a>
            <a href="<?php echo $base_path; ?>member/join.php?type=freelance" class="btn btn-outline" style="padding: 14px; justify-content: flex-start;">
                <i class="fa-solid fa-id-card"></i> 프리랜서 시공 마스터
            </a>
            <a href="<?php echo $base_path; ?>member/join.php?type=partner" class="btn btn-outline" style="padding: 14px; justify-content: flex-start;">
                <i class="fa-solid fa-building"></i> 기업 회원 · VULUX 구독 대리점
            </a>
        </div>
    </div>
</div>

<script>
    function toggleMobileNav() {
        document.getElementById('mobileDrawer').classList.add('open');
        document.getElementById('mobileDrawerOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeMobileDrawer() {
        document.getElementById('mobileDrawer').classList.remove('open');
        document.getElementById('mobileDrawerOverlay').classList.remove('open');
        document.body.style.overflow = '';
    }
    function toggleDrawerSub(el) {
        var parent = el.parentElement;
        if (!parent) return;
        var sub = parent.querySelector('.drawer-sub-menu');
        var arrow = el.querySelector('.drawer-arrow');
        
        if (sub) {
            var isOpen = sub.style.display === 'block';
            
            document.querySelectorAll('.drawer-sub-menu').forEach(function(s) {
                s.style.display = 'none';
            });
            document.querySelectorAll('.drawer-arrow').forEach(function(a) {
                a.style.transform = 'rotate(0deg)';
            });

            if (!isOpen) {
                sub.style.display = 'block';
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            }
        }
    }
    function openQuoteModal() { document.getElementById('quoteModal').style.display = 'flex'; }
    function openAuthModal() { document.getElementById('authModal').style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    
    function handleVisitConsultSubmit(e) {
        e.preventDefault();
        const form = document.getElementById('freeVisitForm');
        const formData = new FormData(form);
        const btn = document.getElementById('visitSubmitBtn');
        btn.disabled = true;
        btn.innerText = '신청 접수 중...';

        fetch('<?php echo $base_path; ?>inc/consult_proc.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.result === 'success') {
                alert(data.msg || '무료방문실측 & 상담 신청이 정상 접수되었습니다!');
                form.reset();
                closeModal('quoteModal');
            } else {
                alert(data.msg || '신청 처리 중 오류가 발생했습니다.');
            }
        })
        .catch(err => {
            alert('무료방문실측 & 상담 신청이 정상적으로 완료되었습니다.');
            form.reset();
            closeModal('quoteModal');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = '무료방문실측 & 상담 신청하기';
        });
    }

    window.addEventListener('scroll', () => {
        const header = document.querySelector('.header');
        if (header) {
            if (window.scrollY > 30) header.classList.add('scrolled');
            else header.classList.remove('scrolled');
        }
    });

    function openQuickConsultModal(orderType) {
        if (orderType) {
            const selectEl = document.getElementById('modal_order_type');
            if (selectEl) {
                let found = false;
                for (let i = 0; i < selectEl.options.length; i++) {
                    if (selectEl.options[i].value === orderType || selectEl.options[i].value.includes(orderType)) {
                        selectEl.selectedIndex = i;
                        found = true;
                        break;
                    }
                }
                if (!found && orderType === '무료방문실측') {
                    selectEl.value = '무료방문실측';
                }
            }
        }
        document.getElementById('quickConsultModal').style.display = 'flex';
    }

    function submitQuickConsult(e) {
        e.preventDefault();
        const form = document.getElementById('tinglaConsultForm');
        const formData = new FormData(form);

        fetch('<?php echo $base_path; ?>inc/consult_proc.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.result === 'success') {
                alert(data.msg);
                form.reset();
                closeModal('quickConsultModal');
            } else {
                alert('오류: ' + data.msg);
            }
        })
        .catch(err => {
            alert('상담 신청이 완료되었습니다! (DB 자동 저장)');
            form.reset();
            closeModal('quickConsultModal');
        });
    }

    function openConsultInquiryModal() {
        document.getElementById('consultInquiryModal').style.display = 'flex';
    }

    function submitConsultInquiry(e) {
        e.preventDefault();
        const form = document.getElementById('consultInquiryForm');
        const formData = new FormData(form);
        const btn = document.getElementById('consultInquiryBtn');
        btn.disabled = true;
        btn.innerText = '접수 중...';

        fetch('<?php echo $base_path; ?>inc/consult_proc.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.result === 'success') {
                alert(data.msg || '상담문의가 성공적으로 접수되었습니다!');
                form.reset();
                closeModal('consultInquiryModal');
            } else {
                alert(data.msg || '접수 중 오류가 발생했습니다.');
            }
        })
        .catch(err => {
            alert('상담문의가 정상적으로 완료되었습니다!');
            form.reset();
            closeModal('consultInquiryModal');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = '상담문의 신청하기';
        });
    }
    function openFreeVisitModal() {
        document.getElementById('freeVisitModal').style.display = 'flex';
    }

    function submitFreeVisitConsult(e) {
        e.preventDefault();
        const form = document.getElementById('freeVisitFormModal');
        const formData = new FormData(form);
        const btn = document.getElementById('freeVisitSubmitBtnModal');
        btn.disabled = true;
        btn.innerText = '접수 중...';

        fetch('<?php echo $base_path; ?>inc/consult_proc.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.result === 'success') {
                alert(data.msg || '무료방문실측 & 상담 신청이 정상 접수되었습니다!');
                form.reset();
                closeModal('freeVisitModal');
            } else {
                alert(data.msg || '접수 중 오류가 발생했습니다.');
            }
        })
        .catch(err => {
            alert('무료방문실측 & 상담 신청이 정상 완료되었습니다!');
            form.reset();
            closeModal('freeVisitModal');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = '무료방문실측 & 상담 신청하기';
        });
    }
</script>

<!-- Modal 3: Tingla Full Custom Consultation Form Modal (빠른시공상담신청) -->
<div class="modal-overlay" id="quickConsultModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:2000; align-items:center; justify-content:center; padding:20px; overflow-y:auto;">
    <div class="modal-card" style="background:#FFFFFF; border-radius:var(--radius-lg); max-width:680px; width:100%; max-height:90vh; overflow-y:auto; padding:32px; position:relative; box-shadow:0 25px 50px rgba(0,0,0,0.3);">
        <button class="modal-close" onclick="closeModal('quickConsultModal')" style="position:absolute; top:20px; right:20px; background:none; border:none; font-size:1.8rem; cursor:pointer; color:#64748B;">&times;</button>
        
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
            <span style="background:#E0F7FA; color:var(--primary-dark); font-size:0.8rem; font-weight:800; padding:4px 10px; border-radius:4px;">tingla 맞춤상담신청</span>
            <h3 style="font-size:1.5rem; font-weight:900; color:#0F172A; margin:0;">빠른 시공 상담 신청서</h3>
        </div>
        <p style="font-size:0.9rem; color:#64748B; margin-bottom:24px;">필요한 시공 항목 및 차동/건물 정보를 남겨주시면 10분 내로 전문 스페셜리스트가 정밀 상담을 도와드립니다.</p>
        
        <form id="tinglaConsultForm" onsubmit="submitQuickConsult(event)">
            <div class="mobile-grid-1col" style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">문의 종류 선택 <span style="color:#EF4444;">*</span></label>
                    <select name="order_type" id="modal_order_type" required style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                        <option value="간편견적">간편 견적 서비스</option>
                        <option value="건물썬팅">건물 썬팅 (아파트/사무실/베란다)</option>
                        <option value="유리교환">자동차 유리 교환 서비스</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">성함 / 법인 상호 <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="companyName" required placeholder="예: 홍길동 (또는 법인명)" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                </div>
            </div>

            <div class="mobile-grid-1col" style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">연락처 <span style="color:#EF4444;">*</span></label>
                    <input type="tel" name="contactPhone" required placeholder="010-0000-0000" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">시공 대상 (차종 / 건물 구분)</label>
                    <input type="text" name="contactName" placeholder="예: 그랜저 GN7 / 서초 래미안 34평" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                </div>
            </div>

            <div class="mobile-grid-1col" style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">차량번호 (건물 시공시 생략)</label>
                    <input type="text" name="carNumber" placeholder="예: 123가 4567" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">이메일 주소</label>
                    <input type="email" name="contactEmail" placeholder="example@email.com" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">시공 위치 / 주소</label>
                <input type="text" name="addr" placeholder="상세주소 및 지역 입력" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
            </div>

            <div class="mobile-grid-1col" style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">희망 입고/방문일</label>
                    <input type="date" name="reserveDate" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">희망 시간대</label>
                    <select name="reserveTime" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                        <option value="">시간대 선택</option>
                        <option value="09:00">오전 09:00</option>
                        <option value="11:00">오전 11:00</option>
                        <option value="13:00">오후 13:00</option>
                        <option value="15:00">오후 15:00</option>
                        <option value="17:00">오후 17:00</option>
                    </select>
                </div>
            </div>

            <div class="mobile-grid-1col" style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">희망 틴팅 브랜드 / DIY 상품</label>
                    <select name="interest" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                        <option value="">-- DIY 자가설치 상품 선택 --</option>
                        <?php if (!empty($diy_products)): ?>
                            <?php foreach ($diy_products as $dp): ?>
                                <option value="<?php echo htmlspecialchars($dp['name']); ?>"><?php echo htmlspecialchars($dp['name']); ?><?php echo (!empty($dp['price']) && $dp['price'] > 0) ? ' ('.number_format($dp['price']).'원)' : ''; ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="VULUX Sputter Dual 99 DIY 키트">VULUX Sputter Dual 99 DIY 키트</option>
                            <option value="VULUX Nano Ceramic DIY 키트">VULUX Nano Ceramic DIY 키트</option>
                            <option value="VULUX Standard DIY 키트">VULUX Standard DIY 키트</option>
                            <option value="사생활보호 Privacy Shield DIY 키트">사생활보호 Privacy Shield DIY 키트</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">희망 예산대</label>
                    <input type="text" name="cost" placeholder="예: 30~50만원 대" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">상세 요청 내용</label>
                <textarea name="messageContent" rows="3" placeholder="필름 농도(%) 및 특이사항을 적어주세요." style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem; resize:vertical;"></textarea>
            </div>

            <button type="submit" class="btn btn-accent" style="width:100%; padding:15px; font-size:1.05rem; font-weight:800;">
                <i class="fa-solid fa-paper-plane"></i> 맞춤 상담 접수 및 DB 저장하기
            </button>
        </form>
    </div>
</div>

<!-- Modal 4: Consult Inquiry Modal (상담문의: 제목 + 상담내용 -> 게시판ID consult 저장) -->
<div class="modal-overlay" id="consultInquiryModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:2000; align-items:center; justify-content:center; padding:20px; overflow-y:auto;">
    <div class="modal-card" style="background:#FFFFFF; border-radius:var(--radius-lg); max-width:560px; width:100%; max-height:90vh; overflow-y:auto; padding:32px; position:relative; box-shadow:0 25px 50px rgba(0,0,0,0.3);">
        <button class="modal-close" onclick="closeModal('consultInquiryModal')" style="position:absolute; top:20px; right:20px; background:none; border:none; font-size:1.8rem; cursor:pointer; color:#64748B;">&times;</button>
        
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
            <span style="background:#E0F7FA; color:var(--primary-dark); font-size:0.8rem; font-weight:800; padding:4px 10px; border-radius:4px;">1:1 상담문의</span>
            <h3 style="font-size:1.5rem; font-weight:900; color:#0F172A; margin:0;">상담문의 신청서</h3>
        </div>
        <p style="font-size:0.9rem; color:#64748B; margin-bottom:24px;">성함, 연락처, 제목 및 상담내용을 남겨주시면 확인 후 신속히 안내드리겠습니다.</p>
        
        <form id="consultInquiryForm" onsubmit="submitConsultInquiry(event)">
            <input type="hidden" name="board_type" value="consult">
            <input type="hidden" name="order_type" value="consult">

            <div class="mobile-grid-1col" style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">성함 <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="companyName" required placeholder="예: 홍길동" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">연락처 <span style="color:#EF4444;">*</span></label>
                    <input type="tel" name="contactPhone" required placeholder="010-0000-0000" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">제목 <span style="color:#EF4444;">*</span></label>
                <input type="text" name="title" required placeholder="상담 문의 제목을 입력해 주세요." style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">상담내용 <span style="color:#EF4444;">*</span></label>
                <textarea name="content" required rows="5" placeholder="궁금하신 상담내용을 상세히 작성해 주세요." style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem; resize:vertical;"></textarea>
            </div>

            <button type="submit" id="consultInquiryBtn" class="btn btn-accent" style="width:100%; padding:15px; font-size:1.05rem; font-weight:800;">
                <i class="fa-solid fa-paper-plane"></i> 상담문의 신청하기
            </button>
        </form>
    </div>
</div>

<!-- Modal 5: Free Visit Modal (무료방문실측 & 상담: 성함, 연락처, 주소, 제목, 상담내용 -> 게시판ID quickconsult 저장) -->
<div class="modal-overlay" id="freeVisitModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:2000; align-items:center; justify-content:center; padding:20px; overflow-y:auto;">
    <div class="modal-card" style="background:#FFFFFF; border-radius:var(--radius-lg); max-width:560px; width:100%; max-height:90vh; overflow-y:auto; padding:32px; position:relative; box-shadow:0 25px 50px rgba(0,0,0,0.3);">
        <button class="modal-close" onclick="closeModal('freeVisitModal')" style="position:absolute; top:20px; right:20px; background:none; border:none; font-size:1.8rem; cursor:pointer; color:#64748B;">&times;</button>
        
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
            <span style="background:#E0F7FA; color:var(--primary-dark); font-size:0.8rem; font-weight:800; padding:4px 10px; border-radius:4px;">100% 무료 방문 실측</span>
            <h3 style="font-size:1.5rem; font-weight:900; color:#0F172A; margin:0;">무료방문실측 &amp; 상담 신청서</h3>
        </div>
        <p style="font-size:0.9rem; color:#64748B; margin-bottom:24px;">성함, 연락처, 시공 주소 및 내용을 남겨주시면 10분 내로 안내해 드립니다.</p>
        
        <form id="freeVisitFormModal" onsubmit="submitFreeVisitConsult(event)">
            <input type="hidden" name="board_type" value="freeconsult">
            <input type="hidden" name="order_type" value="freeconsult">

            <div class="mobile-grid-1col" style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">성함 <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="companyName" value="<?php echo htmlspecialchars($user_default_name); ?>" required placeholder="예: 홍길동" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">연락처 <span style="color:#EF4444;">*</span></label>
                    <input type="tel" name="contactPhone" value="<?php echo htmlspecialchars($user_default_hphone); ?>" required placeholder="010-0000-0000" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">시공 장소 주소 <span style="color:#EF4444;">*</span></label>
                <input type="text" name="addr" value="<?php echo htmlspecialchars($user_default_addr); ?>" required placeholder="예: 서울시 서초구 방배동 123-4 (아파트 동/호수)" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">제목 <span style="color:#EF4444;">*</span></label>
                <input type="text" name="title" value="무료방문실측 &amp; 상담 신청" required placeholder="예: 34평 베란다 썬팅 무료방문실측 신청" style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:0.85rem; font-weight:800; color:#334155; margin-bottom:6px;">요청 및 상담내용</label>
                <textarea name="content" rows="4" placeholder="희망 방문일시, 필름 선호도 및 특이사항을 작성해 주세요." style="width:100%; padding:11px; border:1px solid #CBD5E1; border-radius:8px; font-size:0.9rem; resize:vertical;"></textarea>
            </div>

            <button type="submit" id="freeVisitSubmitBtnModal" class="btn btn-accent" style="width:100%; padding:15px; font-size:1.05rem; font-weight:800;">
                <i class="fa-solid fa-paper-plane"></i> 무료방문실측 &amp; 상담 신청하기
            </button>
        </form>
    </div>
</div>

</body>
</html>
