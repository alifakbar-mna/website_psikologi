<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\Document;
use App\Models\TemporaryPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DocumentRequestController extends Controller
{
    public function studentIndex()
    {
        $requests = DocumentRequest::with('document')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $documents = Document::where('status', 'open')
            ->latest()
            ->get();

        return view(
            'student-requests',
            compact('requests', 'documents')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'request_type' => [
                'required',
                'in:baca_sistem,baca_dokumen,upload,download',
            ],

            'reason' => [
                'required',
                'string',
                'max:500',
            ],
        ]);

        // Validasi Document ID untuk request tipe 'download' atau 'baca_dokumen'
        if (in_array($request->request_type, ['download', 'baca_dokumen'])) {

            $request->validate([
                'document_id' => [
                    'required',
                    'integer',
                    'exists:documents,id',
                ],
            ]);

            $document = Document::where('id', $request->document_id)
                ->where('status', 'open')
                ->first();

            if (!$document) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'document_id' =>
                            'Dokumen yang dipilih tidak tersedia atau sedang disembunyikan.',
                    ])
                    ->withInput();
            }

        } else {
            $request->merge([
                'document_id' => null,
            ]);
        }

        DocumentRequest::create([
            'user_id' => Auth::id(),

            'document_id' =>
                in_array($request->request_type, ['download', 'baca_dokumen'])
                    ? $request->document_id
                    : null,

            'request_type' => $request->request_type,

            'reason' => $request->reason,

            'status' => 'pending',

            'expired_at' => null,
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                'Permohonan berhasil dikirim. Menunggu persetujuan Dosen/Admin.'
            );
    }

    public function lecturerIndex()
    {
        $pendingRequests = DocumentRequest::with([
                'user',
                'document',
            ])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $processedRequests = DocumentRequest::with([
                'user',
                'document',
            ])
            ->whereIn(
                'status',
                ['approved', 'rejected']
            )
            ->latest()
            ->take(15)
            ->get();

        return view(
            'lecturer-requests',
            compact(
                'pendingRequests',
                'processedRequests'
            )
        );
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'status' => [
                'required',
                'in:approved,rejected',
            ],

            'reject_reason' => [
                'required_if:status,rejected',
                'nullable',
                'string',
                'max:1000',
            ],

            'expired_date' => [
                'required_if:status,approved',
                'nullable',
                'date',
            ],

            'expired_time' => [
                'required_if:status,approved',
                'nullable',
                'date_format:H:i',
            ],
        ]);

        $docRequest = DocumentRequest::findOrFail($id);

        if ($request->status === 'rejected') {
            $docRequest->update([
                'status' => 'rejected',
                'reject_reason' => $request->reject_reason,
                'action_by' => Auth::id(),
                'expired_at' => null,
            ]);

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Permohonan berhasil ditolak.'
                );
        }

        $expiredAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->expired_date . ' ' . $request->expired_time,
            config('app.timezone')
        );

        if ($expiredAt->isPast()) {
            return redirect()
                ->back()
                ->withErrors([
                    'expired_date' =>
                        'Waktu berakhir harus setelah waktu sekarang.',
                ])
                ->withInput();
        }

        $docRequest->update([
            'status' => 'approved',
            'reject_reason' => null,
            'action_by' => Auth::id(),
            'expired_at' => $expiredAt,
        ]);

        $permissionQuery = TemporaryPermission::where(
            'user_id',
            $docRequest->user_id
        );

        if ($docRequest->document_id) {
            $permissionQuery->where(
                'document_id',
                $docRequest->document_id
            );
        } else {
            $permissionQuery->whereNull(
                'document_id'
            );
        }

        $permissionQuery->delete();

        TemporaryPermission::create([
            'user_id' =>
                $docRequest->user_id,

            'document_id' =>
                $docRequest->document_id,

            'can_read' =>
                in_array(
                    $docRequest->request_type,
                    [
                        'baca_sistem',
                        'baca_dokumen',
                        'download',
                    ]
                ),

            'can_download' =>
                $docRequest->request_type === 'download',

            'can_upload' =>
                $docRequest->request_type === 'upload',

            'expired_at' =>
                $expiredAt,

            'granted_by' =>
                Auth::id(),
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                'Akses berhasil diberikan hingga ' .
                $expiredAt->format('d M Y, H:i')
            );
    }
}