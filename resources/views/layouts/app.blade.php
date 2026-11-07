<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Repository Laporan Kasus PSY' }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
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
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8F9FA;
        }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex bg-slate-50">

    @auth
    <!-- SIDEBAR LEFT (Hanya tampil jika user sudah login & BUKAN di halaman login) -->
    @if(!request()->routeIs('login'))
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between fixed inset-y-0 left-0 z-40">
        <div class="p-5 space-y-6">
            <!-- App Brand Logo -->
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-100">
                <div class="bg-sage-600 text-white p-2.5 rounded-xl shadow-sm">
                    <i data-lucide="book-open-check" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="font-bold text-base text-sage-800 tracking-tight leading-none">PSY Archive</h1>
                    <span class="text-[11px] text-slate-400 font-medium">Laporan Kasus Psikologi</span>
                </div>
            </div>

            <!-- Navigation Links Based on Role -->
            <nav class="space-y-1">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-2">Navigasi Utama</p>

                <!-- Menu Mahasiswa & Umum -->
                <a href="{{ route('student.catalog') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('student.catalog*') ? 'bg-sage-50 text-sage-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="compass" class="w-4 h-4"></i>
                    <span>Katalog Dokumen</span>
                </a>

                <a href="{{ route('student.bookmarks') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('student.bookmarks') ? 'bg-sage-50 text-sage-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="bookmark" class="w-4 h-4"></i>
                    <span>Simpanan Saya</span>
                </a>

                {{-- Permohonan Akses HANYA TAMPIL untuk Mahasiswa --}}
                @if(Auth::user()->isMahasiswa())
                    <a href="{{ route('student.requests') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('student.requests') ? 'bg-sage-50 text-sage-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="file-question" class="w-4 h-4"></i>
                        <span>Permohonan Akses</span>
                    </a>
                @endif

                <!-- Menu Khusus Dosen & Admin -->
                @if(
                    Auth::user()->isDosen() ||
                    Auth::user()->isAdmin() ||
                    Auth::user()->hasUploadPermission()
                )
                    <div class="pt-4 border-t border-slate-100 my-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-2">
                            Area Dosen
                        </p>

                        {{-- Upload Laporan --}}
                        <a href="{{ route('lecturer.upload') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('lecturer.upload') ? 'bg-sage-50 text-sage-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                            <span>Upload Laporan</span>
                        </a>

                        {{-- Kelola Kategori hanya untuk Dosen/Admin --}}
                        @if(Auth::user()->isDosen() || Auth::user()->isAdmin())

                            <a href="{{ route('lecturer.categories') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('lecturer.categories') ? 'bg-sage-50 text-sage-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i data-lucide="folder-tree" class="w-4 h-4"></i>
                                <span>Kelola Kategori</span>
                            </a>

                            {{-- Persetujuan Akses hanya untuk Dosen/Admin --}}
                            <a href="{{ route('lecturer.requests') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('lecturer.requests') ? 'bg-sage-50 text-sage-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i data-lucide="check-square" class="w-4 h-4"></i>
                                <span>Persetujuan Akses</span>
                            </a>

                        @endif
                    </div>
                @endif

                <!-- Menu Khusus Admin -->
                @if(Auth::user()->role === 'admin')
                    <div class="pt-4 border-t border-slate-100 my-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-2">Panel Admin</p>
                        
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-sage-50 text-sage-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Dashboard Ringkasan</span>
                        </a>

                        <a href="{{ route('admin.users') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.users') ? 'bg-sage-50 text-sage-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            <span>Kelola Pengguna</span>
                        </a>

                        <a href="{{ route('admin.logs') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.logs') ? 'bg-sage-50 text-sage-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i data-lucide="history" class="w-4 h-4"></i>
                            <span>Log Aktivitas</span>
                        </a>
                    </div>
                @endif
            </nav>
        </div>

        <!-- User Profile & Logout Bottom Box -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            <div class="flex items-center justify-between">
                <div class="overflow-hidden pr-2">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                    <span class="inline-block mt-0.5 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-sage-100 text-sage-700 rounded-md">
                        {{ Auth::user()->role }}
                    </span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Keluar">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>
    @endif
    @endauth

    <!-- MAIN CONTENT AREA -->
    <div class="flex-1 flex flex-col min-h-screen @auth @if(!request()->routeIs('login')) ml-64 @endif @endauth">
        <main class="flex-grow p-6 sm:p-8 flex flex-col justify-center">
            @yield('content')
        </main>

        <footer class="bg-white border-t border-slate-200/80 py-4 px-8 text-xs text-slate-400 flex justify-between items-center">
            <span>&copy; {{ date('Y') }} Fakultas Psikologi - Internal Case Reports Repository</span>
            <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded">v1.0.0</span>
        </footer>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>