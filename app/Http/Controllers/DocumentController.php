<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;

class DocumentController extends Controller
{
    public function download($id)
    {
        $document = Document::findOrFail($id);
        $user = auth()->user();

        // Verifikasi Hak Akses
        $isAdmin = $user->role === 'admin';
        
        $hasPermission = false;
        if (!$isAdmin) {
            $hasPermission = \App\Models\TemporaryPermission::where('user_id', $user->id)
                ->where('can_download', true)
                ->where('expired_at', '>', now())
                ->where(function ($q) use ($document) {
                    $q->where('document_id', $document->id)->orWhereNull('document_id');
                })
                ->exists();
        }

        if (!$isAdmin && !$hasPermission) {
            abort(403, 'Anda tidak memiliki izin untuk mengunduh dokumen ini.');
        }

        $fileName = basename($document->pdf_path);
        $filePath = resource_path('pdf/' . $fileName);

        if (!file_exists($filePath)) {
            $filePath = storage_path('app/' . $document->pdf_path);
        }

        if (!file_exists($filePath)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        // Gunakan FPDI untuk menyematkan watermark secara aman
        try {
            $pdf = new \setasign\Fpdi\Fpdi();
            $pageCount = $pdf->setSourceFile($filePath);

            $watermarkText = "Unduhan oleh: " . $user->name . " (" . $user->email . ") - Psikologi Universitas Ciputra";

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                // Tangani format ukuran array/objek FPDI v2
                $w = is_array($size) ? ($size['width'] ?? $size[0] ?? 210) : 210;
                $h = is_array($size) ? ($size['height'] ?? $size[1] ?? 297) : 297;
                $orientation = $w > $h ? 'L' : 'P';

                // Tambahkan halaman dengan orientasi yang sesuai
                $pdf->AddPage($orientation, [$w, $h]);
                $pdf->useTemplate($templateId);

                // Set styling watermark teks di bagian bawah
                $pdf->SetFont('Helvetica', 'B', 9);
                $pdf->SetTextColor(120, 120, 120); // Warna abu-abu

                // Posisi watermark di bawah tengah halaman
                $pdf->SetXY(10, $h - 10);
                $pdf->Cell(0, 8, $watermarkText, 0, 0, 'C');
            }

            $downloadName = Str::slug($document->title) . '-watermarked.pdf';
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->Output('S');
            }, $downloadName, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Throwable $e) {
            // Tangkap error dengan benar menggunakan tanda panah (->)
            dd($e->getMessage(), $e->getFile(), $e->getLine());
        }
    }

    // Form Upload Dokumen
    public function create()
    {
        $user = Auth::user();

        if ($user->isMahasiswa() && !$user->hasUploadPermission()) {
            abort(403, 'Anda tidak memiliki izin untuk mengunggah laporan.');
        }

        $categories = Category::where('is_hidden', false)->get();

        return view('lecturer-upload', compact('categories'));
    }

    // Simpan Dokumen Upload PDF
    public function storeUpload(Request $request)
    {
        $user = Auth::user();

        if ($user->isMahasiswa() && !$user->hasUploadPermission()) {
            abort(403, 'Anda tidak memiliki izin untuk mengunggah laporan.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'tahun' => 'required|numeric',
            'dosen_pembimbing' => 'required|string',
            'angkatan_pembuat' => 'nullable|numeric',
            'pdf_file' => 'required|mimes:pdf|max:10240',
        ]);

        $file = $request->file('pdf_file');

        $fileName = time() . '_' .
            preg_replace(
                '/[^A-Za-z0-9\-]/',
                '',
                str_replace(' ', '-', $request->title)
            ) .
            '.' .
            $file->getClientOriginalExtension();

        $destinationPath = resource_path('pdf');
        $file->move($destinationPath, $fileName);

        Document::create([
            'title' => $request->title,
            'category_id' => $request->category_id,
            'tahun' => $request->tahun,
            'dosen_pembimbing' => $request->dosen_pembimbing,
            'angkatan_pembuat' => $request->angkatan_pembuat,
            'description' => $request->description,
            'status' => $request->status ?? 'open',
            'pdf_path' => 'pdf/' . $fileName,
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                'Dokumen PDF berhasil diunggah ke folder resources/pdf.'
            );
    }

    // Halaman Read / Preview Detail Dokumen
    public function show($id)
    {
        $document = Document::with(['category', 'uploader'])
            ->findOrFail($id);

        if ($document->category->is_hidden || $document->status === 'hide') {
            abort(
                403,
                'Dokumen ini sedang disembunyikan oleh Dosen/Admin.'
            );
        }

        // Cek akses baca untuk mahasiswa saat masuk ke detail dokumen
        if (Auth::user()->isMahasiswa()) {
            if (!Auth::user()->hasAccessToDocument($document->id)) {
                return redirect()
                    ->route('student.requests')
                    ->with(
                        'error',
                        'Anda belum memiliki akses baca ke dokumen ini. Silakan ajukan permohonan baca terlebih dahulu.'
                    );
            }
        }

        return view(
            'student-document-detail',
            compact('document')
        );
    }

    // Katalog Dokumen Mahasiswa (Mahasiswa sekarang bebas akses katalog tanpa redirect awal)
    public function index(Request $request)
    {
        $categories = Category::where(
            'is_hidden',
            false
        )->get();

        $query = Document::with([
                'category',
                'uploader',
                'likes'
            ])
            ->where('status', 'open')
            ->whereHas('category', function ($q) {
                $q->where('is_hidden', false);
            });

        if ($request->filled('search')) {
            $query->where(
                'title',
                'like',
                '%' . trim($request->search) . '%'
            );
        }

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->category_id
            );
        }

        if ($request->filled('tahun')) {
            $query->where(
                'tahun',
                'like',
                '%' . $request->tahun . '%'
            );
        }

        if ($request->filled('dosen')) {
            $query->where(
                'dosen_pembimbing',
                'like',
                '%' . trim($request->dosen) . '%'
            );
        }

        if ($request->filled('angkatan')) {
            $query->where(
                'angkatan_pembuat',
                'like',
                '%' . $request->angkatan . '%'
            );
        }

        $documents = $query
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view(
            'student-catalog',
            compact(
                'documents',
                'categories'
            )
        );
    }
    
    public function stream($filename)
    {
        $user = Auth::user();

        $document = Document::where('pdf_path', 'pdf/' . $filename)
            ->first();

        if (!$document) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        if (!$user->isAdmin() && !$user->isDosen()) {
            if (!$user->hasAccessToDocument($document->id)) {
                abort(
                    403,
                    'Masa akses Anda terhadap dokumen ini telah berakhir atau Anda tidak memiliki izin membaca.'
                );
            }
        }

        $path = resource_path('pdf/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}