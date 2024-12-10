<?php

namespace App\Models;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class MultiplayerGamesPlayed extends Model
{
    use HasFactory;

    public $timestamps = false;

    /*
     * The attributes are
     *  user_id:int
     *  game_id:int
     *  player_won:tinyInteger(1)
     *  pairs_discovered:int
     *  custom:Json
     */

    protected $fillable = [
        'user_id',
        'game_id',
        'player_won',
        'pairs_discovered',
        'custom',
    ];

    protected $casts = [
        'player_won' => 'boolean',
        'pairs_discovered' => 'integer',
        'custom' => 'array',
    ];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Game
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }


}
