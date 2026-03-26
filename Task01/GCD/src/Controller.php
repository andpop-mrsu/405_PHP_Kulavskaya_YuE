<?php

namespace JuliaKulav\Gcd\Controller;

use function JuliaKulav\Gcd\View\showWelcome;
use function JuliaKulav\Gcd\View\showQuestion;
use function JuliaKulav\Gcd\View\showResult;
use function JuliaKulav\Gcd\View\showGameEnd;
use function JuliaKulav\Gcd\View\showFarewell;
use function JuliaKulav\Gcd\View\askQuestion;
use function cli\line;

function gcd(int $a, int $b): int
{
    while ($b !== 0) {
        $temp = $b;
        $b = $a % $b;
        $a = $temp;
    }
    return $a;
}

function startGame(): void
{
    showWelcome();

    $name = askQuestion("Представьтесь, пожалуйста");
    line("\nДобро пожаловать, %s!", $name);
    line("Вам нужно ответить на 3 вопроса.\n");

    $score = 0;
    $totalRounds = 3;

    for ($round = 1; $round <= $totalRounds; $round++) {
        line("Раунд %d из %d", $round, $totalRounds);
        
        $a = rand(10, 99);
        $b = rand(10, 99);
        
        showQuestion($a, $b);
        
        $answer = askQuestion("Ваш ответ");
        $correct = gcd($a, $b);
        
        if ((int)$answer === $correct) {
            showResult(true, $correct);
            $score++;
        } else {
            showResult(false, $correct);
            break;
        }
    }
    
    showGameEnd($name, $score, $totalRounds);
    showFarewell($name);
}