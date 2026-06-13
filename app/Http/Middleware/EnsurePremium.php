<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePremium
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPremiumAccess()) {
            return response()->json([
                'message' => 'Premium subscription required.',
                'code' => 'premium_required',
                'upgrade_url' => '/premium',
            ], 403);
        }

        return $next($request);
    }
}
