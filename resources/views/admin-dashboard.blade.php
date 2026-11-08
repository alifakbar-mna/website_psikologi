@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    @php
        $statLabels = [
            'visitor' => 'Visitor Unik',
            'visits' => 'Kunjungan',
            'reads' => 'Pembacaan Laporan',
            'documents' => 'Laporan Terbit',
            'pending' => 'Menunggu Review',
        ];

        $trendMaximum = max(
            1,
            collect($trend)->max('total') ?? 0
        );

        $topMaximum = max(
            1,
            $top->max('total') ?? 0
        );

        $caseTotal = $cases->sum('total');

        $firstDate = $trend[0]['date'] ?? '-';

        $lastDate = count($trend)
            ? $trend[count($trend) - 1]['date']
            : '-';
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Dashboard
                </h1>

                <p class="text-sm text-slate-500 mt-2">
                    Aktivitas {{ $days }} hari terakhir · WIB
                </p>
            </div>

            <form
                method="GET"
                action="{{ route('admin.dashboard') }}"
                class="flex flex-wrap items-end gap-3"
            >
                <label class="block text-xs font-semibold text-slate-600">
                    Periode Aktivitas

                    <select
                        name="days"
                        class="block mt-2 border border-slate-200 bg-white rounded-xl p-3 text-sm"
                    >
                        @foreach([7, 30, 90] as $period)
                            <option
                                value="{{ $period }}"
                                @selected($days === $period)
                            >
                                {{ $period }} hari
                            </option>
                        @endforeach
                    </select>
                </label>

                <button
                    type="submit"
                    class="bg-sage-600 hover:bg-sage-700 text-white px-4 py-3 rounded-xl text-sm font-semibold transition"
                >
                    Tampilkan
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            @foreach($statLabels as $key => $label)
                <article class="bg-white border border-slate-200 rounded-2xl p-5">
                    <h2 class="text-xs text-slate-500">
                        {{ $label }}
                    </h2>

                    <p class="font-bold text-3xl text-sage-700 mt-3">
                        {{ number_format($stats[$key], 0, ',', '.') }}
                    </p>

                    <p class="text-[11px] text-slate-400 mt-2">
                        @if(in_array($key, ['documents', 'pending'], true))
                            Jumlah saat ini
                        @else
                            {{ $days }} hari terakhir
                        @endif
                    </p>
                </article>
            @endforeach
        </div>

        <div class="bg-sage-50 border border-sage-100 rounded-xl p-4 text-xs text-sage-800 leading-relaxed space-y-2">
            <p>
                <strong>Visitor unik:</strong>
                jumlah akun berbeda yang tercatat mengunjungi
                aplikasi pada periode terpilih.
            </p>

            <p>
                <strong>Kunjungan:</strong>
                kunjungan baru dicatat setelah 30 menit tanpa
                aktivitas halaman pada session tersebut.
            </p>

            <p>
                <strong>Pembacaan laporan:</strong>
                dihitung ketika reader dibuka dari katalog.
                Preview untuk pemeriksaan laporan tidak dihitung.
            </p>
        </div>

        <section class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-5">
            <div>
                <h2 class="font-bold text-slate-800">
                    Tren Kunjungan
                </h2>

                <p class="text-xs text-slate-500 mt-2">
                    Jumlah kunjungan aplikasi per hari.
                    Arahkan kursor ke batang untuk melihat nilainya.
                </p>
            </div>

            @if(collect($trend)->sum('total') === 0)
                <p class="text-sm text-slate-400">
                    Belum ada kunjungan tercatat pada periode ini.
                </p>
            @endif

            <div class="overflow-x-auto">
                <div
                    class="flex items-end gap-1 h-44 min-w-[600px] border-b border-slate-200"
                    role="img"
                    aria-label="Grafik kunjungan harian dari {{ $firstDate }} sampai {{ $lastDate }}. Data lengkap tersedia pada bagian Lihat Data Harian."
                >
                    @foreach($trend as $point)
                        @php
                            $height = (
                                $point['total'] / $trendMaximum
                            ) * 100;
                        @endphp

                        <div class="flex-1 h-full flex items-end">
                            <div
                                class="w-full bg-sage-500 hover:bg-sage-700 rounded-t transition"
                                style="height: {{ $height }}%; min-height: {{ $point['total'] ? 2 : 0 }}px;"
                                title="{{ $point['date'] }}: {{ $point['total'] }} kunjungan"
                            ></div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between gap-4 text-xs text-slate-400 mt-3 min-w-[600px]">
                    <span>{{ $firstDate }}</span>
                    <span>{{ $lastDate }}</span>
                </div>
            </div>

            <details class="text-sm">
                <summary class="cursor-pointer font-semibold text-sage-700">
                    Lihat Data Harian
                </summary>

                <div class="max-h-64 overflow-auto mt-4 border border-slate-100 rounded-xl">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="p-3 text-slate-600">
                                    Tanggal
                                </th>

                                <th scope="col" class="p-3 text-slate-600">
                                    Kunjungan
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($trend as $point)
                                <tr class="border-t border-slate-100">
                                    <td class="p-3 text-slate-600">
                                        {{ $point['date'] }}
                                    </td>

                                    <td class="p-3 font-semibold text-sage-700">
                                        {{ number_format($point['total'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <section class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-5">
                <div>
                    <h2 class="font-bold text-slate-800">
                        Laporan Paling Sering Dibaca
                    </h2>

                    <p class="text-xs text-slate-500 mt-2">
                        Hingga 10 laporan teratas dalam
                        {{ $days }} hari terakhir.
                    </p>
                </div>

                @forelse($top as $entry)
                    @php
                        $width = (
                            $entry->total / $topMaximum
                        ) * 100;
                    @endphp

                    <div>
                        <div class="flex justify-between items-start gap-4 text-sm">
                            <p class="text-slate-600 break-words min-w-0">
                                <span class="font-bold text-sage-700">
                                    {{ $loop->iteration }}.
                                </span>

                                {{ $titles[$entry->document_id] ?? 'Laporan tidak tersedia' }}
                            </p>

                            <span class="font-bold text-sage-700 shrink-0">
                                {{ number_format($entry->total, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="bg-sage-50 rounded-full h-2 mt-3 overflow-hidden">
                            <div
                                class="bg-sage-500 rounded-full h-2"
                                style="width: {{ $width }}%;"
                                aria-hidden="true"
                            ></div>
                        </div>
                    </div>
                @empty
                    <div class="bg-slate-50 rounded-xl p-6 text-center text-sm text-slate-500">
                        Belum ada pembacaan laporan
                        pada periode ini.
                    </div>
                @endforelse
            </section>

            <section class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-5">
                <div>
                    <h2 class="font-bold text-slate-800">
                        Jenis Kasus Laporan Diterima
                    </h2>

                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Seluruh laporan yang sudah diterima,
                        termasuk yang sedang disembunyikan.
                        Tidak dibatasi periode aktivitas.
                    </p>
                </div>

                @forelse($cases as $entry)
                    @php
                        $percentage = $caseTotal > 0
                            ? ($entry->total / $caseTotal) * 100
                            : 0;
                    @endphp

                    <div>
                        <div class="flex justify-between items-start gap-3 text-sm">
                            <p class="font-semibold text-slate-700">
                                {{ $entry->case_type }}
                            </p>

                            <p class="text-slate-500">
                                {{ number_format($entry->total, 0, ',', '.') }}
                                laporan
                                ·
                                {{ number_format($percentage, 1, ',', '.') }}%
                            </p>
                        </div>

                        <div class="bg-sage-50 h-3 rounded-full mt-3 overflow-hidden">
                            <div
                                class="bg-sage-500 h-3 rounded-full"
                                style="width: {{ $percentage }}%;"
                                aria-hidden="true"
                            ></div>
                        </div>
                    </div>
                @empty
                    <div class="bg-slate-50 rounded-xl p-6 text-center text-sm text-slate-500">
                        Belum ada laporan yang diterima.
                    </div>
                @endforelse

                @if($caseTotal > 0)
                    <p class="text-xs text-slate-400 border-t border-slate-100 pt-4">
                        Total:
                        {{ number_format($caseTotal, 0, ',', '.') }}
                        laporan diterima.
                    </p>
                @endif
            </section>
        </div>
    </div>
@endsection