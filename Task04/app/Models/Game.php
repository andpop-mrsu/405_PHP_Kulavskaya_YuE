<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = ['player_name', 'score', 'total_rounds'];
    
    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }
}