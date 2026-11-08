<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\MemberWorkbook;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MemberImportController extends Controller
{
    public function template(MemberWorkbook $workbook)
    {
        return response()
            ->download(
                $workbook->template(),
                'template-anggota.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            )
            ->deleteFileAfterSend(true);
    }

    public function preview(
        Request $request,
        MemberWorkbook $workbook
    ) {
        $request->session()->forget('member_import');

        $request->validate([
            'workbook' => 'required|file|extensions:xlsx|max:2048',
        ], [
            'workbook.required' => 'Pilih file Excel terlebih dahulu.',
            'workbook.file' => 'File Excel tidak valid.',
            'workbook.extensions' => 'Gunakan file Excel berformat .xlsx.',
            'workbook.max' => 'Ukuran file Excel maksimal 2 MB.',
        ]);

        $rows = $workbook->read(
            $request->file('workbook')->getRealPath()
        );

        $items = [];
        $emails = [];
        $hasErrors = false;

        foreach ($rows as $row) {
            [$name, $email, $role, $year] = $row['values'];

            $email = strtolower(trim($email));
            $role = strtolower(trim($role));

            $data = [
                'name' => trim($name),
                'email' => $email,
                'role' => $role,

                'angkatan' => UserController::normalizeAngkatan(
                    $year
                ),
            ];

            $validator = Validator::make(
                $data,
                UserController::rules(),
                UserController::messages()
            );

            $errors = $validator->errors()->all();

            if (isset($emails[$email])) {
                $errors[] = 'Email duplikat dalam file.';
            }

            $emails[$email] = true;

            $canManagePermissions = $request
                ->user()
                ->permits('manage_permissions');

            $isBasicRole = in_array(
                $role,
                ['mahasiswa', 'asdos'],
                true
            );

            if (! $canManagePermissions && ! $isBasicRole) {
                $errors[] = 'Role dosen/admin memerlukan '
                    .'izin Kelola Hak Akses.';
            }

            if ($errors) {
                $hasErrors = true;
            }

            $items[] = [
                'line' => $row['line'],
                'data' => $data,
                'errors' => $errors,
            ];
        }

        $token = bin2hex(random_bytes(24));

        if (! $hasErrors) {
            $request->session()->put('member_import', [
                'token' => $token,
                'user_id' => $request->user()->id,
                'expires' => now()->addMinutes(30)->timestamp,
                'rows' => array_column($items, 'data'),
            ]);
        }

        return view(
            'cms.import-preview',
            compact('items', 'hasErrors', 'token')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $import = $request
            ->session()
            ->get('member_import');

        $validPreview = $import
            && $import['user_id'] === $request->user()->id
            && $import['expires'] > now()->timestamp
            && hash_equals(
                $import['token'],
                $request->token
            );

        abort_unless(
            $validPreview,
            419,
            'Preview kedaluwarsa; unggah Excel kembali.'
        );

        try {
            DB::transaction(function () use (
                $import,
                $request
            ) {
                foreach ($import['rows'] as $data) {
                    Validator::make(
                        $data,
                        UserController::rules(),
                        UserController::messages()
                    )->validate();

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
                }

                ActivityLog::record(
                    'IMPORT_MEMBERS',
                    count($import['rows']).' anggota diimpor'
                );
            });
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages([
                'email' => 'Ada email yang baru saja terdaftar. '
                    .'Preview ulang file Excel; tidak ada anggota '
                    .'dalam batch ini yang disimpan.',
            ]);
        }

        $request->session()->forget('member_import');

        return redirect()
            ->route('admin.users')
            ->with(
                'success',
                count($import['rows'])
                    .' anggota berhasil diimpor.'
            );
    }
}