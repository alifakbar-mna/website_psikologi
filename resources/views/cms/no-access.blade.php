@extends('layouts.app', ['title' => 'Akses Akun'])

@section('content')
    <div class="max-w-xl mx-auto py-12">
        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 text-center space-y-5">
            <div class="w-14 h-14 rounded-2xl bg-sage-50 text-sage-600 flex items-center justify-center mx-auto">
                <i
                    data-lucide="shield-alert"
                    class="w-7 h-7"
                ></i>
            </div>

            <div>
                <h1 class="font-bold text-xl text-slate-800">
                    Belum ada akses fitur
                </h1>

                <p class="mt-3 text-sm text-slate-500 leading-relaxed">
                    Akun Anda sudah terdaftar, tetapi belum
                    memiliki izin untuk membuka fitur aplikasi.
                    Hubungi admin untuk mengatur hak akses akun.
                </p>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 text-left text-xs space-y-2">
                <p>
                    <span class="text-slate-400">Nama:</span>
                    <span class="font-semibold text-slate-700">
                        {{ auth()->user()->name }}
                    </span>
                </p>

                <p class="break-words">
                    <span class="text-slate-400">Email:</span>
                    <span class="font-semibold text-slate-700">
                        {{ auth()->user()->email }}
                    </span>
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-sage-600 hover:bg-sage-700 text-white rounded-xl text-sm font-semibold transition"
                >
                    <i
                        data-lucide="log-out"
                        class="w-4 h-4"
                    ></i>

                    Keluar dari Akun
                </button>
            </form>
        </div>
    </div>
@endsection