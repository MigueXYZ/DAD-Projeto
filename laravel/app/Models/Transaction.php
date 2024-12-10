<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'transaction_datetime',
        'user_id',
        'game_id',
        'type',
        'euros',
        'brain_coins',
        'payment_type',
        'payment_reference',
        'custom',
    ];

    protected $casts = [
        'transaction_datetime' => 'datetime',
        'euros' => 'decimal:2',
        'brain_coins' => 'integer',
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
