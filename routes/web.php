<?php

use App\Http\Controllers\OrderStatusController;
use Illuminate\Support\Facades\Route;
use App\Filament\Admin\Resources\UserResource;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/{id}', function ($id) {

// 7185 - 7293
return "<p>الموقع متوقف للصيانة</p>". "<a href='https://guba-sy.com'>إنتقل للموقع البديل</a>";

    //$users=\App\Models\User::where('level',\App\Enums\LevelUserEnum::USER->value)->pluck('id')->toArray();
   // \App\Models\Balance::where('is_complete',false)->whereIn('user_id',$users)->delete();

    return view('welcome');
});

Route::get('/ship', [OrderStatusController::class, 'index']);

Route::get('phone/{num}',function ($num){
    redirect('wa.me/'.$num);
});

Route::post('/track-shipment',[OrderStatusController::class,'show'])->name('trackShipment');
Route::get('export-order',[\App\Http\Controllers\ExportController::class,'exportOrder'])->name('export-order');
