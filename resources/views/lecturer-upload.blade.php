@extends('layouts.app', ['title' => 'Upload Laporan'])

@section('content')
@php
    $uploadLimits = \App\Support\UploadLimits::current();
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-start gap-3">
        <div class="p-3 bg-sage-100 text-sage-700 rounded-2xl">
            <i data-lucide="files" class="w-6 h-6"></i>
        </div>

        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Upload Laporan Kasus
            </h1>

            <p class="text-xs text-slate-500 mt-2">
                Tambahkan beberapa laporan sekaligus.
                Isian disalin dari laporan terakhir;
                judul dan PDF diisi kembali.
            </p>

            @unless(auth()->user()->permits('upload_direct'))
                <p class="text-xs text-sage-700 mt-2">
                    Laporan akan diperiksa admin sebelum tampil di katalog.
                </p>
            @endunless
        </div>
    </div>

    <div
        class="bg-sage-50 border border-sage-200 rounded-2xl p-4 text-xs text-sage-800 space-y-2"
    >
        <p>
            Maksimal {{ $uploadLimits['maxFiles'] }} laporan per pengiriman.
            Batas PDF aktif:
            {{ round($uploadLimits['fileBytes'] / 1048576, 2) }}
            MB per file.
        </p>

        @if($uploadLimits['postBytes'] > 0)
            <p>
                Batas total data server:
                {{ round($uploadLimits['postBytes'] / 1048576, 2) }} MB,
                termasuk seluruh file dan isian formulir.
            </p>
        @endif

        <p id="upload-summary" aria-live="polite"></p>
    </div>

    <div
        id="upload-errors"
        role="alert"
        tabindex="-1"
        class="hidden bg-rose-50 border border-rose-200 text-rose-700 rounded-xl p-4 text-sm whitespace-pre-line"
    ></div>

    <form
        method="POST"
        action="{{ route('lecturer.upload.store') }}"
        enctype="multipart/form-data"
        id="batch-form"
        class="space-y-5"
    >
        @csrf

        <div id="reports" class="space-y-5">
            @foreach(old('reports', [[]]) as $index => $values)
                <section
                    class="report bg-white border border-slate-200 shadow-sm rounded-2xl p-5 sm:p-7 space-y-5"
                >
                    <div class="flex justify-between gap-3">
                        <h2 class="font-bold text-slate-700 report-number">
                            Laporan {{ $index + 1 }}
                        </h2>

                        <button
                            type="button"
                            class="remove-report text-xs text-rose-600"
                        >
                            Hapus laporan
                        </button>
                    </div>

                    @include('cms.report-fields', [
                        'prefix' => 'reports['.$index.'][',
                        'suffix' => ']',
                        'values' => $values,
                    ])
                </section>
            @endforeach
        </div>

        <button
            type="button"
            id="add-report"
            class="w-full border-2 border-dashed border-sage-500 text-sage-700 rounded-xl p-4 text-sm font-bold disabled:opacity-40"
        >
            + Tambah Laporan
        </button>

        <button
            type="submit"
            id="batch-submit"
            class="w-full bg-sage-600 hover:bg-sage-700 text-white rounded-xl p-4 text-sm font-bold disabled:opacity-50"
        >
            Kirim Semua Laporan
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const limits = @json($uploadLimits);

    const form = document.getElementById('batch-form');
    const reports = document.getElementById('reports');
    const add = document.getElementById('add-report');
    const submit = document.getElementById('batch-submit');
    const errors = document.getElementById('upload-errors');
    const summary = document.getElementById('upload-summary');

    const mb = bytes => (
        bytes / 1048576
    ).toLocaleString('id-ID', {
        maximumFractionDigits: 2,
    }) + ' MB';

    let sending = false;

    function checkUpload() {
        const messages = [];
        let total = 0;
        let count = 0;

        if (!limits.enabled || limits.maxFiles < 1) {
            messages.push(
                'Upload file belum diaktifkan pada server. Hubungi pengelola.'
            );
        }

        reports.querySelectorAll('.report').forEach((block, index) => {
            const input = block.querySelector('[data-field="pdf_file"]');
            const file = input.files[0];

            input.setCustomValidity('');

            if (!file) return;

            total += file.size;
            count++;

            let message = '';

            if (file.size > limits.fileBytes) {
                message =
                    `Laporan ${index + 1}: PDF ${mb(file.size)} `
                    + `melampaui batas ${mb(limits.fileBytes)}.`;
            } else if (
                !/\.pdf$/i.test(file.name)
                || (file.type && file.type !== 'application/pdf')
            ) {
                message = `Laporan ${index + 1}: pilih file PDF.`;
            } else if (file.size === 0) {
                message = `Laporan ${index + 1}: file PDF kosong.`;
            }

            if (message) {
                input.setCustomValidity(message);
                messages.push(message);
            }
        });

        // Cadangan untuk boundary multipart, nama file,
        // dan header setiap isian.
        let estimated = total + 65536;
        const encoder = new TextEncoder();

        for (const [name, value] of new FormData(form).entries()) {
            estimated += encoder.encode(name).length + 1024;

            estimated += typeof value === 'string'
                ? encoder.encode(value).length
                : encoder.encode(value.name).length;
        }

        if (reports.children.length > limits.maxFiles) {
            messages.push(
                `Maksimal ${limits.maxFiles} laporan per pengiriman.`
            );
        }

        if (
            limits.postBytes > 0
            && estimated > limits.postBytes
        ) {
            messages.push(
                `Total file ${mb(total)} beserta isian dan cadangan `
                + `pengiriman melebihi batas server ${mb(limits.postBytes)}. `
                + 'Kurangi ukuran atau jumlah PDF.'
            );
        }

        summary.textContent =
            `${count} PDF dipilih · total file ${mb(total)}.`;

        errors.textContent = messages.join('\n');
        errors.classList.toggle('hidden', messages.length === 0);

        submit.disabled = sending || messages.length > 0;

        return messages.length === 0;
    }

    function renumber() {
        reports.querySelectorAll('.report').forEach((block, index) => {
            block.querySelector('.report-number').textContent =
                'Laporan ' + (index + 1);

            block.querySelector('.remove-report')
                .classList.toggle(
                    'hidden',
                    reports.children.length === 1
                );

            block.querySelectorAll('[data-field]').forEach(input => {
                input.name =
                    `reports[${index}][${input.dataset.field}]`;
            });
        });

        add.disabled =
            reports.children.length >= limits.maxFiles
            || !limits.enabled;

        checkUpload();
    }

    add.addEventListener('click', () => {
        if (
            sending
            || reports.children.length >= limits.maxFiles
        ) {
            return;
        }

        const last = reports.lastElementChild;
        const copy = last.cloneNode(true);

        last.querySelectorAll('[data-field]').forEach(source => {
            const dest = copy.querySelector(
                `[data-field="${source.dataset.field}"]`
            );

            dest.value = ['title', 'pdf_file'].includes(
                source.dataset.field
            ) ? '' : source.value;

            dest.setCustomValidity('');
        });

        reports.append(copy);
        renumber();

        copy.querySelector('[data-field="title"]').focus();
    });

    reports.addEventListener('click', event => {
        const button = event.target.closest('.remove-report');

        if (
            button
            && !sending
            && reports.children.length > 1
        ) {
            button.closest('.report').remove();
            renumber();
        }
    });

    form.addEventListener('input', checkUpload);
    form.addEventListener('change', checkUpload);

    form.addEventListener('submit', event => {
        if (sending || !checkUpload()) {
            event.preventDefault();

            if (!sending) {
                errors.focus();
            }

            return;
        }

        sending = true;
        submit.disabled = true;
        add.disabled = true;
        submit.textContent = 'Mengunggah laporan…';
    });

    window.addEventListener('pageshow', () => {
        sending = false;
        submit.textContent = 'Kirim Semua Laporan';
        renumber();
    });

    renumber();
});
</script>
@endsection