@extends('layouts.app', ['title' => $document->title . ' - Reader'])

@section('content')

@php
    $user = Auth::user();

    $userName = $user->name ?? 'User';
    $userEmail = $user->email ?? '';

    $isAdmin = $user->role === 'admin';

    $expiresAt = null;

    if (!$isAdmin) {
        $permission = \App\Models\TemporaryPermission::where('can_read', true)
            ->where('expired_at', '>', now())
            ->where(function ($query) use ($document) {
                $query->where('document_id', $document->id)
                    ->orWhereNull('document_id');
            })
            ->where(function ($query) use ($user) {
                $query->where('user_id',$user->id)
                    ->orWhere('is_all_students', true)
                    ->orWhere(function ($q) use ($user) {
                        if (!empty($user->angkatan)) {
                            $q->where('target_angkatan',$user->angkatan);
                        }
                    });
            })
            ->orderByDesc('expired_at')
            ->first();

        $expiresAt =$permission?->expired_at;
    }

    $isLiked =$document->likes
        ? $document->likes->where('user_id',$user->id)->count() > 0
        : false;

    $isBookmarked =$document->bookmarks
        ? $document->bookmarks->where('user_id',$user->id)->count() > 0
        : false;

    $pdfUrl = route('pdf.stream', basename($document->pdf_path));
    
    $hasDownloadRoute = \Illuminate\Support\Facades\Route::has('pdf.download');
    $canDownload = $isAdmin || $user->hasDownloadPermission($document->id);
    $downloadUrl = $canDownload && $hasDownloadRoute ? route('pdf.download', $document->id) : '#';
@endphp

