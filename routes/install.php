<?php
/*
|--------------------------------------------------------------------------
| Install Routes
|--------------------------------------------------------------------------
|
| This route is responsible for handling the installation process
*/
use App\Http\Controllers\InstallController;

Route::get('/', [InstallController::class, 'index'])->name('index');

Route::controller(InstallController::class)->prefix('install')->as('install.')->group(function(){    
    Route::get('/', 'index')->name('index');
    Route::get('/agreement', 'agreement')->name('agreement');
    Route::get('/requirement', 'requirement')->name('requirement');
    Route::get('/database', 'database')->name('database');
    Route::get('/setting', 'setting')->name('setting');

    Route::post('/database', 'saveDatabase')->name('savedb'); 
    Route::post('/finish','finishSetup')->name('setup');
});

