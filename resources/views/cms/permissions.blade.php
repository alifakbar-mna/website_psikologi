@extends('layouts.app', ['title' => 'Hak Akses'])

@section('content')
    @php
        $roles = config('cms.roles');
        $permissions = config('cms.permissions');

        $defaults = config(
            'cms.defaults.'.$role,
            []
        );

        $savedRolePermissions = $rolePermissions[$role]
            ?? collect();

        $permissionNotes = [
            'read_all' => 'Izin Baca laporan juga harus aktif. Tidak memerlukan izin baca sementara.',

            'upload_direct' => 'Izin Upload laporan juga harus aktif. Upload tanpa izin sementara dan langsung diterima tanpa review.',

            'review_uploads' => 'Memeriksa PDF dan memberi keputusan terima, perlu revisi, atau tolak.',

            'manage_documents' => 'Mengubah metadata dan visibilitas laporan.',

            'manage_users' => 'Kelola anggota dan import Excel. Pengelolaan dosen/admin juga memerlukan Kelola hak akses.',

            'manage_permissions' => 'Mengubah izin role dan pengecualian akun. Berikan hanya kepada pengelola yang dipercaya.',
        ];
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                Hak Akses
            </h1>

            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                Atur izin seluruh anggota role atau berikan
                pengecualian untuk satu akun.
                Pengaturan akun memiliki prioritas lebih tinggi
                daripada pengaturan role.
            </p>
        </div>

        <section class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-sage-50 text-sage-600">
                    <i
                        data-lucide="shield-check"
                        class="w-5 h-5"
                    ></i>
                </div>

                <div>
                    <h2 class="font-bold text-sm text-slate-800">
                        Pengaturan per Role
                    </h2>

                    <p class="text-[11px] text-slate-400 mt-1">
                        Pilih role untuk langsung memuat pengaturannya.
                    </p>
                </div>
            </div>

            <div class="p-5 space-y-5">
                <form
                    id="role-picker-form"
                    method="GET"
                    action="{{ route('permissions.index') }}"
                >
                    @if($target)
                        <input
                            type="hidden"
                            name="user_id"
                            value="{{ $target->id }}"
                        >
                    @endif

                    <label class="block text-xs font-semibold text-slate-700">
                        Role

                        <select
                            id="role-picker"
                            name="role"
                            aria-describedby="role-picker-status"
                            class="block mt-2 w-full sm:max-w-xs bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs focus:outline-none focus:border-sage-600"
                        >
                            @foreach($roles as $key => $label)
                                <option
                                    value="{{ $key }}"
                                    @selected($role === $key)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <p
                        id="role-picker-status"
                        role="status"
                        aria-live="polite"
                        class="text-[11px] text-slate-400 mt-2"
                    >
                        Simpan perubahan checkbox sebelum
                        berpindah ke role lain.
                    </p>
                </form>

                <form
                    method="POST"
                    action="{{ route('permissions.store') }}"
                    class="space-y-5 border-t border-slate-100 pt-5"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="scope"
                        value="role"
                    >

                    <input
                        type="hidden"
                        name="role"
                        value="{{ $role }}"
                    >

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="text-xs font-bold text-slate-700">
                            Fitur yang diizinkan
                        </h3>

                        <span class="bg-sage-50 text-sage-700 rounded-lg px-3 py-1.5 text-[10px] font-bold">
                            {{ $roles[$role] ?? $role }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($permissions as $permission => $label)
                            @php
                                $saved = $savedRolePermissions
                                    ->firstWhere(
                                        'permission',
                                        $permission
                                    );

                                $defaultAllowed = in_array(
                                    '*',
                                    $defaults,
                                    true
                                ) || in_array(
                                    $permission,
                                    $defaults,
                                    true
                                );

                                $allowed = $saved
                                    ? (bool) $saved->allowed
                                    : $defaultAllowed;
                            @endphp

                            <label class="flex items-start gap-3 p-4 bg-slate-50/50 border border-slate-200 rounded-xl cursor-pointer hover:border-sage-500 transition">
                                <input
                                    type="checkbox"
                                    name="permissions[{{ $permission }}]"
                                    value="allow"
                                    @checked($allowed)
                                    class="mt-0.5 w-4 h-4 shrink-0 accent-[#4a6b5d]"
                                >

                                <span>
                                    <span class="block text-xs font-semibold text-slate-700">
                                        {{ $label }}
                                    </span>

                                    @if(isset($permissionNotes[$permission]))
                                        <span class="block mt-1.5 text-[11px] text-slate-400 leading-relaxed">
                                            {{ $permissionNotes[$permission] }}
                                        </span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Fitur yang tidak dicentang dinonaktifkan
                        untuk role ini. Pengecualian akun tetap
                        berlaku. Izin sementara baca/upload tetap
                        diperlukan kecuali izin tanpa batas
                        sementara yang sesuai diaktifkan.
                    </p>

                    <button
                        type="submit"
                        class="bg-sage-600 hover:bg-sage-700 text-white px-5 py-3 rounded-xl text-xs font-bold transition"
                    >
                        Simpan Izin Role
                    </button>
                </form>
            </div>
        </section>

        <section class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-sage-50 text-sage-600">
                    <i
                        data-lucide="user-cog"
                        class="w-5 h-5"
                    ></i>
                </div>

                <div>
                    <h2 class="font-bold text-sm text-slate-800">
                        Pengecualian per Akun
                    </h2>

                    <p class="text-[11px] text-slate-400 mt-1">
                        Atur satu akun tanpa mengubah anggota
                        role lainnya.
                    </p>
                </div>
            </div>

            <div class="p-5 space-y-5">
                <form
                    method="GET"
                    action="{{ route('permissions.index') }}"
                    class="flex flex-wrap items-end gap-3"
                >
                    <input
                        type="hidden"
                        name="role"
                        value="{{ $role }}"
                    >

                    <label class="block flex-1 min-w-0 text-xs font-semibold text-slate-700">
                        Akun

                        <select
                            name="user_id"
                            required
                            class="block mt-2 w-full max-w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs"
                        >
                            <option value="">
                                Pilih akun
                            </option>

                            @foreach($users as $member)
                                <option
                                    value="{{ $member->id }}"
                                    @selected($target?->id === $member->id)
                                >
                                    {{ $member->name }}
                                    — {{ $member->email }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <button
                        type="submit"
                        class="bg-sage-50 hover:bg-sage-100 text-sage-700 px-4 py-3 rounded-xl text-xs font-bold transition"
                    >
                        Tampilkan Akun
                    </button>
                </form>

                @if($target)
                    <div class="bg-sage-50 border border-sage-100 rounded-xl p-4 space-y-1">
                        <p class="text-xs font-bold text-sage-800">
                            {{ $target->name }}
                        </p>

                        <p class="text-[11px] text-sage-700 break-words">
                            {{ $target->email }}
                        </p>

                        <p class="text-[10px] text-sage-600">
                            Role:
                            {{ $roles[$target->role] ?? $target->role }}
                        </p>
                    </div>

                    <div class="text-[11px] text-slate-500 leading-relaxed space-y-1">
                        <p>
                            <strong>Ikuti Role:</strong>
                            gunakan pengaturan role akun.
                        </p>

                        <p>
                            <strong>Izinkan:</strong>
                            aktifkan fitur khusus untuk akun ini.
                        </p>

                        <p>
                            <strong>Larang:</strong>
                            nonaktifkan fitur meskipun role
                            mengizinkannya. Izin sementara tidak
                            mengalahkan Larang pada fitur
                            baca/upload.
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('permissions.store') }}"
                        class="space-y-5"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="scope"
                            value="user"
                        >

                        <input
                            type="hidden"
                            name="user_id"
                            value="{{ $target->id }}"
                        >

                        <div class="space-y-3">
                            @foreach($permissions as $permission => $label)
                                @php
                                    $hasOverride = $overrides->has(
                                        $permission
                                    );

                                    $overrideAllowed = $hasOverride
                                        && (bool) $overrides[$permission];
                                @endphp

                                <label class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 border border-slate-200 rounded-xl p-4">
                                    <span class="min-w-0">
                                        <span class="block text-xs font-semibold text-slate-700">
                                            {{ $label }}
                                        </span>

                                        @if(isset($permissionNotes[$permission]))
                                            <span class="block mt-1.5 text-[11px] text-slate-400 leading-relaxed">
                                                {{ $permissionNotes[$permission] }}
                                            </span>
                                        @endif
                                    </span>

                                    <select
                                        name="permissions[{{ $permission }}]"
                                        class="border border-slate-200 bg-slate-50 rounded-lg p-2.5 text-xs shrink-0 sm:min-w-[140px]"
                                    >
                                        <option
                                            value="inherit"
                                            @selected(!$hasOverride)
                                        >
                                            Ikuti Role
                                        </option>

                                        <option
                                            value="allow"
                                            @selected($hasOverride && $overrideAllowed)
                                        >
                                            Izinkan
                                        </option>

                                        <option
                                            value="deny"
                                            @selected($hasOverride && !$overrideAllowed)
                                        >
                                            Larang
                                        </option>
                                    </select>
                                </label>
                            @endforeach
                        </div>

                        <button
                            type="submit"
                            class="bg-sage-600 hover:bg-sage-700 text-white px-5 py-3 rounded-xl text-xs font-bold transition"
                        >
                            Simpan Pengecualian Akun
                        </button>
                    </form>
                @else
                    <div class="border border-dashed border-slate-200 rounded-xl p-6 text-center text-xs text-slate-400">
                        Pilih akun terlebih dahulu untuk melihat
                        pengecualian hak aksesnya.
                    </div>
                @endif
            </div>
        </section>

        <p class="text-[11px] text-slate-400 leading-relaxed">
            Minimal satu admin harus tetap memiliki izin
            Kelola Hak Akses. Perubahan berlaku pada
            permintaan halaman berikutnya.
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rolePicker = document.getElementById(
                'role-picker'
            );

            const pickerForm = document.getElementById(
                'role-picker-form'
            );

            const status = document.getElementById(
                'role-picker-status'
            );

            rolePicker.addEventListener('change', function () {
                status.textContent =
                    'Memuat pengaturan role…';

                pickerForm.requestSubmit();
            });
        });
    </script>
@endsection