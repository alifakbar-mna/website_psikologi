@extends('layouts.app', ['title' => 'Edit Laporan'])

@section('content')
    @php
        $formValues = array_merge(
            $document->toArray(),
            old()
        );
    @endphp

    <div class="max-w-4xl mx-auto space-y-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                {{ $manager ? 'Edit Laporan' : 'Perbaiki Laporan' }}
            </h1>

            <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                @if($manager)
                    Perbarui informasi laporan dan visibilitasnya.
                    Perubahan ini tidak mengubah keputusan review.
                @else
                    Perbaiki laporan sesuai catatan reviewer,
                    lalu kirim ulang untuk diperiksa.
                @endif
            </p>
        </div>

        @if($document->review_note)
            <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl text-sm text-amber-900">
                <strong>Catatan reviewer</strong>

                <p class="whitespace-pre-wrap break-words mt-2">{{ $document->review_note }}</p>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('reports.update', $document->id) }}"
            enctype="multipart/form-data"
            class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-7 space-y-5"
        >
            @csrf
            @method('PUT')

            <input
                type="hidden"
                name="revision"
                value="{{ $document->revision }}"
            >

            @include('cms.report-fields', [
                'prefix' => '',
                'suffix' => '',
                'values' => $formValues,
                'editing' => true,
            ])

            @if($manager)
                <label class="block text-xs font-bold text-slate-700">
                    Visibilitas

                    <select
                        name="status"
                        class="block w-full border border-slate-200 bg-slate-50 rounded-xl p-3 mt-2 text-sm focus:outline-none focus:border-sage-600"
                    >
                        <option
                            value="open"
                            @selected(old('status', $document->status) === 'open')
                        >
                            Terbuka setelah diterima
                        </option>

                        <option
                            value="hide"
                            @selected(old('status', $document->status) === 'hide')
                        >
                            Disembunyikan
                        </option>
                    </select>
                </label>
            @endif

            <div class="flex flex-wrap items-center gap-4 pt-2">
                <button
                    type="submit"
                    class="bg-sage-600 hover:bg-sage-700 text-white rounded-xl px-5 py-3 text-sm font-bold transition"
                >
                    {{ $manager ? 'Simpan Perubahan' : 'Simpan & Kirim Ulang untuk Review' }}
                </button>

                <a
                    href="{{ route($manager ? 'reports.manage' : 'reports.mine') }}"
                    class="text-sm font-semibold text-slate-500 hover:text-slate-700"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection