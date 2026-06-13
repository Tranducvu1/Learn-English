<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PremiumFeature;
use App\Models\PremiumPlan;
use App\Models\RoleplayScenario;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'paymentMode' => config('hanviet.premium.payment_mode', 'sandbox'),
        ]);
    }

    public function demoActivate(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['is_premium' => true]);

        return response()->json([
            'ok' => true,
            'isPremium' => true,
            'message' => 'Đã kích hoạt Premium demo.',
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan' => ['required', 'in:monthly,yearly'],
            'method' => ['required', 'in:sandbox,momo,vnpay'],
        ]);

        $user = $request->user();
        $plan = PremiumPlan::where('slug', $data['plan'])->firstOrFail();
        $mode = config('hanviet.premium.payment_mode', 'sandbox');

        if ($user->hasPremiumAccess()) {
            return response()->json([
                'ok' => true,
                'isPremium' => true,
                'message' => 'Bạn đã có Premium.',
            ]);
        }

        if ($data['method'] === 'sandbox' || $mode === 'sandbox') {
            return $this->activateSubscription($user, $plan, 'sandbox');
        }

        return response()->json([
            'ok' => false,
            'code' => 'payment_not_configured',
            'message' => 'Cổng thanh toán Momo/VNPay đang được kích hoạt. Tạm thời dùng Sandbox hoặc liên hệ admin.',
        ], 422);
    }

    private function activateSubscription($user, PremiumPlan $plan, string $provider): JsonResponse
    {
        $months = $plan->slug === 'yearly' ? 12 : 1;
        $ref = 'HV-'.strtoupper(Str::random(10));

        $user->subscriptions()
            ->where('status', 'active')
            ->update(['status' => 'replaced']);

        Subscription::create([
            'user_id' => $user->id,
            'plan_slug' => $plan->slug,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonths($months),
            'payment_provider' => $provider,
            'payment_ref' => $ref,
        ]);

        $user->update(['is_premium' => true]);

        return response()->json([
            'ok' => true,
            'isPremium' => true,
            'message' => $plan->slug === 'yearly'
                ? 'Đã kích hoạt Premium gói năm!'
                : 'Đã kích hoạt Premium gói tháng!',
            'subscription' => [
                'plan' => $plan->slug,
                'provider' => $provider,
                'ref' => $ref,
                'endsAt' => now()->addMonths($months)->toIso8601String(),
            ],
        ]);
    }
}
