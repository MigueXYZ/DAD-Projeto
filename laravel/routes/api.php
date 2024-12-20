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
    Route::get('/transactions', [TransactionController::class , 'index']);
    Route::post('/transactions', [TransactionController::class , 'store']);

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
Route::post('/boards', [BoardController::class, 'store']);
Route::put('/boards/{board}', [BoardController::class, 'update']);
Route::delete('/boards/{board}', [BoardController::class, 'destroy']);

// Game Routes
Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{game}', [GameController::class, 'show']);
Route::post('/games', [GameController::class, 'store']);
Route::delete('/games/{game}', [GameController::class, 'destroy']);
