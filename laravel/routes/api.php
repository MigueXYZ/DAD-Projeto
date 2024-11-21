<?php

//api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\TaskController;
use App\Http\Controllers\api\ProjectController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\BoardController;
use App\Http\Controllers\api\GameController;
use App\Http\Controllers\api\MultiplayerGamesPlayedController;
use App\Http\Controllers\api\TransactionController;
use App\Http\Controllers\api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


//login
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refreshtoken', [AuthController::class, 'refreshToken']);
    Route::get('/users/me', [UserController::class , 'showMe']);
});
Route::post('/auth/login', [AuthController::class, 'login']);

// User Routes
//Route::resource('users', UserController::class); // For all CRUD actions
//Route::post('/users/restore/{id}', [UserController::class, 'restore']); // For restoring soft-deleted users
//Route::post('/login', [UserController::class, 'loginWithRememberToken']);

// Board Routes
Route::get('/boards', [BoardController::class, 'index']);
Route::get('/boards/{board}', [BoardController::class, 'show']);
Route::post('/boards', [BoardController::class, 'store']);
Route::put('/boards/{board}', [BoardController::class, 'update']);
Route::delete('/boards/{board}', [BoardController::class, 'destroy']);

// Game Routes
Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{game}', [GameController::class, 'show']);
Route::post('/games', [GameController::class, 'store']);
Route::put('/games/{game}', [GameController::class, 'update']);
Route::delete('/games/{game}', [GameController::class, 'destroy']);

// MultiplayerGamesPlayed Routes
Route::get('/multiplayer-games-played', [MultiplayerGamesPlayedController::class, 'index']);
Route::get('/multiplayer-games-played/{multiplayerGame}', [MultiplayerGamesPlayedController::class, 'show']);
Route::post('/multiplayer-games-played', [MultiplayerGamesPlayedController::class, 'store']);
Route::put('/multiplayer-games-played/{multiplayerGame}', [MultiplayerGamesPlayedController::class, 'update']);
Route::delete('/multiplayer-games-played/{multiplayerGame}', [MultiplayerGamesPlayedController::class, 'destroy']);

// Transaction Routes
Route::get('transactions', [TransactionController::class, 'index']);
Route::post('transactions', [TransactionController::class, 'store']);
Route::get('transactions/{id}', [TransactionController::class, 'show']);
Route::put('transactions/{id}', [TransactionController::class, 'update']);
Route::delete('transactions/{id}', [TransactionController::class, 'destroy']);
