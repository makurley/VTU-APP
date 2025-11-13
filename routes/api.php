<?php

use App\Http\Controllers\DeveloperController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// User route
Route::controller(DeveloperController::class)->middleware('api-auth')->as('api.')->group(function(){
    Route::get('/user', 'user_details')->name('user');
    Route::get('/user/transactions', 'user_transactions')->name('user.trx');
});
Route::as('api.')->middleware('api-auth')->group(function(){
    Route::post('/data', [App\Http\Controllers\DeveloperController::class, 'buy_data'])->name('data.buy');
    Route::post('/datacard', [App\Http\Controllers\DeveloperController::class, 'buy_datacard'])->name('datacard.buy');
    Route::post('/cable', [App\Http\Controllers\DeveloperController::class, 'buy_cable'])->name('cable.buy');
    Route::post('/bulksms', [App\Http\Controllers\DeveloperController::class, 'send_bulksms'])->name('bulksms.send');
    Route::post('/airtime', [App\Http\Controllers\DeveloperController::class, 'buy_airtime'])->name('airtime.buy');
    Route::post('/power', [App\Http\Controllers\DeveloperController::class, 'buy_power'])->name('power.buy');
    Route::post('/exam', [App\Http\Controllers\DeveloperController::class, 'buy_exam'])->name('result.buy');
    Route::post('/recharge-pin', [App\Http\Controllers\DeveloperController::class, 'print_card'])->name('recharge.buy');
    Route::post('/betting', [App\Http\Controllers\DeveloperController::class, 'buy_betting'])->name('bet.buy');
    // giftcard
    Route::get('/giftcard', [App\Http\Controllers\DeveloperController::class, 'giftcards'])->name('giftcard');
    Route::post('/giftcard', [App\Http\Controllers\DeveloperController::class, 'buy_giftcard'])->name('giftcard.buy');
    // International topup
    Route::post('/international-topup', [App\Http\Controllers\DeveloperController::class, 'buy_topup'])->name('topup');

});
Route::as('api.')->group(function(){
    // verify
    Route::get('/giftcard', [App\Http\Controllers\DeveloperController::class, 'giftcards'])->name('giftcard');
    Route::get('/power/validation', [App\Http\Controllers\DeveloperController::class, 'power_validation'])->name('power.validation');
    Route::get('/bet/validation', [App\Http\Controllers\DeveloperController::class, 'bet_validation'])->name('bet.validation');
    Route::get('/cable/validation', [App\Http\Controllers\DeveloperController::class, 'cable_validation'])->name('cable.validation');
    Route::get('/international-topup/countries', [App\Http\Controllers\DeveloperController::class, 'topup_countries'])->name('topup.countries');
    Route::get('/international-topup/validate', [App\Http\Controllers\DeveloperController::class, 'topup_validation'])->name('topup.validate');
});