<div class="min-h-screen bg-slate-950 text-white">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="border-b border-slate-800 bg-slate-950/95 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                {{-- LEFT --}}
                <div class="flex items-start gap-3 min-w-0">

                    <a
                        href="{{ route('student.catalog') }}"
                        class="flex-shrink-0 mt-1 w-9 h-9 rounded-lg
                               bg-slate-800 hover:bg-slate-700
                               border border-slate-700
                               flex items-center justify-center
                               transition"
                        title="Kembali ke katalog"
                    >
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    </a>

                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-2 mb-1">

                            <span class="text-xs text-slate-400">
                                {{ $document->category->name ?? 'Dokumen' }}
                            </span>

                            @if($isAdmin ||$user->isDosen())

                                <span
                                    class="inline-flex items-center gap-1
                                           px-2 py-1 rounded-md
                                           bg-emerald-500/10
                                           border border-emerald-500/20
                                           text-emerald-400 text-[10px] font-medium"
                                >
                                    <i
                                        data-lucide="shield-check"
                                        class="w-3 h-3"
                                    ></i>

                                    Akses Penuh
                                </span>

                            @else

                                <span
                                    id="access-badge"
                                    class="inline-flex items-center gap-1
                                           px-2 py-1 rounded-md
                                           bg-amber-500/10
                                           border border-amber-500/20
                                           text-amber-400 text-[10px] font-medium"
                                >
                                    <i
                                        data-lucide="clock-3"
                                        class="w-3 h-3"
                                    ></i>

                                    <span id="countdown-text">
                                        Memeriksa akses...
                                    </span>
                                </span>

                            @endif

                        </div>


                        <h1 class="text-lg sm:text-xl font-semibold text-white truncate">
                            {{ $document->title }}
                        </h1>


                        <div
                            class="flex flex-wrap items-center
                                   gap-x-4 gap-y-1 mt-1
                                   text-xs text-slate-500"
                        >

                            <span class="flex items-center gap-1">

                                <i
                                    data-lucide="calendar"
                                    class="w-3 h-3"
                                ></i>

                                {{ $document->tahun }}

                            </span>


                            @if($document->dosen_pembimbing)

                                <span class="flex items-center gap-1">

                                    <i
                                        data-lucide="user-round"
                                        class="w-3 h-3"
                                    ></i>

                                    {{ $document->dosen_pembimbing }}

                                </span>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- RIGHT ACTIONS --}}
                <div class="flex items-center gap-2">

                    {{-- LIKE --}}
                    <button
                        type="button"
                        id="like-btn"
                        data-liked="{{ $isLiked ? '1' : '0' }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg
                               border border-slate-700
                               bg-slate-900 hover:bg-slate-800
                               text-slate-300 hover:text-white
                               transition
                               disabled:opacity-50 disabled:cursor-not-allowed"
                    >

                        <i
                            data-lucide="heart"
                            id="like-icon"
                            class="w-4 h-4 {{ $isLiked ? 'fill-current text-red-400' : '' }}"
                        ></i>


                        <span id="like-text">
                            {{ $isLiked ? 'Disukai' : 'Suka' }}
                        </span>

                    </button>


                    {{-- BOOKMARK --}}
                    <button
                        type="button"
                        id="bookmark-btn"
                        data-bookmarked="{{ $isBookmarked ? '1' : '0' }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg
                               border border-slate-700
                               bg-slate-900 hover:bg-slate-800
                               text-slate-300 hover:text-white
                               transition
                               disabled:opacity-50 disabled:cursor-not-allowed"
                    >

                        <i
                            data-lucide="bookmark"
                            id="bookmark-icon"
                            class="w-4 h-4 {{ $isBookmarked ? 'fill-current text-blue-400' : '' }}"
                        ></i>


                        <span id="bookmark-text">
                            {{ $isBookmarked ? 'Tersimpan' : 'Simpan' }}
                        </span>

                    </button>

                    {{-- TOMBOL UNDUH / REQUEST DOWNLOAD DOKUMEN --}}
                    @if($hasDownloadRoute)
                        @if($canDownload)
                            <a
                                href="{{ $downloadUrl }}"
                                target="_blank"
                                class="flex items-center gap-2 px-3.5 py-2 rounded-lg
                                       border border-blue-600/30
                                       bg-blue-600 hover:bg-blue-500
                                       text-white font-medium text-xs sm:text-sm
                                       shadow-lg shadow-blue-600/20
                                       transition"
                                title="Unduh Dokumen PDF"
                            >
                                <i data-lucide="download" class="w-4 h-4"></i>
                                <span class="hidden sm:inline">Unduh</span>
                            </a>
                        @else
                            <button
                                type="button"
                                onclick="openRequestDownloadModal()"
                                class="flex items-center gap-2 px-3.5 py-2 rounded-lg
                                       border border-amber-600/30
                                       bg-amber-600 hover:bg-amber-500
                                       text-white font-medium text-xs sm:text-sm
                                       shadow-lg shadow-amber-600/20
                                       transition"
                                title="Request Download Dokumen PDF"
                            >
                                <i data-lucide="lock" class="w-4 h-4"></i>
                                <span class="hidden sm:inline">Request Download</span>
                            </button>
                        @endif
                    @endif

                </div>

            </div>

        </div>
    </div>


    {{-- =========================================================
         MAIN
    ========================================================== --}}
    <main class="max-w-7xl mx-auto px-3 sm:px-6 py-4">

        {{-- =====================================================
             PDF READER
        ====================================================== --}}
        <div
            id="pdf-container"
            class="relative w-full
                   rounded-2xl
                   border border-slate-800
                   bg-slate-900
                   overflow-auto
                   shadow-2xl"
            style="height: calc(100vh - 145px);"
        >

            {{-- =================================================
                 STICKY TOOLBAR
            ================================================== --}}
            <div
                id="pdf-toolbar"
                class="sticky top-0 z-[100]
                       w-full
                       bg-slate-900/95
                       backdrop-blur-xl
                       border-b border-slate-700
                       shadow-lg"
            >

                <div
                    class="flex flex-wrap items-center
                           justify-between gap-3 p-3"
                >

                    {{-- PAGE INFO --}}
                    <div class="flex items-center gap-2">

                        <div
                            class="flex items-center gap-2
                                   px-3 py-2 rounded-lg
                                   bg-slate-800
                                   border border-slate-700
                                   text-xs text-slate-300"
                        >

                            <i
                                data-lucide="file-text"
                                class="w-4 h-4 text-slate-400"
                            ></i>

                            <span>

                                Halaman

                                <span
                                    id="current-page"
                                    class="font-semibold text-white"
                                >
                                    1
                                </span>

                                dari

                                <span
                                    id="total-pages"
                                    class="font-semibold text-white"
                                >
                                    -
                                </span>

                            </span>

                        </div>

                    </div>


                    {{-- ZOOM --}}
                    <div class="flex items-center gap-2">

                        <button
                            type="button"
                            id="zoom-out"
                            class="w-9 h-9 rounded-lg
                                   bg-slate-800 hover:bg-slate-700
                                   border border-slate-700
                                   flex items-center justify-center
                                   transition"
                            title="Perkecil"
                        >
                            <i
                                data-lucide="zoom-out"
                                class="w-4 h-4"
                            ></i>
                        </button>


                        <div
                            id="zoom-level"
                            class="min-w-[65px] text-center
                                   px-3 py-2 rounded-lg
                                   bg-slate-800
                                   border border-slate-700
                                   text-xs text-slate-300"
                        >
                            100%
                        </div>


                        <button
                            type="button"
                            id="zoom-in"
                            class="w-9 h-9 rounded-lg
                                   bg-slate-800 hover:bg-slate-700
                                   border border-slate-700
                                   flex items-center justify-center
                                   transition"
                            title="Perbesar"
                        >
                            <i
                                data-lucide="zoom-in"
                                class="w-4 h-4"
                            ></i>
                        </button>


                        <button
                            type="button"
                            id="fit-width"
                            class="flex items-center gap-2
                                   px-3 py-2 rounded-lg
                                   bg-slate-800 hover:bg-slate-700
                                   border border-slate-700
                                   text-xs text-slate-300
                                   transition"
                            title="Sesuaikan dengan lebar"
                        >

                            <i
                                data-lucide="maximize-2"
                                class="w-4 h-4"
                            ></i>

                            <span class="hidden sm:inline">
                                Fit Width
                            </span>

                        </button>

                    </div>


                    {{-- SECURITY INFO --}}
                    <div class="flex items-center gap-2">

                        <div
                            class="hidden sm:flex items-center gap-2
                                   px-3 py-2 rounded-lg
                                   bg-emerald-500/10
                                   border border-emerald-500/20
                                   text-emerald-400 text-xs"
                        >

                            <i
                                data-lucide="shield-check"
                                class="w-4 h-4"
                            ></i>

                            Protected Reader

                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 LOADING
            ================================================== --}}
            <div
                id="pdf-loading"
                class="absolute inset-0 z-[90]
                       flex items-center justify-center
                       bg-slate-950/90
                       backdrop-blur-sm"
            >

                <div class="flex flex-col items-center gap-4">

                    <div
                        class="w-10 h-10 rounded-full
                               border-4 border-slate-700
                               border-t-white
                               animate-spin"
                    ></div>

                    <div class="text-sm text-slate-400">
                        Memuat dokumen...
                    </div>

                </div>

            </div>


            {{-- =================================================
                 PDF PAGES
            ================================================== --}}
            <div
                id="pdf-pages"
                class="flex flex-col items-center gap-6
                       w-full
                       py-6 px-2 sm:px-4"
            >
            </div>


            {{-- =================================================
                 EMPTY / ERROR
            ================================================== --}}
            <div
                id="pdf-placeholder"
                class="hidden min-h-[50vh]
                       flex-col items-center justify-center
                       text-center px-6"
            >

                <div
                    class="w-14 h-14 rounded-2xl
                           bg-red-500/10
                           border border-red-500/20
                           flex items-center justify-center
                           mb-4"
                >

                    <i
                        data-lucide="file-warning"
                        class="w-7 h-7 text-red-400"
                    ></i>

                </div>


                <h3 class="text-lg font-semibold text-white mb-2">
                    Dokumen tidak dapat dimuat
                </h3>


                <p
                    id="pdf-error-message"
                    class="text-sm text-slate-400 max-w-md"
                >
                    Terjadi kesalahan saat memuat dokumen PDF.
                </p>

            </div>

        </div>

    </main>

