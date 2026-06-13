<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AppController extends Controller
{
    public function __invoke(): View
    {
        $ads = config('hanviet.ads', []);
        $clientId = $ads['client_id'] ?? '';
        $enabled = ($ads['enabled'] ?? false) && $clientId !== '';

        return view('app', [
            'appName' => config('hanviet.name'),
            'assetVersion' => config('hanviet.asset_version'),
            'adsEnabled' => $enabled,
            'adsClientId' => $clientId,
            'adsVerification' => $ads['verification'] ?? '',
            'adsAutoAds' => $ads['auto_ads'] ?? true,
            'adsSlots' => $ads['slots'] ?? [],
        ]);
    }
}
