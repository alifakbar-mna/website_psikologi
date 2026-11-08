@extends('layouts.app', ['title' => 'Persetujuan Akses'])

@section('content')
@php
    $types = [
        'baca_sistem' => 'Baca seluruh dokumen',
        'baca_dokumen' => 'Baca dokumen tertentu',
        'upload' => 'Upload laporan',
    ];
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <div class="p-3 bg-sage-100 text-sage-700 rounded-2xl">
            <i data-lucide="user-check" class="w-6 h-6"></i>
        </div>

        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Persetujuan Akses
            </h1>

            <p class="text-xs text-slate-500 mt-1">
                Tinjau permohonan baca dan upload.
                Pemeriksaan isi laporan ada di Review Upload.
            </p>
        </div>
    </div>

    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">
        Menunggu persetujuan ({{ $pendingRequests->count() }})
    </h2>

    <div class="grid md:grid-cols-2 gap-4">
        @forelse($pendingRequests as $req)
            <article
                class="bg-white p-5 rounded-2xl border border-amber-200/80 shadow-sm space-y-4"
            >
                <div class="flex items-center justify-between gap-3">
                    <span
                        class="px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-lg border border-amber-200"
                    >
                        {{ $types[$req->request_type] ?? $req->request_type }}
                    </span>

                    <time
                        class="text-xs text-slate-400"
                        title="{{ $req->created_at->format('d M Y H:i') }}"
                    >
                        {{ $req->created_at->diffForHumans() }}
                    </time>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-800">
                        {{ $req->user?->name ?? 'Akun tidak tersedia' }}
                    </h3>

                    <p class="text-xs text-slate-500 break-all">
                        {{ $req->user?->email }}
                    </p>

                    <p class="text-xs text-slate-400 mt-1">
                        Angkatan: {{ $req->user?->angkatan ?: '—' }}
                    </p>
                </div>

                <div
                    class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs space-y-2"
                >
                    <p class="text-slate-700">
                        <strong>Target:</strong>

                        {{ $req->document?->title
                            ?? ($req->request_type === 'upload'
                                ? 'Upload laporan'
                                : 'Seluruh dokumen') }}
                    </p>

                    <p class="text-slate-500 whitespace-pre-wrap break-words"><strong class="text-slate-700">Alasan:</strong> {{ $req->reason }}</p>
                </div>

                <div class="pt-3 border-t border-slate-100 flex gap-2">
                    <button
                        type="button"
                        data-decision="approved"
                        data-url="{{ route('lecturer.requests.process', $req->id) }}"
                        data-name="{{ $req->user?->name }}"
                        class="flex-1 bg-sage-600 hover:bg-sage-700 text-white font-bold py-2.5 rounded-xl text-xs"
                    >
                        Setujui
                    </button>

                    <button
                        type="button"
                        data-decision="rejected"
                        data-url="{{ route('lecturer.requests.process', $req->id) }}"
                        data-name="{{ $req->user?->name }}"
                        class="px-5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold rounded-xl text-xs"
                    >
                        Tolak
                    </button>
                </div>
            </article>
        @empty
            <div
                class="md:col-span-2 bg-white border border-slate-200 rounded-2xl p-12 text-center"
            >
                <i
                    data-lucide="check-circle"
                    class="w-10 h-10 text-sage-500 mx-auto mb-3"
                ></i>

                <h3 class="font-bold text-slate-700">
                    Semua permohonan sudah ditinjau
                </h3>

                <p class="text-xs text-slate-500 mt-2">
                    Permohonan baru akan muncul di sini.
                    Keputusan sebelumnya tercatat di Log Aktivitas.
                </p>
            </div>
        @endforelse
    </div>
</div>

<dialog
    id="access-dialog"
    class="rounded-2xl p-0 w-full max-w-md backdrop:bg-slate-900/40"
