```blade
@extends('layouts.app', ['title' => 'Kelola Pengguna - Admin'])

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                Kelola Pengguna Sistem
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Daftarkan email civitas akademika agar diizinkan masuk via Google Login.
            </p>
        </div>

        <button
            type="button"
            onclick="openAddUserModal()"
            class="bg-sage-600 hover:bg-sage-700 text-white font-medium px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition shadow-sm"
        >
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Tambah User Baru</span>
        </button>
    </div>

    {{-- Success Message --}}
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs p-4 rounded-xl flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Error Message --}}
    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs p-4 rounded-xl">
            <div class="flex items-start gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>

                <div>
                    <p class="font-bold mb-1">Terjadi kesalahan:</p>

                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Table Users --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">

                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200/80">
                        <th class="p-4">Nama Lengkap</th>
                        <th class="p-4">Email Google</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Angkatan</th>
                        <th class="p-4">Tanggal Terdaftar</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">

                    @forelse($users as $user)

                        <tr class="hover:bg-slate-50/50 transition">

                            {{-- Nama --}}
                            <td class="p-4 font-bold text-slate-800">
                                {{ $user->name }}
                            </td>

                            {{-- Email --}}
                            <td class="p-4 text-slate-600 font-mono text-[11px]">
                                {{ $user->email }}
                            </td>

                            {{-- Role --}}
                            <td class="p-4">
                                <span
                                    class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase
                                    {{ $user->role === 'admin'
                                        ? 'bg-purple-50 text-purple-700 border border-purple-200'
                                        : '' }}

                                    {{ $user->role === 'dosen'
                                        ? 'bg-blue-50 text-blue-700 border border-blue-200'
                                        : '' }}

                                    {{ $user->role === 'mahasiswa'
                                        ? 'bg-sage-50 text-sage-700 border border-sage-200'
                                        : '' }}"
                                >
                                    {{ $user->role }}
                                </span>
                            </td>

                            {{-- Angkatan --}}
                            <td class="p-4 text-slate-500">
                                {{ $user->angkatan ?: '-' }}
                            </td>

                            {{-- Created At --}}
                            <td class="p-4 text-slate-400 text-[11px]">
                                {{ $user->created_at->format('d M Y') }}
                            </td>

                            {{-- Aksi --}}
                            <td class="p-4">

                                <div class="flex items-center justify-center gap-1.5">

                                    {{-- Edit --}}
                                    <button
                                        type="button"
                                        onclick="openEditUserModal(
                                            '{{ $user->id }}',
                                            @js($user->name),
                                            @js($user->email),
                                            '{{ $user->role }}',
                                            @js($user->angkatan)
                                        )"
                                        class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition"
                                        title="Edit User"
                                    >
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>

                                    {{-- Delete --}}
                                    <form
                                        action="{{ route('admin.users.destroy', $user->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin hapus user ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition"
                                            title="Hapus User"
                                        >
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="6"
                                class="p-8 text-center text-slate-400"
                            >
                                Belum ada pengguna terdaftar.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>

    </div>
</div>


{{-- ========================================================= --}}
{{-- MODAL TAMBAH USER --}}
{{-- ========================================================= --}}

<div
    id="add-user-modal"
    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden"
>

    <div
        class="bg-white max-w-md w-full rounded-2xl p-6 space-y-5 shadow-xl border border-slate-100"
    >

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">

            <div>
                <h3 class="text-sm font-bold text-slate-800">
                    Tambah User Baru
                </h3>

                <p class="text-[10px] text-slate-400 mt-0.5">
                    Tambahkan pengguna yang diizinkan login
                </p>
            </div>

            <button
                type="button"
                onclick="closeAddUserModal()"
                class="text-slate-400 hover:text-slate-600"
            >
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

        </div>


        {{-- Form --}}
        <form
            action="{{ route('admin.users.store') }}"
            method="POST"
            class="space-y-4"
        >

            @csrf

            {{-- Nama --}}
            <div>

                <label class="block text-xs font-semibold text-slate-600 mb-1">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    name="name"
                    required
                    placeholder="Nama User"
                    value="{{ old('name') }}"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600"
                >

            </div>


            {{-- Email --}}
            <div>

                <label class="block text-xs font-semibold text-slate-600 mb-1">
                    Email Google Domain
                </label>

                <input
                    type="email"
                    name="email"
                    required
                    placeholder="email@student.ciputra.ac.id"
                    value="{{ old('email') }}"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600"
                >

            </div>


            {{-- Role + Angkatan --}}
            <div class="grid grid-cols-2 gap-3">

                {{-- Role --}}
                <div>

                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        Role Akses
                    </label>

                    <select
                        name="role"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600"
                    >

                        <option value="mahasiswa">
                            Mahasiswa
                        </option>

                        <option value="dosen">
                            Dosen
                        </option>

                        <option value="admin">
                            Admin
                        </option>

                    </select>

                </div>


                {{-- Angkatan --}}
                <div>

                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        Angkatan (Opsional)
                    </label>

                    <input
                        type="text"
                        name="angkatan"
                        placeholder="2026"
                        value="{{ old('angkatan') }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600"
                    >

                </div>

            </div>


            {{-- Buttons --}}
            <div class="pt-2 flex justify-end gap-2">

                <button
                    type="button"
                    onclick="closeAddUserModal()"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="px-5 py-2.5 bg-sage-600 hover:bg-sage-700 text-white rounded-xl text-xs font-bold transition shadow-sm"
                >
                    Simpan User
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ========================================================= --}}
{{-- MODAL EDIT USER --}}
{{-- ========================================================= --}}

<div
    id="edit-user-modal"
    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden"
>

    <div
        class="bg-white max-w-md w-full rounded-2xl p-6 space-y-5 shadow-xl border border-slate-100"
    >

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">

            <div>

                <h3 class="text-sm font-bold text-slate-800">
                    Edit User
                </h3>

                <p class="text-[10px] text-slate-400 mt-0.5">
                    Perbarui informasi pengguna sistem
                </p>

            </div>

            <button
                type="button"
                onclick="closeEditUserModal()"
                class="text-slate-400 hover:text-slate-600"
            >
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

        </div>


        {{-- Form Edit --}}
        <form
            id="edit-user-form"
            method="POST"
            class="space-y-4"
        >

            @csrf
            @method('PUT')


            {{-- Nama --}}
            <div>

                <label class="block text-xs font-semibold text-slate-600 mb-1">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    id="edit-name"
                    name="name"
                    required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600"
                >

            </div>


            {{-- Email --}}
            <div>

                <label class="block text-xs font-semibold text-slate-600 mb-1">
                    Email Google Domain
                </label>

                <input
                    type="email"
                    id="edit-email"
                    name="email"
                    required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600"
                >

            </div>


            {{-- Role + Angkatan --}}
            <div class="grid grid-cols-2 gap-3">

                {{-- Role --}}
                <div>

                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        Role Akses
                    </label>

                    <select
                        id="edit-role"
                        name="role"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600"
                    >

                        <option value="mahasiswa">
                            Mahasiswa
                        </option>

                        <option value="dosen">
                            Dosen
                        </option>

                        <option value="admin">
                            Admin
                        </option>

                    </select>

                </div>


                {{-- Angkatan --}}
                <div>

                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        Angkatan
                    </label>

                    <input
                        type="text"
                        id="edit-angkatan"
                        name="angkatan"
                        placeholder="2026"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600"
                    >

                </div>

            </div>


            {{-- Buttons --}}
            <div class="pt-2 flex justify-end gap-2">

                <button
                    type="button"
                    onclick="closeEditUserModal()"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="px-5 py-2.5 bg-sage-600 hover:bg-sage-700 text-white rounded-xl text-xs font-bold transition shadow-sm"
                >
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ========================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ========================================================= --}}

<script>

    /* ==============================
       ADD USER MODAL
    ============================== */

    function openAddUserModal() {

        document
            .getElementById('add-user-modal')
            .classList
            .remove('hidden');

    }


    function closeAddUserModal() {

        document
            .getElementById('add-user-modal')
            .classList
            .add('hidden');

    }


    /* ==============================
       EDIT USER MODAL
    ============================== */

    function openEditUserModal(
        id,
        name,
        email,
        role,
        angkatan
    ) {

        const modal =
            document.getElementById('edit-user-modal');

        const form =
            document.getElementById('edit-user-form');


        // Set URL form update
        form.action = `/admin/users/${id}`;


        // Isi data user
        document.getElementById('edit-name').value =
            name || '';

        document.getElementById('edit-email').value =
            email || '';

        document.getElementById('edit-role').value =
            role || 'mahasiswa';

        document.getElementById('edit-angkatan').value =
            angkatan || '';


        // Tampilkan modal
        modal.classList.remove('hidden');


        // Refresh Lucide Icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

    }


    function closeEditUserModal() {

        document
            .getElementById('edit-user-modal')
            .classList
            .add('hidden');

    }


    /* ==============================
       CLICK OUTSIDE MODAL
    ============================== */

    document.addEventListener('DOMContentLoaded', function () {

        const addModal =
            document.getElementById('add-user-modal');

        const editModal =
            document.getElementById('edit-user-modal');


        if (addModal) {

            addModal.addEventListener('click', function (event) {

                if (event.target === addModal) {
                    closeAddUserModal();
                }

            });

        }


        if (editModal) {

            editModal.addEventListener('click', function (event) {

                if (event.target === editModal) {
                    closeEditUserModal();
                }

            });

        }


        // Escape untuk menutup modal
        document.addEventListener('keydown', function (event) {

            if (event.key === 'Escape') {

                closeAddUserModal();
                closeEditUserModal();

            }

        });

    });

</script>

@endsection
