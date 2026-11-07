<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    // Relasi: Siapa pelaku aktivitas tersebut
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}