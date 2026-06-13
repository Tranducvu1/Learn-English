<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AppController extends Controller
{
    public function __invoke(): View
    {
        return view('app', [
            'appName' => config('hanviet.name'),
            'assetVersion' => config('hanviet.asset_version'),
        ]);
    }
}
