<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'is_hidden'];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    // Relasi: Kategori memiliki banyak dokumen
    public function documents() {
        return $this->hasMany(Document::class);
    }

    // Relasi khusus: Hanya mengambil dokumen yang statusnya open
    public function openDocuments() {
        return $this->hasMany(Document::class)->where('status', 'open');
    }
}