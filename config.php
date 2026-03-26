<?php
$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    die('Файл .env не найден. Скопируйте .env.example в .env и заполните данные.');
}

$env = parse_ini_file($envPath);

$host = $env['DB_HOST'] ?? 'localhost';
$db = $env['DB_NAME'] ?? '';
$user = $env['DB_USER'] ?? '';
$pass = $env['DB_PASS'] ?? '';
$port = (int)($env['DB_PORT'] ?? 3306);

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
