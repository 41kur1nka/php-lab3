<?php
global $pdo;
require __DIR__ . '/../config.php';
session_start();

// Авто-база URL
$__base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$__base = preg_replace('#/public$#', '', $__base);

// Сбросим токен, чтобы cookie потеряли силу
if (!empty($_SESSION['user_id'])) {
    $pdo->prepare("UPDATE users SET remember_token=NULL, token_expires=NULL WHERE id=?")
        ->execute([$_SESSION['user_id']]);
}

$_SESSION = [];
session_destroy();

// Удалим куки
setcookie('remember_token', '', time()-3600, '/');
setcookie('theme_bg', '', time()-3600, '/');
setcookie('theme_text', '', time()-3600, '/');

header("Location: {$__base}/public/login.php");
exit;
