<?php
if($_SESSION['s_mem_id']=="") {
?>
<script>
	alert('로그인 후 이용해 주세요.');
    history.back(-1);
	//location.href="../member/n_login.php";
</script>
<?php
	exit;
}

$sql_mem_info="select * from members where uid='".$_SESSION['s_mem_id']."'";
$result_mem_info=mysql_query($sql_mem_info);
$row_mem_info=mysql_fetch_array($result_mem_info);

if ($row_mem_info['no']=="") {
?>
<script>
	alert('회원 정보가 없습니다.');
	location.href="../src/logout.php";
</script>
<?
	exit;
}

if ($row_mem_info['mem_state']!="1") {
?>
<script>
	alert('권한이 없습니다. 관리자에 문의 바랍니다.');
	location.href="../src/logout.php";
</script>
<?
	exit;
}

$mem_hphone_arr=explode("-",$row_mem_info['hphone']);

?>