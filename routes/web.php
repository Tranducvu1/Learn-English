<?php

use App\Http\Controllers\Web\AppController;
use Illuminate\Support\Facades\Route;

Route::get('/', AppController::class)->name('app.home');

Route::get('/{path}', AppController::class)
    ->where('path', '^(?!api|up|css|js|favicon\.ico|robots\.txt).+');
