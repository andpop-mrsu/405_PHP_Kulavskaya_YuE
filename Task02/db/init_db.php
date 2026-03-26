<?php
$db = new SQLite3(__DIR__ . '/games.db');

// Создание таблицы games
$db->exec("
CREATE TABLE IF NOT EXISTS games (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    player_name TEXT NOT NULL,
    game_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    score INTEGER DEFAULT 0,
    total_rounds INTEGER DEFAULT 3
)
");

// Создание таблицы game_rounds
$db->exec("
CREATE TABLE IF NOT EXISTS game_rounds (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    game_id INTEGER NOT NULL,
    round_number INTEGER NOT NULL,
    num1 INTEGER NOT NULL,
    num2 INTEGER NOT NULL,
    user_answer INTEGER,
    correct_answer INTEGER NOT NULL,
    is_correct BOOLEAN DEFAULT 0,
    FOREIGN KEY (game_id) REFERENCES games(id)
)
");

echo "База данных успешно создана!\n";
echo "Файл: " . __DIR__ . "/games.db\n";