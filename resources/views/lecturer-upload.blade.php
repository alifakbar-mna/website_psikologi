@extends('layouts.app', ['title' => 'Upload Laporan Kasus - Dosen'])

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Upload Laporan Kasus Baru</h1>
        <p class="text-xs text-slate-500 mt-1">Unggah berkas PDF laporan kasus psikologi ke dalam repositori terlindungi.</p>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs p-4 rounded-xl flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('lecturer.upload.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-sm space-y-5">
        @csrf

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Judul Laporan / Kasus</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Intervensi CBT pada Kasus Gangguan Kecemasan Sosial" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
            @error('title')
                <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori Psikologi</label>
                <select name="category_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tahun Kasus</label>
                <input type="number" name="tahun" value="{{ old('tahun', date('Y')) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
                @error('tahun')
                    <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Dosen Pembimbing</label>
                <input type="text" name="dosen_pembimbing" required value="{{ old('dosen_pembimbing', Auth::user()->name) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
                @error('dosen_pembimbing')
                    <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Angkatan Pembuat</label>
                <input type="number" name="angkatan_pembuat" value="{{ old('angkatan_pembuat') }}" placeholder="Contoh: 2022" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
                @error('angkatan_pembuat')
                    <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Visibilitas</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
                    <option value="open" {{ old('status') == 'open' ? 'selected' : '' }}>Open (Dapat Dilihat)</option>
                    <option value="hide" {{ old('status') == 'hide' ? 'selected' : '' }}>Hide (Sembunyikan Sementara)</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi Singkat / Abstrak</label>
            <textarea name="description" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600" placeholder="Ringkasan singkat mengenai metode intervensi dan subjek kasus...">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">File Dokumen PDF</label>
            <input type="file" name="pdf_file" accept="application/pdf" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sage-50 file:text-sage-700 hover:file:bg-sage-100">
            <p class="text-[11px] text-slate-400 mt-1">Format wajib .PDF dengan ukuran maksimal 10MB.</p>
            @error('pdf_file')
                <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full bg-sage-600 hover:bg-sage-700 text-white font-bold py-3.5 rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                <span>Unggah Laporan Kasus</span>
            </button>
        </div>
    </form>
</div>
@endsection