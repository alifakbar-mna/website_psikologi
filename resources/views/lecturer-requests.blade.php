@extends('layouts.app', ['title' => 'Persetujuan Akses - Dosen'])

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
            Persetujuan Akses Mahasiswa
        </h1>

        <p class="text-xs text-slate-500 mt-1">
            Tinjau permohonan baca, download, atau upload laporan kasus dari mahasiswa.
        </p>
    </div>


    {{-- SUCCESS --}}
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs p-4 rounded-xl flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>

            <span>
                {{ session('success') }}
            </span>
        </div>
    @endif


    {{-- ERRORS --}}
    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs p-4 rounded-xl">
            @foreach ($errors->all() as $error)
                <div>
                    {{ $error }}
                </div>
            @endforeach
        </div>
    @endif


    {{-- =========================================================
         PENDING REQUESTS
    ========================================================== --}}

    <div class="space-y-4">

        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">
            Permohonan Menunggu Persetujuan
            ({{ $pendingRequests->count() }})
        </h2>


        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            @forelse($pendingRequests as $req)

                <div
                    class="bg-white p-5 rounded-2xl
                           border border-amber-200/80
                           shadow-sm space-y-4"
                >

                    {{-- HEADER --}}
                    <div class="flex items-center justify-between">

                        <span
                            class="px-2.5 py-1
                                   bg-amber-50
                                   text-amber-700
                                   text-[10px]
                                   font-bold
                                   rounded-lg
                                   border border-amber-200"
                        >
                            {{ strtoupper(str_replace('_', ' ', $req->request_type)) }}
                        </span>


                        <span class="text-[11px] text-slate-400">
                            {{ $req->created_at->diffForHumans() }}
                        </span>

                    </div>


                    {{-- USER --}}
                    <div>

                        <h3 class="text-xs font-bold text-slate-800">
                            {{ $req->user->name }}
                        </h3>

                        <p class="text-[11px] text-slate-400">
                            {{ $req->user->email }}

                            (Angkatan
                            {{ $req->user->angkatan ?: '-' }})
                        </p>

                    </div>


                    {{-- REQUEST DETAIL --}}
                    <div
                        class="bg-slate-50
                               p-3
                               rounded-xl
                               border border-slate-100
                               text-xs
                               space-y-1"
                    >

                        <p class="font-semibold text-slate-700">

                            Target:

                            <span class="font-normal text-slate-600">

                                {{ $req->document
                                    ? $req->document->title
                                    : 'Akses Global'
                                }}

                            </span>

                        </p>


                        <p class="font-semibold text-slate-700">

                            Alasan:

                            <span
                                class="font-normal
                                       text-slate-500
                                       italic"
                            >
                                "{{ $req->reason }}"
                            </span>

                        </p>

                    </div>


                    {{-- ACTION BUTTONS --}}
                    <div
                        class="pt-2
                               border-t border-slate-100
                               flex items-center gap-2"
                    >

                        {{-- APPROVE --}}
                        <button
                            type="button"
                            onclick="openApproveModal({{ $req->id }})"
                            class="flex-1
                                   bg-sage-600
                                   hover:bg-sage-700
                                   text-white
                                   font-bold
                                   py-2
                                   rounded-xl
                                   text-xs
                                   transition"
                        >
                            Setujui
                        </button>


                        {{-- REJECT --}}
                        <button
                            type="button"
                            onclick="openRejectModal({{ $req->id }})"
                            class="px-4
                                   bg-rose-50
                                   hover:bg-rose-100
                                   text-rose-600
                                   font-bold
                                   py-2
                                   rounded-xl
                                   text-xs
                                   border border-rose-200
                                   transition"
                        >
                            Tolak
                        </button>

                    </div>

                </div>

            @empty

                <div
                    class="md:col-span-2
                           bg-white
                           p-8
                           text-center
                           rounded-2xl
                           border border-slate-200
                           text-slate-400
                           text-xs"
                >
                    Tidak ada permohonan pending saat ini.
                </div>

            @endforelse

        </div>

    </div>

</div>



{{-- =============================================================
     APPROVE MODAL
============================================================= --}}

<div
    id="approve-modal"
    class="fixed inset-0
           bg-slate-900/40
           backdrop-blur-sm
           z-50
           hidden
           items-center
           justify-center
           p-4"
