@extends('layouts.app', ['title' => 'Dashboard Admin - Repository PSY'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard Administrator</h1>
            <p class="text-xs text-slate-500 mt-1">Ringkasan aktivitas sistem, statistik repositori, dan kontrol hak akses.</p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex items-center space-x-2">
            <a href="{{ route('access.grant.create') }}" class="bg-sage-600 hover:bg-sage-700 text-white font-medium px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition shadow-sm">
                <i data-lucide="shield-plus" class="w-4 h-4"></i>
                <span>Beri Akses Sementara</span>
            </a>
            <a href="{{ route('lecturer.requests') }}" class="bg-slate-800 hover:bg-slate-900 text-white font-medium px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition shadow-sm">
                <i data-lucide="inbox" class="w-4 h-4"></i>
                <span>List Permohonan</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400">Total Dokumen</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total_documents'] }}</h3>
            </div>
            <div class="w-11 h-11 bg-sage-50 text-sage-600 rounded-xl flex items-center justify-center">
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400">Mahasiswa Aktif</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total_students'] }}</h3>
            </div>
            <div class="w-11 h-11 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400">Dosen Pembimbing</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total_lecturers'] }}</h3>
            </div>
            <div class="w-11 h-11 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                <i data-lucide="graduation-cap" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400">Permohonan Pending</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_requests'] }}</h3>
            </div>
            <div class="w-11 h-11 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Content Split Section: Logs & Documents -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Activity Logs Overview (2 Columns) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <i data-lucide="activity" class="w-4 h-4 text-sage-600"></i>
                    <h2 class="text-sm font-bold text-slate-800">Audit Trail Aktivitas Terbaru</h2>
                </div>
                <a href="{{ route('admin.logs') }}" class="text-xs font-semibold text-sage-600 hover:text-sage-800 transition">Lihat Semua Logs &rarr;</a>
            </div>

            <div class="space-y-3">
                @forelse($latestLogs as $log)
                    <div class="flex items-start justify-between p-3 bg-slate-50/70 rounded-xl border border-slate-100 text-xs">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800">{{ $log->user ? $log->user->name : 'Sistem' }}</span>
                                <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-slate-200 text-slate-700">{{ $log->action }}</span>
                            </div>
                            <p class="text-slate-500">{{ $log->description }}</p>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">Belum ada catatan aktivitas sistem.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Uploaded Documents (1 Column) -->
        <div class="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center space-x-2 border-b border-slate-100 pb-3">
                <i data-lucide="file-up" class="w-4 h-4 text-sage-600"></i>
                <h2 class="text-sm font-bold text-slate-800">Dokumen Terbaru</h2>
            </div>

            <div class="space-y-3">
                @forelse($recentDocuments as $doc)
                    <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-100 space-y-1">
                        <span class="text-[9px] font-bold text-sage-700 bg-sage-50 px-2 py-0.5 rounded">{{ $doc->category->name }}</span>
                        <h4 class="text-xs font-bold text-slate-800 line-clamp-1">{{ $doc->title }}</h4>
                        <p class="text-[10px] text-slate-400">Tahun: {{ $doc->tahun }} | Pembimbing: {{ $doc->dosen_pembimbing }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">Belum ada dokumen diunggah.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection