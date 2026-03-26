<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);

// Подключение к базе данных
$db = new SQLite3(__DIR__ . '/../db/games.db');

// Функция для нахождения НОД
function gcd($a, $b) {
    while ($b != 0) {
        $temp = $b;
        $b = $a % $b;
        $a = $temp;
    }
    return $a;
}

// Маршрут для главной страницы (отдает index.html)
$app->get('/', function (Request $request, Response $response) {
    $html = file_get_contents(__DIR__ . '/index.html');
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});

// GET /games - получить все игры
$app->get('/games', function (Request $request, Response $response) use ($db) {
    $result = $db->query("SELECT id, player_name, score, total_rounds, game_date FROM games ORDER BY game_date DESC");
    $games = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $games[] = $row;
    }
    $response->getBody()->write(json_encode($games, JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET /games/{id} - получить игру по ID с ходами
$app->get('/games/{id}', function (Request $request, Response $response, $args) use ($db) {
    $id = $args['id'];
    
    $stmt = $db->prepare("SELECT * FROM games WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $game = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$game) {
        $response->getBody()->write(json_encode(['error' => 'Game not found']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
    
    $stmt = $db->prepare("SELECT * FROM game_rounds WHERE game_id = :game_id ORDER BY round_number");
    $stmt->bindValue(':game_id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $rounds = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rounds[] = $row;
    }
    
    $game['rounds'] = $rounds;
    $response->getBody()->write(json_encode($game, JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json');
});

// POST /games - начать новую игру
$app->post('/games', function (Request $request, Response $response) use ($db) {
    $data = json_decode($request->getBody()->getContents(), true);
    $playerName = $data['player_name'] ?? 'Anonymous';
    
    $stmt = $db->prepare("INSERT INTO games (player_name) VALUES (:name)");
    $stmt->bindValue(':name', $playerName, SQLITE3_TEXT);
    $stmt->execute();
    
    $gameId = $db->lastInsertRowID();
    
    $num1 = rand(10, 99);
    $num2 = rand(10, 99);
    
    $response->getBody()->write(json_encode([
        'game_id' => $gameId,
        'num1' => $num1,
        'num2' => $num2,
        'round' => 1,
        'total_rounds' => 3,
        'score' => 0
    ], JSON_UNESCAPED_UNICODE));
    
    return $response->withHeader('Content-Type', 'application/json');
});

// POST /step/{id} - сделать ход
$app->post('/step/{id}', function (Request $request, Response $response, $args) use ($db) {
    $id = $args['id'];
    $data = json_decode($request->getBody()->getContents(), true);
    
    $userAnswer = (int)$data['answer'];
    $num1 = (int)$data['num1'];
    $num2 = (int)$data['num2'];
    $currentRound = (int)$data['round'];
    $currentScore = (int)$data['score'];
    
    $correctAnswer = gcd($num1, $num2);
    $isCorrect = ($userAnswer === $correctAnswer);
    $newScore = $currentScore + ($isCorrect ? 1 : 0);
    $nextRound = $currentRound + 1;
    
    // Сохраняем раунд в БД
    $stmt = $db->prepare("
        INSERT INTO game_rounds (game_id, round_number, num1, num2, user_answer, correct_answer, is_correct)
        VALUES (:game_id, :round, :num1, :num2, :user_answer, :correct_answer, :is_correct)
    ");
    $stmt->bindValue(':game_id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':round', $currentRound, SQLITE3_INTEGER);
    $stmt->bindValue(':num1', $num1, SQLITE3_INTEGER);
    $stmt->bindValue(':num2', $num2, SQLITE3_INTEGER);
    $stmt->bindValue(':user_answer', $userAnswer, SQLITE3_INTEGER);
    $stmt->bindValue(':correct_answer', $correctAnswer, SQLITE3_INTEGER);
    $stmt->bindValue(':is_correct', $isCorrect ? 1 : 0, SQLITE3_INTEGER);
    $stmt->execute();
    
    $isGameOver = ($nextRound > 3);
    
    if ($isGameOver) {
        // Обновляем счет игры
        $stmt = $db->prepare("UPDATE games SET score = :score WHERE id = :game_id");
        $stmt->bindValue(':score', $newScore, SQLITE3_INTEGER);
        $stmt->bindValue(':game_id', $id, SQLITE3_INTEGER);
        $stmt->execute();
        
        $response->getBody()->write(json_encode([
            'is_correct' => $isCorrect,
            'correct_answer' => $correctAnswer,
            'is_game_over' => true,
            'score' => $newScore,
            'total_rounds' => 3
        ], JSON_UNESCAPED_UNICODE));
    } else {
        // Генерируем следующий вопрос
        $newNum1 = rand(10, 99);
        $newNum2 = rand(10, 99);
        
        $response->getBody()->write(json_encode([
            'is_correct' => $isCorrect,
            'correct_answer' => $correctAnswer,
            'is_game_over' => false,
            'next_round' => $nextRound,
            'score' => $newScore,
            'num1' => $newNum1,
            'num2' => $newNum2
        ], JSON_UNESCAPED_UNICODE));
    }
    
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();