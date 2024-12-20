<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'nickname',
        'blocked',
        'photo_filename',
        'brain_coins_balance',
        'custom',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'blocked'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'brain_coins_balance' => 'integer',
        'blocked' => 'boolean',
        'custom' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the games created by the user.
     */
    public function createdGames(): HasMany
    {
        return $this->hasMany(Game::class, 'created_user_id');
    }

    /**
     * Get the games won by the user.
     */
    public function wonGames(): HasMany
    {
        return $this->hasMany(Game::class, 'winner_user_id');
    }

    public function games()
    {
        return $this->hasMany(Game::class, 'created_user_id');
    }

    public function isAdmin()
    {
        return $this->type === 'A';
    }

}
