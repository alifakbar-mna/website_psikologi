@extends('layouts.app', ['title' => 'Kelola Pengguna'])

@section('content')
    @php
        $canManagePermissions = auth()
            ->user()
            ->permits('manage_permissions');

        $availableRoles = collect(config('cms.roles'))
            ->filter(function ($label, $role) use ($canManagePermissions) {
                return $canManagePermissions
                    || in_array(
                        $role,
                        ['mahasiswa', 'asdos'],
                        true
                    );
            });

        $activeTab = request('tab') === 'manage'
            || request()->filled('search')
            || old('_user_form') === 'edit'
                ? 'manage'
                : 'add';

        $restoreAdd = old('_user_form') === 'add';

        $failedUser = old('_user_form') === 'edit'
            ? $users->getCollection()->firstWhere(
                'id',
                old('edit_user_id')
            )
            : null;

        $reopenEdit = null;

        if (
            $failedUser
            && (
                $canManagePermissions
                || in_array(
                    $failedUser->role,
                    ['mahasiswa', 'asdos'],
                    true
                )
            )
        ) {
            $reopenEdit = [
                'url' => route(
                    'admin.users.update',
                    $failedUser->id
                ),
                'id' => $failedUser->id,
                'name' => old('name', $failedUser->name),
                'email' => old('email', $failedUser->email),
                'role' => old('role', $failedUser->role),
                'angkatan' => old(
                    'angkatan',
                    $failedUser->angkatan
                ),
            ];
        }
    @endphp

    <div class="max-w-6xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                Kelola Pengguna Sistem
            </h1>

            <p class="text-xs text-slate-500 mt-2">
                Daftarkan Email Google anggota dan kelola
                informasi akun yang sudah terdaftar.
            </p>
        </div>

        <nav
            aria-label="Bagian pengelolaan pengguna"
            class="flex gap-2 p-1.5 bg-white border border-slate-200 rounded-2xl shadow-sm"
        >
            <a
                href="{{ route('admin.users', ['tab' => 'add']) }}"
                @if($activeTab === 'add')
                    aria-current="page"
                @endif
                class="flex-1 flex items-center justify-center gap-2 px-3 py-3 rounded-xl text-xs font-bold transition {{ $activeTab === 'add' ? 'bg-sage-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}"
            >
                <i
                    data-lucide="user-plus"
                    class="w-4 h-4"
                ></i>

                Tambah Anggota
            </a>

            <a
                href="{{ route('admin.users', ['tab' => 'manage']) }}"
                @if($activeTab === 'manage')
                    aria-current="page"
                @endif
                class="flex-1 flex items-center justify-center gap-2 px-3 py-3 rounded-xl text-xs font-bold transition {{ $activeTab === 'manage' ? 'bg-sage-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}"
            >
                <i
                    data-lucide="users"
                    class="w-4 h-4"
                ></i>

                Kelola Anggota
            </a>
        </nav>

        @if($activeTab === 'add')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
                <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex items-center gap-3">
                        <div class="p-2.5 rounded-xl bg-sage-50 text-sage-600">
                            <i
                                data-lucide="user-plus"
                                class="w-5 h-5"
                            ></i>
                        </div>

                        <div>
                            <h2 class="font-bold text-sm text-slate-800">
                                Tambah Manual
                            </h2>

                            <p class="text-[11px] text-slate-400 mt-1">
                                Daftarkan satu anggota baru.
                            </p>
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.users.store') }}"
                        class="p-5 space-y-4"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="_user_form"
                            value="add"
                        >

                        <label class="block text-xs font-semibold text-slate-700">
                            Nama Lengkap

                            <input
                                name="name"
                                value="{{ $restoreAdd ? old('name') : '' }}"
                                maxlength="255"
                                required
                                class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs focus:outline-none focus:border-sage-600"
                            >
                        </label>

                        <label class="block text-xs font-semibold text-slate-700">
                            Email Google

                            <input
                                type="email"
                                name="email"
                                value="{{ $restoreAdd ? old('email') : '' }}"
                                maxlength="255"
                                required
                                class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs focus:outline-none focus:border-sage-600"
                            >
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="block text-xs font-semibold text-slate-700">
                                Role

                                <select
                                    name="role"
                                    required
                                    class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs"
                                >
                                    @foreach($availableRoles as $role => $label)
                                        <option
                                            value="{{ $role }}"
                                            @selected(($restoreAdd ? old('role', 'mahasiswa') : 'mahasiswa') === $role)
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block text-xs font-semibold text-slate-700">
                                Angkatan

                                <span class="font-normal text-slate-400">
                                    (opsional)
                                </span>

                                <input
                                    type="number"
                                    name="angkatan"
                                    value="{{ $restoreAdd ? old('angkatan') : '' }}"
                                    min="1901"
                                    max="{{ date('Y') }}"
                                    placeholder="Contoh: 2023"
                                    class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs"
                                >
                            </label>
                        </div>

                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Angkatan boleh dikosongkan untuk semua role.
                            Gunakan Email Google yang akan dipakai
                            anggota untuk login.
                        </p>

                        <button
                            type="submit"
                            class="w-full bg-sage-600 hover:bg-sage-700 text-white font-bold py-3 rounded-xl text-xs transition"
                        >
                            Simpan Anggota Baru
                        </button>
                    </form>
                </section>

                <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex items-center gap-3">
                        <div class="p-2.5 rounded-xl bg-sage-50 text-sage-600">
                            <i
                                data-lucide="file-spreadsheet"
                                class="w-5 h-5"
                            ></i>
                        </div>

                        <div>
                            <h2 class="font-bold text-sm text-slate-800">
                                Import dari Excel
                            </h2>

                            <p class="text-[11px] text-slate-400 mt-1">
                                Tambahkan banyak anggota sekaligus.
                            </p>
                        </div>
                    </div>

                    <div class="p-5 space-y-5">
                        <div class="bg-sage-50 border border-sage-100 rounded-xl p-4 text-xs text-sage-800 leading-relaxed space-y-2">
                            <p class="font-bold">
                                Cara import anggota
                            </p>

                            <ol class="list-decimal pl-4 space-y-1">
                                <li>Unduh template di bawah.</li>

                                <li>
                                    Isi nama, Email Google, role,
                                    dan angkatan opsional.
                                </li>

                                <li>
                                    Unggah file, periksa preview,
                                    lalu konfirmasi.
                                </li>
                            </ol>
                        </div>

                        <a
                            href="{{ route('members.template') }}"
                            class="inline-flex items-center gap-2 text-xs font-bold text-sage-700 hover:underline"
                        >
                            <i
                                data-lucide="file-down"
                                class="w-4 h-4"
                            ></i>

                            Unduh Template Excel
                        </a>

                        <form
                            method="POST"
                            action="{{ route('members.preview') }}"
                            enctype="multipart/form-data"
                            class="space-y-4"
                        >
                            @csrf

                            <label class="block text-xs font-semibold text-slate-700">
                                File Excel Anggota

                                <input
                                    type="file"
                                    name="workbook"
                                    accept=".xlsx"
                                    required
                                    class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-sage-100 file:px-3 file:py-2 file:text-sage-700"
                                >
                            </label>

                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Format .xlsx, maksimal 2 MB dan
                                1.000 anggota. Angkatan kosong atau
                                tanda - diperbolehkan.
                                Email lama tidak ditimpa.
                            </p>

                            @unless($canManagePermissions)
                                <p class="text-xs text-amber-700">
                                    Akun Anda hanya dapat menambahkan
                                    role mahasiswa dan ASDOS.
                                </p>
                            @endunless

                            <button
                                type="submit"
                                class="w-full bg-sage-600 hover:bg-sage-700 text-white font-bold py-3 rounded-xl text-xs transition"
                            >
                                Periksa & Preview Excel
                            </button>
                        </form>
                    </div>
                </section>
            </div>
        @else
            <section class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-5 space-y-4 border-b border-slate-100">
                    <div class="flex flex-wrap justify-between items-center gap-3">
                        <h2 class="font-bold text-sm text-slate-800">
                            Daftar Anggota
                        </h2>

                        <span class="text-[11px] font-semibold bg-sage-50 text-sage-700 px-3 py-1.5 rounded-lg">
                            {{ $users->total() }} hasil
                        </span>
                    </div>

                    <form
                        method="GET"
                        action="{{ route('admin.users') }}"
                        class="flex flex-wrap gap-2"
                    >
                        <input
                            type="hidden"
                            name="tab"
                            value="manage"
                        >

                        <label class="relative flex-1 min-w-[180px]">
                            <span class="sr-only">
                                Cari nama, email, atau role
                            </span>

                            <i
                                data-lucide="search"
                                class="absolute left-3 top-3 w-4 h-4 text-slate-400"
                            ></i>

                            <input
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                maxlength="255"
                                placeholder="Cari nama, email, atau role..."
                                class="w-full pl-10 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-sage-600"
                            >
                        </label>

                        <button
                            type="submit"
                            class="bg-sage-600 hover:bg-sage-700 text-white px-5 py-3 rounded-xl text-xs font-bold"
                        >
                            Cari
                        </button>

                        @if(request()->filled('search'))
                            <a
                                href="{{ route('admin.users', ['tab' => 'manage']) }}"
                                class="px-4 py-3 text-xs font-semibold text-slate-500 hover:bg-slate-50 rounded-xl"
                            >
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200/80">
                                <th scope="col" class="p-4">
                                    Nama Lengkap
                                </th>

                                <th scope="col" class="p-4">
                                    Email Google
                                </th>

                                <th scope="col" class="p-4">
                                    Role
                                </th>

                                <th scope="col" class="p-4">
                                    Angkatan
                                </th>

                                <th scope="col" class="p-4">
                                    Terdaftar
                                </th>

                                <th scope="col" class="p-4 text-center">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            @forelse($users as $user)
                                @php
                                    $canEditUser = $canManagePermissions
                                        || in_array(
                                            $user->role,
                                            ['mahasiswa', 'asdos'],
                                            true
                                        );
                                @endphp

                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="p-4 font-bold text-slate-800">
                                        {{ $user->name }}
                                    </td>

                                    <td class="p-4">
                                        {{ $user->email }}
                                    </td>

                                    <td class="p-4">
                                        <span class="px-2.5 py-1 rounded-lg bg-sage-50 text-sage-700 text-[10px] font-bold uppercase">
                                            {{ config('cms.roles.'.$user->role, $user->role) }}
                                        </span>
                                    </td>

                                    <td class="p-4 text-slate-500">
                                        {{ $user->angkatan ?? '-' }}
                                    </td>

                                    <td class="p-4 text-slate-400 whitespace-nowrap">
                                        {{ $user->created_at?->format('d M Y') ?? '-' }}
                                    </td>

                                    <td class="p-4">
                                        @if($canEditUser)
                                            <div class="flex items-center justify-center gap-2">
                                                <button
                                                    type="button"
                                                    data-edit-user
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-user-email="{{ $user->email }}"
                                                    data-user-role="{{ $user->role }}"
                                                    data-user-angkatan="{{ $user->angkatan ?? '' }}"
                                                    data-update-url="{{ route('admin.users.update', $user->id) }}"
                                                    class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition"
                                                    title="Edit anggota"
                                                    aria-label="Edit {{ $user->name }}"
                                                >
                                                    <i
                                                        data-lucide="pencil"
                                                        class="w-4 h-4"
                                                    ></i>
                                                </button>

                                                @if($user->id !== auth()->id())
                                                    <form
                                                        method="POST"
                                                        action="{{ route('admin.users.destroy', $user->id) }}"
                                                        onsubmit="return confirm('Hapus akun ini? Akun yang memiliki laporan tidak dapat dihapus.');"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition"
                                                            title="Hapus anggota"
                                                            aria-label="Hapus {{ $user->name }}"
                                                        >
                                                            <i
                                                                data-lucide="trash-2"
                                                                class="w-4 h-4"
                                                            ></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[11px] text-slate-400">
                                                Akses terbatas
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="6"
                                        class="p-10 text-center text-slate-400"
                                    >
                                        {{ request()->filled('search') ? 'Tidak ada anggota yang cocok dengan pencarian.' : 'Belum ada anggota terdaftar.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            </section>
        @endif
    </div>

    <dialog
        id="edit-user-dialog"
        aria-labelledby="edit-user-title"
        class="rounded-2xl p-0 w-[calc(100%-2rem)] max-w-lg max-h-[90vh] overflow-y-auto shadow-xl backdrop:bg-slate-900/50"
    >
        <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-3">
            <div>
                <h2
                    id="edit-user-title"
                    class="font-bold text-base text-slate-800"
                >
                    Edit Anggota
                </h2>

                <p class="text-xs text-slate-400 mt-1">
                    Perbarui informasi akun yang dipilih.
                </p>
            </div>

            <button
                type="button"
                data-close-edit
                class="p-2 text-slate-400 hover:bg-slate-50 rounded-lg"
                aria-label="Tutup edit anggota"
            >
                <i
                    data-lucide="x"
                    class="w-5 h-5"
                ></i>
            </button>
        </div>

        <form
            id="edit-user-form"
            method="POST"
            class="p-5 space-y-4"
        >
            @csrf
            @method('PUT')

            <input
                type="hidden"
                name="_user_form"
                value="edit"
            >

            <input
                type="hidden"
                name="edit_user_id"
                id="edit-user-id"
            >

            <label class="block text-xs font-semibold text-slate-700">
                Nama Lengkap

                <input
                    id="edit-user-name"
                    name="name"
                    maxlength="255"
                    required
                    class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs focus:outline-none focus:border-sage-600"
                >
            </label>

            <label class="block text-xs font-semibold text-slate-700">
                Email Google

                <input
                    id="edit-user-email"
                    type="email"
                    name="email"
                    maxlength="255"
                    required
                    class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs focus:outline-none focus:border-sage-600"
                >
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="block text-xs font-semibold text-slate-700">
                    Role

                    <select
                        id="edit-user-role"
                        name="role"
                        required
                        class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs"
                    >
                        @foreach($availableRoles as $role => $label)
                            <option value="{{ $role }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-xs font-semibold text-slate-700">
                    Angkatan

                    <span class="font-normal text-slate-400">
                        (opsional)
                    </span>

                    <input
                        id="edit-user-angkatan"
                        type="number"
                        name="angkatan"
                        min="1901"
                        max="{{ date('Y') }}"
                        class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs"
                    >
                </label>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                <button
                    type="button"
                    data-close-edit
                    class="px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="px-4 py-2.5 bg-sage-600 hover:bg-sage-700 text-white rounded-xl text-xs font-bold"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dialog = document.getElementById(
                'edit-user-dialog'
            );

            const form = document.getElementById(
                'edit-user-form'
            );

            function openEdit(data) {
                form.action = data.url;

                document.getElementById(
                    'edit-user-id'
                ).value = data.id;

                document.getElementById(
                    'edit-user-name'
                ).value = data.name ?? '';

                document.getElementById(
                    'edit-user-email'
                ).value = data.email ?? '';

                document.getElementById(
                    'edit-user-role'
                ).value = data.role;

                document.getElementById(
                    'edit-user-angkatan'
                ).value = data.angkatan ?? '';

                dialog.showModal();
            }

            document.querySelectorAll(
                '[data-edit-user]'
            ).forEach(function (button) {
                button.addEventListener('click', function () {
                    openEdit({
                        url: button.dataset.updateUrl,
                        id: button.dataset.userId,
                        name: button.dataset.userName,
                        email: button.dataset.userEmail,
                        role: button.dataset.userRole,
                        angkatan: button.dataset.userAngkatan,
                    });
                });
            });

            document.querySelectorAll(
                '[data-close-edit]'
            ).forEach(function (button) {
                button.addEventListener('click', function () {
                    dialog.close();
                });
            });

            const failedEdit = @json($reopenEdit);

            if (failedEdit) {
                openEdit(failedEdit);
            }
        });
    </script>
@endsection