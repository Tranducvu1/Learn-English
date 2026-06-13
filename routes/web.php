<?php

use App\Http\Controllers\Web\AdsController;
use App\Http\Controllers\Web\AppController;
use App\Http\Controllers\Web\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/ads.txt', [AdsController::class, 'adsTxt']);
Route::get('/privacy', [AdsController::class, 'privacy'])->name('privacy');

Route::get('/auth/google', [SocialAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [SocialAuthController::class, 'callback'])->name('auth.google.callback');

Route::get('/', AppController::class)->name('app.home');

Route::get('/{path}', AppController::class)
    ->where('path', '^(?!api|up|css|js|favicon\.ico|robots\.txt|ads\.txt|privacy).+');
