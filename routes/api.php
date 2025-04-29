<?php

use App\Http\Controllers\Api\BalanceController;
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
        //Auth
        Route::post('/profile', [\App\Http\Controllers\Api\AuthController::class, 'profile']);
        Route::get('me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
        Route::get('users', [\App\Http\Controllers\Api\AuthController::class, 'index']);

        //Tasks
        Route::apiResource('tasks', \App\Http\Controllers\Api\TaskController::class)->only(['index', 'store', 'update']);
        Route::post('tasks/success/{id}', [\App\Http\Controllers\Api\TaskController::class, 'confirmedTask']);
        Route::get('tasks/incomplete-count', [\App\Http\Controllers\Api\TaskController::class, 'incompleteCount']);

        //Orders
        Route::apiResource('orders', \App\Http\Controllers\Api\OrderController::class)->only(['index', 'show']);
        Route::post('orders/success', [\App\Http\Controllers\Api\OrderController::class, 'setToSuccess']);
        Route::post('orders/returned', [\App\Http\Controllers\Api\OrderController::class, 'setToReturned']);
        Route::post('orders/confirmed', [\App\Http\Controllers\Api\OrderController::class, 'setToConfirmedReturned']);
        Route::post('orders/canceled', [\App\Http\Controllers\Api\OrderController::class, 'setToCanceled']);


        //Balances
        Route::apiResource('balances', \App\Http\Controllers\Api\BalanceController::class)->only(['index']);
        Route::post('balances/push', [\App\Http\Controllers\Api\BalanceController::class, 'push']);
        Route::post('balances/push/confirmed/{id}', [\App\Http\Controllers\Api\BalanceController::class, 'pushConfirmed']);
        Route::Post('balances/push/cancel/{id}',[BalanceController::class,'pushCancel']);
        Route::post('balances/pull', [\App\Http\Controllers\Api\BalanceController::class, 'pull']);
        Route::get('balances/pendingbalance-count',[BalanceController::class,'pendingBalancesCount']);


    });
});
