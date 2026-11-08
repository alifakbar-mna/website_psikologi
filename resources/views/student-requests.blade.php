@extends('layouts.app', ['title' => 'Permohonan Akses'])

@section('content')
@php
    $types = [
        'baca_sistem' => 'Baca seluruh dokumen',
        'baca_dokumen' => 'Baca dokumen tertentu',
        'upload' => 'Izin upload laporan',
    ];

    $specificDocument =
        old('request_type', 'baca_sistem') === 'baca_dokumen';
@endphp

<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <div class="p-3 bg-sage-100 text-sage-700 rounded-2xl">
            <i data-lucide="key-round" class="w-6 h-6"></i>
        </div>

        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Permohonan Akses
            </h1>

            <p class="text-xs text-slate-500 mt-1">
                Ajukan izin baca atau upload dan pantau status permohonanmu.
            </p>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('student.requests.store') }}"
        class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6 space-y-5"
    >
        @csrf

        <h2 class="text-sm font-bold text-slate-800">
            Ajukan permohonan baru
        </h2>

        <div>
            <label
                for="request-type"
                class="text-xs font-bold text-slate-600"
            >
                Jenis permohonan
            </label>

            <select
                name="request_type"
                id="request-type"
                class="block mt-2 w-full border border-slate-200 rounded-xl p-3 text-sm"
            >
                @foreach($types as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(old('request_type', 'baca_sistem') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div
            id="document-target"
            @class(['hidden' => !$specificDocument])
        >
            <label
                for="request-document"
                class="block text-xs font-bold text-slate-600"
            >
                Dokumen yang ingin dibaca
            </label>

            <select
                name="document_id"
                id="request-document"
                @required($specificDocument)
                @disabled(!$specificDocument)
                class="block mt-2 w-full border border-slate-200 rounded-xl p-3 text-sm"
            >
                <option value="">Pilih dokumen</option>

                @foreach($documents as $doc)
                    <option
                        value="{{ $doc->id }}"
                        @selected(old('document_id') == $doc->id)
                    >
                        {{ $doc->title }}
                    </option>
                @endforeach
            </select>

            @if($documents->isEmpty())
                <p class="text-xs text-amber-700 mt-2">
                    Belum ada dokumen yang tersedia.
                </p>
            @endif
        </div>

        <div>
            <label
                for="request-reason"
                class="text-xs font-bold text-slate-600"
            >
                Alasan permohonan
            </label>

            <textarea
                id="request-reason"
                name="reason"
                required
                maxlength="500"
                rows="4"
                placeholder="Jelaskan keperluan aksesmu."
                class="block mt-2 w-full border border-slate-200 rounded-xl p-3 text-sm"
            >{{ old('reason') }}</textarea>

            <p class="text-xs text-slate-400 mt-2">
                Maksimal 500 karakter.
            </p>
        </div>

        <button
            class="bg-sage-600 hover:bg-sage-700 text-white rounded-xl px-5 py-3 text-xs font-bold"
        >
            Kirim permohonan
        </button>
    </form>

    <div class="space-y-3">
        <h2 class="text-sm font-bold text-slate-700">
            Status permohonan saya
        </h2>

        @forelse($requests as $req)
            <article
                class="bg-white border border-slate-200 rounded-2xl p-5 space-y-3 shadow-sm"
            >
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-bold text-slate-700">
                        {{ $types[$req->request_type] ?? $req->request_type }}
                    </h3>

                    <span
                        @class([
                            'text-xs font-bold px-3 py-1 rounded-lg',
                            'bg-amber-50 text-amber-700' =>
                                $req->status === 'pending',
                            'bg-emerald-50 text-emerald-700' =>
                                $req->status === 'approved',
                            'bg-rose-50 text-rose-700' =>
                                $req->status === 'rejected',
                        ])
                    >
                        {{ [
                            'pending' => 'Menunggu',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                        ][$req->status] ?? $req->status }}
                    </span>
                </div>

                @if($req->document)
                    <p class="text-xs font-semibold text-slate-600">
                        {{ $req->document->title }}
                    </p>
                @endif

                <p class="text-xs text-slate-500 whitespace-pre-wrap break-words">{{ $req->reason }}</p>

                @if($req->reject_reason)
                    <p class="text-xs text-rose-700 whitespace-pre-wrap break-words"><strong>Alasan penolakan:</strong> {{ $req->reject_reason }}</p>
                @endif

                @if($req->expired_at)
                    <p class="text-xs text-slate-500">
                        Berlaku sampai
                        {{ $req->expired_at->format('d M Y H:i') }}
                        ({{ config('app.timezone') }})

                        {{ $req->expired_at->isPast()
                            ? '· Kedaluwarsa'
                            : '' }}
                    </p>
                @endif

                <p class="text-xs text-slate-400">
                    Diajukan {{ $req->created_at->format('d M Y H:i') }}
                </p>
            </article>
        @empty
            <div
                class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-sm text-slate-400"
            >
                Belum ada permohonan.
            </div>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const type = document.getElementById('request-type');
    const target = document.getElementById('document-target');
    const documentSelect = document.getElementById('request-document');

    function syncDocumentTarget() {
        const specific = type.value === 'baca_dokumen';

        target.classList.toggle('hidden', !specific);

        documentSelect.disabled = !specific;
        documentSelect.required = specific;
    }

    type.addEventListener('change', syncDocumentTarget);
    syncDocumentTarget();
});
</script>
@endsection