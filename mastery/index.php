<?php
require_once __DIR__ . '/../config.php';

$masteries = [];
$errorMessage = '';

$stmt = mysqli_prepare(
    $conn,
    "SELECT m.id,
            CONCAT(s.name, ' ', s.surname) AS student_name,
            sp.name AS spell_name
     FROM mastery m
     JOIN student s ON s.id = m.student_id
     JOIN spell sp ON sp.id = m.spell_id
     ORDER BY s.surname, s.name, sp.name"
);

if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $masteries[] = $row;
        }
    } else {
        $errorMessage = mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    $errorMessage = mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Хогвартс - Освоение заклинаний</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link href="../assets/css/hogwarts.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark hw-navbar">
    <div class="container">
        <a class="navbar-brand hw-brand" href="/index.php">⚡ Хогвартс</a>
        <div class="navbar-nav ms-auto d-flex flex-row gap-2">
            <a href="/spell/index.php" class="btn btn-outline-warning btn-sm">Заклинания</a>
            <a href="/student/index.php" class="btn btn-outline-warning btn-sm">Студенты</a>
            <a href="/mastery/index.php" class="btn btn-outline-warning btn-sm active">Освоение</a>
        </div>
    </div>
</nav>

<main class="container my-4">
    <div class="hw-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="mb-0">Освоение заклинаний</h1>
            <a href="/mastery/add.php" class="btn hw-btn-add">Добавить запись</a>
        </div>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger" role="alert">
                Ошибка запроса: <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php elseif (count($masteries) === 0): ?>
            <div class="alert alert-warning" role="alert">Пока нет записей освоения.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover hw-table align-middle">
                    <thead>
                    <tr>
                        <th>Студент</th>
                        <th>Заклинание</th>
                        <th class="text-end">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($masteries as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['student_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($item['spell_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-end">
                                <a href="/mastery/edit.php?id=<?php echo (int)$item['id']; ?>" class="btn btn-sm hw-btn-edit">Редактировать</a>
                                <a href="/mastery/delete.php?id=<?php echo (int)$item['id']; ?>" class="btn btn-sm hw-btn-delete" onclick="return confirm('Удалить запись освоения?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
