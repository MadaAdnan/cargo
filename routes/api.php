<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::prefix('v1')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/profile', [\App\Http\Controllers\Api\AuthController::class, 'profile']);

        Route::apiResource('tasks', \App\Http\Controllers\Api\TaskController::class)->only(['index', 'store', 'update']);
        Route::apiResource('orders', \App\Http\Controllers\Api\OrderController::class)->only(['index', 'show']);
        Route::apiResource('balances', \App\Http\Controllers\Api\BalanceController::class)->only(['index']);
        Route::get('me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
        Route::get('users', [\App\Http\Controllers\Api\AuthController::class, 'index']);
        Route::post('tasks/success/{id}', [\App\Http\Controllers\Api\TaskController::class, 'confirmedTask']);
        Route::post('orders/success', [\App\Http\Controllers\Api\OrderController::class, 'setToSuccess']);
        Route::post('orders/returned', [\App\Http\Controllers\Api\OrderController::class, 'setToReturned']);
        Route::post('orders/confirmed', [\App\Http\Controllers\Api\OrderController::class, 'setToConfirmedReturned']);
        Route::post('orders/canceled', [\App\Http\Controllers\Api\OrderController::class, 'setToCanceled']);
        Route::post('balances/push', [\App\Http\Controllers\Api\BalanceController::class, 'push']);
        Route::post('balances/push/confirmed/{id}', [\App\Http\Controllers\Api\BalanceController::class, 'pushConfirmed']);

        Route::post('balances/pull', [\App\Http\Controllers\Api\BalanceController::class, 'pull']);

    });
});
