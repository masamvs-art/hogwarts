<?php
require_once __DIR__ . '/../config.php';

$houses = ['Гриффиндор', 'Слизерин', 'Когтевран', 'Пуффендуй'];

$name = '';
$surname = '';
$house = $houses[0];
$course = '';
$isDeleted = 0;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $house = $_POST['house'] ?? $houses[0];
    $courseInput = trim($_POST['course'] ?? '');
    $course = $courseInput;
    $isDeleted = (int)($_POST['is_deleted'] ?? 0);
    $courseValue = ($courseInput === '') ? null : (int)$courseInput;

    if ($name === '' || $surname === '') {
        $errorMessage = 'Заполните имя и фамилию.';
    } elseif (!in_array($house, $houses, true)) {
        $errorMessage = 'Выберите корректный факультет.';
    } elseif ($courseInput !== '' && ($courseValue < 1 || $courseValue > 7)) {
        $errorMessage = 'Курс должен быть от 1 до 7.';
    } elseif ($isDeleted !== 0 && $isDeleted !== 1) {
        $errorMessage = 'Некорректное значение удаления.';
    } else {
        if ($courseValue === null) {
            $checkStmt = mysqli_prepare(
                $conn,
                'SELECT id FROM student WHERE name = ? AND surname = ? AND house = ? AND course IS NULL LIMIT 1'
            );
            if ($checkStmt) {
                mysqli_stmt_bind_param($checkStmt, 'sss', $name, $surname, $house);
            }
        } else {
            $checkStmt = mysqli_prepare(
                $conn,
                'SELECT id FROM student WHERE name = ? AND surname = ? AND house = ? AND course = ? LIMIT 1'
            );
            if ($checkStmt) {
                mysqli_stmt_bind_param($checkStmt, 'sssi', $name, $surname, $house, $courseValue);
            }
        }

        if (!$checkStmt) {
            $errorMessage = mysqli_error($conn);
        } else {
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            $exists = $checkResult && mysqli_fetch_assoc($checkResult);
            mysqli_stmt_close($checkStmt);

            if ($exists) {
                $errorMessage = 'Такой студент уже существует.';
            } else {
                $stmt = mysqli_prepare(
                    $conn,
                    'INSERT INTO student (name, surname, house, course, is_deleted) VALUES (?, ?, ?, ?, ?)'
                );
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'sssii', $name, $surname, $house, $courseValue, $isDeleted);
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        header('Location: index.php');
                        exit;
                    }
                    $errorMessage = mysqli_error($conn);
                    mysqli_stmt_close($stmt);
                } else {
                    $errorMessage = mysqli_error($conn);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Хогвартс - Добавить студента</title>
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
        <h1 class="mb-3">Добавить студента</h1>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger" role="alert">
                Ошибка: <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Имя</label>
                    <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-md-6">
                    <label for="surname" class="form-label">Фамилия</label>
                    <input type="text" class="form-control" id="surname" name="surname" required value="<?php echo htmlspecialchars($surname, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-md-4">
                    <label for="house" class="form-label">Факультет</label>
                    <select class="form-select" id="house" name="house" required>
                        <?php foreach ($houses as $houseItem): ?>
                            <option value="<?php echo htmlspecialchars($houseItem, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $houseItem === $house ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($houseItem, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="course" class="form-label">Курс (1-7)</label>
                    <input type="number" class="form-control" id="course" name="course" min="1" max="7" value="<?php echo htmlspecialchars((string)$course, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-md-4">
                    <label for="is_deleted" class="form-label">Удалён</label>
                    <select class="form-select" id="is_deleted" name="is_deleted">
                        <option value="0" <?php echo $isDeleted === 0 ? 'selected' : ''; ?>>Нет</option>
                        <option value="1" <?php echo $isDeleted === 1 ? 'selected' : ''; ?>>Да</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn hw-btn-add">Сохранить</button>
                <a href="/student/index.php" class="btn btn-outline-warning">← Назад к списку</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>
