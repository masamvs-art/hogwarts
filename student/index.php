<?php
require_once __DIR__ . '/../config.php';

$houses = ['Гриффиндор', 'Слизерин', 'Когтевран', 'Пуффендуй'];

$filters = [
    'name' => trim($_GET['name'] ?? ''),
    'surname' => trim($_GET['surname'] ?? ''),
    'house' => $_GET['house'] ?? '',
    'course' => trim($_GET['course'] ?? ''),
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
if ($filters['house'] !== '' && in_array($filters['house'], $houses, true)) {
    $conditions[] = 'house = ?';
    $types .= 's';
    $params[] = $filters['house'];
}
if ($filters['course'] !== '' && ctype_digit($filters['course'])) {
    $conditions[] = 'course = ?';
    $types .= 'i';
    $params[] = (int)$filters['course'];
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
        <a class="navbar-brand hw-brand" href="/index.php">⚡ Хогвартс</a>
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

        <form method="get" class="row g-2 mb-4 hw-filter-bar">
            <div class="col-md-2">
                <input type="text" class="form-control" name="name" placeholder="Имя" value="<?php echo htmlspecialchars($filters['name'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control" name="surname" placeholder="Фамилия" value="<?php echo htmlspecialchars($filters['surname'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="house">
                    <option value="">Факультет</option>
                    <?php foreach ($houses as $house): ?>
                        <option value="<?php echo htmlspecialchars($house, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filters['house'] === $house ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($house, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <input type="number" class="form-control" name="course" min="1" max="7" placeholder="Курс" value="<?php echo htmlspecialchars($filters['course'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="is_deleted">
                    <option value="">Удалён</option>
                    <option value="0" <?php echo $filters['is_deleted'] === '0' ? 'selected' : ''; ?>>Нет</option>
                    <option value="1" <?php echo $filters['is_deleted'] === '1' ? 'selected' : ''; ?>>Да</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="spell_count" min="0" placeholder="Кол-во заклин." value="<?php echo htmlspecialchars($filters['spell_count'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn hw-btn-edit">Фильтровать</button>
                <a href="/student/index.php" class="btn btn-outline-warning">Сбросить</a>
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
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($student['surname'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($student['house'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo $student['course'] === null ? '-' : (int)$student['course']; ?></td>
                            <td><?php echo ((int)$student['is_deleted'] === 1) ? 'Да' : 'Нет'; ?></td>
                            <td><?php echo (int)$student['spell_count']; ?></td>
                            <td class="text-end">
                                <a href="/student/edit.php?id=<?php echo (int)$student['id']; ?>" class="btn btn-sm hw-btn-edit">Редактировать</a>
                                <a href="/student/delete.php?id=<?php echo (int)$student['id']; ?>" class="btn btn-sm hw-btn-delete" onclick="return confirm('Удалить студента?')">Удалить</a>
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
