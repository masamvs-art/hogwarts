<?php
require_once __DIR__ . '/../config.php';

$houses = ['Гриффиндор', 'Слизерин', 'Когтевран', 'Пуффендуй'];
$allCourses = [1, 2, 3, 4, 5, 6, 7];

$selectedHousesRaw = $_GET['house'] ?? [];
if (!is_array($selectedHousesRaw)) {
    $selectedHousesRaw = $selectedHousesRaw === '' ? [] : [$selectedHousesRaw];
}
$selectedHouses = array_values(array_intersect($houses, $selectedHousesRaw));

$selectedCoursesRaw = $_GET['course'] ?? [];
if (!is_array($selectedCoursesRaw)) {
    $selectedCoursesRaw = $selectedCoursesRaw === '' ? [] : [$selectedCoursesRaw];
}
$selectedCourses = [];
foreach ($selectedCoursesRaw as $courseValue) {
    $courseInt = (int)$courseValue;
    if (in_array($courseInt, $allCourses, true)) {
        $selectedCourses[] = $courseInt;
    }
}
$selectedCourses = array_values(array_unique($selectedCourses));

$filters = [
    'name' => trim($_GET['name'] ?? ''),
    'surname' => trim($_GET['surname'] ?? ''),
    'house' => $selectedHouses,
    'course' => $selectedCourses,
    'is_deleted' => $_GET['is_deleted'] ?? '',
    'spell_count' => trim($_GET['spell_count'] ?? ''),
];

$students = [];
$errorMessage = '';

$sql = 'SELECT id, name, surname, house, course, is_deleted, spell_count FROM student';
$conditions = [];
$types = '';
$params = [];

if ($filters['name'] !== '') {
    $conditions[] = 'name LIKE ?';
    $types .= 's';
    $params[] = '%' . $filters['name'] . '%';
}
if ($filters['surname'] !== '') {
    $conditions[] = 'surname LIKE ?';
    $types .= 's';
    $params[] = '%' . $filters['surname'] . '%';
}
if (count($filters['house']) > 0) {
    $placeholders = implode(',', array_fill(0, count($filters['house']), '?'));
    $conditions[] = "house IN ($placeholders)";
    $types .= str_repeat('s', count($filters['house']));
    foreach ($filters['house'] as $selectedHouse) {
        $params[] = $selectedHouse;
    }
}
if (count($filters['course']) > 0) {
    $placeholders = implode(',', array_fill(0, count($filters['course']), '?'));
    $conditions[] = "course IN ($placeholders)";
    $types .= str_repeat('i', count($filters['course']));
    foreach ($filters['course'] as $selectedCourse) {
        $params[] = $selectedCourse;
    }
}
if ($filters['is_deleted'] === '0' || $filters['is_deleted'] === '1') {
    $conditions[] = 'is_deleted = ?';
    $types .= 'i';
    $params[] = (int)$filters['is_deleted'];
}
if ($filters['spell_count'] !== '' && ctype_digit($filters['spell_count'])) {
    $conditions[] = 'spell_count = ?';
    $types .= 'i';
    $params[] = (int)$filters['spell_count'];
}

if (count($conditions) > 0) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY surname, name';

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $students[] = $row;
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
    <title>Хогвартс - Студенты</title>
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
            <a href="/student/index.php" class="btn btn-outline-warning btn-sm active">Студенты</a>
            <a href="/mastery/index.php" class="btn btn-outline-warning btn-sm">Освоение</a>
        </div>
    </div>
</nav>

<main class="container my-4">
    <div class="hw-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="mb-0">Студенты</h1>
            <a href="/student/add.php" class="btn hw-btn-add">Добавить студента</a>
        </div>

        <form method="get" class="row g-2 align-items-start mb-4 hw-filter-bar hw-filter-compact">
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm" name="name" placeholder="Имя" value="<?php echo htmlspecialchars($filters['name'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm" name="surname" placeholder="Фамилия" value="<?php echo htmlspecialchars($filters['surname'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm hw-multi" name="house[]" multiple size="4" title="Факультеты">
                    <?php foreach ($houses as $house): ?>
                        <option value="<?php echo htmlspecialchars($house, ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array($house, $filters['house'], true) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($house, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-light-emphasis">Ctrl/Cmd: выбрать несколько</small>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm hw-multi" name="course[]" multiple size="7" title="Курсы">
                    <?php foreach ($allCourses as $course): ?>
                        <option value="<?php echo $course; ?>" <?php echo in_array($course, $filters['course'], true) ? 'selected' : ''; ?>>
                            Курс <?php echo $course; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-light-emphasis">Ctrl/Cmd: выбрать несколько</small>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="is_deleted">
                    <option value="">Удалён</option>
                    <option value="0" <?php echo $filters['is_deleted'] === '0' ? 'selected' : ''; ?>>Нет</option>
                    <option value="1" <?php echo $filters['is_deleted'] === '1' ? 'selected' : ''; ?>>Да</option>
                </select>
            </div>
            <div class="col-md-1">
                <input type="number" class="form-control form-control-sm" name="spell_count" min="0" placeholder="Закл." value="<?php echo htmlspecialchars($filters['spell_count'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-1 d-flex flex-column gap-2 hw-filter-actions">
                <button type="submit" class="btn btn-sm hw-btn-edit" aria-label="Применить фильтры" title="Применить фильтры">🔎</button>
                <a href="/student/index.php" class="btn btn-sm btn-outline-warning" aria-label="Сбросить фильтры" title="Сбросить фильтры">↺</a>
            </div>
        </form>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger" role="alert">
                Ошибка запроса: <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php elseif (count($students) === 0): ?>
            <div class="alert alert-warning" role="alert">По текущим фильтрам ничего не найдено.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover hw-table align-middle">
                    <thead>
                    <tr>
                        <th style="width: 80px;">№</th>
                        <th>Имя</th>
                        <th>Фамилия</th>
                        <th>Факультет</th>
                        <th>Курс</th>
                        <th>Удалён</th>
                        <th>Кол-во заклинаний</th>
                        <th class="text-end">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $index => $student): ?>
                        <?php
                        $houseClassMap = [
                            'Гриффиндор' => 'house-gryffindor',
                            'Слизерин' => 'house-slytherin',
                            'Когтевран' => 'house-ravenclaw',
                            'Пуффендуй' => 'house-hufflepuff',
                        ];
                        $houseClass = $houseClassMap[$student['house']] ?? '';
                        ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($student['surname'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="house-badge <?php echo htmlspecialchars($houseClass, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($student['house'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td><?php echo $student['course'] === null ? '-' : (int)$student['course']; ?></td>
                            <td><?php echo ((int)$student['is_deleted'] === 1) ? 'Да' : 'Нет'; ?></td>
                            <td><?php echo (int)$student['spell_count']; ?></td>
                            <td class="text-end">
                                <a href="/student/edit.php?id=<?php echo (int)$student['id']; ?>" class="btn btn-sm hw-btn-edit">Редактировать</a>
                                <a
                                    href="/student/delete.php?id=<?php echo (int)$student['id']; ?>"
                                    class="btn btn-sm hw-btn-delete js-delete-link"
                                    data-label="<?php echo htmlspecialchars($student['name'] . ' ' . $student['surname'], ENT_QUOTES, 'UTF-8'); ?>"
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
        document.getElementById('deleteModalText').textContent = link.dataset.label ? 'Студент: ' + link.dataset.label : '';
        modal.show();
    });
});
</script>
</body>
</html>
