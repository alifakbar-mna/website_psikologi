<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'nim',
        'angkatan',
        'is_registered_by_admin',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'is_registered_by_admin' => 'boolean',
        'angkatan' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | ROLE
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }


    /*
    |--------------------------------------------------------------------------
    | CEK AKSES SISTEM / KATALOG
    |--------------------------------------------------------------------------
    */

    public function hasAnyActiveAccess(): bool
    {
        // Admin dan Dosen selalu memiliki akses
        if ($this->isAdmin() || $this->isDosen()) {
            return true;
        }

        // Cek izin aktif: personal (user_id), per-angkatan, atau all mahasiswa
        return TemporaryPermission::where('can_read', true)
            ->where('expired_at', '>', now())
            ->where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('is_all_students', true)
                    ->orWhere(function ($q) {
                        if (!empty($this->angkatan)) {
                            $q->where('target_angkatan', $this->angkatan);
                        }
                    });
            })
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | CEK AKSES UPLOAD
    |--------------------------------------------------------------------------
    |
    | Digunakan untuk menentukan apakah menu Upload Dokumen
    | boleh ditampilkan kepada mahasiswa.
    |
    */

    public function hasUploadPermission(): bool
    {
        // Admin dan Dosen selalu boleh upload
        if ($this->isAdmin() || $this->isDosen()) {
            return true;
        }

        // Cek izin upload aktif: personal (user_id), per-angkatan, atau all mahasiswa
        return TemporaryPermission::where('can_upload', true)
            ->where('expired_at', '>', now())
            ->where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('is_all_students', true)
                    ->orWhere(function ($q) {
                        if (!empty($this->angkatan)) {
                            $q->where('target_angkatan', $this->angkatan);
                        }
                    });
            })
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | CEK AKSES DOKUMEN TERTENTU
    |--------------------------------------------------------------------------
    */

    public function hasAccessToDocument($documentId): bool
    {
        // Admin dan Dosen selalu boleh membaca
        if ($this->isAdmin() || $this->isDosen()) {
            return true;
        }

        return TemporaryPermission::where('can_read', true)
            ->where('expired_at', '>', now())
            ->where(function ($query) use ($documentId) {
                // Dokumen spesifik ATAU global ke semua dokumen
                $query->where('document_id', $documentId)
                    ->orWhereNull('document_id');
            })
            ->where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('is_all_students', true)
                    ->orWhere(function ($q) {
                        if (!empty($this->angkatan)) {
                            $q->where('target_angkatan', $this->angkatan);
                        }
                    });
            })
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | CEK AKSES DOWNLOAD DOKUMEN
    |--------------------------------------------------------------------------
    */

    public function hasDownloadPermission($documentId = null): bool
    {
        if ($this->isAdmin() || $this->isDosen()) {
            return true;
        }

        return TemporaryPermission::where('can_download', true)
            ->where('expired_at', '>', now())
            ->where(function ($query) use ($documentId) {
                if ($documentId) {
                    $query->where('document_id', $documentId)
                        ->orWhereNull('document_id');
                }
            })
            ->where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('is_all_students', true)
                    ->orWhere(function ($q) {
                        if (!empty($this->angkatan)) {
                            $q->where('target_angkatan', $this->angkatan);
                        }
                    });
            })
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function documents()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function requests()
    {
        return $this->hasMany(DocumentRequest::class, 'user_id');
    }

    public function temporaryPermissions()
    {
        return $this->hasMany(TemporaryPermission::class, 'user_id');
    }

    public function bookmarkedDocuments()
    {
        return $this->belongsToMany(
            Document::class,
            'bookmarks'
        )->withTimestamps();
    }

    public function likedDocuments()
    {
        return $this->belongsToMany(
            Document::class,
            'likes'
        )->withTimestamps();
    }
}