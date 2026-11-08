<p class="mb-4 text-xs font-semibold text-slate-500">
    Menampilkan {{ $documents->count() }}
    dari {{ $documents->total() }} dokumen
</p>

@forelse($documents as $doc)
    @if($loop->first)
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-5">
    @endif

    @php
        $hasReadAccess = auth()
            ->user()
            ->hasAccessToDocument($doc->id);
    @endphp

    <article class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between gap-4 hover:border-sage-500 hover:shadow-md transition">
        <div class="space-y-3 min-w-0">
            <div class="flex items-center justify-between gap-2">
                <span class="truncate px-2.5 py-1 text-[10px] font-semibold bg-sage-50 text-sage-700 rounded-lg">
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

            <div class="text-xs text-slate-500 space-y-2 border-t border-slate-100 pt-3">
                <p class="break-words">
                    Pembimbing: {{ $doc->dosen_pembimbing }}
                </p>

                <p>
                    Angkatan: {{ $doc->angkatan_pembuat ?? '-' }}
                </p>

                <p>
                    Jenis kasus: {{ $doc->case_type }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
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
                    class="px-3 py-2 bg-sage-50 hover:bg-sage-600 text-sage-700 hover:text-white rounded-lg text-xs font-bold transition"
                >
                    Baca
                </a>
            @elseif(auth()->user()->permits('request_access'))
                <button
                    type="button"
                    data-request-id="{{ $doc->id }}"
                    data-request-title="{{ $doc->title }}"
                    class="px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-bold transition"
                >
                    Request Baca
                </button>
            @else
                <span class="text-xs text-slate-400">
                    Akses baca belum diberikan
                </span>
            @endif
        </div>
    </article>

    @if($loop->last)
        </div>
    @endif
@empty
    <div class="bg-white p-10 text-center rounded-2xl border border-slate-200 space-y-3">
        <h2 class="font-semibold text-slate-700">
            Tidak ada dokumen ditemukan
        </h2>

        <p class="text-sm text-slate-500">
            Coba ubah kata kunci atau filter pencarian.
        </p>
    </div>
@endforelse

<div class="pagination pt-5">
    {{ $documents->links() }}
</div>