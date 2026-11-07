<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Bookmark;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkLikeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Helper: Cek Akses User ke Dokumen
    |--------------------------------------------------------------------------
    */

    private function checkDocumentAccess($document)
    {
        $user = Auth::user();

        // Admin dan Dosen selalu boleh
        if ($user->isAdmin() || $user->isDosen()) {
            return true;
        }

        // Mahasiswa harus punya permission aktif
        return $user->hasAccessToDocument($document->id);
    }


    /*
    |--------------------------------------------------------------------------
    | Halaman Simpanan Saya
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Mahasiswa yang belum punya akses tidak boleh membuka bookmark
        |--------------------------------------------------------------------------
        */

        if ($user->isMahasiswa() && !$user->hasAnyActiveAccess()) {
            return redirect()
                ->route('student.requests')
                ->with(
                    'error',
                    'Anda belum memiliki akses. Silakan ajukan permohonan akses terlebih dahulu.'
                );
        }

        $userId = $user->id;

        // Mengambil dokumen yang telah di-bookmark oleh user
        $bookmarkedDocuments = Document::whereHas('bookmarks', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['category', 'likes', 'bookmarks'])
        ->latest()
        ->paginate(12);

        return view('student-bookmarks', [
            'documents' => $bookmarkedDocuments
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Bookmark
    |--------------------------------------------------------------------------
    */

    public function toggleBookmark($id)
    {
        $user = Auth::user();

        $document = Document::with('category')->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Cek permission
        |--------------------------------------------------------------------------
        */

        if (!$this->checkDocumentAccess($document)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki akses ke dokumen ini.'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Cek apakah sudah di-bookmark
        |--------------------------------------------------------------------------
        */

        $bookmark = Bookmark::where('user_id', $user->id)
            ->where('document_id', $document->id)
            ->first();

        if ($bookmark) {

            $bookmark->delete();

            $isBookmarked = false;
            $message = 'Dokumen dihapus dari Simpanan Saya.';

        } else {

            Bookmark::create([
                'user_id' => $user->id,
                'document_id' => $document->id,
            ]);

            $isBookmarked = true;
            $message = 'Dokumen berhasil disimpan ke Simpanan Saya.';
        }

        return response()->json([
            'success' => true,
            'is_bookmarked' => $isBookmarked,
            'message' => $message
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Like
    |--------------------------------------------------------------------------
    */

    public function toggleLike($id)
    {
        $user = Auth::user();

        $document = Document::with('category')->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Cek permission
        |--------------------------------------------------------------------------
        */

        if (!$this->checkDocumentAccess($document)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki akses ke dokumen ini.'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Cek apakah sudah di-like
        |--------------------------------------------------------------------------
        */

        $like = Like::where('user_id', $user->id)
            ->where('document_id', $document->id)
            ->first();

        if ($like) {

            $like->delete();

            $isLiked = false;

        } else {

            Like::create([
                'user_id' => $user->id,
                'document_id' => $document->id,
            ]);

            $isLiked = true;
        }

        $totalLikes = Like::where('document_id', $document->id)->count();

        return response()->json([
            'success' => true,
            'is_liked' => $isLiked,
            'total_likes' => $totalLikes
        ]);
    }
}