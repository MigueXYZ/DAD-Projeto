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
    Route::patch('/users/me', [UserController::class , 'updateMe']);
    Route::delete('/users/me', [UserController::class , 'destroyMe']);
    Route::get('/games/me', [GameController::class , 'showMe']);
    Route::get('/games', [GameController::class , 'index']);
    Route::get('/games/record/{game}', [GameController::class , 'checkForRecord']);
    Route::post('/users/me/brain_coins', [TransactionController::class , 'store']);
    Route::get('/transactions/me', [TransactionController::class , 'showMe']);
    Route::post('/transactions', [TransactionController::class , 'store']);
    Route::post('/games/{game}/players', [GameController::class , 'storeMultiplayerGame']);
    Route::patch('/multiplayer-games',[MultiplayerGamesPlayedController::class, 'updateIt']);
    Route::get('/games/{game}/players', [GameController::class , 'getMultiplayerGames']);
    Route::get('/multiplayer-scoreboard', [MultiplayerGamesPlayedController::class, 'getScoreboard']);

    //routes for the admin
    Route::get('/transactions', [TransactionController::class , 'index']);
    //estatisticas brain coins
    Route::get('/brain_coins', [TransactionController::class , 'getBrainCoins']);
    Route::get('/brain_coins/active', [UserController::class , 'getBrainCoins']);
    //estatisticas jogos
    Route::get('/games/total', [GameController::class , 'getNumGames']);
    Route::get('/games/total/board', [GameController::class , 'getNumGamesByBoard']);
    Route::get('/games/total/gamemode', [GameController::class , 'getNumGamesByGameMode']);
    //estatisticas transacoes
    Route::get('/transactions/total', [TransactionController::class , 'getNumTransactions']);
    Route::get('/transactions/total/payment_method', [TransactionController::class , 'getNumTransactionsByPaymentMethod']);
    Route::get('/transactions/total/type', [TransactionController::class , 'getNumTransactionsByType']);
    //estatisticas utilizadores
    Route::get('/users/total', [UserController::class , 'getNumUsers']);
    Route::get('/users/total/active', [UserController::class , 'getNumActiveUsers']);


});
Route::post('/upload-avatar', [UserController::class , 'uploadAvatar']);

Route::patch('/games/{game}', [GameController::class, 'update']);

Route::get('/games/{game}', [GameController::class, 'show']);

Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/users/top', [UserController::class , 'getTop']);

Route::post('/users', [UserController::class, 'store']);

Route::get('/users/names', [UserController::class, 'getNames']);


// Board Routes
Route::get('/boards', [BoardController::class, 'index']);
Route::get('/boards/{board}', [BoardController::class, 'show']);

// Game Routes
Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{game}', [GameController::class, 'show']);
Route::post('/games', [GameController::class, 'store']);
Route::delete('/games/{game}', [GameController::class, 'destroy']);
