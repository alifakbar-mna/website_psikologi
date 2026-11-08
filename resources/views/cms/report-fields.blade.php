@php
    $v = $values ?? [];
    $isEditing = $editing ?? false;

    $textFields = [
        'title' => 'Judul Laporan',
        'tahun' => 'Tahun Kasus',
        'dosen_pembimbing' => 'Dosen Pembimbing',
        'angkatan_pembuat' => 'Angkatan Pembuat',
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @foreach($textFields as $key => $label)
        @php
            $isYearField = in_array(
                $key,
                ['tahun', 'angkatan_pembuat'],
                true
            );

            $defaultValue = '';

            if ($key === 'tahun') {
                $defaultValue = date('Y');
            } elseif ($key === 'dosen_pembimbing') {
                $defaultValue = auth()->user()->name;
            }
        @endphp

        <label class="block text-xs font-semibold text-slate-700">
            {{ $label }}

            @if($key === 'angkatan_pembuat')
                <span class="font-normal text-slate-400">
                    (opsional)
                </span>
            @endif

            <input
                data-field="{{ $key }}"
                name="{{ $prefix }}{{ $key }}{{ $suffix }}"
                value="{{ $v[$key] ?? $defaultValue }}"
                type="{{ $isYearField ? 'number' : 'text' }}"

                @if($key !== 'angkatan_pembuat')
                    required
                @endif

                @if($isYearField)
                    min="1901"
                    max="{{ date('Y') }}"
                @else
                    maxlength="255"
                @endif

                class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-none focus:border-sage-600"
            >
        </label>
    @endforeach

    <label class="block text-xs font-semibold text-slate-700">
        Kategori

        <select
            data-field="category_id"
            name="{{ $prefix }}category_id{{ $suffix }}"
            required
            class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-none focus:border-sage-600"
        >
            <option value="">
                Pilih kategori
            </option>

            @foreach($categories as $category)
                <option
                    value="{{ $category->id }}"
                    @selected(($v['category_id'] ?? '') == $category->id)
                >
                    {{ $category->name }}{{ $category->is_hidden ? ' (disembunyikan)' : '' }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="block text-xs font-semibold text-slate-700">
        Jenis Kasus

        <select
            data-field="case_type"
            name="{{ $prefix }}case_type{{ $suffix }}"
            required
            class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-none focus:border-sage-600"
        >
            @foreach(['Individu', 'Kelompok'] as $type)
                <option
                    value="{{ $type }}"
                    @selected(($v['case_type'] ?? 'Individu') === $type)
                >
                    {{ $type }}
                </option>
            @endforeach
        </select>
    </label>
</div>

<label class="block text-xs font-semibold text-slate-700">
    Deskripsi Singkat Laporan Kasus

    <textarea
        data-field="description"
        name="{{ $prefix }}description{{ $suffix }}"
        rows="4"
        maxlength="20000"
        placeholder="Tuliskan ringkasan laporan kasus..."
        class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-none focus:border-sage-600"
    >{{ $v['description'] ?? '' }}</textarea>
</label>

<label class="block text-xs font-semibold text-slate-700">
    {{ $isEditing ? 'Ganti PDF (opsional)' : 'File PDF' }}

    <input
        data-field="pdf_file"
        name="{{ $prefix }}pdf_file{{ $suffix }}"
        type="file"
        accept="application/pdf,.pdf"

        @if(!$isEditing)
            required
        @endif

        class="mt-2 w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-sage-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-sage-700"
    >

    <span class="block mt-2 text-slate-500 font-normal leading-relaxed">
        Maksimal 10 MB per PDF.

        @if($isEditing)
            Kosongkan jika tetap menggunakan file sebelumnya.
        @endif
    </span>
</label>