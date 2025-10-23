<?php
global $pdo;
require __DIR__ . '/../config.php';
session_start();

// Авто-база URL
$__base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$__base = preg_replace('#/public$#', '', $__base);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Некорректный e-mail";
    } else {
        $user = findUserByEmail($pdo, $email);
        if (!$user || !password_verify($pass, $user['pass_hash'])) {
            $error = "Неверный e-mail или пароль";
        } else {
            // новый токен на 7 дней
            $token   = bin2hex(random_bytes(32));
            $expires = (new DateTime('+7 days'))->format('Y-m-d H:i:s');
            $pdo->prepare("UPDATE users SET remember_token=?, token_expires=? WHERE id=?")
                    ->execute([$token, $expires, $user['id']]);

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['theme_bg']   = $user['theme_bg'] ?? '#ffffff';
            $_SESSION['theme_text'] = $user['theme_text'] ?? '#000000';

            setcookie('remember_token', $token, time()+7*24*3600, '/');
            setcookie('theme_bg',  $_SESSION['theme_bg'],  time()+365*24*3600, '/');
            setcookie('theme_text',$_SESSION['theme_text'],time()+365*24*3600, '/');

            header("Location: {$__base}/index.php");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Вход</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .card { max-width: 520px; margin: 40px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
        input, button { width: 100%; padding: 8px; margin: 6px 0; }
        a { color: #0366d6; text-decoration: none; }
    </style>
</head>
<body>
<div class="card">
    <h3>Вход</h3>
    <?php if (!empty($error)) : ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Войти</button>
    </form>
    <p><a href="<?= $__base ?>/index.php">На главную</a> ·
        <a href="<?= $__base ?>/public/register.php">Регистрация</a></p>
</div>
</body>
</html>
