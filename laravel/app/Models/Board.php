<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;

    // Disable timestamps for this model
    public $timestamps = false;

    protected $fillable = [
        'board_cols',
        'board_rows',
        'custom',
    ];

    protected $casts = [
        'board_cols' => 'integer',
        'board_rows' => 'integer',
        'custom' => 'array',
    ];

    /**
     * Relationship with Game
     */
    public function games()
    {
        return $this->hasMany(Game::class);
    }
}
