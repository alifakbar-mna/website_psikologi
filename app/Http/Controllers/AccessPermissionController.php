<?php

namespace App\Http\Controllers;

use App\Models\TemporaryPermission;
use App\Models\User;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AccessPermissionController extends Controller
{
    // Form Beri Akses
    public function create()
    {
        $documents = Document::where('status', 'open')->get();
        return view('admin-grant-access', compact('documents'));
    }

    // Eksekusi Simpan Akses
    public function store(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:single,angkatan,all',
            'duration_type' => 'required|in:default,custom',
        ]);

        // Kalkulasi Waktu Expired
        if ($request->duration_type === 'custom' && $request->filled('expired_at')) {
            $expiredAt = Carbon::parse($request->expired_at);
        } else {
            $expiredAt = Carbon::now()->addDays(3); // Default 3 Hari
        }

        $userId = null;
        $targetAngkatan = null;
        $isAllStudents = false;

        if ($request->target_type === 'single') {
            $user = User::where('email', $request->user_email)->first();
            if (!$user) {
                return back()->withErrors(['user_email' => 'Email mahasiswa tidak ditemukan.']);
            }
            $userId = $user->id;
        } elseif ($request->target_type === 'angkatan') {
            $targetAngkatan = $request->target_angkatan;
        } else {
            $isAllStudents = true;
        }

        // Simpan Record Temporary Permission
        TemporaryPermission::create([
            'user_id' => $userId,
            'target_angkatan' => $targetAngkatan,
            'is_all_students' => $isAllStudents,
            'document_id' => $request->document_id ?: null,
            'can_read' => $request->has('can_read'),
            'can_download' => $request->has('can_download'),
            'can_upload' => $request->has('can_upload'),
            'expired_at' => $expiredAt,
            'granted_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Izin akses berhasil diberikan dan berlaku hingga ' . $expiredAt->format('d M Y H:i'));
    }
}