>

    <div
        class="bg-white
               max-w-md
               w-full
               rounded-2xl
               p-6
               shadow-xl
               border border-slate-100"
    >

        {{-- HEADER --}}
        <div
            class="flex items-center
                   justify-between
                   border-b border-slate-100
                   pb-3"
        >

            <div>

                <h3 class="text-sm font-bold text-slate-800">
                    Setujui Permohonan
                </h3>

                <p class="text-[11px] text-slate-400 mt-1">
                    Tentukan tanggal dan waktu berakhirnya akses mahasiswa.
                </p>

            </div>


            <button
                type="button"
                onclick="closeApproveModal()"
                class="text-slate-400 hover:text-slate-600"
            >
                <i
                    data-lucide="x"
                    class="w-4 h-4"
                ></i>
            </button>

        </div>


        {{-- FORM --}}
        <form
            id="approve-form"
            method="POST"
            class="mt-5 space-y-5"
        >

            @csrf


            <input
                type="hidden"
                name="status"
                value="approved"
            >


            {{-- EXPIRATION --}}
            <div>

                {{-- LABEL & SHORTCUT BUTTONS DENGAN SPACER --}}
                <div class="flex items-center justify-between mb-2">
                    <label
                        class="block
                               text-xs
                               font-semibold
                               text-slate-600"
                    >
                        Masa Berlaku Akses
                    </label>

                    <div class="flex items-center space-x-1.5">
                        <button type="button" onclick="setExpireDate(7)" class="px-2 py-0.5 text-[10px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition">1 Mgg</button>
                        <button type="button" onclick="setExpireDate(30)" class="px-2 py-0.5 text-[10px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition">1 Bln</button>
                        <button type="button" onclick="setExpireDate(90)" class="px-2 py-0.5 text-[10px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition">3 Bln</button>
                        <button type="button" onclick="setExpireDate(180)" class="px-2 py-0.5 text-[10px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition">6 Bln</button>
                    </div>
                </div>


                <div class="grid grid-cols-2 gap-3">

                    {{-- DATE --}}
                    <div>

                        <label
                            class="block
                                   text-[11px]
                                   font-medium
                                   text-slate-500
                                   mb-2"
                        >
                            Tanggal Berakhir
                        </label>

                        <input
                            type="date"
                            name="expired_date"
                            id="expired_date"
                            min="{{ now()->format('Y-m-d') }}"
                            required
                            class="w-full
                                   bg-slate-50
                                   border border-slate-200
                                   rounded-xl
                                   p-3
                                   text-xs
                                   text-slate-700
                                   focus:outline-none
                                   focus:border-sage-600"
                        >

                    </div>


                    {{-- TIME --}}
                    <div>

                        <label
                            class="block
                                   text-[11px]
                                   font-medium
                                   text-slate-500
                                   mb-2"
                        >
                            Jam & Menit
                        </label>

                        <input
                            type="time"
                            name="expired_time"
                            id="expired_time"
                            required
                            step="60"
                            class="w-full
                                   bg-slate-50
                                   border border-slate-200
                                   rounded-xl
                                   p-3
                                   text-xs
                                   text-slate-700
                                   focus:outline-none
                                   focus:border-sage-600"
                        >

                    </div>

                </div>


                <p
                    class="text-[10px]
                           text-slate-400
                           mt-2"
                >
                    Akses akan berakhir tepat pada tanggal dan waktu yang dipilih.
                </p>

            </div>


            {{-- BUTTONS --}}
            <div
                class="flex justify-end
                       gap-2
                       pt-2"
            >

                <button
                    type="button"
                    onclick="closeApproveModal()"
                    class="px-4
                           py-2.5
                           rounded-xl
                           border border-slate-200
                           text-xs
                           font-medium
                           text-slate-600
                           hover:bg-slate-50"
                >
                    Batal
                </button>


                <button
                    type="submit"
                    class="px-5
                           py-2.5
                           bg-sage-600
                           hover:bg-sage-700
                           text-white
                           rounded-xl
                           text-xs
                           font-bold
                           transition
                           shadow-sm"
                >
                    Setujui Permohonan
                </button>

            </div>

        </form>

    </div>

