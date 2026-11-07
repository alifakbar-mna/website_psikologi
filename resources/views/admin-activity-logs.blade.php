@extends('layouts.app', ['title' => 'Audit Activity Logs - Admin'])

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Audit Trail Activity Logs</h1>
            <p class="text-xs text-slate-500 mt-1">Lacak seluruh aktivitas login, baca, download, dan modifikasi data secara mendalam.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-sage-700 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- Activity Log Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200/80">
                        <th class="p-4">Waktu</th>
                        <th class="p-4">Pengguna</th>
                        <th class="p-4">Aksi</th>
                        <th class="p-4">Deskripsi Aktivitas</th>
                        <th class="p-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-4 text-slate-400 font-mono text-[11px] whitespace-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="p-4 font-semibold">
                                {{ $log->user ? $log->user->name : 'Sistem' }}
                                <span class="block text-[10px] font-normal text-slate-400">{{ $log->user ? $log->user->email : '-' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-md bg-slate-100 text-slate-700 uppercase">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="p-4 text-slate-600">
                                {{ $log->description }}
                            </td>
                            <td class="p-4 font-mono text-[11px] text-slate-400">
                                {{ $log->ip_address ?: '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">
                                Belum ada log aktivitas tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection