<?php
$page_title = "빠른 시공 상담 신청서 관리";
$active_page = "quotes";
$active_sub = "quick_consult";
$adm_path_prefix = "../";
$site_path_prefix = "../../";

include_once __DIR__ . "/../../inc/dbconn.php";
include_once __DIR__ . "/../inc/auth_check.php";

// tb_consult_request 테이블 존재 및 컬럼(state) 확인
$createTableSql = "
CREATE TABLE IF NOT EXISTS tb_consult_request (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    order_type VARCHAR(50) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(50) NOT NULL,
    building_car_info VARCHAR(255) DEFAULT '',
    car_number VARCHAR(50) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    addr VARCHAR(255) DEFAULT '',
    reserve_date VARCHAR(20) DEFAULT '',
    reserve_time VARCHAR(20) DEFAULT '',
    brand VARCHAR(100) DEFAULT '',
    budget VARCHAR(50) DEFAULT '',
    message TEXT,
    ip_addr VARCHAR(50) DEFAULT '',
    state VARCHAR(20) DEFAULT '신규',
    reg_date DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
mysqli_query($conn, $createTableSql);
@mysqli_query($conn, "ALTER TABLE tb_consult_request ADD COLUMN state VARCHAR(20) DEFAULT '신규'");

$allowed_states = ['신규', '상담중', '완료'];

$state_filter = isset($_GET['state']) && in_array($_GET['state'], $allowed_states) ? $_GET['state'] : '';
$sk           = trim((isset($_GET['sk']) ? $_GET['sk'] : ''));

$where = " where 1=1";
if ($state_filter !== '') {
    if ($state_filter === '신규') {
        $where .= " and (state='신규' or state='' or state IS NULL)";
    } else {
        $where .= " and state='" . mysqli_real_escape_string($conn, $state_filter) . "'";
    }
}
if ($sk !== '') {
    $sk_esc = mysqli_real_escape_string($conn, $sk);
    $where .= " and (company_name like '%$sk_esc%' or contact_phone like '%$sk_esc%' or building_car_info like '%$sk_esc%' or brand like '%$sk_esc%' or addr like '%$sk_esc%')";
}

// 통계 수치
$total_cnt     = sql_cnt('tb_consult_request', '');
$new_cnt       = sql_cnt('tb_consult_request', "and (state='신규' or state='' or state IS NULL)");
$consulting_cnt= sql_cnt('tb_consult_request', "and state='상담중'");
$completed_cnt = sql_cnt('tb_consult_request', "and state='완료'");

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$filtered_total = sql_cnt('tb_consult_request', str_replace(' where 1=1', '', $where));
$total_pages = max(1, (int)ceil($filtered_total / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$rows = sql_one('tb_consult_request', '*', str_replace(' where 1=1', '', $where) . " order by idx desc limit $offset, $limit");

include_once __DIR__ . "/../inc/adm_head.php";
?>

<div class="adm-stat-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-headset"></i> 전체 신청 건수</div>
        <div class="val"><?php echo number_format($total_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-envelope-open-text" style="color:#0077B6;"></i> 신규 접수</div>
        <div class="val" style="color:#0077B6;"><?php echo number_format($new_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-comments" style="color:#D97706;"></i> 상담 진행중</div>
        <div class="val" style="color:#D97706;"><?php echo number_format($consulting_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="lbl"><i class="fa-solid fa-circle-check" style="color:#059669;"></i> 상담 완료</div>
        <div class="val" style="color:#059669;"><?php echo number_format($completed_cnt); ?><span style="font-size:0.9rem; font-weight:600; color:var(--adm-muted);">건</span></div>
    </div>
</div>

<div class="adm-section">
    <div class="adm-section-head" style="flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <h3><i class="fa-solid fa-clipboard-list"></i> 빠른 시공 상담 신청 목록 (<?php echo number_format($filtered_total); ?>건)</h3>
            <div style="display:flex; gap:6px;">
                <a href="?state=&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '' ? '#0077B6' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '' ? '#fff' : '#334155'; ?>;">전체</a>
                <a href="?state=신규&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '신규' ? '#0077B6' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '신규' ? '#fff' : '#334155'; ?>;">신규</a>
                <a href="?state=상담중&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '상담중' ? '#D97706' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '상담중' ? '#fff' : '#334155'; ?>;">상담중</a>
                <a href="?state=완료&sk=<?php echo urlencode($sk); ?>" style="font-size:0.8rem; font-weight:700; padding:5px 11px; border-radius:6px; background:<?php echo $state_filter === '완료' ? '#059669' : '#F1F5F9'; ?>; color:<?php echo $state_filter === '완료' ? '#fff' : '#334155'; ?>;">완료</a>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:10px;">
            <form method="get" style="display:flex; gap:6px; align-items:center;">
                <input type="hidden" name="state" value="<?php echo htmlspecialchars($state_filter); ?>">
                <input type="text" name="sk" value="<?php echo htmlspecialchars($sk); ?>" placeholder="성함/연락처/시공대상/주소" style="padding:6px 12px; border:1px solid var(--adm-border); border-radius:6px; font-size:0.85rem; outline:none; width:210px;">
                <button type="submit" class="adm-btn" style="padding:6px 12px; font-size:0.82rem;"><i class="fa-solid fa-magnifying-glass"></i> 검색</button>
            </form>
            <button type="button" onclick="copySelectedConsults()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#0284C7; color:#0284C7;"><i class="fa-solid fa-copy"></i> 선택 신청건 복사</button>
            <button type="button" onclick="deleteSelectedConsults()" class="adm-btn adm-btn-outline" style="padding:7px 14px; font-size:0.85rem; border-color:#EF4444; color:#EF4444;"><i class="fa-solid fa-trash"></i> 선택 신청건 삭제</button>
        </div>
    </div>

    <form id="bulkForm" method="post" action="quick_consult_proc.php">
        <input type="hidden" name="mode" id="bulk_mode" value="copy_bulk">

        <div style="overflow-x:auto;">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;"><input type="checkbox" id="chk_all" onclick="toggleCheckAll(this)" style="cursor:pointer;"></th>
                        <th style="width:60px; text-align:center;">번호</th>
                        <th>문의구분</th>
                        <th>성함 / 상호</th>
                        <th>연락처</th>
                        <th>시공대상 / 차종</th>
                        <th>희망 틴팅브랜드 / DIY</th>
                        <th>희망예산</th>
                        <th>희망일시</th>
                        <th>주소</th>
                        <th style="text-align:center;">상태</th>
                        <th>신청일시</th>
                        <th style="width:140px; text-align:center;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr class="empty-row"><td colspan="13">신청된 빠른 시공 상담 내역이 없습니다.</td></tr>
                    <?php else: foreach ($rows as $r):
                        $st = !empty($r['state']) ? $r['state'] : '신규';
                    ?>
                        <tr>
                            <td style="text-align:center;"><input type="checkbox" name="chk_idx[]" value="<?php echo $r['idx']; ?>" class="chk-item" style="cursor:pointer;"></td>
                            <td style="text-align:center; font-weight:600; color:var(--adm-muted);"><?php echo $r['idx']; ?></td>
                            <td><span class="adm-badge type-partner"><?php echo htmlspecialchars($r['order_type'] ? $r['order_type'] : '상담신청'); ?></span></td>
                            <td><strong style="color:var(--adm-text);"><?php echo htmlspecialchars($r['company_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($r['contact_phone']); ?></td>
                            <td><span class="adm-badge type-customer"><?php echo htmlspecialchars($r['building_car_info'] ? $r['building_car_info'] : '-'); ?></span></td>
                            <td><strong style="color:var(--adm-primary);"><?php echo htmlspecialchars($r['brand'] ? $r['brand'] : '-'); ?></strong></td>
                            <td><?php echo htmlspecialchars($r['budget'] ? $r['budget'] : '-'); ?></td>
                            <td style="font-size:0.82rem; color:var(--adm-muted);"><?php echo htmlspecialchars(trim(($r['reserve_date'] ? $r['reserve_date'] : '') . ' ' . ($r['reserve_time'] ? $r['reserve_time'] : ''))); ?></td>
                            <td style="font-size:0.82rem; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo htmlspecialchars($r['addr']); ?>"><?php echo htmlspecialchars($r['addr'] ? $r['addr'] : '-'); ?></td>
                            <td style="text-align:center;">
                                <select onchange="changeConsultState(<?php echo $r['idx']; ?>, this.value)" class="state-select state-<?php echo $st; ?>">
                                    <?php foreach ($allowed_states as $s_item): ?>
                                        <option value="<?php echo $s_item; ?>" <?php echo $st === $s_item ? 'selected' : ''; ?>><?php echo $s_item; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="font-size:0.82rem; color:var(--adm-muted);"><?php echo htmlspecialchars($r['reg_date']); ?></td>
                            <td style="text-align:center; white-space:nowrap;">
                                <button type="button" class="adm-btn adm-btn-outline" style="padding:4px 9px; font-size:0.78rem;" onclick='openDetailModal(<?php echo json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'><i class="fa-solid fa-circle-info"></i> 상세보기</button>
                                <a href="quick_consult_proc.php?mode=delete&idx=<?php echo $r['idx']; ?>" onclick="return confirm('이 신청건을 삭제하시겠습니까?');" class="adm-btn" style="padding:4px 9px; font-size:0.78rem; background:#EF4444;"><i class="fa-solid fa-trash"></i> 삭제</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </form>
    <?php render_adm_pagination($page, $total_pages, $_GET); ?>
</div>

<!-- 상세 보기 레이어 모달 -->
<div id="detailModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:2000; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:14px; width:640px; max-width:95%; max-height:90vh; overflow-y:auto; padding:30px; position:relative; box-shadow:0 25px 50px rgba(0,0,0,0.3);">
        <button onclick="closeDetailModal()" style="position:absolute; top:18px; right:18px; background:none; border:none; font-size:1.5rem; cursor:pointer; color:#94A3B8;">&times;</button>
        <h3 style="font-size:1.2rem; font-weight:800; color:var(--adm-text); margin-bottom:18px; border-bottom:2px solid #F1F5F9; padding-bottom:10px;">
            <i class="fa-solid fa-clipboard-list" style="color:var(--adm-primary);"></i> 빠른 시공 상담 신청 상세 내역
        </h3>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px;">
            <div style="background:#F8FAFC; padding:12px 16px; border-radius:8px; border:1px solid var(--adm-border);">
                <div style="font-size:0.78rem; color:var(--adm-muted); font-weight:700;">문의 구분 / 게시판</div>
                <div id="md_order_type" style="font-size:1rem; font-weight:800; color:var(--adm-text); margin-top:2px;"></div>
            </div>
            <div style="background:#F8FAFC; padding:12px 16px; border-radius:8px; border:1px solid var(--adm-border);">
                <div style="font-size:0.78rem; color:var(--adm-muted); font-weight:700;">신청일시</div>
                <div id="md_reg_date" style="font-size:0.95rem; font-weight:700; color:var(--adm-text); margin-top:2px;"></div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px;">
            <div>
                <label style="font-size:0.82rem; font-weight:800; color:#334155; display:block; margin-bottom:4px;">성함 / 법인 상호</label>
                <div id="md_company_name" style="padding:10px; background:#fff; border:1px solid var(--adm-border); border-radius:6px; font-weight:700;"></div>
            </div>
            <div>
                <label style="font-size:0.82rem; font-weight:800; color:#334155; display:block; margin-bottom:4px;">연락처</label>
                <div id="md_contact_phone" style="padding:10px; background:#fff; border:1px solid var(--adm-border); border-radius:6px; font-weight:700; color:var(--adm-primary);"></div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px;">
            <div>
                <label style="font-size:0.82rem; font-weight:800; color:#334155; display:block; margin-bottom:4px;">시공 대상 (차종 / 건물)</label>
                <div id="md_building_car_info" style="padding:10px; background:#fff; border:1px solid var(--adm-border); border-radius:6px;"></div>
            </div>
            <div>
                <label style="font-size:0.82rem; font-weight:800; color:#334155; display:block; margin-bottom:4px;">차량 번호</label>
                <div id="md_car_number" style="padding:10px; background:#fff; border:1px solid var(--adm-border); border-radius:6px;"></div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px;">
            <div>
                <label style="font-size:0.82rem; font-weight:800; color:#334155; display:block; margin-bottom:4px;">희망 틴팅 브랜드 / DIY 상품</label>
                <div id="md_brand" style="padding:10px; background:#fff; border:1px solid var(--adm-border); border-radius:6px; font-weight:700; color:var(--adm-primary);"></div>
            </div>
            <div>
                <label style="font-size:0.82rem; font-weight:800; color:#334155; display:block; margin-bottom:4px;">희망 예산대</label>
                <div id="md_budget" style="padding:10px; background:#fff; border:1px solid var(--adm-border); border-radius:6px;"></div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px;">
            <div>
                <label style="font-size:0.82rem; font-weight:800; color:#334155; display:block; margin-bottom:4px;">희망 입고 / 방문일시</label>
                <div id="md_reserve" style="padding:10px; background:#fff; border:1px solid var(--adm-border); border-radius:6px;"></div>
            </div>
            <div>
                <label style="font-size:0.82rem; font-weight:800; color:#334155; display:block; margin-bottom:4px;">이메일 주소</label>
                <div id="md_email" style="padding:10px; background:#fff; border:1px solid var(--adm-border); border-radius:6px;"></div>
            </div>
        </div>

        <div style="margin-bottom:16px;">
            <label style="font-size:0.82rem; font-weight:800; color:#334155; display:block; margin-bottom:4px;">시공 위치 / 상세 주소</label>
            <div id="md_addr" style="padding:10px; background:#fff; border:1px solid var(--adm-border); border-radius:6px; font-weight:700;"></div>
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-size:0.82rem; font-weight:800; color:#334155; display:block; margin-bottom:4px;">상세 요청 내용 / 메모</label>
            <div id="md_message" style="padding:14px; background:#F8FAFC; border:1px solid var(--adm-border); border-radius:8px; min-height:90px; white-space:pre-wrap; font-size:0.9rem; color:#1E293B;"></div>
        </div>

        <div style="display:flex; justify-content:flex-end;">
            <button class="adm-btn adm-btn-outline" onclick="closeDetailModal()">닫기</button>
        </div>
    </div>
</div>

<script>
function toggleCheckAll(master) {
    const items = document.querySelectorAll('.chk-item');
    items.forEach(el => el.checked = master.checked);
}

function copySelectedConsults() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('복사할 신청건을 하나 이상 선택해 주세요.');
        return;
    }
    if (confirm('선택한 ' + checked.length + '개 신청건을 복사하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'copy_bulk';
        document.getElementById('bulkForm').submit();
    }
}

function deleteSelectedConsults() {
    const checked = document.querySelectorAll('.chk-item:checked');
    if (checked.length === 0) {
        alert('삭제할 신청건을 하나 이상 선택해 주세요.');
        return;
    }
    if (confirm('선택한 ' + checked.length + '개 신청건을 정말로 삭제하시겠습니까?')) {
        document.getElementById('bulk_mode').value = 'delete_bulk';
        document.getElementById('bulkForm').submit();
    }
}

function changeConsultState(idx, state) {
    fetch('quick_consult_proc.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'mode=change_state&idx=' + idx + '&state=' + encodeURIComponent(state)
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert('상태 변경 중 오류가 발생했습니다.');
        }
    })
    .catch(err => {
        location.reload();
    });
}

function openDetailModal(data) {
    document.getElementById('md_order_type').textContent = data.order_type || '빠른시공상담';
    document.getElementById('md_reg_date').textContent = data.reg_date || '';
    document.getElementById('md_company_name').textContent = data.company_name || '-';
    document.getElementById('md_contact_phone').textContent = data.contact_phone || '-';
    document.getElementById('md_building_car_info').textContent = data.building_car_info || '-';
    document.getElementById('md_car_number').textContent = data.car_number || '-';
    document.getElementById('md_brand').textContent = data.brand || '-';
    document.getElementById('md_budget').textContent = data.budget || '-';
    document.getElementById('md_reserve').textContent = ((data.reserve_date || '') + ' ' + (data.reserve_time || '')).trim() || '-';
    document.getElementById('md_email').textContent = data.email || '-';
    document.getElementById('md_addr').textContent = data.addr || '-';
    document.getElementById('md_message').textContent = data.message || '-';

    document.getElementById('detailModal').style.display = 'flex';
}

function closeDetailModal() {
    document.getElementById('detailModal').style.display = 'none';
}
</script>

<?php
include_once __DIR__ . "/../inc/adm_foot.php";
?>
