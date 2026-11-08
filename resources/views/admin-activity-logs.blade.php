@extends('layouts.app', ['title' => 'Log Aktivitas'])

@section('content')
    @php
        $actionLabels = [
            'VISIT' => 'Kunjungan',
            'LOGIN' => 'Login',
            'LOGOUT' => 'Logout',
            'READ_DOCUMENT' => 'Baca Laporan',
            'UPLOAD' => 'Upload Laporan',
            'RESUBMIT' => 'Kirim Revisi',
            'EDIT_DOCUMENT' => 'Edit Laporan',
            'REVIEW_UPLOAD' => 'Review Upload',
            'GRANT_ACCESS' => 'Berikan Akses',
            'PROCESS_ACCESS' => 'Proses Permohonan',
            'CREATE_USER' => 'Tambah Anggota',
            'UPDATE_USER' => 'Edit Anggota',
            'DELETE_USER' => 'Hapus Anggota',
            'IMPORT_MEMBERS' => 'Import Anggota',
            'PERMISSIONS_UPDATED' => 'Ubah Hak Akses',
        ];
    @endphp

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Log Aktivitas
                </h1>

                <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                    Riwayat kunjungan, login, pembacaan laporan,
                    dan perubahan data yang dicatat sistem.
                    Aktivitas terbaru ditampilkan terlebih dahulu.
                </p>
            </div>

            @if(auth()->user()->permits('dashboard'))
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="inline-flex items-center gap-2 text-xs font-semibold text-sage-700 hover:underline"
                >
                    <i
                        data-lucide="arrow-left"
                        class="w-4 h-4"
                    ></i>

                    Kembali ke Dashboard
                </a>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 text-xs font-bold text-slate-500 border-b border-slate-200">
                            <th scope="col" class="p-4">
                                Waktu — WIB
                            </th>

                            <th scope="col" class="p-4">
                                Pengguna
                            </th>

                            <th scope="col" class="p-4">
                                Aksi
                            </th>

                            <th scope="col" class="p-4">
                                Deskripsi Aktivitas
                            </th>

                            <th scope="col" class="p-4">
                                Alamat IP
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/50 transition align-top">
                                <td class="p-4 text-slate-400 font-mono whitespace-nowrap">
                                    {{ $log->created_at?->format('d M Y H:i:s') ?? '-' }}
                                </td>

                                <td class="p-4">
                                    <p class="font-semibold">
                                        {{ $log->user?->name ?? 'Sistem / akun tidak tersedia' }}
                                    </p>

                                    <p class="text-[11px] text-slate-400 mt-1 break-words">
                                        {{ $log->user?->email ?? '-' }}
                                    </p>
                                </td>

                                <td class="p-4">
                                    <span class="inline-block px-2.5 py-1.5 text-[10px] font-semibold rounded-lg bg-sage-50 text-sage-700 whitespace-nowrap">
                                        {{ $actionLabels[$log->action] ?? $log->action }}
                                    </span>
                                </td>

                                <td class="p-4 min-w-[220px]">
                                    <p class="text-slate-600 whitespace-pre-wrap break-words">{{ $log->description }}</p>

                                    @if($log->document_id)
                                        <p class="text-[10px] text-slate-400 mt-2">
                                            ID laporan:
                                            {{ $log->document_id }}
                                        </p>
                                    @endif
                                </td>

                                <td class="p-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                    {{ $log->ip_address ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="p-10 text-center text-sm text-slate-400"
                                >
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