</div>

{{-- MODAL REQUEST DOWNLOAD --}}
<div id="requestDownloadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl space-y-4 text-white">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Permohonan Izin Download Dokumen</h3>
            <button type="button" onclick="closeRequestDownloadModal()" class="text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('student.requests.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="request_type" value="download">
            <input type="hidden" name="document_id" value="{{ $document->id }}">
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">Dokumen Target</label>
                <input type="text" value="{{ $document->title }}" readonly class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-xs text-slate-300 font-medium">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">Alasan Permohonan Unduh</label>
                <textarea name="reason" rows="3" required placeholder="Tuliskan alasan mengapa Anda perlu mengunduh dokumen ini..." class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-blue-500"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeRequestDownloadModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition">Kirim Request Download</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

<script>
function openRequestDownloadModal() {
    document.getElementById('requestDownloadModal').classList.remove('hidden');
}
function closeRequestDownloadModal() {
    document.getElementById('requestDownloadModal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

    const pdfUrl = @json($pdfUrl);
    const userName = @json($userName);
    const userEmail = @json($userEmail);
    const isAdmin = @json($isAdmin);
    const expiresAt = @json($expiresAt);

    const pdfContainer = document.getElementById('pdf-container');
    const pdfPages = document.getElementById('pdf-pages');
    const pdfLoading = document.getElementById('pdf-loading');
    const pdfPlaceholder = document.getElementById('pdf-placeholder');
    const pdfErrorMessage = document.getElementById('pdf-error-message');
    const currentPageElement = document.getElementById('current-page');
    const totalPagesElement = document.getElementById('total-pages');
    const zoomLevelElement = document.getElementById('zoom-level');
    const zoomInButton = document.getElementById('zoom-in');
    const zoomOutButton = document.getElementById('zoom-out');
    const fitWidthButton = document.getElementById('fit-width');

    let pdfDoc = null;
    let userScale = 1;
    let baseScale = 1;
    let rendering = false;
    let renderAgain = false;
    let currentPage = 1;
    let resizeTimer = null;

    const watermarkColor = 'rgba(100, 116, 139, 0.20)';
    const watermarkText = "Psikologi Universitas Ciputra Surabaya";

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    loadPdf();

    async function loadPdf() {
        try {
            pdfLoading.classList.remove('hidden');
            pdfPlaceholder.classList.add('hidden');

            pdfDoc = await pdfjsLib.getDocument({
                url: pdfUrl,
                disableAutoFetch: false,
                disableStream: false
            }).promise;

            totalPagesElement.textContent = pdfDoc.numPages;

            await calculateBaseScale();
            await renderAllPages(1);

            pdfLoading.classList.add('hidden');
        } catch (error) {
            console.error('PDF loading error:', error);
            pdfLoading.classList.add('hidden');
            pdfPlaceholder.classList.remove('hidden');
            pdfPlaceholder.classList.add('flex');
            pdfErrorMessage.textContent =
                'PDF tidak dapat dibuka. Pastikan file tersedia dan akses Anda masih valid.';
        }
    }

    async function calculateBaseScale() {
        if (!pdfDoc) return;

        const firstPage = await pdfDoc.getPage(1);
        const viewport = firstPage.getViewport({ scale: 1 });
        const containerWidth = pdfContainer.clientWidth;
        const horizontalPadding = window.innerWidth < 640 ? 20 : 50;
        const availableWidth = Math.max(300, containerWidth - horizontalPadding);

        baseScale = availableWidth / viewport.width;

        if (baseScale < 0.5) {
            baseScale = 0.5;
        }
    }

    async function renderAllPages(pageToRestore = currentPage) {
        if (!pdfDoc) return;

        if (rendering) {
            renderAgain = true;
            return;
        }

        rendering = true;

        try {
            pdfPages.innerHTML = '';

            for (let pageNumber = 1; pageNumber <= pdfDoc.numPages; pageNumber++) {
                await renderPage(pageNumber);
            }

            requestAnimationFrame(function () {
                scrollToPage(pageToRestore);
            });

        } catch (error) {
            console.error('Render error:', error);
        } finally {
            rendering = false;

            if (renderAgain) {
                renderAgain = false;
                const page = currentPage;
                renderAllPages(page);
            }
        }
    }

    async function renderPage(pageNumber) {
        const page = await pdfDoc.getPage(pageNumber);
        const displayScale = baseScale * userScale;
        const viewport = page.getViewport({ scale: displayScale });
        const outputScale = Math.min(window.devicePixelRatio || 1, 2);

        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d', { alpha: false });

        canvas.width = Math.floor(viewport.width * outputScale);
        canvas.height = Math.floor(viewport.height * outputScale);
        canvas.style.width = Math.floor(viewport.width) + 'px';
        canvas.style.height = Math.floor(viewport.height) + 'px';
        canvas.className = 'pdf-page';

        const wrapper = document.createElement('div');
        wrapper.className = 'pdf-page-wrapper';
        wrapper.dataset.pageNumber = pageNumber;
        wrapper.style.width = '100%';
        wrapper.style.display = 'flex';
        wrapper.style.justifyContent = 'center';
        wrapper.style.position = 'relative';
        wrapper.style.userSelect = 'none';

        canvas.style.userSelect = 'none';

        wrapper.appendChild(canvas);
        pdfPages.appendChild(wrapper);

        const renderContext = {
            canvasContext: context,
            viewport: viewport,
            transform: outputScale !== 1 ? [outputScale, 0, 0, outputScale, 0, 0] : null,
            intent: 'display'
        };

        await page.render(renderContext).promise;

        drawWatermark(context, canvas.width, canvas.height, outputScale);
    }

    function drawWatermark(ctx, canvasWidth, canvasHeight, outputScale) {
        ctx.save();
        ctx.globalAlpha = 1;
        ctx.fillStyle = watermarkColor;
        ctx.strokeStyle = 'rgba(100, 116, 139, 0.10)';

        const fontSize = Math.max(18, Math.min(28, canvasWidth / 35));
        ctx.font = `600 ${fontSize}px Arial, sans-serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        ctx.translate(canvasWidth / 2, canvasHeight / 2);
        ctx.rotate(-25 * Math.PI / 180);

        const horizontalGap = Math.max(250, canvasWidth / 3);
        const verticalGap = Math.max(130, canvasHeight / 7);

        const startX = -canvasWidth * 1.2;
        const endX = canvasWidth * 1.2;
        const startY = -canvasHeight * 1.2;
        const endY = canvasHeight * 1.2;

        for (let y = startY; y <= endY; y += verticalGap) {
            for (let x = startX; x <= endX; x += horizontalGap) {
                ctx.fillText(watermarkText, x, y);
            }
        }

        ctx.globalAlpha = 0.10;
        ctx.font = `700 ${fontSize * 1.35}px Arial, sans-serif`;
        ctx.fillText(watermarkText, 0, 0);

        ctx.restore();
    }

    function updateZoomUI() {
        const percentage = Math.round(userScale * 100);
        zoomLevelElement.textContent = percentage + '%';
    }

    zoomInButton.addEventListener('click', async function () {
        if (rendering) return;
        const pageBeforeZoom = getCurrentPage();
        userScale = Math.min(userScale + 0.15, 2.5);
        updateZoomUI();
        pdfLoading.classList.remove('hidden');
        await renderAllPages(pageBeforeZoom);
        pdfLoading.classList.add('hidden');
    });

    zoomOutButton.addEventListener('click', async function () {
        if (rendering) return;
        const pageBeforeZoom = getCurrentPage();
        userScale = Math.max(userScale - 0.15, 0.5);
        updateZoomUI();
        pdfLoading.classList.remove('hidden');
        await renderAllPages(pageBeforeZoom);
        pdfLoading.classList.add('hidden');
    });

    fitWidthButton.addEventListener('click', async function () {
        if (rendering) return;
        const pageBeforeZoom = getCurrentPage();
        userScale = 1;
        updateZoomUI();
        await calculateBaseScale();
        pdfLoading.classList.remove('hidden');
        await renderAllPages(pageBeforeZoom);
        pdfLoading.classList.add('hidden');
    });

    function getCurrentPage() {
        const wrappers = document.querySelectorAll('.pdf-page-wrapper');
        if (!wrappers.length) return currentPage;

        const toolbar = document.getElementById('pdf-toolbar');
        const toolbarHeight = toolbar ? toolbar.offsetHeight : 0;
        const targetY = pdfContainer.getBoundingClientRect().top + toolbarHeight + 30;

        let closestPage = 1;
        let closestDistance = Infinity;

        wrappers.forEach(function (wrapper) {
            const rect = wrapper.getBoundingClientRect();
            const distance = Math.abs(rect.top - targetY);

            if (distance < closestDistance) {
                closestDistance = distance;
                closestPage = parseInt(wrapper.dataset.pageNumber, 10);
            }
        });

        currentPage = closestPage;
        currentPageElement.textContent = currentPage;
        return currentPage;
    }

    function scrollToPage(pageNumber) {
        const wrapper = document.querySelector(`.pdf-page-wrapper[data-page-number="${pageNumber}"]`);
        if (!wrapper) return;

        const toolbar = document.getElementById('pdf-toolbar');
        const toolbarHeight = toolbar ? toolbar.offsetHeight : 0;
        const containerRect = pdfContainer.getBoundingClientRect();
        const wrapperRect = wrapper.getBoundingClientRect();

        const offset = wrapperRect.top - containerRect.top + pdfContainer.scrollTop - toolbarHeight - 15;

        pdfContainer.scrollTo({
            top: Math.max(0, offset),
            behavior: 'instant'
        });

        currentPage = pageNumber;
        currentPageElement.textContent = pageNumber;
    }

    let scrollTimeout = null;

    pdfContainer.addEventListener('scroll', function () {
        if (scrollTimeout) {
            cancelAnimationFrame(scrollTimeout);
        }
        scrollTimeout = requestAnimationFrame(function () {
            getCurrentPage();
        });
    }, { passive: true });

    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(async function () {
            if (!pdfDoc || rendering) return;
            const pageBeforeResize = getCurrentPage();
            await calculateBaseScale();
            await renderAllPages(pageBeforeResize);
        }, 250);
    });

    /*
    |--------------------------------------------------------------------------
    | LIKE & BOOKMARK
    |--------------------------------------------------------------------------
    */
    const likeButton = document.getElementById('like-btn');
    likeButton.addEventListener('click', async function () {
        try {
            likeButton.disabled = true;
            const response = await fetch(`/document/{{ $document->id }}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });

            if (!response.ok) throw new Error('Like failed');

            const data = await response.json();
            const liked = Boolean(data.is_liked);

            likeButton.dataset.liked = liked ? '1' : '0';
            const icon = document.getElementById('like-icon');
            const text = document.getElementById('like-text');

            if (liked) {
                icon.classList.add('fill-current', 'text-red-400');
                text.textContent = 'Disukai';
            } else {
                icon.classList.remove('fill-current', 'text-red-400');
                text.textContent = 'Suka';
            }
        } catch (error) {
            console.error('Like error:', error);
        } finally {
            likeButton.disabled = false;
        }
    });

    const bookmarkButton = document.getElementById('bookmark-btn');
    bookmarkButton.addEventListener('click', async function () {
        try {
            bookmarkButton.disabled = true;
            const response = await fetch(`/document/{{ $document->id }}/bookmark`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });

            if (!response.ok) throw new Error('Bookmark failed');

            const data = await response.json();
            const bookmarked = Boolean(data.is_bookmarked);

            bookmarkButton.dataset.bookmarked = bookmarked ? '1' : '0';
            const icon = document.getElementById('bookmark-icon');
            const text = document.getElementById('bookmark-text');

            if (bookmarked) {
                icon.classList.add('fill-current', 'text-blue-400');
                text.textContent = 'Tersimpan';
            } else {
                icon.classList.remove('fill-current', 'text-blue-400');
                text.textContent = 'Simpan';
            }
        } catch (error) {
            console.error('Bookmark error:', error);
        } finally {
            bookmarkButton.disabled = false;
        }
    });

    /*
    |--------------------------------------------------------------------------
    | ACCESS COUNTDOWN LOGIC
    |--------------------------------------------------------------------------
    */
    @if(!$isAdmin &&$expiresAt)
        const countdownElement = document.getElementById('countdown-text');
        const accessBadge = document.getElementById('access-badge');
        const expiresTimestamp = new Date(@json(\Carbon\Carbon::parse($expiresAt)->toIso8601String())).getTime();

        function updateCountdown() {
            const now = Date.now();
            const difference = expiresTimestamp - now;

            if (difference <= 0) {
                if (countdownElement) {
                    countdownElement.textContent = 'Akses berakhir';
                }
                if (accessBadge) {
                    accessBadge.classList.remove('text-amber-400', 'bg-amber-500/10', 'border-amber-500/20');
                    accessBadge.classList.add('text-red-400', 'bg-red-500/10', 'border-red-500/20');
                }

                setTimeout(function () {
                    window.location.reload();
                }, 1500);
                return;
            }

            const totalSeconds = Math.floor(difference / 1000);
            const days = Math.floor(totalSeconds / 86400);
            const hours = Math.floor((totalSeconds % 86400) / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            let result = '';
            if (days > 0) result += days + 'h ';
            result += String(hours).padStart(2, '0') + ':' +
                      String(minutes).padStart(2, '0') + ':' +
                      String(seconds).padStart(2, '0');

            if (countdownElement) {
                countdownElement.textContent = 'Akses ' + result;
            }
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    @endif

    /*
    |--------------------------------------------------------------------------
    | SECURITY
    |--------------------------------------------------------------------------
    */
    document.addEventListener('contextmenu', function (e) { e.preventDefault(); }, true);

    document.addEventListener('selectstart', function (e) {
        const target = e.target;
        if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable) return;
        e.preventDefault();
    }, true);

    document.addEventListener('dragstart', function (e) { e.preventDefault(); }, true);
    document.addEventListener('copy', function (e) { e.preventDefault(); }, true);
    document.addEventListener('cut', function (e) { e.preventDefault(); }, true);

    document.addEventListener('keydown', function (e) {
        const key = e.key.toLowerCase();
        const modifier = e.ctrlKey || e.metaKey;

        if (modifier && (key === 'c' || key === 's' || key === 'p' || key === 'u')) {
            e.preventDefault();
            return;
        }

        if (modifier && e.shiftKey && key === 'i') {
            e.preventDefault();
            return;
        }

        if (e.key === 'F12') {
            e.preventDefault();
            return;
        }
    }, true);

    pdfContainer.addEventListener('dblclick', function (e) { e.preventDefault(); });

    window.addEventListener('beforeprint', function () {
        document.body.dataset.printBlocked = 'true';
    });

    updateZoomUI();
});
</script>
@endsection