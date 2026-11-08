<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    @php
        $brandName = 'Case Management System';

        $pageTitle = $title ?? $brandName;

        if (!str_contains($pageTitle, $brandName)) {
            $pageTitle .= ' | '.$brandName;
        }

        $user = auth()->user();

        $showSidebar = $user
            && !request()->routeIs('login');

        $pendingAccessCount = 0;
        $navigationGroups = [];

        if ($showSidebar) {
            if ($user->permits('approve_access')) {
                $pendingAccessCount = \App\Models\DocumentRequest::where(
                    'status',
                    'pending'
                )->count();
            }

            $navigationGroups = [
                'Referensi' => [
                    [
                        'catalog',
                        'student.catalog',
                        'compass',
                        'Katalog Dokumen',
                    ],
                    [
                        'bookmarks',
                        'student.bookmarks',
                        'bookmark',
                        'Simpanan Saya',
                    ],
                ],

                'Laporan' => [
                    [
                        'upload',
                        'lecturer.upload',
                        'upload-cloud',
                        'Upload Laporan',
                    ],
                    [
                        'upload',
                        'reports.mine',
                        'files',
                        'Laporan Saya',
                    ],
                    [
                        'review_uploads',
                        'reports.reviews',
                        'clipboard-check',
                        'Review Upload',
                    ],
                    [
                        'manage_documents',
                        'reports.manage',
                        'file-pen-line',
                        'Kelola Laporan',
                    ],
                    [
                        'manage_categories',
                        'lecturer.categories',
                        'folder-tree',
                        'Kelola Kategori',
                    ],
                ],

                'Persetujuan & Akses' => [
                    [
                        'request_access',
                        'student.requests',
                        'file-question',
                        'Permohonan Akses',
                    ],
                    [
                        'approve_access',
                        'lecturer.requests',
                        'check-square',
                        'Persetujuan Akses',
                    ],
                    [
                        'grant_access',
                        'access.grant.create',
                        'key-round',
                        'Berikan Akses',
                    ],
                ],

                'Administrasi' => [
                    [
                        'dashboard',
                        'admin.dashboard',
                        'layout-dashboard',
                        'Dashboard',
                    ],
                    [
                        'manage_users',
                        'admin.users',
                        'users',
                        'Kelola Pengguna',
                    ],
                    [
                        'manage_permissions',
                        'permissions.index',
                        'shield-check',
                        'Hak Akses',
                    ],
                    [
                        'activity_logs',
                        'admin.logs',
                        'history',
                        'Log Aktivitas',
                    ],
                ],
            ];
        }
    @endphp

    <title>{{ $pageTitle }}</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        if (window.tailwind) {
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            sage: {
                                50: '#f4f7f5',
                                100: '#e3ebe6',
                                500: '#5a7c6d',
                                600: '#4a6b5d',
                                700: '#3c554a',
                                800: '#2d3f37',
                            },
                        },
                    },
                },
            };
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8F9FA;
        }

        #description-dialog::backdrop {
            background: rgba(15, 23, 42, 0.55);
        }
    </style>
</head>

