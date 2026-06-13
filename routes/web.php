<?php

use App\Http\Controllers\Web\AdsController;
use App\Http\Controllers\Web\AppController;
use Illuminate\Support\Facades\Route;

Route::get('/ads.txt', [AdsController::class, 'adsTxt']);
Route::get('/privacy', [AdsController::class, 'privacy'])->name('privacy');

Route::get('/', AppController::class)->name('app.home');

Route::get('/{path}', AppController::class)
    ->where('path', '^(?!api|up|css|js|favicon\.ico|robots\.txt|ads\.txt|privacy).+');
