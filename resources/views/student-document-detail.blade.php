@extends('layouts.app', [
    'title' => $document->title.' - Reader',
])

@section('content')
    @php
        $user = auth()->user();

        $isPreview = request()->routeIs('reports.preview');

        if ($isPreview) {
            if ($user->permits('review_uploads')) {
                $backRoute = 'reports.reviews';
            } elseif ($user->permits('manage_documents')) {
                $backRoute = 'reports.manage';
            } else {
                $backRoute = 'reports.mine';
            }
        } else {
            $backRoute = $user->permits('catalog')
                ? 'student.catalog'
                : 'home';
        }

        $hasUnlimitedAccess = $user->permits('read_all')
            || $isPreview;

        $expiresAt = null;

        if (!$hasUnlimitedAccess) {
            $expiresAt = $user->activePermissions()
                ->where('can_read', true)
                ->where(function ($query) use ($document) {
                    $query->where('document_id', $document->id)
                        ->orWhereNull('document_id');
                })
                ->max('expired_at');
        }

        $expiresAtIso = $expiresAt
            ? \Carbon\Carbon::parse($expiresAt)->toIso8601String()
            : null;

        $isLiked = $document->likes
            ->where('user_id', $user->id)
            ->isNotEmpty();

        $isBookmarked = $document->bookmarks
            ->where('user_id', $user->id)
            ->isNotEmpty();

        $canInteract = $user->permits('bookmarks')
            && !$isPreview;

        $pdfUrl = route(
            'pdf.stream',
            basename($document->pdf_path)
        );

        $likeUrl = route('document.like', $document->id);

        $bookmarkUrl = route(
            'document.bookmark',
            $document->id
        );
    @endphp

    <style>
        #case-reader .pdf-page {
            display: block;
            background: white;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
            user-select: none;
            -webkit-user-select: none;
        }

        #case-reader .pdf-page-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            flex-shrink: 0;
            user-select: none;
            -webkit-user-select: none;
        }

        @media print {
            #case-reader {
                display: none !important;
            }
        }
    </style>

    <div
        id="case-reader"
        class="bg-slate-950 text-white rounded-2xl overflow-hidden"
    >
        <header class="border-b border-slate-800 bg-slate-950">
            <div class="px-4 sm:px-6 py-4">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <div class="flex items-start gap-3 min-w-0">
                        <a
                            href="{{ route($backRoute) }}"
                            title="Kembali ke daftar laporan"
                            aria-label="Kembali ke daftar laporan"
                            class="shrink-0 mt-1 w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 flex items-center justify-center transition"
                        >
                            <i
                                data-lucide="arrow-left"
                                class="w-4 h-4"
                            ></i>
                        </a>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="text-xs text-slate-400">
                                    {{ $document->category->name ?? 'Dokumen' }}
                                    · {{ $document->case_type }}
                                </span>

                                @if($hasUnlimitedAccess)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-medium">
                                        <i
                                            data-lucide="shield-check"
                                            class="w-3 h-3"
                                        ></i>

                                        {{ $isPreview ? 'Preview Laporan' : 'Akses Penuh' }}
                                    </span>
                                @else
                                    <span
                                        id="access-badge"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-medium"
                                    >
                                        <i
                                            data-lucide="clock-3"
                                            class="w-3 h-3"
                                        ></i>

                                        <span id="countdown-text">
                                            Memeriksa akses…
                                        </span>
                                    </span>
                                @endif
                            </div>

                            <h1 class="text-lg sm:text-xl font-semibold text-white break-words">
                                {{ $document->title }}
                            </h1>

                            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-slate-400">
                                <span class="inline-flex items-center gap-1">
                                    <i
                                        data-lucide="calendar"
                                        class="w-3 h-3"
                                    ></i>

                                    {{ $document->tahun }}
                                </span>

                                @if($document->dosen_pembimbing)
                                    <span class="inline-flex items-center gap-1">
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

                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            type="button"
                            id="like-btn"
                            data-liked="{{ $isLiked ? '1' : '0' }}"
                            @disabled(!$canInteract)
                            class="flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-700 bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white transition disabled:opacity-50 disabled:cursor-not-allowed"
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

                        <button
                            type="button"
                            id="bookmark-btn"
                            data-bookmarked="{{ $isBookmarked ? '1' : '0' }}"
                            @disabled(!$canInteract)
                            class="flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-700 bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white transition disabled:opacity-50 disabled:cursor-not-allowed"
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
                    </div>
                </div>

                <p
                    id="reader-action-message"
                    role="status"
                    aria-live="polite"
                    class="mt-3 text-xs text-amber-300"
                ></p>
            </div>
        </header>

        <div class="p-3 sm:p-5">
            <div
                id="pdf-container"
                class="relative w-full rounded-2xl border border-slate-800 bg-slate-900 overflow-auto shadow-2xl"
                style="height: 75vh; min-height: 360px;"
            >
                <div
                    id="pdf-toolbar"
                    class="sticky top-0 z-20 w-full bg-slate-900/95 backdrop-blur-xl border-b border-slate-700 shadow-lg"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3 p-3">
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-xs text-slate-300">
                            <i
                                data-lucide="file-text"
                                class="w-4 h-4 text-slate-400"
                            ></i>

                            <span>
                                Halaman

                                <span
                                    id="current-page"
                                    class="font-semibold text-white"
                                >1</span>

                                dari

                                <span
                                    id="total-pages"
                                    class="font-semibold text-white"
                                >-</span>
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                id="zoom-out"
                                title="Perkecil"
                                aria-label="Perkecil PDF"
                                class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 flex items-center justify-center disabled:opacity-40"
                            >
                                <i
                                    data-lucide="zoom-out"
                                    class="w-4 h-4"
                                ></i>
                            </button>

                            <div
                                id="zoom-level"
                                class="min-w-[65px] text-center px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-xs text-slate-300"
                            >
                                100%
                            </div>

                            <button
                                type="button"
                                id="zoom-in"
                                title="Perbesar"
                                aria-label="Perbesar PDF"
                                class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 flex items-center justify-center disabled:opacity-40"
                            >
                                <i
                                    data-lucide="zoom-in"
                                    class="w-4 h-4"
                                ></i>
                            </button>

                            <button
                                type="button"
                                id="fit-width"
                                title="Sesuaikan dengan lebar"
                                aria-label="Sesuaikan PDF dengan lebar"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs text-slate-300 disabled:opacity-40"
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

                        <span class="hidden lg:inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs">
                            <i
                                data-lucide="shield-check"
                                class="w-4 h-4"
                            ></i>

                            Protected Reader
                        </span>
                    </div>
                </div>

                <div
                    id="pdf-loading"
                    role="status"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-slate-950/90"
                >
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-10 h-10 rounded-full border-4 border-slate-700 border-t-white animate-spin"></div>

                        <p class="text-sm text-slate-400">
                            Memuat dokumen…
                        </p>
                    </div>
                </div>

                <div
                    id="pdf-pages"
                    class="flex flex-col items-center gap-6 w-full py-6 px-2 sm:px-4"
                ></div>

                <div
                    id="pdf-placeholder"
                    role="alert"
                    class="hidden min-h-[50vh] flex-col items-center justify-center text-center px-6"
                >
                    <i
                        data-lucide="file-warning"
                        class="w-10 h-10 text-red-400 mb-4"
                    ></i>

                    <h2 class="text-lg font-semibold text-white mb-2">
                        Dokumen tidak dapat dimuat
                    </h2>

                    <p
                        id="pdf-error-message"
                        class="text-sm text-slate-400 max-w-md"
                    >
                        Terjadi kesalahan saat memuat dokumen PDF.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pdfUrl = @json($pdfUrl);
            const likeUrl = @json($likeUrl);
            const bookmarkUrl = @json($bookmarkUrl);
            const csrfToken = @json(csrf_token());
            const canInteract = @json($canInteract);
            const expiresAt = @json($expiresAtIso);

            const reader = document.getElementById('case-reader');
            const container = document.getElementById('pdf-container');
            const pages = document.getElementById('pdf-pages');
            const loading = document.getElementById('pdf-loading');
            const placeholder = document.getElementById('pdf-placeholder');
            const errorMessage = document.getElementById('pdf-error-message');
            const currentPageLabel = document.getElementById('current-page');
            const totalPagesLabel = document.getElementById('total-pages');
            const zoomLabel = document.getElementById('zoom-level');
            const zoomIn = document.getElementById('zoom-in');
            const zoomOut = document.getElementById('zoom-out');
            const fitWidth = document.getElementById('fit-width');
            const likeButton = document.getElementById('like-btn');
            const bookmarkButton = document.getElementById('bookmark-btn');
            const actionMessage = document.getElementById('reader-action-message');

            let pdfDocument = null;
            let loadingTask = null;
            let userScale = 1;
            let baseScale = 1;
            let currentPage = 1;
            let rendering = false;
            let resizeTimer = null;
            let scrollFrame = null;
            let countdownInterval = null;
            let accessExpired = false;

            const zoomButtons = [
                zoomIn,
                zoomOut,
                fitWidth,
            ];

            function updateControls() {
                const disabled = !pdfDocument
                    || rendering
                    || accessExpired;

                zoomButtons.forEach(function (button) {
                    button.disabled = disabled;
                });

                zoomLabel.textContent =
                    Math.round(userScale * 100) + '%';
            }

            function showError(message) {
                loading.classList.add('hidden');
                pages.replaceChildren();

                placeholder.classList.remove('hidden');
                placeholder.classList.add('flex');

                errorMessage.textContent = message;
            }

            function expireAccess() {
                if (accessExpired) {
                    return;
                }

                accessExpired = true;

                clearInterval(countdownInterval);
                clearTimeout(resizeTimer);

                likeButton.disabled = true;
                bookmarkButton.disabled = true;

                updateControls();

                const countdown = document.getElementById(
                    'countdown-text'
                );

                if (countdown) {
                    countdown.textContent = 'Akses berakhir';
                }

                showError(
                    'Masa akses baca telah berakhir. '
                    + 'Silakan ajukan akses kembali.'
                );

                if (pdfDocument) {
                    Promise.resolve(
                        pdfDocument.destroy()
                    ).catch(function () {});
                } else if (loadingTask) {
                    Promise.resolve(
                        loadingTask.destroy()
                    ).catch(function () {});
                }
            }

            function updateCountdown() {
                if (!expiresAt) {
                    return;
                }

                const remaining =
                    new Date(expiresAt).getTime() - Date.now();

                if (remaining <= 0) {
                    expireAccess();
                    return;
                }

                const totalSeconds = Math.floor(remaining / 1000);
                const days = Math.floor(totalSeconds / 86400);
                const hours = Math.floor(
                    (totalSeconds % 86400) / 3600
                );
                const minutes = Math.floor(
                    (totalSeconds % 3600) / 60
                );
                const seconds = totalSeconds % 60;

                const time = [
                    hours,
                    minutes,
                    seconds,
                ].map(function (value) {
                    return String(value).padStart(2, '0');
                }).join(':');

                const countdown = document.getElementById(
                    'countdown-text'
                );

                if (countdown) {
                    countdown.textContent =
                        'Akses '
                        + (days > 0 ? days + ' hari ' : '')
                        + time;
                }
            }

            async function calculateBaseScale() {
                if (!pdfDocument || accessExpired) {
                    return;
                }

                const firstPage = await pdfDocument.getPage(1);

                const viewport = firstPage.getViewport({
                    scale: 1,
                });

                const padding = window.innerWidth < 640
                    ? 24
                    : 48;

                const availableWidth = Math.max(
                    120,
                    container.clientWidth - padding
                );

                baseScale = availableWidth / viewport.width;
            }

            function drawWatermark(context, width, height) {
                const text =
                    'Psikologi Universitas Ciputra Surabaya';

                const fontSize = Math.max(
                    18,
                    Math.min(28, width / 35)
                );

                context.save();

                context.fillStyle =
                    'rgba(100, 116, 139, 0.20)';

                context.font =
                    `600 ${fontSize}px Arial, sans-serif`;

                context.textAlign = 'center';
                context.textBaseline = 'middle';

                context.translate(
                    width / 2,
                    height / 2
                );

                context.rotate(
                    -25 * Math.PI / 180
                );

                const horizontalGap = Math.max(
                    250,
                    width / 3
                );

                const verticalGap = Math.max(
                    130,
                    height / 7
                );

                for (
                    let y = -height * 1.2;
                    y <= height * 1.2;
                    y += verticalGap
                ) {
                    for (
                        let x = -width * 1.2;
                        x <= width * 1.2;
                        x += horizontalGap
                    ) {
                        context.fillText(text, x, y);
                    }
                }

                context.globalAlpha = 0.10;

                context.font =
                    `700 ${fontSize * 1.35}px Arial, sans-serif`;

                context.fillText(text, 0, 0);

                context.restore();
            }

            async function renderPage(pageNumber) {
                if (accessExpired) {
                    return;
                }

                const page = await pdfDocument.getPage(
                    pageNumber
                );

                if (accessExpired) {
                    return;
                }

                const viewport = page.getViewport({
                    scale: baseScale * userScale,
                });

                const outputScale = Math.min(
                    window.devicePixelRatio || 1,
                    2
                );

                const canvas = document.createElement('canvas');

                const context = canvas.getContext('2d', {
                    alpha: false,
                });

                if (!context) {
                    throw new Error(
                        'Canvas tidak tersedia.'
                    );
                }

                canvas.width = Math.floor(
                    viewport.width * outputScale
                );

                canvas.height = Math.floor(
                    viewport.height * outputScale
                );

                canvas.style.width =
                    Math.floor(viewport.width) + 'px';

                canvas.style.height =
                    Math.floor(viewport.height) + 'px';

                canvas.className = 'pdf-page';

                const wrapper = document.createElement('div');

                wrapper.className = 'pdf-page-wrapper';
                wrapper.dataset.pageNumber = pageNumber;

                wrapper.appendChild(canvas);
                pages.appendChild(wrapper);

                await page.render({
                    canvasContext: context,
                    viewport: viewport,

                    transform: outputScale !== 1
                        ? [
                            outputScale,
                            0,
                            0,
                            outputScale,
                            0,
                            0,
                        ]
                        : null,

                    intent: 'display',
                }).promise;

                if (!accessExpired) {
                    drawWatermark(
                        context,
                        canvas.width,
                        canvas.height
                    );
                }
            }

            function getCurrentPage() {
                const wrappers = pages.querySelectorAll(
                    '.pdf-page-wrapper'
                );

                if (!wrappers.length) {
                    return currentPage;
                }

                const toolbar = document.getElementById(
                    'pdf-toolbar'
                );

                const targetY =
                    container.getBoundingClientRect().top
                    + toolbar.offsetHeight
                    + 30;

                let closestPage = 1;
                let closestDistance = Infinity;

                wrappers.forEach(function (wrapper) {
                    const distance = Math.abs(
                        wrapper.getBoundingClientRect().top
                        - targetY
                    );

                    if (distance < closestDistance) {
                        closestDistance = distance;

                        closestPage = Number(
                            wrapper.dataset.pageNumber
                        );
                    }
                });

                currentPage = closestPage;
                currentPageLabel.textContent = currentPage;

                return currentPage;
            }

            function scrollToPage(pageNumber) {
                const wrapper = pages.querySelector(
                    `.pdf-page-wrapper[data-page-number="${pageNumber}"]`
                );

                if (!wrapper) {
                    return;
                }

                const toolbar = document.getElementById(
                    'pdf-toolbar'
                );

                const offset =
                    wrapper.getBoundingClientRect().top
                    - container.getBoundingClientRect().top
                    + container.scrollTop
                    - toolbar.offsetHeight
                    - 15;

                container.scrollTo({
                    top: Math.max(0, offset),
                    behavior: 'auto',
                });

                currentPage = pageNumber;
                currentPageLabel.textContent = pageNumber;
            }

            async function renderAllPages(pageToRestore = 1) {
                if (
                    !pdfDocument
                    || rendering
                    || accessExpired
                ) {
                    return;
                }

                rendering = true;

                updateControls();

                loading.classList.remove('hidden');

                try {
                    pages.replaceChildren();

                    placeholder.classList.add('hidden');
                    placeholder.classList.remove('flex');

                    for (
                        let pageNumber = 1;
                        pageNumber <= pdfDocument.numPages;
                        pageNumber++
                    ) {
                        if (accessExpired) {
                            break;
                        }

                        await renderPage(pageNumber);
                    }

                    if (!accessExpired) {
                        scrollToPage(pageToRestore);
                    }
                } catch (error) {
                    if (!accessExpired) {
                        showError(
                            'Halaman PDF gagal ditampilkan. '
                            + 'Muat ulang halaman untuk mencoba lagi.'
                        );
                    }
                } finally {
                    rendering = false;

                    loading.classList.add('hidden');

                    updateControls();
                }
            }

            async function loadPdf() {
                if (accessExpired) {
                    return;
                }

                if (!window.pdfjsLib) {
                    showError(
                        'Komponen PDF gagal dimuat. '
                        + 'Periksa koneksi internet lalu muat ulang halaman.'
                    );

                    return;
                }

                pdfjsLib.GlobalWorkerOptions.workerSrc =
                    'https://cdnjs.cloudflare.com/ajax/libs/'
                    + 'pdf.js/2.16.105/pdf.worker.min.js';

                try {
                    loadingTask = pdfjsLib.getDocument({
                        url: pdfUrl,
                        disableAutoFetch: false,
                        disableStream: false,
                        isEvalSupported: false,
                    });

                    pdfDocument = await loadingTask.promise;

                    if (accessExpired) {
                        await pdfDocument.destroy();
                        return;
                    }

                    totalPagesLabel.textContent =
                        pdfDocument.numPages;

                    await calculateBaseScale();
                    await renderAllPages(1);
                } catch (error) {
                    if (!accessExpired) {
                        showError(
                            'PDF tidak dapat dibuka. '
                            + 'Pastikan file tersedia dan '
                            + 'akses Anda masih valid.'
                        );
                    }
                }
            }

            async function changeZoom(nextScale, fit = false) {
                if (
                    !pdfDocument
                    || rendering
                    || accessExpired
                ) {
                    return;
                }

                const pageBeforeZoom = getCurrentPage();

                userScale = nextScale;

                if (fit) {
                    await calculateBaseScale();
                }

                await renderAllPages(pageBeforeZoom);
            }

            zoomIn.addEventListener('click', function () {
                changeZoom(
                    Math.min(userScale + 0.15, 2.5)
                );
            });

            zoomOut.addEventListener('click', function () {
                changeZoom(
                    Math.max(userScale - 0.15, 0.5)
                );
            });

            fitWidth.addEventListener('click', function () {
                changeZoom(1, true);
            });

            container.addEventListener(
                'scroll',
                function () {
                    if (scrollFrame) {
                        cancelAnimationFrame(scrollFrame);
                    }

                    scrollFrame = requestAnimationFrame(
                        getCurrentPage
                    );
                },
                {
                    passive: true,
                }
            );

            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);

                resizeTimer = setTimeout(async function () {
                    if (
                        !pdfDocument
                        || rendering
                        || accessExpired
                    ) {
                        return;
                    }

                    const pageBeforeResize = getCurrentPage();

                    await calculateBaseScale();
                    await renderAllPages(pageBeforeResize);
                }, 250);
            });

            async function toggleAction(button, url, type) {
                if (
                    !canInteract
                    || button.disabled
                    || accessExpired
                ) {
                    return;
                }

                button.disabled = true;
                actionMessage.textContent = '';

                try {
                    const response = await fetch(url, {
                        method: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },

                        body: JSON.stringify({}),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(
                            data.message
                            || 'Tindakan gagal diproses.'
                        );
                    }

                    if (type === 'like') {
                        const active = Boolean(
                            data.is_liked
                        );

                        button.dataset.liked =
                            active ? '1' : '0';

                        const icon = document.getElementById(
                            'like-icon'
                        );

                        icon.classList.toggle(
                            'fill-current',
                            active
                        );

                        icon.classList.toggle(
                            'text-red-400',
                            active
                        );

                        document.getElementById(
                            'like-text'
                        ).textContent = active
                            ? 'Disukai'
                            : 'Suka';
                    } else {
                        const active = Boolean(
                            data.is_bookmarked
                        );

                        button.dataset.bookmarked =
                            active ? '1' : '0';

                        const icon = document.getElementById(
                            'bookmark-icon'
                        );

                        icon.classList.toggle(
                            'fill-current',
                            active
                        );

                        icon.classList.toggle(
                            'text-blue-400',
                            active
                        );

                        document.getElementById(
                            'bookmark-text'
                        ).textContent = active
                            ? 'Tersimpan'
                            : 'Simpan';
                    }
                } catch (error) {
                    actionMessage.textContent =
                        error.message
                        || 'Tindakan gagal. Silakan coba lagi.';
                } finally {
                    button.disabled =
                        !canInteract || accessExpired;
                }
            }

            likeButton.addEventListener('click', function () {
                toggleAction(
                    likeButton,
                    likeUrl,
                    'like'
                );
            });

            bookmarkButton.addEventListener('click', function () {
                toggleAction(
                    bookmarkButton,
                    bookmarkUrl,
                    'bookmark'
                );
            });

            reader.addEventListener(
                'contextmenu',
                function (event) {
                    event.preventDefault();
                }
            );

            reader.addEventListener(
                'selectstart',
                function (event) {
                    event.preventDefault();
                }
            );

            reader.addEventListener(
                'dragstart',
                function (event) {
                    event.preventDefault();
                }
            );

            ['copy', 'cut'].forEach(function (eventName) {
                reader.addEventListener(
                    eventName,
                    function (event) {
                        event.preventDefault();
                    }
                );
            });

            document.addEventListener(
                'keydown',
                function (event) {
                    const key = event.key.toLowerCase();
                    const modifier =
                        event.ctrlKey || event.metaKey;

                    if (
                        modifier
                        && ['c', 's', 'p', 'u'].includes(key)
                    ) {
                        event.preventDefault();
                    }
                }
            );

            updateControls();

            if (expiresAt) {
                updateCountdown();

                if (!accessExpired) {
                    countdownInterval = setInterval(
                        updateCountdown,
                        1000
                    );
                }
            }

            if (!accessExpired) {
                loadPdf();
            }

            window.lucide?.createIcons();
        });
    </script>
@endsection