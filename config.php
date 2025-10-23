<?php
$DB_HOST = '127.0.0.1';
$DB_PORT = '3306';
$DB_NAME = 'lab3_php';
$DB_USER = 'root';
$DB_PASS = 'root';

$dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4";

try{
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (Throwable $e){
    die("DB connection error: " . $e->getMessage());
}

function findUserByEmail(PDO $pdo, string $email): ?array {
    $st = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $st->execute([$email]);
    $u = $st->fetch(PDO::FETCH_ASSOC);
    return $u ?: null;
}
