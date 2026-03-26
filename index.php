<?php
require_once __DIR__ . '/config.php';

$dbStatus = mysqli_ping($conn) ? 'Соединение с БД успешно.' : 'Проблема с подключением к БД.';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Хогвартс - Главная</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link href="assets/css/hogwarts.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark hw-navbar">
    <div class="container">
        <a class="navbar-brand hw-brand" href="/hogwarts/index.php">⚡ Хогвартс</a>
        <div class="navbar-nav ms-auto d-flex flex-row gap-2">
            <a href="/hogwarts/spell/index.php" class="btn btn-outline-warning btn-sm">Заклинания</a>
            <a href="/hogwarts/student/index.php" class="btn btn-outline-warning btn-sm">Студенты</a>
            <a href="/hogwarts/mastery/index.php" class="btn btn-outline-warning btn-sm">Освоение</a>
        </div>
    </div>
</nav>

<main class="container my-4">
    <div class="hw-card p-4 p-md-5">
        <h1 class="mb-3">Hogwarts CRUD</h1>
        <p class="mb-4 text-light-emphasis">Управление сущностями базы данных в стиле Хогвартса.</p>
        <div class="alert alert-info mb-4" role="alert">
            <?php echo htmlspecialchars($dbStatus, ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <a class="section-card text-decoration-none d-block h-100" href="/hogwarts/spell/index.php">
                    <h2 class="h4 mb-2">🦃 Заклинания</h2>
                    <p class="mb-0">CRUD для таблицы <code>spell</code>.</p>
                </a>
            </div>
            <div class="col-md-4">
                <a class="section-card text-decoration-none d-block h-100" href="/hogwarts/student/index.php">
                    <h2 class="h4 mb-2">🧙 Студенты</h2>
                    <p class="mb-0">CRUD для таблицы <code>student</code>.</p>
                </a>
            </div>
            <div class="col-md-4">
                <a class="section-card text-decoration-none d-block h-100" href="/hogwarts/mastery/index.php">
                    <h2 class="h4 mb-2">⚡ Освоение</h2>
                    <p class="mb-0">CRUD для таблицы <code>mastery</code>.</p>
                </a>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
