<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke Kategori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke User (Uploader)
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Relasi ke Bookmarks (TAMBAHKAN INI)
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    // Relasi ke Likes (TAMBAHKAN INI)
    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}