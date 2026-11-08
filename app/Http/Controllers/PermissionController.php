<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'role' => [
                'nullable',
                Rule::in(array_keys(config('cms.roles'))),
            ],

            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $target = $request->filled('user_id')
            ? User::findOrFail($request->user_id)
            : null;

        $users = User::orderBy('name')->get();

        $role = $request
            ->string('role', 'mahasiswa')
            ->toString();

        $rolePermissions = DB::table('role_permissions')
            ->get()
            ->groupBy('role');

        $overrides = $target
            ? DB::table('user_permissions')
                ->where('user_id', $target->id)
                ->pluck('allowed', 'permission')
            : collect();

        return view('cms.permissions', [
            'users' => $users,
            'target' => $target,
            'role' => $role,
            'rolePermissions' => $rolePermissions,
            'overrides' => $overrides,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'scope' => 'required|in:role,user',

            'role' => [
                'required_if:scope,role',
                Rule::in(array_keys(config('cms.roles'))),
            ],

            'user_id' => 'required_if:scope,user'
                .'|nullable|exists:users,id',

            'permissions' => 'nullable|array',

            'permissions.*' => 'required|in:allow,deny,inherit',
        ]);

        $entries = $data['permissions'] ?? [];

        foreach (array_keys($entries) as $permission) {
            abort_unless(
                array_key_exists(
                    $permission,
                    config('cms.permissions')
                ),
                422
            );
        }

        DB::transaction(function () use (
            $data,
            $entries
        ) {
            $admins = User::where('role', 'admin')
                ->lockForUpdate()
                ->get();

            $isRoleScope = $data['scope'] === 'role';

            $table = $isRoleScope
                ? 'role_permissions'
                : 'user_permissions';

            $target = $isRoleScope
                ? ['role' => $data['role']]
                : ['user_id' => $data['user_id']];

            DB::table($table)
                ->where($target)
                ->delete();

            foreach (
                config('cms.permissions') as $permission => $label
            ) {
                $choice = $entries[$permission]
                    ?? ($isRoleScope ? 'deny' : 'inherit');

                if ($choice !== 'inherit') {
                    DB::table($table)->insert(
                        $target + [
                            'permission' => $permission,
                            'allowed' => $choice === 'allow',
                        ]
                    );
                }
            }

            $hasPermissionAdministrator = $admins->contains(
                function ($admin) {
                    return $admin
                        ->fresh()
                        ->permits('manage_permissions');
                }
            );

            abort_unless(
                $hasPermissionAdministrator,
                422,
                'Minimal satu admin harus tetap dapat '
                    .'mengelola hak akses.'
            );

            ActivityLog::record(
                'PERMISSIONS_UPDATED',
                'Pengaturan hak akses diperbarui'
            );
        });

        return back()->with(
            'success',
            'Hak akses diperbarui.'
        );
    }
}