>
    <form
        id="access-decision-form"
        method="POST"
        class="p-6 space-y-4"
    >
        @csrf

        <input type="hidden" name="status" id="decision-status">

        <div class="flex items-center justify-between gap-3">
            <h2
                id="decision-heading"
                class="text-lg font-bold text-slate-800"
            ></h2>

            <button
                type="button"
                data-close-dialog
                aria-label="Tutup"
                class="p-2 rounded-lg hover:bg-slate-100"
            >
                ✕
            </button>
        </div>

        <p
            id="decision-member"
            class="text-sm text-slate-500 break-words"
        ></p>

        <div id="approval-fields" class="space-y-3 hidden">
            <p class="text-xs text-slate-500">
                Tentukan kapan izin berakhir
                ({{ config('app.timezone') }}).
            </p>

            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="text-xs font-semibold text-slate-600">
                    Masa Berlaku Akses
                </span>

                <div class="flex flex-wrap gap-1.5">
                    @foreach([
                        7 => '1 Mgg',
                        30 => '1 Bln',
                        90 => '3 Bln',
                        180 => '6 Bln',
                    ] as $days => $label)
                        <button
                            type="button"
                            data-days="{{ $days }}"
                            class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <label class="text-xs font-bold text-slate-600">
                    Tanggal berakhir

                    <input
                        type="date"
                        name="expired_date"
                        id="decision-date"
                        class="block mt-2 border rounded-xl p-3 w-full text-sm"
                    >
                </label>

                <label class="text-xs font-bold text-slate-600">
                    Jam berakhir

                    <input
                        type="time"
                        name="expired_time"
                        id="decision-time"
                        class="block mt-2 border rounded-xl p-3 w-full text-sm"
                    >
                </label>
            </div>
        </div>

        <div id="rejection-fields" class="hidden">
            <label
                class="block text-xs font-bold text-slate-600"
                for="decision-reason"
            >
                Alasan penolakan
            </label>

            <textarea
                name="reject_reason"
                id="decision-reason"
                maxlength="1000"
                rows="4"
                class="mt-2 border rounded-xl p-3 w-full text-sm"
                placeholder="Jelaskan alasan penolakan kepada pemohon."
            ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
            <button
                type="button"
                data-close-dialog
                class="px-4 py-2.5 text-sm text-slate-500"
            >
                Batal
            </button>

            <button
                id="decision-submit"
                class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-sage-600"
            >
                Simpan keputusan
            </button>
        </div>
    </form>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('access-dialog');
    const form = document.getElementById('access-decision-form');
    const date = document.getElementById('decision-date');
    const time = document.getElementById('decision-time');
    const reason = document.getElementById('decision-reason');

    const serverNow = @json(now()->toIso8601String());
    const loadedAt = Date.now();
    const timezone = @json(config('app.timezone'));

    function preset(days) {
        const target = new Date(
            new Date(serverNow).getTime()
            + Date.now()
            - loadedAt
            + days * 86400000
        );

        const parts = new Intl.DateTimeFormat('en-GB', {
            timeZone: timezone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23',
        }).formatToParts(target);

        const values = Object.fromEntries(
            parts.map(part => [part.type, part.value])
        );

        date.value = `${values.year}-${values.month}-${values.day}`;
        time.value = `${values.hour}:${values.minute}`;
    }

    document.querySelectorAll('[data-decision]').forEach(button => {
        button.addEventListener('click', () => {
            form.reset();
            form.action = button.dataset.url;

            const approved = button.dataset.decision === 'approved';

            document.getElementById('decision-status').value =
                button.dataset.decision;

            document.getElementById('decision-heading').textContent =
                approved ? 'Setujui permohonan' : 'Tolak permohonan';

            document.getElementById('decision-member').textContent =
                button.dataset.name;

            document.getElementById('approval-fields')
                .classList.toggle('hidden', !approved);

            document.getElementById('rejection-fields')
                .classList.toggle('hidden', approved);

            [date, time].forEach(input => {
                input.disabled = !approved;
                input.required = approved;
            });

            reason.disabled = approved;
            reason.required = !approved;

            preset(3);
            dialog.showModal();
        });
    });

    document.querySelectorAll('[data-days]').forEach(button => {
        button.addEventListener('click', () => {
            preset(Number(button.dataset.days));
        });
    });

    dialog.querySelectorAll('[data-close-dialog]').forEach(button => {
        button.addEventListener('click', () => dialog.close());
    });
});
</script>
@endsection