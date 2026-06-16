<?php

use App\Http\Controllers\Web\AdsController;
use App\Http\Controllers\Web\AppController;
use App\Http\Controllers\Web\LandingController;
use App\Http\Controllers\Web\SeoController;
use App\Http\Controllers\Web\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');

Route::get('/ads.txt', [AdsController::class, 'adsTxt']);
Route::get('/privacy', [AdsController::class, 'privacy'])->name('privacy');

Route::get('/hoc-tieng-trung', [LandingController::class, 'hocTiengTrung'])->name('landing.hoc-tieng-trung');
Route::get('/luyen-thi-hsk', [LandingController::class, 'luyenThiHsk'])->name('landing.luyen-thi-hsk');
Route::get('/tu-vung-hsk', [LandingController::class, 'tuVungHsk'])->name('landing.tu-vung-hsk');
Route::get('/hsk-{level}', [LandingController::class, 'hskLevel'])
    ->whereNumber('level')
    ->name('landing.hsk');

Route::get('/auth/google', [SocialAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [SocialAuthController::class, 'callback'])->name('auth.google.callback');

Route::get('/', AppController::class)->name('app.home');

Route::get('/{path}', AppController::class)
    ->where('path', '^(?!api|up|css|js|favicon\.ico|ads\.txt|privacy|sitemap\.xml|robots\.txt|og|hoc-tieng-trung|luyen-thi-hsk|tu-vung-hsk|hsk-[1-6]|auth).+');
