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
            'googleEnabled' => (bool) config('services.google.client_id'),
            'hanvietConfig' => [
                'apiUrl' => url('/api'),
                'appName' => config('hanviet.name'),
                'requiresBackend' => true,
                'adsEnabled' => $enabled,
                'adsClientId' => $clientId,
                'adsAutoAds' => ($ads['auto_ads'] ?? true) && $enabled,
                'adsSlots' => $ads['slots'] ?? [],
                'googleEnabled' => (bool) config('services.google.client_id'),
                'premiumPaymentMode' => config('hanviet.premium.payment_mode', 'sandbox'),
            ],
        ]);
    }
}
