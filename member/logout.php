<?php
include_once __DIR__ . "/../inc/dbconn.php";
$_SESSION = [];
session_destroy();
header("Location: ../index.php");
exit;
