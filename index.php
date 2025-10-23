<?php
global $pdo;
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';
session_start();
$__base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$__base = preg_replace('#/public$#', '', $__base);

if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $st = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? AND token_expires > NOW()");
    $st->execute([$token]);
    if ($user = $st->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['theme_bg']   = $user['theme_bg'] ?? '#ffffff';
        $_SESSION['theme_text'] = $user['theme_text'] ?? '#000000';
    }
}

// Применяем тему: приоритет cookie → затем сессия → затем дефолт
$theme_bg   = $_COOKIE['theme_bg']  ?? ($_SESSION['theme_bg']  ?? '#ffffff');
$theme_text = $_COOKIE['theme_text']?? ($_SESSION['theme_text']?? '#000000');

$isAuth = !empty($_SESSION['user_id']);
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Lab 3</title>
    <style>
        body { background: <?= htmlspecialchars($theme_bg) ?>; color: <?= htmlspecialchars($theme_text) ?>; font-family: Arial, sans-serif; }
        .card { max-width: 560px; margin: 40px auto; padding: 20px; border: 1px solid #ddd; border-radius: 12px; }
        input, button { width: 100%; padding: 8px; margin: 6px 0; }
        a { color: inherit; }
    </style>
</head>
<body>
<div class="card">
    <h2>Lab 3 — Cookies + Auth</h2>

    <?php if ($isAuth): ?>
        <p>Вы вошли как: <b><?= htmlspecialchars($_SESSION['email']) ?></b></p>

        <form method="post" action="<?= $__base ?>/public/save_settings.php">
            <label>Цвет фона:</label>
            <input type="color" name="theme_bg" value="<?= htmlspecialchars($theme_bg) ?>">
            <label>Цвет текста:</label>
            <input type="color" name="theme_text" value="<?= htmlspecialchars($theme_text) ?>">
            <button type="submit">Сохранить</button>
        </form>

        <p><a href="<?= $__base ?>/public/logout.php">Выйти</a></p>

    <?php else: ?>
        <p>Вы не авторизованы.</p>
        <p>
            <a href="<?= $__base ?>/public/register.php">Регистрация</a> ·
            <a href="<?= $__base ?>/public/login.php">Вход</a>
        </p>
    <?php endif; ?>
</div>
</body>
</html>