<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

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

    private ?array $effectivePermissions = null;

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

    public function isAsdos(): bool
    {
        return $this->role === 'asdos';
    }

    public function permits(string $permission): bool
    {
        if (! array_key_exists($permission, config('cms.permissions'))) {
            return false;
        }

        if ($this->effectivePermissions === null) {
            $defaults = config('cms.defaults.'.$this->role, []);

            $map = array_fill_keys(
                array_keys(config('cms.permissions')),
                in_array('*', $defaults, true)
            );

            foreach ($defaults as $permissionName) {
                if ($permissionName !== '*') {
                    $map[$permissionName] = true;
                }
            }

            $rolePermissions = DB::table('role_permissions')
                ->where('role', $this->role)
                ->get();

            foreach ($rolePermissions as $rolePermission) {
                $map[$rolePermission->permission] = (bool) $rolePermission->allowed;
            }

            $userPermissions = DB::table('user_permissions')
                ->where('user_id', $this->id)
                ->get();

            foreach ($userPermissions as $userPermission) {
                $map[$userPermission->permission] = (bool) $userPermission->allowed;
            }

            $this->effectivePermissions = $map;
        }

        return $this->effectivePermissions[$permission] ?? false;
    }

    public function activePermissions()
    {
        return TemporaryPermission::where('expired_at', '>', now())
            ->where(function ($query) {
                $query->where('user_id', $this->id);

                if ($this->isMahasiswa()) {
                    $query->orWhere('is_all_students', true);

                    if ($this->angkatan) {
                        $query->orWhere('target_angkatan', $this->angkatan);
                    }
                }
            });
    }

    public function hasAnyActiveAccess(): bool
    {
        return $this->permits('read')
            && (
                $this->permits('read_all')
                || $this->activePermissions()
                    ->where('can_read', true)
                    ->exists()
            );
    }

    public function hasUploadPermission(): bool
    {
        return $this->permits('upload')
            && (
                $this->permits('upload_direct')
                || $this->activePermissions()
                    ->where('can_upload', true)
                    ->whereNull('document_id')
                    ->exists()
            );
    }

    public function hasAccessToDocument($documentId): bool
    {
        return $this->permits('read')
            && (
                $this->permits('read_all')
                || $this->activePermissions()
                    ->where('can_read', true)
                    ->where(function ($query) use ($documentId) {
                        $query->where('document_id', $documentId)
                            ->orWhereNull('document_id');
                    })
                    ->exists()
            );
    }

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