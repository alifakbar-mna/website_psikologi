@extends('layouts.app', [
    'title' => [
        'mine' => 'Laporan Saya',
        'review' => 'Review Upload',
        'manage' => 'Kelola Laporan',
    ][$mode],
])

@section('content')
    @php
        $pageTitles = [
            'mine' => 'Laporan Saya',
            'review' => 'Review Upload',
            'manage' => 'Kelola Laporan',
        ];
    @endphp

    <div class="max-w-6xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                {{ $pageTitles[$mode] }}
            </h1>

            <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                @if($mode === 'review')
                    Baca isi laporan, lalu tentukan keputusan
                    dan catatan untuk pengunggah.
                @else
                    Status review terpisah dari visibilitas.
                    Laporan hanya tampil di katalog setelah
                    diterima dan dibuka.
                @endif
            </p>
        </div>

        @if($mode === 'manage')
            <form
                method="GET"
                action="{{ route('reports.manage') }}"
                class="flex flex-wrap gap-3"
            >
                <input
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari judul laporan"
                    aria-label="Cari judul laporan"
                    class="flex-1 min-w-0 border border-slate-200 bg-white rounded-xl p-3 text-sm focus:outline-none focus:border-sage-600"
                >

                <button
                    type="submit"
                    class="bg-sage-600 hover:bg-sage-700 text-white rounded-xl px-5 py-3 text-sm font-semibold transition"
                >
                    Cari
                </button>
            </form>
        @endif

        @forelse($documents as $doc)
            <article class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-4">
                <div class="flex flex-wrap justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="font-bold text-slate-800 break-words">
                            {{ $doc->title }}
                        </h2>

                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            {{ $doc->category->name ?? '-' }}
                            · {{ $doc->case_type }}
                            · {{ $doc->tahun }}

                            @if($mode !== 'mine')
                                · {{ $doc->uploader->name ?? '-' }}
                            @endif
                        </p>
                    </div>

                    <div class="text-xs flex flex-wrap items-center gap-2">
                        <span class="inline-block rounded-lg bg-sage-50 text-sage-700 px-3 py-2 font-semibold">
                            {{ $doc->reviewLabel() }}
                        </span>

                        <span class="text-slate-500">
                            {{ $doc->status === 'hide' ? 'Disembunyikan' : 'Terbuka' }}
                        </span>
                    </div>
                </div>

                @if($doc->review_note)
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-sm text-amber-900">
                        <strong>Catatan reviewer</strong>

                        <p class="whitespace-pre-wrap break-words mt-2">{{ $doc->review_note }}</p>
                    </div>
                @endif

                @if($doc->description)
                    <div>
                        <p class="text-sm text-slate-600 line-clamp-3 whitespace-pre-wrap break-words">{{ $doc->description }}</p>

                        <button
                            type="button"
                            data-description="{{ $doc->description }}"
                            class="mt-2 text-xs text-sage-700 font-semibold underline"
                        >
                            Detail deskripsi
                        </button>
                    </div>
                @endif

                <div class="flex gap-4 flex-wrap text-sm">
                    <a
                        class="text-sage-700 font-semibold hover:underline"
                        href="{{ route('reports.preview', $doc->id) }}"
                    >
                        Baca PDF
                    </a>

                    @if(
                        $mode === 'manage'
                        || (
                            $mode === 'mine'
                            && $doc->review_status === 'needs_revision'
                        )
                    )
                        <a
                            class="text-sage-700 font-semibold hover:underline"
                            href="{{ route('reports.edit', $doc->id) }}"
                        >
                            {{ $mode === 'mine' ? 'Perbaiki & kirim ulang' : 'Edit laporan / visibilitas' }}
                        </a>
                    @endif
                </div>

                @if($mode === 'review')
                    @if($doc->uploaded_by === auth()->id())
                        <p class="text-sm text-amber-700">
                            Laporan Anda memerlukan reviewer lain.
                        </p>
                    @else
                        <form
                            method="POST"
                            action="{{ route('reports.decide', $doc->id) }}"
                            class="space-y-4 border-t border-slate-100 pt-4"
                        >
                            @csrf

                            <input
                                type="hidden"
                                name="revision"
                                value="{{ $doc->revision }}"
                            >

                            <label class="block text-xs font-bold text-slate-700">
                                Keputusan

                                <select
                                    name="decision"
                                    required
                                    class="block mt-2 w-full sm:w-auto border border-slate-200 bg-slate-50 rounded-xl p-3 text-sm focus:outline-none focus:border-sage-600"
                                >
                                    <option value="approved">
                                        Terima
                                    </option>

                                    <option value="needs_revision">
                                        Perlu revisi
                                    </option>

                                    <option value="rejected">
                                        Tolak
                                    </option>
                                </select>
                            </label>

                            <label class="block text-xs font-bold text-slate-700">
                                Catatan

                                <span class="font-normal text-slate-400">
                                    (wajib untuk revisi atau penolakan)
                                </span>

                                <textarea
                                    name="note"
                                    maxlength="5000"
                                    rows="3"
                                    placeholder="Tuliskan catatan untuk pengunggah..."
                                    class="w-full mt-2 border border-slate-200 bg-slate-50 rounded-xl p-3 text-sm focus:outline-none focus:border-sage-600"
                                ></textarea>
                            </label>

                            <button
                                type="submit"
                                class="bg-sage-600 hover:bg-sage-700 text-white rounded-xl px-5 py-3 text-sm font-semibold transition"
                            >
                                Simpan Keputusan & Beri Tahu Pengunggah
                            </button>
                        </form>
                    @endif
                @endif
            </article>
        @empty
            <div class="bg-white rounded-2xl p-10 border border-slate-200 text-center text-sm text-slate-500">
                Belum ada laporan pada daftar ini.
            </div>
        @endforelse

        <div>
            {{ $documents->links() }}
        </div>
    </div>
@endsection