<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemporaryPermission extends Model
{
    protected $fillable = [
        'user_id',
        'target_angkatan',
        'is_all_students',
        'document_id',
        'can_read',
        'can_download',
        'can_upload',
        'expired_at',
        'granted_by',
    ];

    protected $casts = [
        'target_angkatan' => 'integer',
        'is_all_students' => 'boolean',
        'can_read' => 'boolean',
        'can_download' => 'boolean',
        'can_upload' => 'boolean',
        'expired_at' => 'datetime', // Otomatis dikonversi ke Carbon Instance
    ];

    // Scope untuk mengecek apakah izin masih berlaku (Belum Expired)
    public function scopeActive($query) {
        return $query->where('expired_at', '>', now());
    }

    // Relasi: Penerima akses jika ditembak per individu satu mahasiswa
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: Dokumen target (bisa null jika akses bersifat global untuk semua dokumen)
    public function document() {
        return $this->belongsTo(Document::class);
    }

    // Relasi: Dosen/Admin yang memberikan akses ini
    public function grantor() {
        return $this->belongsTo(User::class, 'granted_by');
    }
}