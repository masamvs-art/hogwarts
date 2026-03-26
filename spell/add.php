<?php
require_once __DIR__ . '/../config.php';

$name = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $errorMessage = 'Введите название заклинания.';
    } else {
        $checkStmt = mysqli_prepare($conn, 'SELECT id FROM spell WHERE name = ? LIMIT 1');
        if ($checkStmt) {
            mysqli_stmt_bind_param($checkStmt, 's', $name);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            $exists = $checkResult && mysqli_fetch_assoc($checkResult);
            mysqli_stmt_close($checkStmt);

            if ($exists) {
                $errorMessage = 'Такое заклинание уже существует.';
            } else {
                $stmt = mysqli_prepare($conn, 'INSERT INTO spell (name) VALUES (?)');
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 's', $name);
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        header('Location: index.php');
                        exit;
                    }
                    $errorMessage = (mysqli_errno($conn) === 1062)
                        ? 'Такое заклинание уже существует.'
                        : mysqli_error($conn);
                    mysqli_stmt_close($stmt);
                } else {
                    $errorMessage = mysqli_error($conn);
                }
            }
        } else {
            $errorMessage = mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Хогвартс - Добавить заклинание</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link href="../assets/css/hogwarts.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark hw-navbar">
    <div class="container">
        <a class="navbar-brand hw-brand" href="/index.php">
            <span class="hw-brand-icon">🏰</span>
            <span class="hw-brand-text">Hogwarts</span>
        </a>
        <div class="navbar-nav ms-auto d-flex flex-row gap-2">
            <a href="/spell/index.php" class="btn btn-outline-warning btn-sm active">Заклинания</a>
            <a href="/student/index.php" class="btn btn-outline-warning btn-sm">Студенты</a>
            <a href="/mastery/index.php" class="btn btn-outline-warning btn-sm">Освоение</a>
        </div>
    </div>
</nav>

<main class="container my-4">
    <div class="hw-card p-4">
        <h1 class="mb-3">Добавить заклинание</h1>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger" role="alert">
                Ошибка: <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="name" class="form-label">Название</label>
                <input
                    type="text"
                    class="form-control"
                    id="name"
                    name="name"
                    maxlength="120"
                    required
                    value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn hw-btn-add">Сохранить</button>
                <a href="/spell/index.php" class="btn btn-outline-warning">← Назад к списку</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>
