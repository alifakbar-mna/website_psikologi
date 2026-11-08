<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_id',
        'request_type',
        'reason',
        'status',
        'reject_reason',
        'action_by',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query
            ->where('status', 'approved')
            ->where(function ($permissionQuery) {
                $permissionQuery->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}