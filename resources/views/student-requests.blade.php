@extends('layouts.app', ['title' => 'Status Permohonan - Mahasiswa'])

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                Status Permohonan Akses
            </h1>

            <p class="text-xs text-slate-500 mt-1">
                Pantau status izin baca, download, atau upload laporan kasus Anda.
            </p>
        </div>

        {{-- Modal Trigger --}}
        <button
            onclick="openRequestModal()"
            class="bg-sage-600 hover:bg-sage-700 text-white font-medium px-5 py-2.5 rounded-xl text-xs flex items-center gap-2 transition shadow-sm self-start sm:self-auto"
        >
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Buat Permohonan Baru</span>
        </button>
    </div>


    {{-- =========================================================
         SUCCESS MESSAGE
    ========================================================== --}}
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs p-4 rounded-xl flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>

            <span>{{ session('success') }}</span>
        </div>
    @endif


    {{-- =========================================================
         ERROR MESSAGE
    ========================================================== --}}
    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs p-4 rounded-xl">
            <div class="flex items-start gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>

                <div>
                    <p class="font-semibold mb-1">
                        Permohonan tidak dapat dikirim.
                    </p>

                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif


    {{-- =========================================================
         REQUEST TABLE LIST
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-left border-collapse">

                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200/80">

                        <th class="p-4">
                            Tipe Request
                        </th>

                        <th class="p-4">
                            Target Dokumen
                        </th>

                        <th class="p-4">
                            Alasan Mahasiswa
                        </th>

                        <th class="p-4">
                            Status
                        </th>

                        <th class="p-4">
                            Masa Berlaku
                        </th>

                        <th class="p-4">
                            Catatan / Alasan Ditolak
                        </th>

                        <th class="p-4">
                            Tanggal Submit
                        </th>

                    </tr>
                </thead>


                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">

                    @forelse($requests as $req)

                        <tr class="hover:bg-slate-50/50 transition">

                            {{-- =================================================
                                 REQUEST TYPE
                            ================================================== --}}
                            <td class="p-4 font-semibold capitalize">

                                <span class="px-2.5 py-1 rounded-lg text-[10px] bg-slate-100 text-slate-700">

                                    @if($req->request_type === 'baca_sistem')
                                        Izin Baca / Akses Sistem

                                    @elseif($req->request_type === 'download')
                                        Download PDF

                                    @elseif($req->request_type === 'upload')
                                        Upload Dokumen

                                    @else
                                        {{ str_replace('_', ' ', $req->request_type) }}
                                    @endif

                                </span>

                            </td>


                            {{-- =================================================
                                 TARGET DOCUMENT
                            ================================================== --}}
                            <td class="p-4 max-w-[200px]">

                                @if($req->document)

                                    <div class="truncate" title="{{ $req->document->title }}">
                                        {{ $req->document->title }}
                                    </div>

                                @else

                                    <span class="text-slate-400">
                                        Akses Global Sistem
                                    </span>

                                @endif

                            </td>


                            {{-- =================================================
                                 REASON
                            ================================================== --}}
                            <td class="p-4 max-w-[250px] text-slate-500 italic">

                                <div
                                    class="truncate"
                                    title="{{ $req->reason }}"
                                >
                                    "{{ $req->reason }}"
                                </div>

                            </td>


                            {{-- =================================================
                                 STATUS
                            ================================================== --}}
                            <td class="p-4">

                                @if($req->status === 'pending')

                                    <span class="px-2.5 py-1 text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-full">
                                        Pending
                                    </span>

                                @elseif($req->status === 'approved')

                                    @if($req->expired_at && $req->expired_at->isPast())

                                        <span class="px-2.5 py-1 text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-300 rounded-full">
                                            Kedaluwarsa
                                        </span>

                                    @else

                                        <span class="px-2.5 py-1 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
                                            Disetujui
                                        </span>

                                    @endif

                                @else

                                    <span class="px-2.5 py-1 text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 rounded-full">
                                        Ditolak
                                    </span>

                                @endif

                            </td>


                            {{-- =================================================
                                 EXPIRATION
                            ================================================== --}}
                            <td class="p-4 text-[11px]">

                                @if($req->status === 'approved' && $req->expired_at)

                                    @if($req->expired_at->isFuture())

                                        <div class="text-emerald-700 font-medium">
                                            s.d. {{ $req->expired_at->format('d M Y, H:i') }}
                                        </div>

                                        <div class="text-[10px] text-slate-400">
                                            ({{ $req->expired_at->diffForHumans() }})
                                        </div>

                                    @else

                                        <div class="text-rose-600 font-medium line-through">
                                            {{ $req->expired_at->format('d M Y, H:i') }}
                                        </div>

                                        <div class="text-[10px] text-rose-500 font-semibold">
                                            Akses Berakhir
                                        </div>

                                    @endif

                                @elseif($req->status === 'approved')

                                    <span class="text-slate-500 font-medium">
                                        Permanen
                                    </span>

                                @else

                                    <span class="text-slate-400">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- =================================================
                                 REJECT REASON
                            ================================================== --}}
                            <td class="p-4 text-slate-500 max-w-[250px]">

                                @if($req->reject_reason)

                                    <div
                                        class="truncate"
                                        title="{{ $req->reject_reason }}"
                                    >
                                        {{ $req->reject_reason }}
                                    </div>

                                @else

                                    <span class="text-slate-400">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- =================================================
                                 CREATED AT
                            ================================================== --}}
                            <td class="p-4 text-slate-400 text-[11px] whitespace-nowrap">

                                {{ $req->created_at->format('d M Y, H:i') }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="p-8 text-center text-slate-400"
                            >
                                Belum ada riwayat permohonan akses.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- =========================================================
     REQUEST MODAL
========================================================= --}}
<div
    id="request-modal"
    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden"
