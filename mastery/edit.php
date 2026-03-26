<?php
require_once __DIR__ . '/../config.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$studentId = 0;
$spellId = 0;
$errorMessage = '';
$students = [];
$spells = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = (int)($_POST['student_id'] ?? 0);
    $spellId = (int)($_POST['spell_id'] ?? 0);
} else {
    $currentStmt = mysqli_prepare(
        $conn,
        'SELECT student_id, spell_id FROM mastery WHERE id = ?'
    );
    if ($currentStmt) {
        mysqli_stmt_bind_param($currentStmt, 'i', $id);
        mysqli_stmt_execute($currentStmt);
        $currentResult = mysqli_stmt_get_result($currentStmt);
        $current = $currentResult ? mysqli_fetch_assoc($currentResult) : null;
        mysqli_stmt_close($currentStmt);

        if (!$current) {
            header('Location: index.php');
            exit;
        }

        $studentId = (int)$current['student_id'];
        $spellId = (int)$current['spell_id'];
    } else {
        $errorMessage = mysqli_error($conn);
    }
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorMessage === '') {
    if ($studentId <= 0 || $spellId <= 0) {
        $errorMessage = 'Выберите студента и заклинание.';
    } else {
        $checkStmt = mysqli_prepare(
            $conn,
            'SELECT id FROM mastery WHERE student_id = ? AND spell_id = ? AND id <> ? LIMIT 1'
        );
        if ($checkStmt) {
            mysqli_stmt_bind_param($checkStmt, 'iii', $studentId, $spellId, $id);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            $exists = $checkResult && mysqli_fetch_assoc($checkResult);
            mysqli_stmt_close($checkStmt);

            if ($exists) {
                $errorMessage = 'Такая пара студент + заклинание уже существует.';
            } else {
                $updateStmt = mysqli_prepare(
                    $conn,
                    'UPDATE mastery SET student_id = ?, spell_id = ? WHERE id = ?'
                );
                if ($updateStmt) {
                    mysqli_stmt_bind_param($updateStmt, 'iii', $studentId, $spellId, $id);
                    if (mysqli_stmt_execute($updateStmt)) {
                        mysqli_stmt_close($updateStmt);
                        header('Location: index.php');
                        exit;
                    }
                    $errorMessage = (mysqli_errno($conn) === 1062)
                        ? 'Такая пара студент + заклинание уже существует.'
                        : mysqli_error($conn);
                    mysqli_stmt_close($updateStmt);
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
    <title>Хогвартс - Редактировать освоение</title>
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
        <h1 class="mb-3">Редактировать освоение</h1>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger" role="alert">
                Ошибка: <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="student_id" class="form-label">Студент</label>
                    <select class="form-select" id="student_id" name="student_id" required>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo (int)$student['id']; ?>" <?php echo ((int)$student['id'] === $studentId) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="spell_id" class="form-label">Заклинание</label>
                    <select class="form-select" id="spell_id" name="spell_id" required>
                        <?php foreach ($spells as $spell): ?>
                            <option value="<?php echo (int)$spell['id']; ?>" <?php echo ((int)$spell['id'] === $spellId) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spell['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn hw-btn-edit">Сохранить</button>
                <a href="/mastery/index.php" class="btn btn-outline-warning">← Назад к списку</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>
