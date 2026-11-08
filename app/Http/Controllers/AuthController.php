<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where(
                'email',
                $googleUser->getEmail()
            )->first();

            if (! $user) {
                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Email Anda ('.$googleUser->getEmail().') '
                            .'belum terdaftar di sistem. '
                            .'Silakan hubungi Admin.'
                    );
            }

            Auth::login($user);

            $request->session()->regenerate();

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'LOGIN',
                'description' => $user->name
                    .' berhasil login via Google API.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($user->permits('dashboard')) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->permits('approve_access')) {
                return redirect()->route('lecturer.requests');
            }

            return redirect()->route('home');
        } catch (\Exception $exception) {
            Log::error(
                'Google Login Error: '.$exception->getMessage()
            );

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Gagal login via Google. Silakan coba lagi.'
                );
        }
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'LOGOUT',
                'description' => Auth::user()->name
                    .' melakukan logout.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}