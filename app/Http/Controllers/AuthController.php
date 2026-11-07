<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // Redirect ke Google OAuth Page
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Callback dari Google
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Tambahkan ->stateless() untuk mencegah Session Deadlock di server lokal
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Cari user berdasarkan email
            $user = User::where('email', $googleUser->getEmail())->first();

            // Jika user tidak terdaftar oleh Admin, tolak akses
            if (!$user) {
                return redirect()->route('login')->with('error', 'Email Anda (' . $googleUser->getEmail() . ') belum terdaftar di sistem. Silakan hubungi Admin.');
            }

            // Login-kan user
            Auth::login($user);

            // Log aktivitas login
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'LOGIN',
                'description' => $user->name . ' berhasil login via Google API.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Redirect sesuai role
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->isDosen()) {
                return redirect()->route('lecturer.requests');
            }

            return redirect()->route('student.catalog');

        } catch (\Exception $e) {
            // Log error asli agar mudah dicari di storage/logs/laravel.log jika ada kendala
            \Illuminate\Support\Facades\Log::error('Google Login Error: ' . $e->getMessage());

            return redirect()->route('login')->with('error', 'Gagal login via Google. Silakan coba lagi.');
        }
    }

    // Logout
    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'LOGOUT',
                'description' => Auth::user()->name . ' melakukan logout.',
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