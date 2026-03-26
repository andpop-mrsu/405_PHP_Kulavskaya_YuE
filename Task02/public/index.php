<?php
session_start();

$db = new SQLite3(__DIR__ . '/../db/games.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['player_name'])) {
    $playerName = $_POST['player_name'];
    
    $_SESSION['player_name'] = $playerName;
    $_SESSION['score'] = 0;
    $_SESSION['current_round'] = 1;
    
    $stmt = $db->prepare("INSERT INTO games (player_name) VALUES (:name)");
    $stmt->bindValue(':name', $playerName, SQLITE3_TEXT);
    $stmt->execute();
    
    $_SESSION['game_id'] = $db->lastInsertRowID();
    
    $_SESSION['num1'] = rand(10, 99);
    $_SESSION['num2'] = rand(10, 99);
    
    header('Location: game.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>НОД - игра</title>
</head>
<body>
    <h1>Наибольший общий делитель</h1>
    <p>Найдите НОД двух чисел.</p>
    <p>Пример: НОД(12, 18) = 6</p>
    
    <form method="POST">
        <label>Ваше имя:</label>
        <input type="text" name="player_name" required>
        <button type="submit">Начать</button>
    </form>
    
    <hr>
    <p>Правила: 3 вопроса, за каждый правильный ответ - 1 балл.</p>
</body>
</html>