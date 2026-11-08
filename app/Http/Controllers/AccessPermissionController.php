<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\TemporaryPermission;
use App\Models\User;
use Illuminate\Http\Request;

class AccessPermissionController extends Controller
{
    public function create()
    {
        $documents = Document::published()->get();

        return view(
            'admin-grant-access',
            compact('documents')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:single,angkatan,all',

            'duration_type' => 'required|in:default,custom',

            'user_email' => [
                'nullable',
                'required_if:target_type,single',
                'email',
                'exists:users,email',
            ],

            'target_angkatan' => 'nullable'
                .'|required_if:target_type,angkatan'
                .'|integer|between:1901,'.date('Y'),

            'expired_at' => [
                'nullable',
                'required_if:duration_type,custom',
                'date',
                'after:now',
            ],

            'document_id' => 'nullable|exists:documents,id',

            'can_read' => 'nullable|boolean',

            'can_upload' => 'nullable|boolean',
        ]);

        $canRead = $request->boolean('can_read');
        $canUpload = $request->boolean('can_upload');

        if (! $canRead && ! $canUpload) {
            return back()
                ->withErrors([
                    'can_read' => 'Pilih minimal satu izin.',
                ])
                ->withInput();
        }

        if ($canUpload && $request->filled('document_id')) {
            return back()
                ->withErrors([
                    'document_id' => 'Izin upload berlaku global; '
                        .'pilih Semua Dokumen.',
                ])
                ->withInput();
        }

        $userId = null;
        $targetAngkatan = null;
        $isAllStudents = false;

        if ($request->target_type === 'single') {
            $user = User::where(
                'email',
                $request->user_email
            )->firstOrFail();

            $userId = $user->id;
        } elseif ($request->target_type === 'angkatan') {
            $targetAngkatan = $request->target_angkatan;
        } else {
            $isAllStudents = true;
        }

        $expiredAt = $request->duration_type === 'custom'
            ? $request->expired_at
            : now()->addDays(3);

        TemporaryPermission::create([
            'user_id' => $userId,
            'target_angkatan' => $targetAngkatan,
            'is_all_students' => $isAllStudents,
            'document_id' => $request->document_id,
            'can_read' => $canRead,
            'can_upload' => $canUpload,
            'expired_at' => $expiredAt,
            'granted_by' => $request->user()->id,
        ]);

        ActivityLog::record(
            'GRANT_ACCESS',
            'Izin sementara diberikan'
        );

        return back()->with(
            'success',
            'Izin sementara berhasil diberikan.'
        );
    }
}