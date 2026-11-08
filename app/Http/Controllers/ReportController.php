<?php

namespace App\Http\Controllers;

use App\Jobs\SendReviewDecision;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ReportController extends Controller
{
    public function mine()
    {
        $documents = Document::with('category')
            ->where('uploaded_by', auth()->id())
            ->latest()
            ->paginate(15);

        return view('cms.reports', [
            'documents' => $documents,
            'mode' => 'mine',
        ]);
    }

    public function reviews()
    {
        $documents = Document::with([
            'category',
            'uploader',
        ])
            ->where('review_status', 'pending')
            ->oldest()
            ->paginate(15);

        return view('cms.reports', [
            'documents' => $documents,
            'mode' => 'review',
        ]);
    }

    public function manage(Request $request)
    {
        $query = Document::with([
            'category',
            'uploader',
        ]);

        if ($request->filled('search')) {
            $query->where(
                'title',
                'like',
                '%'.$request->string('search').'%'
            );
        }

        $documents = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('cms.reports', [
            'documents' => $documents,
            'mode' => 'manage',
        ]);
    }

    public function edit($id)
    {
        $document = Document::findOrFail($id);

        $user = auth()->user();

        $manager = $user->permits('manage_documents');

        $canReviseOwnReport = $document->uploaded_by === $user->id
            && $user->permits('upload')
            && $document->review_status === 'needs_revision';

        abort_unless(
            $manager || $canReviseOwnReport,
            403
        );

        $categories = Category::orderBy('name')->get();

        return view('cms.edit-report', [
            'document' => $document,
            'manager' => $manager,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        $user = $request->user();

        $manager = $user->permits('manage_documents');

        $canReviseOwnReport = $document->uploaded_by === $user->id
            && $user->permits('upload')
            && $document->review_status === 'needs_revision';

        abort_unless(
            $manager || $canReviseOwnReport,
            403
        );

        /*
         * Permintaan revisi mengizinkan perbaikan laporan tersebut,
         * meskipun izin upload sementara awal sudah kedaluwarsa.
         * Fitur upload pada akun tetap harus diizinkan.
         */
        $rules = DocumentController::rules(false);

        $rules['revision'] = 'required|integer|min:1';

        if ($manager) {
            $rules['category_id'] = 'required|exists:categories,id';
            $rules['status'] = 'required|in:open,hide';
        }

        $data = $request->validate($rules);

        $newPath = null;
        $oldPath = $document->pdf_path;

        try {
            if ($request->hasFile('pdf_file')) {
                $newPath = $request
                    ->file('pdf_file')
                    ->store('reports', 'local');

                if (! $newPath) {
                    throw new \RuntimeException(
                        'Penyimpanan gagal.'
                    );
                }
            }

            unset(
                $data['pdf_file'],
                $data['revision']
            );

            DB::transaction(function () use (
                $request,
                $id,
                $manager,
                $data,
                $newPath
            ) {
                $lockedDocument = Document::lockForUpdate()
                    ->findOrFail($id);

                abort_unless(
                    $lockedDocument->revision
                        === (int) $request->revision,
                    409,
                    'Laporan berubah. Muat ulang sebelum menyimpan.'
                );

                if (! $manager) {
                    abort_unless(
                        $lockedDocument->uploaded_by
                            === $request->user()->id
                            && $lockedDocument->review_status
                                === 'needs_revision',
                        409
                    );
                }

                if ($newPath) {
                    $data['pdf_path'] = $newPath;
                }

                if (! $manager) {
                    $data['review_status'] = 'pending';
                }

                $data['revision'] = $lockedDocument->revision + 1;

                $lockedDocument->update($data);

                ActivityLog::record(
                    $manager ? 'EDIT_DOCUMENT' : 'RESUBMIT',
                    'Laporan diperbarui',
                    $lockedDocument->id
                );
            });
        } catch (\Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }

            if (
                $exception instanceof HttpExceptionInterface
                || $exception instanceof ValidationException
            ) {
                throw $exception;
            }

            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'pdf_file' => 'Perubahan gagal disimpan. '
                        .'Pilih ulang PDF jika mengganti file, '
                        .'lalu coba lagi.',
                ]);
        }

        if (
            $newPath
            && str_starts_with($oldPath, 'reports/')
        ) {
            try {
                Storage::disk('local')->delete($oldPath);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        $redirectRoute = $manager
            ? 'reports.manage'
            : 'reports.mine';

        $message = $manager
            ? 'Laporan disimpan.'
            : 'Laporan disimpan. Dikirim ulang untuk review.';

        return redirect()
            ->route($redirectRoute)
            ->with('success', $message);
    }

    public function decide(Request $request, $id)
    {
        $data = $request->validate([
            'decision' => 'required|in:approved,needs_revision,rejected',

            'note' => [
                'nullable',
                'required_if:decision,needs_revision,rejected',
                'string',
                'max:5000',
            ],

            'revision' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use (
            $request,
            $id,
            $data
        ) {
            $document = Document::lockForUpdate()
                ->findOrFail($id);

            abort_if(
                $document->uploaded_by === $request->user()->id,
                403,
                'Review laporan sendiri tidak diizinkan.'
            );

            abort_unless(
                $document->review_status === 'pending'
                    && $document->revision === (int) $data['revision'],
                409,
                'Laporan sudah berubah atau diproses. Muat ulang.'
            );

            $document->update([
                'review_status' => $data['decision'],
                'review_note' => $data['note'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'revision' => $document->revision + 1,
            ]);

            ActivityLog::record(
                'REVIEW_UPLOAD',
                'Keputusan: '.$document->reviewLabel(),
                $document->id
            );

            /*
             * Job email dimasukkan ke database queue
             * dalam transaksi yang sama dengan keputusan review.
             */
            SendReviewDecision::dispatch(
                $document->uploader->email,
                $document->title,
                $document->reviewLabel(),
                $document->review_note
            )
                ->onConnection('database')
                ->beforeCommit();
        });

        return back()->with(
            'success',
            'Keputusan tersimpan. Pemberitahuan email masuk antrean.'
        );
    }
}