<body class="text-slate-800 antialiased min-h-screen flex bg-slate-50">
    @if($showSidebar)
        <button
            id="cms-sidebar-backdrop"
            type="button"
            aria-label="Tutup navigasi"
            class="hidden lg:hidden fixed inset-0 z-30 bg-slate-950/40"
        ></button>

        <aside
            id="cms-sidebar"
            aria-label="Menu aplikasi"
            class="hidden lg:flex fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex-col"
        >
            <div class="px-5 pt-5 pb-4 shrink-0 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="bg-sage-600 text-white p-2.5 rounded-xl shadow-sm shrink-0">
                        <i
                            data-lucide="book-open-check"
                            class="w-6 h-6"
                        ></i>
                    </div>

                    <div class="min-w-0">
                        <h1 class="font-bold text-sm text-sage-800 tracking-tight leading-snug">
                            Case Management System
                        </h1>

                        <p class="text-[10px] text-slate-400 font-medium mt-1">
                            Laporan Kasus Psikologi
                        </p>
                    </div>
                </div>

                <button
                    id="cms-close-sidebar"
                    type="button"
                    class="lg:hidden mt-4 text-xs font-semibold text-slate-500"
                >
                    Tutup Menu ×
                </button>
            </div>

            <nav
                aria-label="Navigasi utama"
                class="flex-1 min-h-0 overflow-y-auto px-3 py-4 space-y-5"
            >
                @foreach($navigationGroups as $groupLabel => $links)
                    @php
                        $visibleLinks = collect($links)->filter(
                            fn ($link) => $user->permits($link[0])
                        );
                    @endphp

                    @if($visibleLinks->isNotEmpty())
                        <section>
                            <h2 class="px-3 mb-2 text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                                {{ $groupLabel }}
                            </h2>

                            <div class="space-y-1">
                                @foreach($visibleLinks as [$permission, $route, $icon, $label])
                                    <a
                                        href="{{ route($route) }}"
                                        @if(request()->routeIs($route))
                                            aria-current="page"
                                        @endif
                                        class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[11px] font-semibold transition {{ request()->routeIs($route) ? 'bg-sage-50 text-sage-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                                    >
                                        <i
                                            data-lucide="{{ $icon }}"
                                            class="w-4 h-4 shrink-0"
                                        ></i>

                                        <span class="flex-1 min-w-0">
                                            {{ $label }}
                                        </span>

                                        @if($route === 'lecturer.requests' && $pendingAccessCount > 0)
                                            <span
                                                id="access-request-badge"
                                                aria-label="{{ $pendingAccessCount }} permohonan menunggu persetujuan"
                                                title="{{ $pendingAccessCount }} permohonan menunggu"
                                                class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-amber-100 text-amber-800 text-[9px] font-bold shrink-0"
                                            >
                                                {{ $pendingAccessCount > 99 ? '99+' : $pendingAccessCount }}
                                            </span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach
            </nav>

            <div class="p-4 border-t border-slate-100 bg-slate-50/50 shrink-0">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-800 truncate">
                            {{ $user->name }}
                        </p>

                        <span class="inline-block mt-1 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-sage-100 text-sage-700 rounded-md">
                            {{ config('cms.roles.'.$user->role, $user->role) }}
                        </span>
                    </div>

                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            title="Keluar"
                            aria-label="Keluar dari aplikasi"
                            class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                        >
                            <i
                                data-lucide="log-out"
                                class="w-4 h-4"
                            ></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
    @endif

    <div class="flex-1 flex flex-col min-h-screen min-w-0 {{ $showSidebar ? 'lg:ml-64' : '' }}">
        @if($showSidebar)
            <button
                id="cms-open-sidebar"
                type="button"
                aria-label="Buka navigasi"
                aria-controls="cms-sidebar"
                aria-expanded="false"
                class="lg:hidden p-3 bg-white border-b text-xs font-semibold text-left text-slate-600"
            >
                ☰ Menu
            </button>
        @endif

        <main class="flex-grow p-4 sm:p-8 min-w-0">
            @if(session('success'))
                <div
                    role="status"
                    class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs"
                >
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div
                    role="alert"
                    class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-100 text-rose-800 text-xs"
                >
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div
                    role="alert"
                    class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-100 text-rose-800 text-xs"
                >
                    <p class="font-bold">
                        Periksa isian berikut:
                    </p>

                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                    @if(request()->routeIs('lecturer.upload*', 'reports.edit', 'reports.update'))
                        <p class="mt-2">
                            Jika mengunggah atau mengganti PDF,
                            pilih ulang file sebelum mengirim.
                        </p>
                    @endif
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="bg-white border-t border-slate-200/80 py-4 px-4 sm:px-8 text-[11px] text-slate-400 flex flex-wrap justify-between items-center gap-3">
            <span>
                &copy; {{ date('Y') }} Fakultas Psikologi —
                Case Management System
            </span>

            <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded">
                Internal
            </span>
        </footer>
    </div>

    <dialog
        id="description-dialog"
        aria-labelledby="description-dialog-title"
        class="rounded-2xl p-6 w-[calc(100%-2rem)] max-w-xl max-h-[85vh] overflow-y-auto shadow-xl"
    >
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2
                id="description-dialog-title"
                class="font-bold text-lg"
            >
                Detail deskripsi
            </h2>

            <form method="dialog">
                <button
                    type="submit"
                    aria-label="Tutup detail"
                    class="px-3 py-1.5 rounded-lg bg-slate-100 text-xs font-semibold text-slate-600"
                >
                    Tutup
                </button>
            </form>
        </div>

        <p
            id="description-content"
            class="whitespace-pre-wrap text-sm text-slate-600 break-words"
        ></p>
    </dialog>

    <script>
        window.lucide?.createIcons();

        const sidebar = document.getElementById(
            'cms-sidebar'
        );

        const sidebarBackdrop = document.getElementById(
            'cms-sidebar-backdrop'
        );

        const openSidebarButton = document.getElementById(
            'cms-open-sidebar'
        );

        const closeSidebarButton = document.getElementById(
            'cms-close-sidebar'
        );

        function setSidebarOpen(open) {
            if (!sidebar) {
                return;
            }

            sidebar.classList.toggle(
                'hidden',
                !open
            );

            sidebar.classList.toggle(
                'flex',
                open
            );

            sidebarBackdrop.classList.toggle(
                'hidden',
                !open
            );

            openSidebarButton.setAttribute(
                'aria-expanded',
                String(open)
            );

            if (open) {
                closeSidebarButton.focus();
            }
        }

        openSidebarButton?.addEventListener(
            'click',
            function () {
                setSidebarOpen(true);
            }
        );

        closeSidebarButton?.addEventListener(
            'click',
            function () {
                setSidebarOpen(false);
                openSidebarButton.focus();
            }
        );

        sidebarBackdrop?.addEventListener(
            'click',
            function () {
                setSidebarOpen(false);
            }
        );

        document.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.key === 'Escape'
                    && sidebar
                    && !sidebar.classList.contains('hidden')
                ) {
                    setSidebarOpen(false);
                }
            }
        );

        window.addEventListener(
            'resize',
            function () {
                if (window.innerWidth >= 1024) {
                    setSidebarOpen(false);
                }
            }
        );

        function updateDescriptionButtons() {
            document.querySelectorAll(
                '.description-snippet'
            ).forEach(function (block) {
                const text = block.querySelector(
                    '.description-text'
                );

                const button = block.querySelector(
                    '.description-more'
                );

                if (!text || !button) {
                    return;
                }

                button.classList.toggle(
                    'hidden',
                    text.scrollHeight <= text.clientHeight + 1
                );
            });
        }

        const mainContent = document.querySelector('main');

        if (mainContent) {
            const observer = new MutationObserver(
                function () {
                    requestAnimationFrame(
                        updateDescriptionButtons
                    );
                }
            );

            observer.observe(mainContent, {
                childList: true,
                subtree: true,
            });
        }

        window.addEventListener(
            'resize',
            updateDescriptionButtons
        );

        window.addEventListener(
            'load',
            updateDescriptionButtons
        );

        if (document.fonts) {
            document.fonts.ready.then(
                updateDescriptionButtons
            );
        }

        requestAnimationFrame(
            updateDescriptionButtons
        );

        document.addEventListener(
            'click',
            function (event) {
                const button = event.target.closest(
                    '[data-description]'
                );

                if (!button) {
                    return;
                }

                document.getElementById(
                    'description-content'
                ).textContent = button.dataset.description || '';

                document.getElementById(
                    'description-dialog'
                ).showModal();
            }
        );
    </script>
</body>
</html>