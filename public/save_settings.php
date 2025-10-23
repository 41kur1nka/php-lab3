<?php
global $pdo;
require __DIR__ . '/../config.php';
session_start();

// Авто-база URL
$__base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$__base = preg_replace('#/public$#', '', $__base);

if (empty($_SESSION['user_id'])) {
    header("Location: {$__base}/public/login.php");
    exit;
}

$bg  = $_POST['theme_bg']  ?? '#ffffff';
$txt = $_POST['theme_text']?? '#000000';

$pdo->prepare("UPDATE users SET theme_bg=?, theme_text=? WHERE id=?")
    ->execute([$bg, $txt, $_SESSION['user_id']]);

$_SESSION['theme_bg']   = $bg;
$_SESSION['theme_text'] = $txt;

setcookie('theme_bg',  $bg,  time()+365*24*3600, '/');
setcookie('theme_text',$txt, time()+365*24*3600, '/');

header("Location: {$__base}/index.php");
