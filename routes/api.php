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

        Route::apiResource('tasks', \App\Http\Controllers\Api\TaskController::class)->only(['index','store','update']);
        Route::post('orders/success',[\App\Http\Controllers\Api\OrderController::class,'SetToSuccess']);
    });
});
