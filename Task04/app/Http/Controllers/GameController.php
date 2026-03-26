<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameRound;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    private function gcd($a, $b) {
        while ($b != 0) {
            $temp = $b;
            $b = $a % $b;
            $a = $temp;
        }
        return $a;
    }
    
    public function index(): JsonResponse
    {
        $games = Game::orderBy('created_at', 'desc')->get();
        return response()->json($games);
    }
    
    public function show($id): JsonResponse
    {
        $game = Game::with('rounds')->find($id);
        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }
        return response()->json($game);
    }
    
    public function store(Request $request): JsonResponse
    {
        $playerName = $request->input('player_name', 'Anonymous');
        
        $game = Game::create([
            'player_name' => $playerName,
            'score' => 0,
            'total_rounds' => 3
        ]);
        
        $num1 = rand(10, 99);
        $num2 = rand(10, 99);
        
        return response()->json([
            'game_id' => $game->id,
            'num1' => $num1,
            'num2' => $num2,
            'round' => 1,
            'total_rounds' => 3,
            'score' => 0
        ]);
    }
    
    public function step(Request $request, $id): JsonResponse
    {
        $game = Game::find($id);
        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }
        
        $userAnswer = (int)$request->input('answer');
        $num1 = (int)$request->input('num1');
        $num2 = (int)$request->input('num2');
        $currentRound = (int)$request->input('round');
        $currentScore = (int)$request->input('score');
        
        $correctAnswer = $this->gcd($num1, $num2);
        $isCorrect = ($userAnswer === $correctAnswer);
        $newScore = $currentScore + ($isCorrect ? 1 : 0);
        $nextRound = $currentRound + 1;
        
        GameRound::create([
            'game_id' => $id,
            'round_number' => $currentRound,
            'num1' => $num1,
            'num2' => $num2,
            'user_answer' => $userAnswer,
            'correct_answer' => $correctAnswer,
            'is_correct' => $isCorrect
        ]);
        
        $isGameOver = ($nextRound > 3);
        
        if ($isGameOver) {
            $game->update(['score' => $newScore]);
            
            return response()->json([
                'is_correct' => $isCorrect,
                'correct_answer' => $correctAnswer,
                'is_game_over' => true,
                'score' => $newScore,
                'total_rounds' => 3
            ]);
        } else {
            $newNum1 = rand(10, 99);
            $newNum2 = rand(10, 99);
            
            return response()->json([
                'is_correct' => $isCorrect,
                'correct_answer' => $correctAnswer,
                'is_game_over' => false,
                'next_round' => $nextRound,
                'score' => $newScore,
                'num1' => $newNum1,
                'num2' => $newNum2
            ]);
        }
    }
}