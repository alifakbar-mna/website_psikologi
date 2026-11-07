@extends('layouts.app', ['title' => 'Kelola Kategori - Dosen'])

@section('content')
<div class="space-y-6">

    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                Kelola Kategori Laporan
            </h1>

            <p class="text-xs text-slate-500 mt-1">
                Atur kategori dokumen/laporan kasus psikologi untuk memudahkan pengelompokan.
            </p>
        </div>
    </div>


    <!-- Alert Flash Message -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold rounded-xl flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif


    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold rounded-xl flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif


    <!-- Validation Error -->
    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
            <div class="flex items-start gap-2">

                <i
                    data-lucide="alert-circle"
                    class="w-4 h-4 mt-0.5 flex-shrink-0"
                ></i>

                <div>

                    <p class="font-semibold mb-1">
                        Terjadi kesalahan:
                    </p>

                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>

            </div>
        </div>
    @endif


    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">


        <!-- ===================================================== -->
        <!-- FORM TAMBAH KATEGORI -->
        <!-- ===================================================== -->

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit">

            <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">

                <i
                    data-lucide="folder-plus"
                    class="w-4 h-4 text-sage-600"
                ></i>

                Tambah Kategori Baru

            </h2>


            <form
                action="{{ route('lecturer.categories.store') }}"
                method="POST"
                class="space-y-4"
            >

                @csrf


                <!-- Nama Kategori -->
                <div>

                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Kategori
                    </label>

                    <input
                        type="text"
                        name="name"
                        required
                        placeholder="Contoh: Psikologi Klinis Anak"
                        value="{{ old('name') }}"
                        class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sage-500"
                    >

                    @error('name')
                        <span class="text-[10px] text-rose-500 mt-1 block">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                <!-- Submit -->
                <button
                    type="submit"
                    class="w-full py-2.5 bg-sage-600 hover:bg-sage-700 text-white font-semibold text-xs rounded-xl transition shadow-sm flex items-center justify-center gap-2"
                >

                    <i
                        data-lucide="plus"
                        class="w-4 h-4"
                    ></i>

                    <span>
                        Simpan Kategori
                    </span>

                </button>

            </form>

        </div>


        <!-- ===================================================== -->
        <!-- TABEL KATEGORI -->
        <!-- ===================================================== -->

        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">


            <!-- Table Header -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">

                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">

                    <i
                        data-lucide="folder-tree"
                        class="w-4 h-4 text-sage-600"
                    ></i>

                    Daftar Kategori

                </h2>


                <span class="text-xs text-slate-400">
                    Total: {{ $categories->count() }} Kategori
                </span>

            </div>


            <!-- Table -->
            <div class="overflow-x-auto">

                <table class="w-full text-left text-xs text-slate-600">

                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-bold text-[10px] tracking-wider">

                        <tr>

                            <th class="p-4">
                                Nama Kategori
                            </th>

                            <th class="p-4">
                                Jumlah Dokumen
                            </th>

                            <th class="p-4">
                                Visibilitas
                            </th>

                            <th class="p-4 text-center">
                                Edit
                            </th>

                            <th class="p-4 text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @forelse($categories as $category)

                            <tr class="hover:bg-slate-50/50 transition">


                                <!-- Nama Kategori -->
                                <td class="p-4">

                                    <p class="font-bold text-slate-800">
                                        {{ $category->name }}
                                    </p>

                                </td>


                                <!-- Jumlah Dokumen -->
                                <td class="p-4">

                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 font-semibold rounded-lg text-[10px]">
                                        {{ $category->documents_count ?? 0 }} Dokumen
                                    </span>

                                </td>


                                <!-- Visibilitas -->
                                <td class="p-4">

                                    @if($category->is_hidden)

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-[10px] font-semibold border border-amber-200">

                                            <i
                                                data-lucide="eye-off"
                                                class="w-3 h-3"
                                            ></i>

                                            Disembunyikan

                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[10px] font-semibold border border-emerald-200">

                                            <i
                                                data-lucide="eye"
                                                class="w-3 h-3"
                                            ></i>

                                            Tampil

                                        </span>

                                    @endif

                                </td>


                                <!-- ================================================= -->
                                <!-- EDIT -->
                                <!-- ================================================= -->

                                <td class="p-4">

                                    <div class="flex items-center justify-center">

                                        <button
                                            type="button"
                                            onclick="openEditCategoryModal(
                                                '{{ $category->id }}',
                                                @js($category->name)
                                            )"
                                            class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition"
                                            title="Edit Kategori"
                                        >

                                            <i
                                                data-lucide="pencil"
                                                class="w-4 h-4"
                                            ></i>

                                        </button>

                                    </div>

                                </td>


                                <!-- ================================================= -->
                                <!-- AKSI -->
                                <!-- ================================================= -->

                                <td class="p-4">

                                    <div class="flex items-center justify-center gap-2">


                                        <!-- Toggle Hide / Show -->
                                        <form
                                            action="{{ route('lecturer.categories.toggle', $category->id) }}"
                                            method="POST"
                                        >

                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition"
                                                title="{{ $category->is_hidden ? 'Tampilkan' : 'Sembunyikan' }}"
                                            >

                                                <i
                                                    data-lucide="{{ $category->is_hidden ? 'eye' : 'eye-off' }}"
                                                    class="w-4 h-4"
                                                ></i>

                                            </button>

                                        </form>


                                        <!-- Delete -->
                                        <form
                                            action="{{ route('lecturer.categories.destroy', $category->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                                title="Hapus"
                                            >

                                                <i
                                                    data-lucide="trash-2"
                                                    class="w-4 h-4"
                                                ></i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    class="p-8 text-center text-slate-400"
                                >

                                    <i
                                        data-lucide="folder-x"
                                        class="w-8 h-8 mx-auto mb-2 text-slate-300"
                                    ></i>

                                    <span>
                                        Belum ada kategori yang ditambahkan.
                                    </span>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<!-- ========================================================= -->
