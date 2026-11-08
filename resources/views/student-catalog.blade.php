@if(request()->ajax())
    @include('cms.catalog-results')
@else
    @extends('layouts.app', ['title' => 'Katalog Dokumen'])

    @section('content')
        <div class="max-w-7xl mx-auto space-y-6">
            <section class="bg-white p-5 sm:p-7 rounded-2xl border border-slate-200 space-y-5">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">
                        Katalog Laporan Kasus
                    </h1>

                    <p class="text-sm text-slate-500 mt-2">
                        Cari referensi laporan kasus berdasarkan
                        judul dan informasi laporan.
                    </p>
                </div>

                <form
                    id="filterForm"
                    action="{{ route('student.catalog') }}"
                    method="GET"
                    class="space-y-4"
                >
                    <label class="block text-xs font-semibold text-slate-600">
                        Cari Judul Laporan

                        <input
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Masukkan judul atau kata kunci..."
                            class="auto-filter mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-none focus:border-sage-600"
                        >
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <label class="block text-xs font-semibold text-slate-600">
                            Kategori

                            <select
                                name="category_id"
                                class="auto-filter mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm"
                            >
                                <option value="">
                                    Semua Kategori
                                </option>

                                @foreach($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        @selected(request('category_id') == $category->id)
                                    >
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block text-xs font-semibold text-slate-600">
                            Tahun Kasus

                            <input
                                type="number"
                                name="tahun"
                                value="{{ request('tahun') }}"
                                placeholder="Contoh: 2024"
                                min="1901"
                                max="{{ date('Y') }}"
                                class="auto-filter mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm"
                            >
                        </label>

                        <label class="block text-xs font-semibold text-slate-600">
                            Dosen Pembimbing

                            <input
                                type="text"
                                name="dosen"
                                value="{{ request('dosen') }}"
                                placeholder="Nama pembimbing"
                                class="auto-filter mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm"
                            >
                        </label>

                        <label class="block text-xs font-semibold text-slate-600">
                            Angkatan Pembuat

                            <input
                                type="number"
                                name="angkatan"
                                value="{{ request('angkatan') }}"
                                placeholder="Contoh: 2023"
                                min="1901"
                                max="{{ date('Y') }}"
                                class="auto-filter mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm"
                            >
                        </label>

                        <label class="block text-xs font-semibold text-slate-600">
                            Jenis Kasus

                            <select
                                name="case_type"
                                class="auto-filter mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm"
                            >
                                <option value="">
                                    Semua Jenis
                                </option>

                                <option
                                    value="Individu"
                                    @selected(request('case_type') === 'Individu')
                                >
                                    Individu
                                </option>

                                <option
                                    value="Kelompok"
                                    @selected(request('case_type') === 'Kelompok')
                                >
                                    Kelompok
                                </option>
                            </select>
                        </label>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <button
                            type="submit"
                            class="bg-sage-600 hover:bg-sage-700 text-white px-5 py-2.5 rounded-xl text-xs font-semibold"
                        >
                            Cari
                        </button>

                        <button
                            type="button"
                            id="resetBtn"
                            class="text-xs text-rose-600 hover:underline"
                        >
                            Reset Filter
                        </button>
                    </div>
                </form>
            </section>

            <p
                id="catalogStatus"
                role="status"
                aria-live="polite"
                class="text-sm text-slate-500"
            ></p>

            <div id="catalogContent">
                @include('cms.catalog-results')
            </div>
        </div>

        @if(auth()->user()->permits('request_access'))
            <dialog
                id="requestModal"
                aria-labelledby="requestModalTitle"
                class="rounded-2xl p-6 w-[calc(100%-2rem)] max-w-md shadow-xl backdrop:bg-slate-900/50"
            >
                <div class="flex items-center justify-between gap-3 mb-5">
                    <h2
                        id="requestModalTitle"
                        class="text-base font-bold text-slate-800"
                    >
                        Permohonan Izin Baca
                    </h2>

                    <button
                        type="button"
                        data-close-request
                        class="text-xs text-slate-500"
                    >
                        Tutup
                    </button>
                </div>

                <form
                    action="{{ route('student.requests.store') }}"
                    method="POST"
                    class="space-y-4"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="request_type"
                        value="baca_dokumen"
                    >

                    <input
                        type="hidden"
                        name="document_id"
                        id="modalDocumentId"
                    >

                    <label class="block text-xs font-semibold text-slate-600">
                        Dokumen Target

                        <input
                            type="text"
                            id="modalDocumentTitle"
                            readonly
                            class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm"
                        >
                    </label>

                    <label class="block text-xs font-semibold text-slate-600">
                        Alasan Permohonan

                        <textarea
                            name="reason"
                            rows="4"
                            maxlength="500"
                            required
                            placeholder="Tuliskan alasan ingin membaca laporan ini..."
                            class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm"
                        ></textarea>
                    </label>

                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            data-close-request
                            class="px-4 py-2 rounded-xl bg-slate-100 text-xs text-slate-600"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="px-4 py-2 rounded-xl bg-sage-600 text-xs font-semibold text-white"
                        >
                            Kirim Permohonan
                        </button>
                    </div>
                </form>
            </dialog>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('filterForm');
                const content = document.getElementById('catalogContent');
                const status = document.getElementById('catalogStatus');
                const dialog = document.getElementById('requestModal');

                let timer = null;
                let activeRequest = null;

                function filterUrl() {
                    const url = new URL(form.action);

                    url.search = new URLSearchParams(
                        new FormData(form)
                    ).toString();

                    return url;
                }

                async function loadDocuments(url) {
                    if (activeRequest) {
                        activeRequest.abort();
                    }

                    const controller = new AbortController();

                    activeRequest = controller;

                    content.setAttribute('aria-busy', 'true');
                    status.textContent = 'Memuat dokumen…';

                    try {
                        const response = await fetch(url.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },

                            signal: controller.signal,
                        });

                        if (response.redirected) {
                            window.location.assign(response.url);
                            return;
                        }

                        if (!response.ok) {
                            throw new Error('Request gagal');
                        }

                        const html = await response.text();

                        if (controller.signal.aborted) {
                            return;
                        }

                        content.innerHTML = html;

                        window.history.replaceState(
                            {},
                            '',
                            url.toString()
                        );

                        window.lucide?.createIcons();

                        if (
                            typeof updateDescriptionButtons
                            === 'function'
                        ) {
                            requestAnimationFrame(
                                updateDescriptionButtons
                            );
                        }

                        status.textContent = '';
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            status.textContent =
                                'Dokumen gagal dimuat. '
                                + 'Klik Cari untuk mencoba lagi.';
                        }
                    } finally {
                        if (activeRequest === controller) {
                            content.removeAttribute('aria-busy');
                            activeRequest = null;
                        }
                    }
                }

                function search() {
                    clearTimeout(timer);
                    loadDocuments(filterUrl());
                }

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    search();
                });

                form.querySelectorAll('.auto-filter').forEach(
                    function (input) {
                        const eventType = input.tagName === 'SELECT'
                            ? 'change'
                            : 'input';

                        input.addEventListener(
                            eventType,
                            function () {
                                clearTimeout(timer);
                                timer = setTimeout(search, 300);
                            }
                        );
                    }
                );

                document.getElementById('resetBtn').addEventListener(
                    'click',
                    function () {
                        form.querySelectorAll('.auto-filter').forEach(
                            function (input) {
                                input.value = '';
                            }
                        );

                        search();
                    }
                );

                content.addEventListener('click', function (event) {
                    const page = event.target.closest(
                        '.pagination a'
                    );

                    if (page) {
                        if (
                            event.button !== 0
                            || event.ctrlKey
                            || event.metaKey
                            || event.shiftKey
                            || event.altKey
                        ) {
                            return;
                        }

                        event.preventDefault();

                        clearTimeout(timer);

                        loadDocuments(new URL(page.href));

                        return;
                    }

                    const button = event.target.closest(
                        '[data-request-id]'
                    );

                    if (button && dialog) {
                        document.getElementById(
                            'modalDocumentId'
                        ).value = button.dataset.requestId;

                        document.getElementById(
                            'modalDocumentTitle'
                        ).value = button.dataset.requestTitle;

                        dialog.querySelector('textarea').value = '';

                        dialog.showModal();
                    }
                });

                document.querySelectorAll(
                    '[data-close-request]'
                ).forEach(function (button) {
                    button.addEventListener('click', function () {
                        dialog?.close();
                    });
                });
            });
        </script>
    @endsection
@endif