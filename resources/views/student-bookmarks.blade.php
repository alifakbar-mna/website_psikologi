@extends('layouts.app', ['title' => 'Simpanan Saya - Repository PSY'])

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Simpanan Saya</h1>
        <p class="text-sm text-slate-500 mt-1">Daftar laporan kasus dan dokumen yang Anda simpan sebagai favorit.</p>
    </div>

    @if($documents->isEmpty())
        <div class="bg-white p-12 text-center rounded-2xl border border-slate-200 space-y-3">
            <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto">
                <i data-lucide="bookmark-x" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-700">Belum Ada Dokumen Tersimpan</h3>
            <p class="text-xs text-slate-400">Klik ikon simpan/bookmark pada dokumen di katalog untuk menyimpannya di sini.</p>
            <a href="{{ route('student.catalog') }}" class="inline-block mt-2 px-4 py-2 bg-sage-600 text-white text-xs font-semibold rounded-xl hover:bg-sage-700 transition">
                Jelajahi Katalog
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($documents as $doc)
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 flex flex-col justify-between hover:border-sage-500 hover:shadow-md transition duration-200 group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 text-[10px] font-semibold bg-sage-50 text-sage-700 rounded-lg truncate max-w-[150px]">
                                {{ $doc->category->name }}
                            </span>
                            <span class="text-xs font-medium text-slate-400">{{ $doc->tahun }}</span>
                        </div>

                        <h3 class="text-sm font-bold text-slate-800 line-clamp-2 leading-snug group-hover:text-sage-700 transition">
                            {{ $doc->title }}
                        </h3>

                        <div class="text-xs text-slate-500 space-y-1 pt-1 border-t border-slate-100">
                            <p class="truncate"><span class="text-slate-400">Pembimbing:</span> {{ $doc->dosen_pembimbing }}</p>
                            <p><span class="text-slate-400">Angkatan:</span> {{ $doc->angkatan_pembuat ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center space-x-3 text-slate-400 text-xs">
                            <span class="flex items-center gap-1">
                                <i data-lucide="heart" class="w-3.5 h-3.5 text-rose-400 fill-current"></i> 
                                {{ $doc->likes ? $doc->likes->count() : 0 }}
                            </span>
                        </div>

                        <a href="{{ route('student.document.show', $doc->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sage-50 hover:bg-sage-600 text-sage-700 hover:text-white rounded-lg text-xs font-bold transition">
                            <span>Baca</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-4">
            {{ $documents->links() }}
        </div>
    @endif
</div>
@endsection