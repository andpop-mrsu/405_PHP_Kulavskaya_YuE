<?php

namespace JuliaKulav\Gcd\View;

use function cli\line;
use function cli\prompt;

function showWelcome(): void
{
    line("\n");
    line("==================================================");
    line("                ИГРА: НАЙДИ НОД");
    line("           Наибольший Общий Делитель");
    line("==================================================");
    line("\nДобро пожаловать в математическую игру!");
    line("Правила: найдите наибольший общий делитель двух чисел.");
    line("Пример: НОД(12, 18) = 6");
    line("==================================================\n");
}

function showQuestion(int $a, int $b): void
{
    line("\n--------------------------------------------------");
    line("Вопрос: НОД(%d, %d) = ?", $a, $b);
    line("--------------------------------------------------");
}

function showResult(bool $isCorrect, int $correct): void
{
    if ($isCorrect) {
        line("\n[ВЕРНО] Правильный ответ!");
        line("------------------------------------------");
    } else {
        line("\n[НЕВЕРНО] Правильный ответ: %d", $correct);
        line("------------------------------------------");
    }
}

function showGameEnd(string $name, int $score, int $total): void
{
    line("\n==================================================");
    line("                  РЕЗУЛЬТАТ");
    line("==================================================");
    line("Игрок: %s", $name);
    line("Правильных ответов: %d из %d", $score, $total);
    
    if ($score == $total) {
        line("Поздравляю! Вы победили!");
    } elseif ($score >= $total / 2) {
        line("Хороший результат!");
    } else {
        line("Попробуйте еще раз!");
    }
    line("==================================================\n");
}

function showFarewell(string $name): void
{
    line("Спасибо за игру, %s!", $name);
    line("Для повторного запуска введите: php bin/gcd\n");
}

function askQuestion(string $prompt): string
{
    return prompt($prompt);
}