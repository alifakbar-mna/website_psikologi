<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\TemporaryPermission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentRequestController extends Controller
{
    public function studentIndex()
    {
        return view('student-requests', [
            'requests' => DocumentRequest::with('document')
                ->where('user_id', auth()->id())
                ->latest()
                ->get(),

            'documents' => Document::published()
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'request_type' => 'required|in:baca_sistem,baca_dokumen,upload',
            'reason' => 'required|string|max:500',
            'document_id' => [
                'nullable',
                'required_if:request_type,baca_dokumen',
                'integer',
                'exists:documents,id',
            ],
        ]);

        if ($data['request_type'] === 'baca_dokumen') {
            abort_unless(
                Document::findOrFail($data['document_id'])->isPublished(),
                422,
                'Laporan tidak tersedia.'
            );
        } else {
            $data['document_id'] = null;
        }

        DocumentRequest::create($data + [
            'user_id' => $r->user()->id,
            'status' => 'pending',
        ]);

        return back()->with(
            'success',
            'Permohonan dikirim. Menunggu persetujuan.'
        );
    }

    public function lecturerIndex()
    {
        return view('lecturer-requests', [
            'pendingRequests' => DocumentRequest::with(['user', 'document'])
                ->where('status', 'pending')
                ->latest()
                ->get(),
        ]);
    }

    public function process(Request $r, $id)
    {
        $data = $r->validate([
            'status' => 'required|in:approved,rejected',
            'reject_reason' => [
                'nullable',
                'required_if:status,rejected',
                'string',
                'max:1000',
            ],
            'expired_date' => [
                'nullable',
                'required_if:status,approved',
                'date_format:Y-m-d',
            ],
            'expired_time' => [
                'nullable',
                'required_if:status,approved',
                'date_format:H:i',
            ],
        ]);

        $expires = null;

        if ($data['status'] === 'approved') {
            $expires = Carbon::createFromFormat(
                'Y-m-d H:i',
                $data['expired_date'].' '.$data['expired_time'],
                config('app.timezone')
            );

            if ($expires->isPast()) {
                return back()->withErrors([
                    'expired_date' => 'Waktu berakhir harus setelah sekarang.',
                ]);
            }
        }

        DB::transaction(function () use ($id, $r, $data, $expires) {
            $req = DocumentRequest::lockForUpdate()->findOrFail($id);

            abort_unless(
                $req->status === 'pending',
                409,
                'Permohonan sudah diproses.'
            );

            abort_unless(
                in_array(
                    $req->request_type,
                    ['baca_sistem', 'baca_dokumen', 'upload'],
                    true
                ),
                422
            );

            if ($req->document_id && $data['status'] === 'approved') {
                abort_unless($req->document?->isPublished(), 422);
            }

            $req->update([
                'status' => $data['status'],
                'reject_reason' => $data['status'] === 'rejected'
                    ? $data['reject_reason']
                    : null,
                'expired_at' => $expires,
                'action_by' => $r->user()->id,
            ]);

            if ($data['status'] === 'approved') {
                TemporaryPermission::create([
                    'user_id' => $req->user_id,
                    'document_id' => $req->document_id,
                    'can_read' => in_array(
                        $req->request_type,
                        ['baca_sistem', 'baca_dokumen'],
                        true
                    ),
                    'can_upload' => $req->request_type === 'upload',
                    'expired_at' => $expires,
                    'granted_by' => $r->user()->id,
                ]);
            }

            ActivityLog::record(
                'PROCESS_ACCESS',
                'Permohonan '.$data['status'],
                $req->document_id
            );
        });

        return back()->with('success', 'Permohonan berhasil diproses.');
    }
}