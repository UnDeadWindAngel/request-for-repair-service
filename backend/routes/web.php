<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function () {
    // Публичный маршрут логина
    Route::post('/login', [AuthController::class, 'login']);

    // Защищённые маршруты (аутентифицированные пользователи)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
    Route::middleware(['auth:sanctum', 'role:dispatcher'])->group(function () {
        // назначить мастера, отменить
    });
});
