<?php
session_start();

if (!isset($_SESSION['player_name'])) {
    header('Location: index.php');
    exit;
}

$db = new SQLite3(__DIR__ . '/../db/games.db');

function gcd($a, $b) {
    while ($b != 0) {
        $temp = $b;
        $b = $a % $b;
        $a = $temp;
    }
    return $a;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $userAnswer = (int)$_POST['answer'];
    $correctAnswer = gcd($_SESSION['num1'], $_SESSION['num2']);
    $isCorrect = ($userAnswer === $correctAnswer);
    
    if ($isCorrect) {
        $_SESSION['score']++;
    }
    
    $stmt = $db->prepare("
        INSERT INTO game_rounds (game_id, round_number, num1, num2, user_answer, correct_answer, is_correct)
        VALUES (:game_id, :round, :num1, :num2, :user_answer, :correct_answer, :is_correct)
    ");
    $stmt->bindValue(':game_id', $_SESSION['game_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':round', $_SESSION['current_round'], SQLITE3_INTEGER);
    $stmt->bindValue(':num1', $_SESSION['num1'], SQLITE3_INTEGER);
    $stmt->bindValue(':num2', $_SESSION['num2'], SQLITE3_INTEGER);
    $stmt->bindValue(':user_answer', $userAnswer, SQLITE3_INTEGER);
    $stmt->bindValue(':correct_answer', $correctAnswer, SQLITE3_INTEGER);
    $stmt->bindValue(':is_correct', $isCorrect ? 1 : 0, SQLITE3_INTEGER);
    $stmt->execute();
    
    $_SESSION['current_round']++;
    
    if ($_SESSION['current_round'] > 3) {
        $stmt = $db->prepare("UPDATE games SET score = :score WHERE id = :game_id");
        $stmt->bindValue(':score', $_SESSION['score'], SQLITE3_INTEGER);
        $stmt->bindValue(':game_id', $_SESSION['game_id'], SQLITE3_INTEGER);
        $stmt->execute();
        
        header('Location: result.php');
        exit;
    }
    
    $_SESSION['num1'] = rand(10, 99);
    $_SESSION['num2'] = rand(10, 99);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>НОД - игра</title>
</head>
<body>
    <p>Игрок: <?php echo htmlspecialchars($_SESSION['player_name']); ?></p>
    <p>Счет: <?php echo $_SESSION['score']; ?></p>
    <p>Раунд: <?php echo $_SESSION['current_round']; ?> из 3</p>
    <hr>
    
    <p>Вопрос:</p>
    <p>НОД(<?php echo $_SESSION['num1']; ?>, <?php echo $_SESSION['num2']; ?>) = ?</p>
    
    <form method="POST">
        <label>Ответ:</label>
        <input type="number" name="answer" required autofocus>
        <button type="submit">Ответить</button>
    </form>
</body>
</html>