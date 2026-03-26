<?php
require_once __DIR__ . '/../config.php';

$spells = [];
$errorMessage = '';

$stmt = mysqli_prepare($conn, 'SELECT id, name FROM spell ORDER BY name');
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $spells[] = $row;
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
    <title>Хогвартс - Заклинания</title>
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="mb-0">Заклинания</h1>
            <a href="/spell/add.php" class="btn hw-btn-add">Добавить заклинание</a>
        </div>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger" role="alert">
                Ошибка запроса: <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php elseif (count($spells) === 0): ?>
            <div class="alert alert-warning" role="alert">Пока нет заклинаний.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover hw-table align-middle">
                    <thead>
                    <tr>
                        <th style="width: 80px;">№</th>
                        <th>Название</th>
                        <th class="text-end">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($spells as $index => $spell): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($spell['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-end">
                                <a href="/spell/edit.php?id=<?php echo (int)$spell['id']; ?>" class="btn btn-sm hw-btn-edit">Редактировать</a>
                                <a
                                    href="/spell/delete.php?id=<?php echo (int)$spell['id']; ?>"
                                    class="btn btn-sm hw-btn-delete js-delete-link"
                                    data-label="<?php echo htmlspecialchars($spell['name'], ENT_QUOTES, 'UTF-8'); ?>"
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
                <p class="mb-2">Удалить запись?</p>
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
        document.getElementById('deleteConfirmBtn').setAttribute('href', link.getAttribute('href'));
        document.getElementById('deleteModalText').textContent = link.dataset.label ? 'Заклинание: ' + link.dataset.label : '';
        modal.show();
    });
});
</script>
</body>
</html>
