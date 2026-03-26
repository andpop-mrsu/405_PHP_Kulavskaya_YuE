<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>НОД - игра на Laravel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        input, button {
            padding: 10px;
            margin: 5px;
            font-size: 16px;
        }
        input {
            width: 200px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background: #3490dc;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            padding: 10px 20px;
        }
        button:hover {
            background: #2779bd;
        }
        .question {
            font-size: 28px;
            font-weight: bold;
            margin: 30px 0;
            text-align: center;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .correct {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .wrong {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #e2e3e5;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .games-list {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .games-list ul {
            list-style: none;
            padding: 0;
        }
        .games-list li {
            background: #f8f9fa;
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .btn-small {
            background: #6c757d;
            font-size: 14px;
            padding: 5px 10px;
        }
        .btn-small:hover {
            background: #5a6268;
        }
        .score {
            font-weight: bold;
            color: #3490dc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Наибольший общий делитель</h1>
        
        <div id="start-screen">
            <div class="info">
                <p><strong>Правила игры:</strong></p>
                <p>Вам будет задано 3 вопроса. Найдите наибольший общий делитель (НОД) двух чисел.</p>
                <p>Пример: НОД(12, 18) = 6</p>
            </div>
            <label>Ваше имя:</label>
            <input type="text" id="player-name" placeholder="Введите имя">
            <button onclick="startGame()">Начать игру</button>
        </div>
        
        <div id="game-screen" style="display:none;">
            <div class="info">
                <p>Игрок: <strong id="player-name-display"></strong></p>
                <p>Счет: <span id="score" class="score">0</span> | Раунд: <span id="round">1</span> из 3</p>
            </div>
            
            <div class="question">
                НОД(<span id="num1"></span>, <span id="num2"></span>) = ?
            </div>
            
            <input type="number" id="answer" placeholder="Введите ответ" autofocus>
            <button onclick="submitAnswer()">Ответить</button>
            
            <div id="result" class="result"></div>
        </div>
        
        <div id="end-screen" style="display:none;">
            <div class="info">
                <h2>Игра завершена!</h2>
                <p>Игрок: <strong id="final-player-name"></strong></p>
                <p>Правильных ответов: <strong id="final-score"></strong> из 3</p>
            </div>
            <button onclick="restartGame()">Новая игра</button>
        </div>
        
        <div class="games-list">
            <h3>История игр</h3>
            <button onclick="loadGames()" class="btn-small">Загрузить историю</button>
            <div id="games-list"></div>
        </div>
    </div>

    <script>
        let currentGameId = null;
        let currentRound = 1;
        let currentScore = 0;
        let totalRounds = 3;
        
        function startGame() {
            const playerName = document.getElementById('player-name').value;
            if (!playerName) {
                alert('Введите ваше имя');
                return;
            }
            
            fetch('/games', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ player_name: playerName })
            })
            .then(response => response.json())
            .then(data => {
                currentGameId = data.game_id;
                currentRound = data.round;
                currentScore = data.score;
                
                document.getElementById('player-name-display').innerText = playerName;
                document.getElementById('score').innerText = currentScore;
                document.getElementById('round').innerText = currentRound;
                document.getElementById('num1').innerText = data.num1;
                document.getElementById('num2').innerText = data.num2;
                
                document.getElementById('start-screen').style.display = 'none';
                document.getElementById('game-screen').style.display = 'block';
                document.getElementById('result').innerHTML = '';
                document.getElementById('answer').value = '';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при начале игры');
            });
        }
        
        function submitAnswer() {
            const answer = document.getElementById('answer').value;
            const num1 = parseInt(document.getElementById('num1').innerText);
            const num2 = parseInt(document.getElementById('num2').innerText);
            
            if (!answer) {
                alert('Введите ответ');
                return;
            }
            
            fetch(`/step/${currentGameId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    answer: answer,
                    num1: num1,
                    num2: num2,
                    round: currentRound,
                    score: currentScore
                })
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('result');
                if (data.is_correct) {
                    resultDiv.innerHTML = '<div class="result correct">✓ Верно! Правильный ответ: ' + data.correct_answer + '</div>';
                } else {
                    resultDiv.innerHTML = '<div class="result wrong">✗ Неверно! Правильный ответ: ' + data.correct_answer + '</div>';
                }
                
                if (data.is_game_over) {
                    document.getElementById('final-player-name').innerText = document.getElementById('player-name-display').innerText;
                    document.getElementById('final-score').innerText = data.score;
                    document.getElementById('game-screen').style.display = 'none';
                    document.getElementById('end-screen').style.display = 'block';
                } else {
                    currentRound = data.next_round;
                    currentScore = data.score;
                    document.getElementById('score').innerText = currentScore;
                    document.getElementById('round').innerText = currentRound;
                    document.getElementById('num1').innerText = data.num1;
                    document.getElementById('num2').innerText = data.num2;
                    document.getElementById('answer').value = '';
                    document.getElementById('answer').focus();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при отправке ответа');
            });
        }
        
        function restartGame() {
            document.getElementById('end-screen').style.display = 'none';
            document.getElementById('start-screen').style.display = 'block';
            document.getElementById('player-name').value = '';
            document.getElementById('games-list').innerHTML = '';
            currentGameId = null;
        }
        
        function loadGames() {
            fetch('/games')
                .then(response => response.json())
                .then(games => {
                    const gamesList = document.getElementById('games-list');
                    if (games.length === 0) {
                        gamesList.innerHTML = '<p>Нет сохраненных игр</p>';
                        return;
                    }
                    let html = '<ul>';
                    games.forEach(game => {
                        const date = new Date(game.created_at);
                        const dateStr = date.toLocaleString('ru-RU');
                        html += `<li><strong>${game.player_name}</strong> - ${game.score}/${game.total_rounds} (${dateStr})</li>`;
                    });
                    html += '</ul>';
                    gamesList.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ошибка при загрузке истории');
                });
        }
    </script>
</body>
</html>