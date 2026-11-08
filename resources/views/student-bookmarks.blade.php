@extends('layouts.app', ['title' => 'Simpanan Saya'])

@section('content')
    @php
        $user = auth()->user();

        $canViewCatalog = $user->permits('catalog');

        $canRequestAccess = $user->permits(
            'request_access'
        );
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Simpanan Saya
                </h1>

                <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                    Daftar laporan yang Anda simpan sebagai
                    referensi. Hak baca tetap mengikuti
                    izin akses yang berlaku.
                </p>
            </div>

            @if($canViewCatalog)
                <a
                    href="{{ route('student.catalog') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-sage-700 hover:underline"
                >
                    <i
                        data-lucide="compass"
                        class="w-4 h-4"
                    ></i>

                    Buka Katalog
                </a>
            @endif
        </div>

        @if($documents->isEmpty())
            <div class="bg-white p-8 sm:p-12 text-center rounded-2xl border border-slate-200 space-y-4">
                <div class="w-14 h-14 bg-sage-50 text-sage-600 rounded-2xl flex items-center justify-center mx-auto">
                    <i
                        data-lucide="bookmark"
                        class="w-7 h-7"
                    ></i>
                </div>

                <h2 class="text-base font-semibold text-slate-700">
                    Belum ada laporan tersimpan
                </h2>

                <p class="text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                    Buka laporan yang boleh Anda baca,
                    lalu tekan tombol Simpan pada reader.
                    Laporan yang disembunyikan atau belum
                    diterima tidak ditampilkan di sini.
                </p>

                @if($canViewCatalog)
                    <a
                        href="{{ route('student.catalog') }}"
                        class="inline-flex items-center justify-center px-5 py-3 bg-sage-600 hover:bg-sage-700 text-white text-sm font-semibold rounded-xl transition"
                    >
                        Jelajahi Katalog
                    </a>
                @endif
            </div>
        @else
            <p class="text-xs text-slate-500">
                Menampilkan {{ $documents->count() }}
                dari {{ $documents->total() }}
                laporan tersimpan.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-5">
                @foreach($documents as $doc)
                    @php
                        $hasReadAccess = $user
                            ->hasAccessToDocument($doc->id);
                    @endphp

                    <article class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between gap-4 hover:border-sage-500 hover:shadow-md transition">
                        <div class="space-y-3 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-1 text-[10px] font-semibold bg-sage-50 text-sage-700 rounded-lg truncate">
                                    {{ $doc->category->name }}
                                </span>

                                <span class="text-xs text-slate-400 shrink-0">
                                    {{ $doc->tahun }}
                                </span>
                            </div>

                            <h2 class="text-sm font-bold text-slate-800 line-clamp-2 break-words">
                                {{ $doc->title }}
                            </h2>

                            @include('cms.description-snippet', [
                                'doc' => $doc,
                            ])

                            <div class="text-xs text-slate-500 space-y-2 pt-3 border-t border-slate-100">
                                <p class="break-words">
                                    Pembimbing:
                                    {{ $doc->dosen_pembimbing }}
                                </p>

                                <p>
                                    Angkatan:
                                    {{ $doc->angkatan_pembuat ?? '-' }}
                                </p>

                                <p>
                                    Jenis kasus:
                                    {{ $doc->case_type }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                            <span
                                class="inline-flex items-center gap-1 text-xs text-slate-400"
                                aria-label="{{ $doc->likes->count() }} suka"
                            >
                                <i
                                    data-lucide="heart"
                                    class="w-4 h-4 text-rose-400"
                                ></i>

                                {{ $doc->likes->count() }}
                            </span>

                            @if($hasReadAccess)
                                <a
                                    href="{{ route('student.document.show', $doc->id) }}"
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-sage-50 hover:bg-sage-600 text-sage-700 hover:text-white rounded-lg text-xs font-bold transition"
                                >
                                    Baca

                                    <i
                                        data-lucide="arrow-right"
                                        class="w-3.5 h-3.5"
                                    ></i>
                                </a>
                            @elseif($canRequestAccess)
                                <a
                                    href="{{ route('student.requests') }}"
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold transition"
                                >
                                    <i
                                        data-lucide="lock"
                                        class="w-3.5 h-3.5"
                                    ></i>

                                    Ajukan Izin Baca
                                </a>
                            @else
                                <span class="text-xs text-slate-400">
                                    Akses baca belum diberikan
                                </span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="pt-3">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
@endsection