@extends('layouts.app', ['title' => 'Preview Import Anggota'])

@section('content')
    @php
        $errorCount = collect($items)
            ->filter(function ($item) {
                return !empty($item['errors']);
            })
            ->count();

        $validCount = count($items) - $errorCount;
    @endphp

    <div class="max-w-6xl mx-auto space-y-5">
        <div>
            <h1 class="font-bold text-2xl text-slate-800">
                Preview Import Anggota
            </h1>

            <p class="text-sm text-slate-500 mt-2">
                Periksa data sebelum menyimpannya.
                Pada tahap ini belum ada anggota yang ditambahkan.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="bg-white border border-slate-200 rounded-xl p-4">
                <p class="text-xs text-slate-500">
                    Total Baris Anggota
                </p>

                <p class="text-2xl font-bold text-slate-800 mt-2">
                    {{ count($items) }}
                </p>
            </div>

            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                <p class="text-xs text-emerald-700">
                    Baris Valid
                </p>

                <p class="text-2xl font-bold text-emerald-800 mt-2">
                    {{ $validCount }}
                </p>
            </div>

            <div class="bg-rose-50 border border-rose-100 rounded-xl p-4">
                <p class="text-xs text-rose-700">
                    Baris Perlu Diperbaiki
                </p>

                <p class="text-2xl font-bold text-rose-800 mt-2">
                    {{ $errorCount }}
                </p>
            </div>
        </div>

        @if($hasErrors)
            <div
                role="alert"
                class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-900 leading-relaxed"
            >
                Perbaiki semua kesalahan pada file Excel,
                lalu unggah ulang melalui Kelola Pengguna.
                Belum ada data yang disimpan.
            </div>
        @else
            <div
                role="status"
                class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-sm text-emerald-800 leading-relaxed"
            >
                Semua baris valid. Konfirmasi untuk menyimpan
                seluruh anggota. Preview berlaku selama 30 menit.
            </div>
        @endif

        <div class="overflow-x-auto bg-white border border-slate-200 rounded-2xl">
            <table class="w-full text-sm text-left">
                <thead class="bg-sage-50 text-sage-800">
                    <tr>
                        <th scope="col" class="p-3">
                            Baris Excel
                        </th>

                        <th scope="col" class="p-3">
                            Nama Lengkap
                        </th>

                        <th scope="col" class="p-3">
                            Email Google
                        </th>

                        <th scope="col" class="p-3">
                            Role
                        </th>

                        <th scope="col" class="p-3">
                            Angkatan
                        </th>

                        <th scope="col" class="p-3">
                            Hasil Pemeriksaan
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($items as $item)
                        <tr class="border-t border-slate-100 align-top">
                            <td class="p-3 text-slate-500">
                                {{ $item['line'] }}
                            </td>

                            <td class="p-3 text-slate-700">
                                {{ $item['data']['name'] }}
                            </td>

                            <td class="p-3 text-slate-700 break-words">
                                {{ $item['data']['email'] }}
                            </td>

                            <td class="p-3 text-slate-700">
                                {{ config('cms.roles.'.$item['data']['role'], $item['data']['role']) }}
                            </td>

                            <td class="p-3 text-slate-700">
                                {{ $item['data']['angkatan'] ?? '-' }}
                            </td>

                            <td class="p-3">
                                @if($item['errors'])
                                    <ul class="list-disc pl-4 space-y-1 text-xs text-rose-600 min-w-[200px]">
                                        @foreach($item['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="inline-block rounded-lg px-2 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold">
                                        Valid
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            @if(!$hasErrors)
                <form
                    method="POST"
                    action="{{ route('members.import') }}"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="token"
                        value="{{ $token }}"
                    >

                    <button
                        type="submit"
                        class="bg-sage-600 hover:bg-sage-700 text-white px-5 py-3 rounded-xl text-sm font-semibold transition"
                    >
                        Konfirmasi Import {{ count($items) }} Anggota
                    </button>
                </form>
            @endif

            <a
                href="{{ route('admin.users') }}"
                class="text-sm font-semibold text-sage-700 hover:underline"
            >
                {{ $hasErrors ? 'Kembali & Unggah Ulang' : 'Batal / Kembali' }}
            </a>
        </div>
    </div>
@endsection