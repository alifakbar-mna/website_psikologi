@if(request()->ajax())
    <!-- Section ini khusus dikembalikan saat request AJAX (tanpa reload) -->
    <div class="flex items-center justify-between mb-4">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
            Menampilkan {{ $documents->count() }} Dokumen
        </p>
    </div>

    @if($documents->isEmpty())
        <div class="bg-white p-12 text-center rounded-2xl border border-slate-200 space-y-3">
            <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto">
                <i data-lucide="file-x" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-700">Tidak ada dokumen ditemukan</h3>
            <p class="text-xs text-slate-400">Coba ubah kata kunci atau kata pencarian filter Anda.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($documents as $doc)
                @php
                    $user = Auth::user();
                    $isMahasiswa = $user && $user->isMahasiswa();
                    $hasReadAccess = !$isMahasiswa || $user->hasAccessToDocument($doc->id);
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 flex flex-col justify-between hover:border-sage-500 hover:shadow-md transition duration-200 group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 text-[10px] font-semibold bg-sage-50 text-sage-700 rounded-lg truncate max-w-[150px]">
                                {{ $doc->category->name }}
                            </span>
                            <span class="text-xs font-medium text-slate-400">{{ $doc->tahun }}</span>
                        </div>

                        <h3 class="text-sm font-bold text-slate-800 line-clamp-2 leading-snug group-hover:text-sage-700 transition">
                            {{ $doc->title }}
                        </h3>

                        <div class="text-xs text-slate-500 space-y-1 pt-1 border-t border-slate-100">
                            <p class="truncate"><span class="text-slate-400">Pembimbing:</span> {{ $doc->dosen_pembimbing }}</p>
                            <p><span class="text-slate-400">Angkatan:</span> {{ $doc->angkatan_pembuat ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center space-x-3 text-slate-400 text-xs">
                            <span class="flex items-center gap-1">
                                <i data-lucide="heart" class="w-3.5 h-3.5 text-rose-400"></i> 
                                {{ $doc->likes ? $doc->likes->count() : 0 }}
                            </span>
                        </div>

                        @if($hasReadAccess)
                            <a href="{{ route('student.document.show', $doc->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sage-50 hover:bg-sage-600 text-sage-700 hover:text-white rounded-lg text-xs font-bold transition">
                                <span>Baca</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @else
                            <button type="button" onclick="openRequestModal('{{ $doc->id }}', '{{ addslashes($doc->title) }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white rounded-lg text-xs font-bold transition">
                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                <span>Request Baca</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-4 pagination">
            {{ $documents->links() }}
        </div>
    @endif
@else
    <!-- Layout Utama Tampilan Normal (First Load) -->
    @extends('layouts.app', ['title' => 'Katalog Dokumen - Repository PSY'])

    @section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- Header & Search Bar Section -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-sm space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Katalog Laporan Kasus</h1>
                <p class="text-sm text-slate-500 mt-1">Cari dan temukan referensi laporan kasus psikologi dari kakak tingkat.</p>
            </div>

            <!-- Form Search & Live Filter -->
            <form id="filterForm" action="{{ route('student.catalog') }}" method="GET" onsubmit="return false;" class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-grow">
                        <i data-lucide="search" class="w-5 h-5 absolute left-3.5 top-3.5 text-slate-400"></i>
                        <input type="text" name="search" id="searchInput" value="{{ request('search') }}" 
                            placeholder="Cari judul dokumen atau kata kunci..." 
                            class="auto-filter w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sage-500/20 focus:border-sage-600 transition">
                    </div>
                </div>

                <!-- Extended Filters Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 border-t border-slate-100">
                    <!-- Filter Kategori -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Kategori</label>
                        <select name="category_id" class="auto-filter w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Tahun Dokumen -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Tahun Dokumen</label>
                        <input type="number" name="tahun" placeholder="Contoh: 2024" value="{{ request('tahun') }}"
                            class="auto-filter w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
                    </div>

                    <!-- Filter Dosen Pembimbing -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Dosen Pembimbing</label>
                        <input type="text" name="dosen" placeholder="Nama Dosen..." value="{{ request('dosen') }}"
                            class="auto-filter w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
                    </div>

                    <!-- Filter Angkatan Pembuat -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Angkatan Pembuat</label>
                        <input type="number" name="angkatan" placeholder="Contoh: 2021" value="{{ request('angkatan') }}"
                            class="auto-filter w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-xs text-slate-700 focus:outline-none focus:border-sage-600">
                    </div>
                </div>
                
                <div id="resetContainer" class="flex justify-end pt-1 {{ request()->anyFilled(['search', 'category_id', 'tahun', 'dosen', 'angkatan']) ? '' : 'hidden' }}">
                    <button type="button" id="resetBtn" class="text-xs text-rose-500 hover:underline">Reset Filter</button>
                </div>
            </form>
        </div>

        <!-- Main Content Container -->
        <div class="grid grid-cols-1 space-y-6">
            <div id="catalogContent">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        Menampilkan {{ $documents->count() }} Dokumen
                    </p>
                </div>

                @if($documents->isEmpty())
                    <div class="bg-white p-12 text-center rounded-2xl border border-slate-200 space-y-3">
                        <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto">
                            <i data-lucide="file-x" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-base font-semibold text-slate-700">Tidak ada dokumen ditemukan</h3>
                        <p class="text-xs text-slate-400">Coba ubah kata kunci atau kata pencarian filter Anda.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        @foreach($documents as $doc)
                            @php
                                $user = Auth::user();
                                $isMahasiswa = $user && $user->isMahasiswa();
                                $hasReadAccess = !$isMahasiswa || $user->hasAccessToDocument($doc->id);
                            @endphp
                            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 flex flex-col justify-between hover:border-sage-500 hover:shadow-md transition duration-200 group">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="px-2.5 py-1 text-[10px] font-semibold bg-sage-50 text-sage-700 rounded-lg truncate max-w-[150px]">
                                            {{ $doc->category->name }}
                                        </span>
                                        <span class="text-xs font-medium text-slate-400">{{ $doc->tahun }}</span>
                                    </div>

                                    <h3 class="text-sm font-bold text-slate-800 line-clamp-2 leading-snug group-hover:text-sage-700 transition">
                                        {{ $doc->title }}
                                    </h3>

                                    <div class="text-xs text-slate-500 space-y-1 pt-1 border-t border-slate-100">
                                        <p class="truncate"><span class="text-slate-400">Pembimbing:</span> {{ $doc->dosen_pembimbing }}</p>
                                        <p><span class="text-slate-400">Angkatan:</span> {{ $doc->angkatan_pembuat ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                                    <div class="flex items-center space-x-3 text-slate-400 text-xs">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="heart" class="w-3.5 h-3.5 text-rose-400"></i> 
                                            {{ $doc->likes ? $doc->likes->count() : 0 }}
                                        </span>
                                    </div>

                                    @if($hasReadAccess)
                                        <a href="{{ route('student.document.show', $doc->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sage-50 hover:bg-sage-600 text-sage-700 hover:text-white rounded-lg text-xs font-bold transition">
                                            <span>Baca</span>
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                        </a>
                                    @else
                                        <button type="button" onclick="openRequestModal('{{ $doc->id }}', '{{ addslashes($doc->title) }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white rounded-lg text-xs font-bold transition">
                                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                            <span>Request Baca</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4 pagination">
                        {{ $documents->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL REQUEST BACA -->
    <div id="requestModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl space-y-4 border border-slate-100">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800">Permohonan Izin Baca Dokumen</h3>
                <button type="button" onclick="closeRequestModal()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('student.requests.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="request_type" value="baca_dokumen">
                <input type="hidden" name="document_id" id="modalDocumentId">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Dokumen Target</label>
                    <input type="text" id="modalDocumentTitle" readonly class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Alasan Permohonan</label>
                    <textarea name="reason" rows="3" required placeholder="Tuliskan alasan mengapa Anda ingin membaca dokumen ini..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-700 focus:outline-none focus:border-sage-600"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeRequestModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sage-600 hover:bg-sage-700 text-white rounded-xl text-xs font-semibold transition">Kirim Request</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openRequestModal(id, title) {
        document.getElementById('modalDocumentId').value = id;
        document.getElementById('modalDocumentTitle').value = title;
        document.getElementById('requestModal').classList.remove('hidden');
    }
    function closeRequestModal() {
        document.getElementById('requestModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const filterForm = document.getElementById('filterForm');
        const inputs = filterForm.querySelectorAll('.auto-filter');
        const catalogContent = document.getElementById('catalogContent');
        const resetBtn = document.getElementById('resetBtn');
        const resetContainer = document.getElementById('resetContainer');

        let debounceTimer = null;

        function fetchDocuments(url = filterForm.action) {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);

            const newUrl = `${url.split('?')[0]}?${params.toString()}`;
            window.history.pushState({}, '', newUrl);

            let isAnyFilled = Array.from(formData.values()).some(val => val.trim() !== '');
            if (isAnyFilled) {
                resetContainer.classList.remove('hidden');
            } else {
                resetContainer.classList.add('hidden');
            }

            fetch(newUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                catalogContent.innerHTML = html;
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            })
            .catch(err => console.error('Error fetching documents:', err));
        }

        inputs.forEach(input => {
            const eventType = input.tagName === 'SELECT' ? 'change' : 'input';
            
            input.addEventListener(eventType, function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchDocuments();
                }, 300);
            });
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                filterForm.reset();
                inputs.forEach(i => i.value = '');
                fetchDocuments();
            });
        }

        document.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('#catalogContent .pagination a');
            if (paginationLink) {
                e.preventDefault();
                fetchDocuments(paginationLink.href);
            }
        });
    });
    </script>
    @endsection
@endif