>

    <div
        class="bg-white max-w-md w-full rounded-2xl p-6 space-y-5 shadow-xl border border-slate-100"
    >

        {{-- =====================================================
             MODAL HEADER
        ====================================================== --}}
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">

            <h3 class="text-sm font-bold text-slate-800">
                Form Pengajuan Permohonan Akses
            </h3>

            <button
                type="button"
                onclick="closeRequestModal()"
                class="text-slate-400 hover:text-slate-600"
            >
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

        </div>


        {{-- =====================================================
             FORM
        ====================================================== --}}
        <form
            action="{{ route('student.requests.store') }}"
            method="POST"
            id="request-form"
            class="space-y-4"
        >

            @csrf


            {{-- =================================================
                 REQUEST TYPE
            ================================================== --}}
            <div>

                <label
                    for="request_type"
                    class="block text-xs font-semibold text-slate-600 mb-1"
                >
                    Jenis Akses
                </label>

                <select
                    name="request_type"
                    id="request_type"
                    required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600"
                >

                    <option value="baca_sistem">
                        Izin Baca / Akses Sistem
                    </option>

                    <option value="download">
                        Izin Download File PDF Spesifik
                    </option>

                    <option value="upload">
                        Izin Upload Dokumen Baru
                    </option>

                </select>

            </div>


            {{-- =================================================
                 DOCUMENT
            ================================================== --}}
            <div id="document-field" class="hidden">

                <label
                    for="document_id"
                    class="block text-xs font-semibold text-slate-600 mb-1"
                >
                    Target Dokumen
                    <span class="text-rose-500">*</span>
                </label>

                <select
                    name="document_id"
                    id="document_id"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600"
                >

                    <option value="">
                        -- Pilih Dokumen --
                    </option>

                    @foreach($documents as $doc)

                        <option value="{{ $doc->id }}">
                            {{ $doc->title }}
                        </option>

                    @endforeach

                </select>

                <p class="text-[10px] text-slate-400 mt-1">
                    Dokumen wajib dipilih untuk permohonan download.
                </p>

            </div>


            {{-- =================================================
                 REASON
            ================================================== --}}
            <div>

                <label
                    for="reason"
                    class="block text-xs font-semibold text-slate-600 mb-1"
                >
                    Alasan Pengajuan
                </label>

                <textarea
                    name="reason"
                    id="reason"
                    rows="3"
                    maxlength="500"
                    required
                    placeholder="Jelaskan kebutuhan studi / referensi Anda..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600"
                >{{ old('reason') }}</textarea>

                <div class="text-[10px] text-slate-400 mt-1">
                    Maksimal 500 karakter.
                </div>

            </div>


            {{-- =================================================
                 BUTTON
            ================================================== --}}
            <div class="pt-2 flex justify-end gap-2">

                <button
                    type="button"
                    onclick="closeRequestModal()"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="px-5 py-2.5 bg-sage-600 hover:bg-sage-700 text-white rounded-xl text-xs font-bold transition shadow-sm"
                >
                    Kirim Permohonan
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =========================================================
     JAVASCRIPT
========================================================= --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('request-modal');

    const requestType =
        document.getElementById('request_type');

    const documentField =
        document.getElementById('document-field');

    const documentSelect =
        document.getElementById('document_id');


    /*
    |--------------------------------------------------------------------------
    | UPDATE DOCUMENT FIELD
    |--------------------------------------------------------------------------
    */

    function updateDocumentField() {

        const isDownload =
            requestType.value === 'download';


        if (isDownload) {

            // Tampilkan field
            documentField.classList.remove('hidden');

            // Wajib memilih dokumen
            documentSelect.required = true;

        } else {

            // Sembunyikan field
            documentField.classList.add('hidden');

            // Tidak wajib
            documentSelect.required = false;

            // Hapus pilihan
            documentSelect.value = '';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CHANGE REQUEST TYPE
    |--------------------------------------------------------------------------
    */

    requestType.addEventListener(
        'change',
        updateDocumentField
    );


    /*
    |--------------------------------------------------------------------------
    | INITIAL STATE
    |--------------------------------------------------------------------------
    */

    updateDocumentField();


    /*
    |--------------------------------------------------------------------------
    | CLOSE MODAL WHEN CLICKING BACKDROP
    |--------------------------------------------------------------------------
    */

    modal.addEventListener('click', function (event) {

        if (event.target === modal) {
            closeRequestModal();
        }

    });


    /*
    |--------------------------------------------------------------------------
    | ESC TO CLOSE
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {
            closeRequestModal();
        }

    });

});


/*
|--------------------------------------------------------------------------
| OPEN MODAL
|--------------------------------------------------------------------------
*/

function openRequestModal() {

    const modal =
        document.getElementById('request-modal');

    modal.classList.remove('hidden');

    const requestType =
        document.getElementById('request_type');

    const documentField =
        document.getElementById('document-field');

    const documentSelect =
        document.getElementById('document_id');


    if (requestType.value === 'download') {

        documentField.classList.remove('hidden');

        documentSelect.required = true;

    } else {

        documentField.classList.add('hidden');

        documentSelect.required = false;

    }

}


/*
|--------------------------------------------------------------------------
| CLOSE MODAL
|--------------------------------------------------------------------------
*/

function closeRequestModal() {

    document
        .getElementById('request-modal')
        .classList.add('hidden');

}

</script>

@endsection