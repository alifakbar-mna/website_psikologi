<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public static function rules(?int $id = null): array
    {
        return [
            'name' => 'required|string|max:255',

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($id),
            ],

            'role' => [
                'required',
                Rule::in(array_keys(config('cms.roles'))),
            ],

            'angkatan' => 'nullable|integer|between:1901,'.date('Y'),
        ];
    }

    public static function normalizeAngkatan(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        if (in_array($text, ['', '-', '–', '—'], true)) {
            return null;
        }

        if (is_numeric($text)) {
            $number = (float) $text;

            $isWholeYear = is_finite($number)
                && floor($number) === $number
                && $number >= 1901
                && $number <= (int) date('Y');

            if ($isWholeYear) {
                return (int) $number;
            }
        }

        return $text;
    }

    public static function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',

            'email.required' => 'Email Google wajib diisi.',
            'email.email' => 'Format Email Google tidak valid.',
            'email.unique' => 'Email Google sudah terdaftar.',

            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role harus admin, dosen, asdos, atau mahasiswa.',

            'angkatan.integer' => 'Angkatan harus berupa tahun bulat, '
                .'misalnya 2023, atau dikosongkan.',

            'angkatan.between' => 'Angkatan harus antara 1901 dan '
                .date('Y').'.',
        ];
    }

    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $query = User::query();

        $search = trim(
            (string) $request->input('search', '')
        );

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('role', 'like', '%'.$search.'%');
            });
        }

        $users = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin-users',
            compact('users')
        );
    }

    public function store(Request $request)
    {
        $request->merge([
            'email' => strtolower(
                trim((string) $request->email)
            ),

            'angkatan' => self::normalizeAngkatan(
                $request->input('angkatan')
            ),
        ]);

        $data = $request->validate(
            self::rules(),
            self::messages()
        );

        $canManagePermissions = $request
            ->user()
            ->permits('manage_permissions');

        $isBasicRole = in_array(
            $data['role'],
            ['mahasiswa', 'asdos'],
            true
        );

        abort_unless(
            $canManagePermissions || $isBasicRole,
            403
        );

        User::create(
            $data + [
                'is_registered_by_admin' => true,
            ]
        );

        ActivityLog::record(
            'CREATE_USER',
            'Anggota ditambahkan'
        );

        return back()->with(
            'success',
            'Anggota ditambahkan.'
        );
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'email' => strtolower(
                trim((string) $request->email)
            ),

            'angkatan' => self::normalizeAngkatan(
                $request->input('angkatan')
            ),
        ]);

        $data = $request->validate(
            self::rules((int) $id),
            self::messages()
        );

        DB::transaction(function () use (
            $request,
            $id,
            $data
        ) {
            User::where('role', 'admin')
                ->lockForUpdate()
                ->get();

            $user = User::findOrFail($id);

            $canManagePermissions = $request
                ->user()
                ->permits('manage_permissions');

            $canManageBasicRole = in_array(
                $user->role,
                ['mahasiswa', 'asdos'],
                true
            ) && in_array(
                $data['role'],
                ['mahasiswa', 'asdos'],
                true
            );

            abort_unless(
                $canManagePermissions || $canManageBasicRole,
                403
            );

            $user->update($data);

            $hasPermissionAdministrator = User::where(
                'role',
                'admin'
            )
                ->get()
                ->contains(function ($admin) {
                    return $admin->permits(
                        'manage_permissions'
                    );
                });

            abort_unless(
                $hasPermissionAdministrator,
                422,
                'Minimal satu admin pengelola hak akses harus tersisa.'
            );

            ActivityLog::record(
                'UPDATE_USER',
                'Anggota diperbarui'
            );
        });

        return back()->with(
            'success',
            'Anggota diperbarui.'
        );
    }

    public function destroy(Request $request, $id)
    {
        abort_if(
            (int) $id === $request->user()->id,
            422,
            'Tidak dapat menghapus akun sendiri.'
        );

        DB::transaction(function () use (
            $request,
            $id
        ) {
            User::where('role', 'admin')
                ->lockForUpdate()
                ->get();

            $user = User::findOrFail($id);

            $canManagePermissions = $request
                ->user()
                ->permits('manage_permissions');

            $isBasicRole = in_array(
                $user->role,
                ['mahasiswa', 'asdos'],
                true
            );

            abort_unless(
                $canManagePermissions || $isBasicRole,
                403
            );

            abort_if(
                $user->documents()->exists(),
                422,
                'Akun memiliki laporan. '
                    .'Batasi hak aksesnya tanpa menghapus akun.'
            );

            $user->delete();

            $hasPermissionAdministrator = User::where(
                'role',
                'admin'
            )
                ->get()
                ->contains(function ($admin) {
                    return $admin->permits(
                        'manage_permissions'
                    );
                });

            abort_unless(
                $hasPermissionAdministrator,
                422,
                'Admin terakhir tidak boleh dihapus.'
            );

            ActivityLog::record(
                'DELETE_USER',
                'Anggota dihapus'
            );
        });

        return back()->with(
            'success',
            'Anggota dihapus.'
        );
    }
}