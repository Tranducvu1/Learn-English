<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id')) {
            return redirect('/?auth_status=google_disabled');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (! config('services.google.client_id')) {
            return redirect('/?auth_status=google_disabled');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth failed', ['error' => $e->getMessage()]);

            return redirect('/?auth_status=google_error');
        }

        $email = $googleUser->getEmail();
        if (! $email) {
            return redirect('/?auth_status=google_no_email');
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $updates = [];
            if (! $user->google_id) {
                $updates['google_id'] = $googleUser->getId();
            }
            if (! $user->email_verified_at) {
                $updates['email_verified_at'] = now();
            }
            if ($updates !== []) {
                $user->update($updates);
            }
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: 'Học viên',
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ]);

            $user->settings()->create([
                'dark_mode' => false,
                'show_pinyin' => true,
                'font_size' => 'medium',
                'tts_engine' => 'youdao',
            ]);
        }

        $token = $user->createToken('hanviet-app')->plainTextToken;

        return redirect(config('app.url').'/?auth_token='.urlencode($token).'&auth_status=success');
    }
}
