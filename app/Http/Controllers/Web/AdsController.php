<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdsController extends Controller
{
    public function adsTxt(): Response
    {
        $clientId = config('hanviet.ads.client_id', '');
        $pubId = str_starts_with($clientId, 'ca-pub-')
            ? substr($clientId, 7)
            : $clientId;

        if ($pubId === '') {
            return response("contact: ads@hanviet.local\n", 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        $body = "google.com, pub-{$pubId}, DIRECT, f08c47fec0942fa0\n";

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function privacy(): View
    {
        return view('privacy', [
            'appName' => config('hanviet.name'),
            'siteUrl' => config('app.url'),
        ]);
    }
}
