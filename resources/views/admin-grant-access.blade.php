@extends('layouts.app', ['title' => 'Beri Akses Sementara - Admin'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Header & Title -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Atur & Beri Akses Sementara</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola izin baca, download, atau upload dengan durasi auto-expired.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-sage-700 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- Alert Success / Error Flash Message -->
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs p-4 rounded-xl flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Main Grant Access Form -->
    <form action="{{ route('access.grant.store') }}" method="POST" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-sm space-y-6">
        @csrf

        <!-- 1. Target Penerima Akses -->
        <div class="space-y-3">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">1. Target Penerima Akses</label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <label class="border border-slate-200 rounded-xl p-3.5 flex items-start gap-3 cursor-pointer hover:border-sage-500 transition">
                    <input type="radio" name="target_type" value="single" class="mt-0.5 text-sage-600 focus:ring-sage-500" checked onclick="toggleTargetInput('single')">
                    <div>
                        <span class="block text-xs font-semibold text-slate-800">1 Mahasiswa Spesifik</span>
                        <span class="block text-[11px] text-slate-400 mt-0.5">Input via Email/NIM</span>
                    </div>
                </label>

                <label class="border border-slate-200 rounded-xl p-3.5 flex items-start gap-3 cursor-pointer hover:border-sage-500 transition">
                    <input type="radio" name="target_type" value="angkatan" class="mt-0.5 text-sage-600 focus:ring-sage-500" onclick="toggleTargetInput('angkatan')">
                    <div>
                        <span class="block text-xs font-semibold text-slate-800">Per Angkatan</span>
                        <span class="block text-[11px] text-slate-400 mt-0.5">Pilih tahun angkatan</span>
                    </div>
                </label>

                <label class="border border-slate-200 rounded-xl p-3.5 flex items-start gap-3 cursor-pointer hover:border-sage-500 transition">
                    <input type="radio" name="target_type" value="all" class="mt-0.5 text-sage-600 focus:ring-sage-500" onclick="toggleTargetInput('all')">
                    <div>
                        <span class="block text-xs font-semibold text-slate-800">Semua Mahasiswa</span>
                        <span class="block text-[11px] text-slate-400 mt-0.5">Seluruh akun aktif</span>
                    </div>
                </label>
            </div>

            <!-- Dynamic Target Form Input -->
            <div id="target-single-box" class="pt-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Email Mahasiswa</label>
                <input type="email" name="user_email" placeholder="mahasiswa@student.ciputra.ac.id" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
            </div>

            <div id="target-angkatan-box" class="pt-2 hidden">
                <label class="block text-xs font-medium text-slate-600 mb-1">Pilih Angkatan</label>
                <select name="target_angkatan" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
                    <option value="2026">Angkatan 2026</option>
                    <option value="2025">Angkatan 2025</option>
                    <option value="2024">Angkatan 2024</option>
                    <option value="2023">Angkatan 2023</option>
                </select>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- 2. Objek Dokumen Spesifik (Optional) -->
        <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">2. Dokumen Target (Opsional)</label>
            <select name="document_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
                <option value="">Akses Global (Semua Dokumen / Izin Login Umum)</option>
                @foreach($documents as $doc)
                    <option value="{{ $doc->id }}">{{ $doc->title }} ({{ $doc->tahun }})</option>
                @endforeach
            </select>
            <p class="text-[11px] text-slate-400">Pilih dokumen tertentu jika ingin membatasi izin hanya untuk 1 file PDF tersebut.</p>
        </div>

        <hr class="border-slate-100">

        <!-- 3. Checklist Akses yang Diberikan -->
        <div class="space-y-3">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">3. Akses Yang Diberikan</label>
            <div class="grid grid-cols-3 gap-3">
                <label class="flex items-center gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 cursor-pointer">
                    <input type="checkbox" name="can_read" value="1" class="rounded text-sage-600 focus:ring-sage-500" checked>
                    <span>Izin Baca (View)</span>
                </label>
                <label class="flex items-center gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 cursor-pointer">
                    <input type="checkbox" name="can_download" value="1" class="rounded text-sage-600 focus:ring-sage-500">
                    <span>Izin Download</span>
                </label>
                <label class="flex items-center gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 cursor-pointer">
                    <input type="checkbox" name="can_upload" value="1" class="rounded text-sage-600 focus:ring-sage-500">
                    <span>Izin Upload</span>
                </label>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- 4. Durasi Masa Berlaku Access (Auto Expired) -->
        <div class="space-y-3">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">4. Masa Berlaku Akses (Auto-Expired)</label>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="border border-slate-200 rounded-xl p-3.5 flex items-start gap-3 cursor-pointer hover:border-sage-500 transition">
                    <input type="radio" name="duration_type" value="default" class="mt-0.5 text-sage-600 focus:ring-sage-500" checked onclick="toggleDurationInput('default')">
                    <div>
                        <span class="block text-xs font-semibold text-slate-800">Default (3 Hari)</span>
                        <span class="block text-[11px] text-slate-400 mt-0.5">Otomatis hangus dalam 72 jam</span>
                    </div>
                </label>

                <label class="border border-slate-200 rounded-xl p-3.5 flex items-start gap-3 cursor-pointer hover:border-sage-500 transition">
                    <input type="radio" name="duration_type" value="custom" class="mt-0.5 text-sage-600 focus:ring-sage-500" onclick="toggleDurationInput('custom')">
                    <div>
                        <span class="block text-xs font-semibold text-slate-800">Kustom Tanggal & Jam</span>
                        <span class="block text-[11px] text-slate-400 mt-0.5">Pilih tanggal spesifik</span>
                    </div>
                </label>
            </div>

            <!-- Custom DateTime Picker -->
            <div id="duration-custom-box" class="pt-2 hidden">
                <label class="block text-xs font-medium text-slate-600 mb-1">Pilih Tanggal & Jam Kadaluarsa</label>
                <input type="datetime-local" name="expired_at" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="w-full bg-sage-600 hover:bg-sage-700 text-white font-bold py-3.5 rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>Simpan Akses & Kirim Notifikasi Email</span>
            </button>
        </div>
    </form>
</div>

<script>
    function toggleTargetInput(type) {
        document.getElementById('target-single-box').classList.add('hidden');
        document.getElementById('target-angkatan-box').classList.add('hidden');

        if (type === 'single') {
            document.getElementById('target-single-box').classList.remove('hidden');
        } else if (type === 'angkatan') {
            document.getElementById('target-angkatan-box').classList.remove('hidden');
        }
    }

    function toggleDurationInput(type) {
        if (type === 'custom') {
            document.getElementById('duration-custom-box').classList.remove('hidden');
        } else {
            document.getElementById('duration-custom-box').classList.add('hidden');
        }
    }
</script>
@endsection