<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'document_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    public static function record(
        string $action,
        string $description,
        ?int $documentId = null
    ): void {
        static::create([
            'user_id' => auth()->id(),
            'document_id' => $documentId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}