<?php
// Forward to unified proc.php
$no = (int)((isset($_POST['no']) ? $_POST['no'] : 0));
$_POST['mode'] = $no ? 'update' : 'insert';
include __DIR__ . "/proc.php";