<!-- MODAL EDIT KATEGORI -->
<!-- ========================================================= -->

<div
    id="edit-category-modal"
    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden"
>

    <div
        class="bg-white max-w-md w-full rounded-2xl p-6 space-y-5 shadow-xl border border-slate-100"
    >


        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">

            <div>

                <h3 class="text-sm font-bold text-slate-800">
                    Edit Kategori
                </h3>

                <p class="text-[10px] text-slate-400 mt-0.5">
                    Perbarui nama kategori laporan
                </p>

            </div>


            <button
                type="button"
                onclick="closeEditCategoryModal()"
                class="text-slate-400 hover:text-slate-600"
            >

                <i
                    data-lucide="x"
                    class="w-4 h-4"
                ></i>

            </button>

        </div>


        <!-- Form Edit -->
        <form
            id="edit-category-form"
            method="POST"
            class="space-y-4"
        >

            @csrf

            @method('PUT')


            <!-- Nama Kategori -->
            <div>

                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Nama Kategori
                </label>

                <input
                    type="text"
                    id="edit-category-name"
                    name="name"
                    required
                    placeholder="Nama kategori"
                    class="w-full px-3 py-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sage-500"
                >

            </div>


            <!-- Buttons -->
            <div class="pt-2 flex justify-end gap-2">

                <button
                    type="button"
                    onclick="closeEditCategoryModal()"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50"
                >
                    Batal
                </button>


                <button
                    type="submit"
                    class="px-5 py-2.5 bg-sage-600 hover:bg-sage-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2"
                >

                    <i
                        data-lucide="save"
                        class="w-4 h-4"
                    ></i>

                    Simpan Perubahan

                </button>

            </div>

        </form>

    </div>

</div>


<!-- ========================================================= -->
<!-- JAVASCRIPT -->
<!-- ========================================================= -->

<script>

    /*
    |--------------------------------------------------------------------------
    | Open Edit Modal
    |--------------------------------------------------------------------------
    */

    function openEditCategoryModal(id, name) {

        const modal =
            document.getElementById('edit-category-modal');

        const form =
            document.getElementById('edit-category-form');

        const nameInput =
            document.getElementById('edit-category-name');


        /*
        |--------------------------------------------------------------------------
        | Set Form Action
        |--------------------------------------------------------------------------
        |
        | PUT /lecturer/categories/{id}
        |
        */

        form.action = `/lecturer/categories/${id}`;


        /*
        |--------------------------------------------------------------------------
        | Isi Nama
        |--------------------------------------------------------------------------
        */

        nameInput.value = name || '';


        /*
        |--------------------------------------------------------------------------
        | Tampilkan Modal
        |--------------------------------------------------------------------------
        */

        modal.classList.remove('hidden');


        /*
        |--------------------------------------------------------------------------
        | Refresh Lucide Icons
        |--------------------------------------------------------------------------
        */

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

    }


    /*
    |--------------------------------------------------------------------------
    | Close Edit Modal
    |--------------------------------------------------------------------------
    */

    function closeEditCategoryModal() {

        const modal =
            document.getElementById('edit-category-modal');

        modal.classList.add('hidden');

    }


    /*
    |--------------------------------------------------------------------------
    | Click Outside Modal
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function () {

        const modal =
            document.getElementById('edit-category-modal');


        if (modal) {

            modal.addEventListener('click', function (event) {

                if (event.target === modal) {

                    closeEditCategoryModal();

                }

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Escape Key
        |--------------------------------------------------------------------------
        */

        document.addEventListener('keydown', function (event) {

            if (event.key === 'Escape') {

                closeEditCategoryModal();

            }

        });

    });

</script>

@endsection