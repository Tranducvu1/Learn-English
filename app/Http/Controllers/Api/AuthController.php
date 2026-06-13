<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'hsk_level' => ['nullable', 'string', 'max:8'],
            'speech_enabled' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->settings()->create([
            'dark_mode' => false,
            'show_pinyin' => true,
            'font_size' => 'medium',
            'tts_engine' => 'youdao',
        ]);

        if (! empty($data['speech_enabled'])) {
            $user->settings()->update(['tts_engine' => 'browser']);
        }

        $token = $user->createToken('hanviet-app')->plainTextToken;

        return response()->json([
            'user' => $this->userPayload($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng. Chưa có tài khoản? Chuyển sang tab Đăng ký.'],
            ]);
        }

        if ($user->google_id && ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Tài khoản này đăng nhập bằng Google.'],
            ]);
        }

        if (! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng.'],
            ]);
        }

        $token = $user->createToken('hanviet-app')->plainTextToken;

        return response()->json([
            'user' => $this->userPayload($user->fresh()),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    private function userPayload(User $user): array
    {
        $user->loadMissing('settings', 'subscriptions');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'isPremium' => $user->hasPremiumAccess(),
            'streak' => $user->streak ?? 0,
            'lastStudyDate' => $user->last_study_date?->format('Y-m-d'),
            'totalStudyMinutes' => $user->total_study_minutes ?? 0,
            'wordsLearned' => $user->words_learned ?? 0,
            'settings' => $user->settings ? [
                'darkMode' => $user->settings->dark_mode,
                'showPinyin' => $user->settings->show_pinyin,
                'fontSize' => $user->settings->font_size,
                'ttsEngine' => $user->settings->tts_engine,
            ] : null,
        ];
    }
}
