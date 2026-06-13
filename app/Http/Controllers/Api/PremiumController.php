<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PremiumFeature;
use App\Models\PremiumPlan;
use App\Models\RoleplayScenario;
use Illuminate\Http\JsonResponse;

class PremiumController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = PremiumPlan::all()->keyBy('slug');

        return response()->json([
            'pricing' => [
                'monthly' => isset($plans['monthly']) ? [
                    'amount' => $plans['monthly']->amount,
                    'currency' => $plans['monthly']->currency,
                    'label' => $plans['monthly']->label,
                ] : null,
                'yearly' => isset($plans['yearly']) ? [
                    'amount' => $plans['yearly']->amount,
                    'currency' => $plans['yearly']->currency,
                    'label' => $plans['yearly']->label,
                    'savings' => $plans['yearly']->savings,
                ] : null,
            ],
            'features' => PremiumFeature::orderBy('sort_order')->get()->map(fn ($f) => [
                'id' => $f->id,
                'icon' => $f->icon,
                'title' => $f->title,
                'tagline' => $f->tagline,
                'description' => $f->description,
                'highlights' => $f->highlights,
            ]),
            'roleplayScenarios' => RoleplayScenario::all()->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'level' => $s->level_id,
            ]),
        ]);
    }

    public function demoActivate(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['is_premium' => true]);

        return response()->json([
            'ok' => true,
            'isPremium' => true,
            'message' => 'Đã kích hoạt Premium demo.',
        ]);
    }
}
