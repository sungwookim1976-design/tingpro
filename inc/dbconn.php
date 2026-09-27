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
date_default_timezone_set('Asia/Seoul');

$hostname = "localhost";
$db       = "corea27";
$user     = "corea27";
$password = "corea27!!@^^";

if($_SERVER['SERVER_PORT']=="8010"){
	$hostname = "localhost";
	$db       = "corea27";
	$user     = "root";
	$password = "1234";
}

$conn = @mysqli_connect($hostname, $user, $password, $db);
if (!$conn) {
    $conn = @mysqli_connect("localhost", "root", "1234", $db);
}

if (!$conn) {
    echo "host name : $hostname<br>
    user name : $user<br>
    데이터 베이스 연결에 실패하였습니다. " . mysqli_connect_error();
    exit;
}

mysqli_set_charset($conn, "utf8");

function dbaccess($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    if (!$result && mysqli_errno($conn) != 1065) {
        $errorNo  = mysqli_errno($conn);
        $errorMsg = mysqli_error($conn);
        echo "<br>Query Error입니다.<br>";
        echo "에러코드 : " . $errorNo . " : " . $errorMsg . "<br>";
        echo "Query String : $query<br>";
        exit;
    }
    return $result;
}

function dbsqliAccess($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    if (!$result && mysqli_errno($conn) != 1065) {
        $errorNo  = mysqli_errno($conn);
        $errorMsg = mysqli_error($conn);
        echo "<br>Query Error입니다.<br>";
        echo "에러코드 : " . $errorNo . " : " . $errorMsg . "<br>";
        echo "Query String : $query<br>";
        exit;
    }
    return $result;
}

function sql_one($table, $field, $where) {
    global $conn;
    $sql = 'select ' . $field . ' from ' . $table . ' where 1 ' . $where;
    $ret = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    $out = array();
    while ($rows = mysqli_fetch_array($ret)) $out[] = $rows;
    return $out;
}

function sql_one_one($table, $field, $where) {
    global $conn;
    $sql = 'select ' . $field . ' from ' . $table . ' where 1 ' . $where;
    $ret = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    return mysqli_fetch_array($ret);
}

function sql_cnt($table, $where) {
    global $conn;
    $sql = 'select count(*) from ' . $table . ' where 1 ' . $where;
    $ret = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    $row = mysqli_fetch_row($ret);
    return $row[0];
}

function sql_in($table, $field) {
    global $conn;
    $sql = "insert into $table set $field";
    $ret = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    return $ret;
}

function sql_up($table, $field, $where) {
    global $conn;
    $sql = "update $table set $field where 1 $where";
    $ret = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    return $ret;
}

function sql_del($table, $where) {
    global $conn;
    $sql = "delete from $table where 1 $where";
    $ret = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    return $ret;
}

function render_adm_pagination($page, $total_pages, $query_params = array()) {
    if ($total_pages <= 1) return;
    
    echo '<div style="display:flex; justify-content:center; align-items:center; gap:6px; margin-top:24px; padding-top:16px; border-top:1px solid var(--adm-border);">';
    
    if (isset($query_params['page'])) {
        unset($query_params['page']);
    }
    $qs = http_build_query($query_params);
    $link_prefix = '?' . ($qs !== '' ? $qs . '&' : '') . 'page=';
    
    if ($page > 1) {
        echo '<a href="' . $link_prefix . ($page - 1) . '" class="adm-btn adm-btn-outline" style="padding:5px 10px; font-size:0.8rem;"><i class="fa-solid fa-chevron-left"></i> 이전</a>';
    }
    
    for ($p = 1; $p <= $total_pages; $p++) {
        $active_style = ($p === $page) ? 'background:var(--adm-primary); color:#fff; border-color:var(--adm-primary);' : 'background:#fff; color:#334155;';
        echo '<a href="' . $link_prefix . $p . '" class="adm-btn adm-btn-outline" style="padding:5px 11px; font-size:0.8rem; font-weight:700; ' . $active_style . '">' . $p . '</a>';
    }
    
    if ($page < $total_pages) {
        echo '<a href="' . $link_prefix . ($page + 1) . '" class="adm-btn adm-btn-outline" style="padding:5px 10px; font-size:0.8rem;">다음 <i class="fa-solid fa-chevron-right"></i></a>';
    }
    
    echo '</div>';
}

/**
 * 기업고객이 VULUX 상품을 1개라도 구매 시 대리점(partner)으로 자동 전환/인식하는 공통 함수
 */
function check_and_update_partner_status($mem_no) {
    global $conn;
    $mem_no = (int)$mem_no;
    if ($mem_no <= 0) return 'customer';

    $m = sql_one_one('members', 'mem_type', "and no=" . $mem_no);
    if (!$m) return 'customer';

    $mem_type = $m['mem_type'];

    // 기업고객(corporate) 또는 대리점(partner) 대상 검사
    if ($mem_type === 'corporate' || $mem_type === 'partner') {
        // VULUX 상품 구매 내역(주문 취소 제외) 존재 여부 확인
        $v_order = sql_one_one('orders', 'no', "and mem_no=" . $mem_no . " and product_name LIKE '%VULUX%' and state <> '취소'");
        if ($v_order) {
            if ($mem_type !== 'partner') {
                sql_up('members', "mem_type='partner'", "and no=" . $mem_no);
                if (isset($_SESSION['s_mem_no']) && (int)$_SESSION['s_mem_no'] === $mem_no) {
                    $_SESSION['s_mem_type'] = 'partner';
                }
            }
            return 'partner';
        }
    }
    return $mem_type;
}

/**
 * 기업고객 중 VULUX 상품을 1개라도 구매한 회원들을 일괄 대리점(partner)으로 갱신하는 공통 함수
 */
function sync_all_partner_statuses() {
    global $conn;
    @mysqli_query($conn, "UPDATE members m SET m.mem_type='partner' WHERE (m.mem_type='corporate' OR m.mem_type='partner') AND EXISTS (SELECT 1 FROM orders o WHERE o.mem_no=m.no AND o.product_name LIKE '%VULUX%' AND o.state <> '취소')");
}
?>