</div>



{{-- =============================================================
     REJECT MODAL
============================================================= --}}

<div
    id="reject-modal"
    class="fixed inset-0
           bg-slate-900/40
           backdrop-blur-sm
           z-50
           hidden
           items-center
           justify-center
           p-4"
>

    <div
        class="bg-white
               max-w-md
               w-full
               rounded-2xl
               p-6
               shadow-xl
               border border-slate-100"
    >

        {{-- HEADER --}}
        <div
            class="flex items-center
                   justify-between
                   border-b border-slate-100
                   pb-3"
        >

            <h3 class="text-sm font-bold text-slate-800">
                Tolak Permohonan
            </h3>


            <button
                type="button"
                onclick="closeRejectModal()"
                class="text-slate-400 hover:text-slate-600"
            >

                <i
                    data-lucide="x"
                    class="w-4 h-4"
                ></i>

            </button>

        </div>


        {{-- FORM --}}
        <form
            id="reject-form"
            method="POST"
            class="mt-5 space-y-4"
        >

            @csrf


            <input
                type="hidden"
                name="status"
                value="rejected"
            >


            <div>

                <label
                    class="block
                           text-xs
                           font-semibold
                           text-slate-600
                           mb-2"
                >
                    Alasan Penolakan
                </label>


                <textarea
                    id="reject_reason"
                    name="reject_reason"
                    rows="4"
                    required
                    placeholder="Masukkan alasan penolakan..."
                    class="w-full
                           bg-slate-50
                           border border-slate-200
                           rounded-xl
                           p-3
                           text-xs
                           text-slate-700
                           focus:outline-none
                           focus:border-rose-500"
                ></textarea>

            </div>


            {{-- BUTTONS --}}
            <div
                class="flex justify-end
                       gap-2
                       pt-2"
            >

                <button
                    type="button"
                    onclick="closeRejectModal()"
                    class="px-4
                           py-2.5
                           rounded-xl
                           border border-slate-200
                           text-xs
                           font-medium
                           text-slate-600
                           hover:bg-slate-50"
                >
                    Batal
                </button>


                <button
                    type="submit"
                    class="px-5
                           py-2.5
                           bg-rose-600
                           hover:bg-rose-700
                           text-white
                           rounded-xl
                           text-xs
                           font-bold
                           transition
                           shadow-sm"
                >
                    Tolak Permohonan
                </button>

            </div>

        </form>

    </div>

</div>



{{-- =============================================================
     SCRIPT
============================================================= --}}

<script>

/*
|--------------------------------------------------------------------------
| GLOBAL SHORTCUT FUNCTION FOR DATE PICKER
|--------------------------------------------------------------------------
*/
function setExpireDate(days) {
    const targetDate = new Date();
    targetDate.setDate(targetDate.getDate() + days);

    const year = targetDate.getFullYear();
    const month = String(targetDate.getMonth() + 1).padStart(2, '0');
    const day = String(targetDate.getDate()).padStart(2, '0');

    const formattedDate = `${year}-${month}-${day}`;
    
    const expiredDateInput = document.getElementById('expired_date');
    if (expiredDateInput) {
        expiredDateInput.value = formattedDate;
        
        // Trigger event change agar validasi min time ikut menyesuaikan
        expiredDateInput.dispatchEvent(new Event('change'));
    }
}


