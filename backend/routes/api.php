<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RepairRequestController;
use Illuminate\Support\Facades\Route;

// Публичный маршрут логина
Route::post('/login', [AuthController::class, 'login']);

// Защищённые маршруты (аутентифицированные пользователи)
Route::middleware('auth:sanctum')->group(function () {
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/me', [AuthController::class, 'me']);

Route::get('/requests', [RepairRequestController::class, 'index']);
Route::post('/requests', [RepairRequestController::class, 'store']);
Route::get('/requests/{repairRequest}', [RepairRequestController::class, 'show']);
Route::post('/requests/{repairRequest}/assign', [RepairRequestController::class, 'assign']);
Route::post('/requests/{repairRequest}/cancel', [RepairRequestController::class, 'cancel']);
Route::post('/requests/{repairRequest}/take', [RepairRequestController::class, 'take']);
Route::post('/requests/{repairRequest}/complete', [RepairRequestController::class, 'complete']);
});

Route::middleware(['auth:sanctum', 'role:dispatcher'])->group(function () {
// назначить мастера, отменить
});
