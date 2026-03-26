<?php
session_start();

if (!isset($_SESSION['player_name']) || !isset($_SESSION['score'])) {
    header('Location: index.php');
    exit;
}

$playerName = $_SESSION['player_name'];
$score = $_SESSION['score'];
$totalRounds = 3;

session_destroy();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>НОД - результат</title>
</head>
<body>
    <h1>Результат игры</h1>
    <p>Игрок: <?php echo htmlspecialchars($playerName); ?></p>
    <p>Правильных ответов: <?php echo $score; ?> из <?php echo $totalRounds; ?></p>
    
    <?php if ($score == $totalRounds): ?>
        <p>Победа!</p>
    <?php elseif ($score >= 2): ?>
        <p>Хорошо</p>
    <?php else: ?>
        <p>Попробуйте еще раз</p>
    <?php endif; ?>
    
    <hr>
    <a href="index.php">Новая игра</a>
</body>
</html>