document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ROUTE
    |--------------------------------------------------------------------------
    */

    const processRequestUrl =
        "{{ url('/lecturer/requests') }}";


    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const approveModal =
        document.getElementById(
            'approve-modal'
        );

    const approveForm =
        document.getElementById(
            'approve-form'
        );

    const expiredDate =
        document.getElementById(
            'expired_date'
        );

    const expiredTime =
        document.getElementById(
            'expired_time'
        );


    const rejectModal =
        document.getElementById(
            'reject-modal'
        );

    const rejectForm =
        document.getElementById(
            'reject-form'
        );

    const rejectReason =
        document.getElementById(
            'reject_reason'
        );


    /*
    |--------------------------------------------------------------------------
    | CURRENT LOCAL DATETIME
    |--------------------------------------------------------------------------
    */

    function getCurrentDateTime() {

        const now =
            new Date();


        const year =
            now.getFullYear();


        const month =
            String(
                now.getMonth() + 1
            ).padStart(
                2,
                '0'
            );


        const day =
            String(
                now.getDate()
            ).padStart(
                2,
                '0'
            );


        const hours =
            String(
                now.getHours()
            ).padStart(
                2,
                '0'
            );


        const minutes =
            String(
                now.getMinutes()
            ).padStart(
                2,
                '0'
            );


        return {

            date:
                `${year}-${month}-${day}`,

            time:
                `${hours}:${minutes}`

        };

    }


    /*
    |--------------------------------------------------------------------------
    | APPROVE MODAL
    |--------------------------------------------------------------------------
    */

    window.openApproveModal =
        function (id) {

            approveForm.action =
                processRequestUrl +
                '/' +
                id +
                '/process';


            const current =
                getCurrentDateTime();


            /*
            |--------------------------------------------------------------------------
            | MIN DATE
            |--------------------------------------------------------------------------
            */

            expiredDate.min =
                current.date;


            /*
            |--------------------------------------------------------------------------
            | DEFAULT DATE
            |--------------------------------------------------------------------------
            */

            expiredDate.value =
                current.date;


            /*
            |--------------------------------------------------------------------------
            | DEFAULT TIME
            |--------------------------------------------------------------------------
            */

            expiredTime.value =
                current.time;


            /*
            |--------------------------------------------------------------------------
            | OPEN
            |--------------------------------------------------------------------------
            */

            approveModal.classList.remove(
                'hidden'
            );

            approveModal.classList.add(
                'flex'
            );


            /*
            |--------------------------------------------------------------------------
            | FOCUS
            |--------------------------------------------------------------------------
            */

            setTimeout(
                function () {

                    expiredDate.focus();

                },
                100
            );

        };


    /*
    |--------------------------------------------------------------------------
    | CLOSE APPROVE
    |--------------------------------------------------------------------------
    */

    window.closeApproveModal =
        function () {

            approveModal.classList.add(
                'hidden'
            );

            approveModal.classList.remove(
                'flex'
            );


            approveForm.reset();


            expiredDate.value =
                '';

            expiredTime.value =
                '';

        };


    /*
    |--------------------------------------------------------------------------
    | PREVENT PAST TIME
    |--------------------------------------------------------------------------
    */

    expiredDate.addEventListener(
        'change',
        function () {

            const current =
                getCurrentDateTime();


            /*
            |--------------------------------------------------------------------------
            | Jika tanggal = hari ini,
            | minimum waktu = sekarang
            |--------------------------------------------------------------------------
            */

            if (
                expiredDate.value ===
                current.date
            ) {

                expiredTime.min =
                    current.time;

            } else {

                expiredTime.removeAttribute(
                    'min'
                );

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | REJECT MODAL
    |--------------------------------------------------------------------------
    */

    window.openRejectModal =
        function (id) {

            rejectForm.action =
                processRequestUrl +
                '/' +
                id +
                '/process';


            rejectForm.reset();


            rejectModal.classList.remove(
                'hidden'
            );

            rejectModal.classList.add(
                'flex'
            );


            setTimeout(
                function () {

                    rejectReason.focus();

                },
                100
            );

        };


    /*
    |--------------------------------------------------------------------------
    | CLOSE REJECT
    |--------------------------------------------------------------------------
    */

    window.closeRejectModal =
        function () {

            rejectModal.classList.add(
                'hidden'
            );

            rejectModal.classList.remove(
                'flex'
            );


            rejectForm.reset();

        };


    /*
    |--------------------------------------------------------------------------
    | BACKDROP
    |--------------------------------------------------------------------------
    */

    approveModal.addEventListener(
        'click',
    function (event) {

            if (
                event.target ===
                approveModal
            ) {

                closeApproveModal();

            }

        }
    );


    rejectModal.addEventListener(
        'click',
        function (event) {

            if (
                event.target ===
                rejectModal
            ) {

                closeRejectModal();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | ESCAPE
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key ===
                'Escape'
            ) {

                closeApproveModal();

                closeRejectModal();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | LUCIDE
    |--------------------------------------------------------------------------
    */

    if (
        typeof lucide !==
        'undefined'
    ) {

        lucide.createIcons();

    }

});

</script>

@endsection