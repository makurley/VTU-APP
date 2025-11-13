<?php

use App\Http\Controllers\PaymentController;
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
// Payment Callback
Route::controller(PaymentController::class)->group(function(){
    Route::post('/monnify-webhook-notification', 'monnify_webhook')->name('monnify.webhook');
    Route::any('/payvessel-webhook', 'payvessel_webhook')->name('payvessel.webhook');
    Route::post('/wema-playground-webhook', 'wema_webhook')->name('wema.webhook');
});
