<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public static function rules(bool $fileRequired = true): array
    {
        return [
            'title' => 'required|string|max:255',

            'category_id' => [
                'required',
                Rule::exists('categories', 'id')
                    ->where('is_hidden', false),
            ],

            'tahun' => 'required|integer|between:1901,'.date('Y'),

            'dosen_pembimbing' => 'required|string|max:255',

            'angkatan_pembuat' => 'nullable|integer|between:1901,'.date('Y'),

            'case_type' => 'required|in:Individu,Kelompok',

            'description' => 'nullable|string|max:20000',

            'pdf_file' => [
                $fileRequired ? 'required' : 'nullable',
                'file',
                'mimes:pdf',
                'mimetypes:application/pdf',
                'max:10240',

                function ($attribute, $value, $fail) {
                    if (
                        $value instanceof UploadedFile
                        && file_get_contents(
                            $value->getRealPath(),
                            false,
                            null,
                            0,
                            5
                        ) !== '%PDF-'
                    ) {
                        $fail('File harus berupa PDF yang valid.');
                    }
                },
            ],
        ];
    }

    public function create()
    {
        abort_unless(
            auth()->user()->hasUploadPermission(),
            403,
            'Ajukan izin upload terlebih dahulu.'
        );

        $categories = Category::where('is_hidden', false)->get();

        return view(
            'lecturer-upload',
            compact('categories')
        );
    }

    public function storeUpload(Request $request)
    {
        abort_unless(
            $request->user()->hasUploadPermission(),
            403,
            'Izin upload tidak aktif.'
        );

        $rules = [
            'reports' => 'required|array|min:1|max:10',
        ];

        foreach (self::rules() as $field => $fieldRules) {
            $rules['reports.*.'.$field] = $fieldRules;
        }

        $data = $request->validate($rules);

        $storedPaths = [];

        try {
            DB::transaction(function () use (
                $data,
                $request,
                &$storedPaths
            ) {
                foreach ($data['reports'] as $index => $report) {
                    $path = $request
                        ->file("reports.$index.pdf_file")
                        ->store('reports', 'local');

                    if (! $path) {
                        throw new \RuntimeException(
                            'File gagal disimpan.'
                        );
                    }

                    $storedPaths[] = $path;

                    unset($report['pdf_file']);

                    $directUpload = $request
                        ->user()
                        ->permits('upload_direct');

                    $document = Document::create(
                        $report + [
                            'pdf_path' => $path,
                            'uploaded_by' => $request->user()->id,
                            'status' => 'open',
                            'review_status' => $directUpload
                                ? 'approved'
                                : 'pending',
                        ]
                    );

                    ActivityLog::record(
                        'UPLOAD',
                        'Laporan diunggah',
                        $document->id
                    );
                }
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'reports' => 'Upload gagal; tidak ada laporan '
                        .'dalam batch ini yang disimpan. '
                        .'Silakan pilih file dan coba lagi.',
                ]);
        }

        return redirect()
            ->route('reports.mine')
            ->with(
                'success',
                count($data['reports'])
                    .' laporan berhasil diunggah.'
            );
    }

    public function index(Request $request)
    {
        $query = Document::published()
            ->with([
                'category',
                'uploader',
                'likes',
            ]);

        $filters = [
            'search' => 'title',
            'tahun' => 'tahun',
            'dosen' => 'dosen_pembimbing',
            'angkatan' => 'angkatan_pembuat',
        ];

        foreach ($filters as $input => $column) {
            if ($request->filled($input)) {
                $query->where(
                    $column,
                    'like',
                    '%'.trim($request->string($input)).'%'
                );
            }
        }

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->category_id
            );
        }

        if (
            in_array(
                $request->case_type,
                ['Individu', 'Kelompok'],
                true
            )
        ) {
            $query->where(
                'case_type',
                $request->case_type
            );
        }

        $documents = $query
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where(
            'is_hidden',
            false
        )->get();

        return view(
            'student-catalog',
            compact('documents', 'categories')
        );
    }

    public function show($id)
    {
        $document = Document::with([
            'category',
            'uploader',
            'likes',
            'bookmarks',
        ])->findOrFail($id);

        abort_unless(
            $document->isPublished()
                && auth()->user()->hasAccessToDocument(
                    $document->id
                ),
            403,
            'Laporan tidak tersedia atau izin baca tidak aktif.'
        );

        ActivityLog::record(
            'READ_DOCUMENT',
            'Membuka reader laporan',
            $document->id
        );

        return view(
            'student-document-detail',
            compact('document')
        );
    }

    public function stream($filename)
    {
        abort_unless(
            basename($filename) === $filename,
            404
        );

        $document = Document::where(
            'pdf_path',
            'pdf/'.$filename
        )
            ->orWhere(
                'pdf_path',
                'reports/'.$filename
            )
            ->firstOrFail();

        $user = auth()->user();

        $canReview = $user->permits('review_uploads')
            || $user->permits('manage_documents');

        $isOwner = $document->uploaded_by === $user->id
            && $user->permits('upload');

        $canReadPublished = $document->isPublished()
            && $user->hasAccessToDocument($document->id);

        abort_unless(
            $canReview || $isOwner || $canReadPublished,
            403
        );

        $path = str_starts_with(
            $document->pdf_path,
            'reports/'
        )
            ? Storage::disk('local')->path($document->pdf_path)
            : resource_path($document->pdf_path);

        abort_unless(
            is_file($path),
            404,
            'File PDF tidak ditemukan.'
        );

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function preview($id)
    {
        $document = Document::with([
            'category',
            'uploader',
            'likes',
            'bookmarks',
        ])->findOrFail($id);

        $user = auth()->user();

        $canReview = $user->permits('review_uploads')
            || $user->permits('manage_documents');

        $isOwner = $document->uploaded_by === $user->id
            && $user->permits('upload');

        abort_unless(
            $canReview || $isOwner,
            403
        );

        return view(
            'student-document-detail',
            compact('document')
        );
    }
}