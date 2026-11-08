@extends('layouts.app', ['title' => 'Berikan Akses Sementara'])

@section('content')
    @php
        $targetType = old('target_type', 'single');
        $durationType = old('duration_type', 'default');

        $isReturningForm = (bool) old('_grant_form');

        $readChecked = $isReturningForm
            ? (bool) old('can_read', false)
            : true;

        $uploadChecked = (bool) old('can_upload', false);
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Berikan Akses Sementara
            </h1>

            <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                Berikan izin baca atau upload dengan batas waktu.
                Pengaturan fitur pada Hak Akses tetap berlaku.
            </p>
        </div>

        <form
            action="{{ route('access.grant.store') }}"
            method="POST"
            id="grant-access-form"
            class="bg-white p-5 sm:p-7 rounded-2xl border border-slate-200 space-y-6"
        >
            @csrf

            <input
                type="hidden"
                name="_grant_form"
                value="1"
            >

            <section class="space-y-4">
                <h2 class="text-sm font-bold text-slate-800">
                    1. Penerima Akses
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="border border-slate-200 rounded-xl p-4 flex items-start gap-3 cursor-pointer hover:border-sage-500">
                        <input
                            type="radio"
                            name="target_type"
                            value="single"
                            @checked($targetType === 'single')
                            class="mt-1 accent-[#4a6b5d]"
                        >

                        <span>
                            <span class="block text-xs font-semibold text-slate-800">
                                Satu Akun
                            </span>

                            <span class="block text-xs text-slate-400 mt-1">
                                Berdasarkan Email Google
                            </span>
                        </span>
                    </label>

                    <label class="border border-slate-200 rounded-xl p-4 flex items-start gap-3 cursor-pointer hover:border-sage-500">
                        <input
                            type="radio"
                            name="target_type"
                            value="angkatan"
                            @checked($targetType === 'angkatan')
                            class="mt-1 accent-[#4a6b5d]"
                        >

                        <span>
                            <span class="block text-xs font-semibold text-slate-800">
                                Per Angkatan
                            </span>

                            <span class="block text-xs text-slate-400 mt-1">
                                Mahasiswa pada angkatan tertentu
                            </span>
                        </span>
                    </label>

                    <label class="border border-slate-200 rounded-xl p-4 flex items-start gap-3 cursor-pointer hover:border-sage-500">
                        <input
                            type="radio"
                            name="target_type"
                            value="all"
                            @checked($targetType === 'all')
                            class="mt-1 accent-[#4a6b5d]"
                        >

                        <span>
                            <span class="block text-xs font-semibold text-slate-800">
                                Semua Mahasiswa
                            </span>

                            <span class="block text-xs text-slate-400 mt-1">
                                Seluruh akun dengan role mahasiswa
                            </span>
                        </span>
                    </label>
                </div>

                <label
                    id="target-single-box"
                    class="block text-xs font-semibold text-slate-700"
                >
                    Email Google Akun

                    <input
                        type="email"
                        name="user_email"
                        value="{{ old('user_email') }}"
                        placeholder="nama@example.com"
                        class="block mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm"
                    >
                </label>

                <label
                    id="target-angkatan-box"
                    class="block text-xs font-semibold text-slate-700"
                >
                    Angkatan Mahasiswa

                    <input
                        type="number"
                        name="target_angkatan"
                        value="{{ old('target_angkatan') }}"
                        min="1901"
                        max="{{ date('Y') }}"
                        placeholder="Contoh: 2023"
                        class="block mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm"
                    >
                </label>
            </section>

            <section class="space-y-4 border-t border-slate-100 pt-5">
                <h2 class="text-sm font-bold text-slate-800">
                    2. Jenis Akses
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm cursor-pointer">
                        <input
                            type="checkbox"
                            name="can_read"
                            value="1"
                            @checked($readChecked)
                            class="accent-[#4a6b5d]"
                        >

                        Izin Baca
                    </label>

                    <label class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm cursor-pointer">
                        <input
                            type="checkbox"
                            name="can_upload"
                            id="grant-upload"
                            value="1"
                            @checked($uploadChecked)
                            class="accent-[#4a6b5d]"
                        >

                        Izin Upload
                    </label>
                </div>

                <label class="block text-xs font-semibold text-slate-700">
                    Dokumen Target

                    <select
                        name="document_id"
                        id="grant-document"
                        class="block mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm disabled:opacity-60"
                    >
                        <option value="">
                            Semua Dokumen / Akses Global
                        </option>

                        @foreach($documents as $doc)
                            <option
                                value="{{ $doc->id }}"
                                @selected(old('document_id') == $doc->id)
                            >
                                {{ $doc->title }} ({{ $doc->tahun }})
                            </option>
                        @endforeach
                    </select>
                </label>

                <p class="text-xs text-slate-500 leading-relaxed">
                    Untuk izin baca, Anda dapat memilih satu
                    dokumen atau seluruh dokumen.
                    Izin upload selalu berlaku secara global.
                    Jika ingin memberi izin baca satu dokumen
                    sekaligus izin upload, simpan sebagai dua
                    pemberian akses terpisah.
                </p>
            </section>

            <section class="space-y-4 border-t border-slate-100 pt-5">
                <h2 class="text-sm font-bold text-slate-800">
                    3. Masa Berlaku
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="border border-slate-200 rounded-xl p-4 flex items-start gap-3 cursor-pointer">
                        <input
                            type="radio"
                            name="duration_type"
                            value="default"
                            @checked($durationType === 'default')
                            class="mt-1 accent-[#4a6b5d]"
                        >

                        <span>
                            <span class="block text-xs font-semibold text-slate-800">
                                Default — 3 Hari
                            </span>

                            <span class="block text-xs text-slate-400 mt-1">
                                Berlaku selama 72 jam
                            </span>
                        </span>
                    </label>

                    <label class="border border-slate-200 rounded-xl p-4 flex items-start gap-3 cursor-pointer">
                        <input
                            type="radio"
                            name="duration_type"
                            value="custom"
                            @checked($durationType === 'custom')
                            class="mt-1 accent-[#4a6b5d]"
                        >

                        <span>
                            <span class="block text-xs font-semibold text-slate-800">
                                Tanggal & Jam Khusus
                            </span>

                            <span class="block text-xs text-slate-400 mt-1">
                                Tentukan waktu berakhir
                            </span>
                        </span>
                    </label>
                </div>

                <label
                    id="duration-custom-box"
                    class="block text-xs font-semibold text-slate-700"
                >
                    Waktu Berakhir — WIB

                    <input
                        type="datetime-local"
                        name="expired_at"
                        value="{{ old('expired_at') }}"
                        min="{{ now()->format('Y-m-d\TH:i') }}"
                        class="block mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm"
                    >
                </label>
            </section>

            <button
                type="submit"
                class="w-full bg-sage-600 hover:bg-sage-700 text-white font-semibold py-3 rounded-xl text-sm transition"
            >
                Simpan Izin Akses
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById(
                'grant-access-form'
            );

            const singleBox = document.getElementById(
                'target-single-box'
            );

            const angkatanBox = document.getElementById(
                'target-angkatan-box'
            );

            const durationBox = document.getElementById(
                'duration-custom-box'
            );

            const upload = document.getElementById(
                'grant-upload'
            );

            const documentSelect = document.getElementById(
                'grant-document'
            );

            function syncTarget() {
                const selected = form.querySelector(
                    '[name="target_type"]:checked'
                ).value;

                const singleInput = singleBox.querySelector(
                    'input'
                );

                const angkatanInput = angkatanBox.querySelector(
                    'input'
                );

                singleBox.hidden = selected !== 'single';
                singleInput.required = selected === 'single';
                singleInput.disabled = selected !== 'single';

                angkatanBox.hidden = selected !== 'angkatan';
                angkatanInput.required = selected === 'angkatan';
                angkatanInput.disabled = selected !== 'angkatan';
            }

            function syncDuration() {
                const selected = form.querySelector(
                    '[name="duration_type"]:checked'
                ).value;

                const input = durationBox.querySelector(
                    'input'
                );

                const custom = selected === 'custom';

                durationBox.hidden = !custom;
                input.required = custom;
                input.disabled = !custom;
            }

            function syncUploadScope() {
                if (upload.checked) {
                    documentSelect.value = '';
                }

                documentSelect.disabled = upload.checked;
            }

            form.querySelectorAll(
                '[name="target_type"]'
            ).forEach(function (input) {
                input.addEventListener('change', syncTarget);
            });

            form.querySelectorAll(
                '[name="duration_type"]'
            ).forEach(function (input) {
                input.addEventListener('change', syncDuration);
            });

            upload.addEventListener(
                'change',
                syncUploadScope
            );

            syncTarget();
            syncDuration();
            syncUploadScope();
        });
    </script>
@endsection