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
    $bg    = $_POST['theme_bg'] ?? '#ffffff';
    $txt   = $_POST['theme_text'] ?? '#000000';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Некорректный email";
    } elseif (strlen($pass) < 4) {
        $error = "Пароль должен быть не короче 4 символов";
    } elseif (findUserByEmail($pdo, $email)) {
        $error = "Такой пользователь уже существует";
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (email, pass_hash, theme_bg, theme_text) VALUES (?, ?, ?, ?)")
                ->execute([$email, $hash, $bg, $txt]);

        $userId = (int)$pdo->lastInsertId();

        // remember-me токен на 7 дней
        $token   = bin2hex(random_bytes(32));
        $expires = (new DateTime('+7 days'))->format('Y-m-d H:i:s');
        $pdo->prepare("UPDATE users SET remember_token=?, token_expires=? WHERE id=?")
                ->execute([$token, $expires, $userId]);

        // Сессия
        $_SESSION['user_id'] = $userId;
        $_SESSION['email']   = $email;
        $_SESSION['theme_bg']   = $bg;
        $_SESSION['theme_text'] = $txt;

        // Куки: токен и цвета (на год)
        setcookie('remember_token', $token, time()+7*24*3600, '/');
        setcookie('theme_bg',  $bg,  time()+365*24*3600, '/');
        setcookie('theme_text',$txt, time()+365*24*3600, '/');

        header("Location: {$__base}/index.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Регистрация</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .card { max-width: 520px; margin: 40px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
        input, button { width: 100%; padding: 8px; margin: 6px 0; }
    </style>
</head>
<body>
<div class="card">
    <h3>Регистрация</h3>
    <?php if (!empty($error)) echo "<p style='color:red;'>".htmlspecialchars($error)."</p>"; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Пароль (≥4)" required>
        <label>Цвет фона: <input type="color" name="theme_bg" value="#ffffff"></label>
        <label>Цвет текста: <input type="color" name="theme_text" value="#000000"></label>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <p><a href="<?= $__base ?>/index.php">На главную</a></p>
</div>
</body>
</html>
