<?php
require_once __DIR__ . '/../config.php';

$selectedStudentId = (int)($_GET['student_id'] ?? 0);
$selectedSpellId = (int)($_GET['spell_id'] ?? 0);

$masteries = [];
$students = [];
$spells = [];
$errorMessage = '';

$studentsStmt = mysqli_prepare(
    $conn,
    "SELECT id, CONCAT(name, ' ', surname) AS label FROM student ORDER BY surname, name"
);
if ($studentsStmt) {
    mysqli_stmt_execute($studentsStmt);
    $studentsResult = mysqli_stmt_get_result($studentsStmt);
    if ($studentsResult) {
        while ($row = mysqli_fetch_assoc($studentsResult)) {
            $students[] = $row;
        }
    } else {
        $errorMessage = mysqli_error($conn);
    }
    mysqli_stmt_close($studentsStmt);
} else {
    $errorMessage = mysqli_error($conn);
}

$spellsStmt = mysqli_prepare($conn, 'SELECT id, name FROM spell ORDER BY name');
if ($spellsStmt) {
    mysqli_stmt_execute($spellsStmt);
    $spellsResult = mysqli_stmt_get_result($spellsStmt);
    if ($spellsResult) {
        while ($row = mysqli_fetch_assoc($spellsResult)) {
            $spells[] = $row;
        }
    } else {
        $errorMessage = mysqli_error($conn);
    }
    mysqli_stmt_close($spellsStmt);
} else {
    $errorMessage = mysqli_error($conn);
}

$sql = "SELECT m.id,
               CONCAT(s.name, ' ', s.surname) AS student_name,
               sp.name AS spell_name
        FROM mastery m
        JOIN student s ON s.id = m.student_id
        JOIN spell sp ON sp.id = m.spell_id";
$conditions = [];
$types = '';
$params = [];

if ($selectedStudentId > 0) {
    $conditions[] = 'm.student_id = ?';
    $types .= 'i';
    $params[] = $selectedStudentId;
}
if ($selectedSpellId > 0) {
    $conditions[] = 'm.spell_id = ?';
    $types .= 'i';
    $params[] = $selectedSpellId;
}
if (count($conditions) > 0) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY s.surname, s.name, sp.name';

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
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
        <a class="navbar-brand hw-brand" href="/index.php">
            <span class="hw-brand-icon">🏰</span>
            <span class="hw-brand-text">Hogwarts</span>
        </a>
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

        <form method="get" class="row g-2 mb-4 hw-filter-bar">
            <div class="col-md-4">
                <select class="form-select" name="student_id">
                    <option value="0">Все ученики</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo (int)$student['id']; ?>" <?php echo ((int)$student['id'] === $selectedStudentId) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($student['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select" name="spell_id">
                    <option value="0">Все заклинания</option>
                    <?php foreach ($spells as $spell): ?>
                        <option value="<?php echo (int)$spell['id']; ?>" <?php echo ((int)$spell['id'] === $selectedSpellId) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($spell['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn hw-btn-edit">Показать</button>
                <a href="/mastery/index.php" class="btn btn-outline-warning">Сбросить</a>
            </div>
        </form>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger" role="alert">
                Ошибка запроса: <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php elseif (count($masteries) === 0): ?>
            <div class="alert alert-warning" role="alert">По текущим фильтрам ничего не найдено.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover hw-table align-middle">
                    <thead>
                    <tr>
                        <th style="width: 80px;">№</th>
                        <th>Студент</th>
                        <th>Заклинание</th>
                        <th class="text-end">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($masteries as $index => $item): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($item['student_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($item['spell_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-end">
                                <a href="/mastery/edit.php?id=<?php echo (int)$item['id']; ?>" class="btn btn-sm hw-btn-edit">Редактировать</a>
                                <a
                                    href="/mastery/delete.php?id=<?php echo (int)$item['id']; ?>"
                                    class="btn btn-sm hw-btn-delete js-delete-link"
                                    data-student="<?php echo htmlspecialchars($item['student_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-spell="<?php echo htmlspecialchars($item['spell_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                >Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border border-warning-subtle">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Удалить запись освоения?</p>
                <p class="mb-0 small text-warning" id="deleteModalText"></p>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-warning" data-bs-dismiss="modal">Отмена</button>
                <a href="#" class="btn hw-btn-delete" id="deleteConfirmBtn">Удалить</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.js-delete-link').forEach(function (link) {
    link.addEventListener('click', function (event) {
        event.preventDefault();
        var modalElement = document.getElementById('deleteModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        var student = link.dataset.student || '';
        var spell = link.dataset.spell || '';
        document.getElementById('deleteConfirmBtn').setAttribute('href', link.getAttribute('href'));
        document.getElementById('deleteModalText').textContent = student && spell ? (student + ' — ' + spell) : '';
        modal.show();
    });
});
</script>
</body>
</html>
