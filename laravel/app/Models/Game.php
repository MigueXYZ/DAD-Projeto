<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_user_id',
        'winner_user_id',
        'type',
        'status',
        'began_at',
        'ended_at',
        'total_time',
        'board_id',
        'custom',
        'total_turns_winner',
    ];

    protected $casts = [
        'began_at' => 'datetime',
        'ended_at' => 'datetime',
        'total_time' => 'decimal:2',
        'custom' => 'array',
    ];


    /**
     * Relationship with User who created the game
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    /**
     * Relationship with User who won the game
     */
    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    /**
     * Relationship with Board
     */
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Relationship with MultiplayerGamesPlayed
     */
    public function multiplayerGamesPlayed()
    {
        return $this->hasMany(MultiplayerGamesPlayed::class);
    }

    /**
     * Relationship with Transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
