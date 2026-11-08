<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'revision' => 'integer',
        'uploaded_by' => 'integer',
        'category_id' => 'integer',
    ];

    public function scopePublished($query)
    {
        return $query
            ->where('review_status', 'approved')
            ->where('status', 'open')
            ->whereHas('category', function ($categoryQuery) {
                $categoryQuery->where('is_hidden', false);
            });
    }

    public function isPublished(): bool
    {
        return $this->review_status === 'approved'
            && $this->status === 'open'
            && $this->category
            && ! $this->category->is_hidden;
    }

    public function reviewLabel(): string
    {
        return [
            'pending' => 'Menunggu review',
            'needs_revision' => 'Perlu revisi',
            'rejected' => 'Ditolak',
            'approved' => 'Diterima',
        ][$this->review_status] ?? $this->review_status;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}