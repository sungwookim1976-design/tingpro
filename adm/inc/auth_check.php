<?php
if (empty($_SESSION['s_adm_id'])) {
    $prefix = isset($adm_path_prefix) ? $adm_path_prefix : '';
    header("Location: " . $prefix . "login.php");
    exit;
}
