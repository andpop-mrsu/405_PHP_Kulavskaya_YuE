<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRound extends Model
{
    protected $fillable = ['game_id', 'round_number', 'num1', 'num2', 'user_answer', 'correct_answer', 'is_correct'];
    
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}