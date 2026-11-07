<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAccessExpiration
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Jika user adalah mahasiswa dan waktu aksesnya sudah lewat
        if ($user && $user->role !== 'admin') {
            if ($user->expires_at && now()->greaterThan($user->expires_at)) {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Masa akses akun/dokumen Anda telah berakhir.');
            }
        }

        return $next($request);
    }
}
