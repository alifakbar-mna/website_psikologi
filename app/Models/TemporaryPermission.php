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
        'can_upload',
        'expired_at',
        'granted_by',
    ];

    protected $casts = [
        'target_angkatan' => 'integer',
        'is_all_students' => 'boolean',
        'can_read' => 'boolean',
        'can_upload' => 'boolean',
        'expired_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('expired_at', '>', now());
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function grantor()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}