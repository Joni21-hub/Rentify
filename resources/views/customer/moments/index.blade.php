@extends('layouts.app')

@section('title', 'Rentify Moments — Feed Komunitas Rental')

@push('head')
<style>
    html, body {
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        background-color: #000000 !important;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .scrollbar-none {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .scrollbar-none::-webkit-scrollbar {
        display: none;
    }
    .glass-rail-btn {
        background: rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    .glass-product-card {
        background: rgba(15, 23, 42, 0.78);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.22);
    }
    @keyframes heartBurst {
        0% { transform: translate(-50%, -50%) scale(0); opacity: 0; }
        50% { transform: translate(-50%, -50%) scale(1.35); opacity: 0.95; }
        100% { transform: translate(-50%, -50%) scale(1.6); opacity: 0; }
    }
    .animate-heart-burst {
        animation: heartBurst 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
    @keyframes pulseCenterIcon {
        0% { transform: scale(0.7); opacity: 0; }
        50% { transform: scale(1.15); opacity: 0.9; }
        100% { transform: scale(1.3); opacity: 0; }
    }
    .animate-pulse-center {
        animation: pulseCenterIcon 0.45s ease-out forwards;
    }
</style>
@endpush

@section('content')
<div id="rentify-page-content" data-tab="momen" class="fixed inset-0 w-full h-[100dvh] bg-black overflow-hidden select-none" id="moments-app">

    <!-- MAIN VERTICAL SNAP FEED (TikTok / IG Reels Fullscreen Experience) -->
    <main 
        id="feed-container" 
        class="w-full h-[100dvh] max-w-lg mx-auto bg-black relative overflow-y-scroll snap-y snap-mandatory scrollbar-none"
        style="scroll-snap-type: y mandatory; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch;"
    >
        <!-- Feed Skeleton Loading Reels -->
        <div id="feed-skeleton" class="w-full h-[100dvh] relative bg-slate-950 flex flex-col items-center justify-center animate-pulse snap-start snap-always shrink-0">
            <!-- Top Right Action in Skeleton -->
            <div class="absolute top-4 right-4 z-20 pointer-events-auto">
                <button onclick="handleFabClick()" class="glass-rail-btn w-9 h-9 rounded-full flex items-center justify-center text-white hover:text-sky-300 active:scale-95 transition shadow-lg group" title="Upload Momen" aria-label="Upload Momen">
                    <i class="fa-solid fa-plus text-xs group-hover:scale-110 transition-transform"></i>
                </button>
            </div>
            <div class="w-12 h-12 rounded-full border-4 border-sky-500/20 border-t-sky-500 animate-spin mb-4"></div>
            <p class="text-xs font-bold text-slate-400">Memuat momen terbaik...</p>
            <div class="absolute bottom-28 left-4 right-16 space-y-2">
                <div class="h-4 bg-slate-800 rounded-full w-28"></div>
                <div class="h-3 bg-slate-800 rounded-full w-44"></div>
                <div class="h-3 bg-slate-800 rounded-full w-36"></div>
            </div>
        </div>
    </main>

    <!-- Loading Indicator Infinite Scroll -->
    <div id="scroll-loading" class="hidden absolute top-4 left-1/2 -translate-x-1/2 z-30 bg-black/60 backdrop-blur-md rounded-full px-3 py-1.5 text-white text-[11px] font-bold border border-white/10 shadow-lg">
        <i class="fa-solid fa-circle-notch fa-spin text-sky-400 mr-1.5"></i> Memuat...
    </div>

    <!-- End of Feed Message -->
    <div id="end-of-feed" class="hidden pointer-events-none absolute bottom-24 left-1/2 -translate-x-1/2 z-30 bg-black/75 backdrop-blur-md text-white/90 border border-white/15 rounded-full px-4 py-1.5 text-xs font-semibold shadow-xl">
        <i class="fa-solid fa-check text-sky-400 mr-1.5"></i> Semua momen telah ditampilkan
    </div>

    <!-- ============================================================== -->
    <!-- DRAWER: KOMENTAR MOMEN (TIKTOK / REELS BOTTOM SHEET) -->
    <!-- ============================================================== -->
    <div id="comments-drawer" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex flex-col justify-end transition-opacity duration-200">
        <!-- Backdrop click area -->
        <div class="flex-1 w-full" onclick="closeCommentsDrawer()"></div>

        <!-- Bottom Sheet Content -->
        <div class="w-full max-w-lg mx-auto bg-slate-900/95 backdrop-blur-2xl rounded-t-3xl border-t border-white/15 shadow-2xl flex flex-col max-h-[70vh] min-h-[45vh] text-white overflow-hidden">
            <!-- Handle & Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-comment-dots text-sky-400 text-sm"></i>
                    <h3 class="font-black text-sm text-white">
                        Komentar (<span id="drawer-comments-count">0</span>)
                    </h3>
                </div>
                <button onclick="closeCommentsDrawer()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Comments Scroll Area -->
            <div id="comments-list" class="flex-1 overflow-y-auto p-4 space-y-3.5 scrollbar-none">
                <!-- Dynamically populated via loadComments -->
            </div>

            <!-- Comment Input Box -->
            <div class="p-3 bg-slate-950/90 border-t border-white/10 shrink-0">
                <form id="comment-form" onsubmit="submitComment(event)" class="flex items-center gap-2">
                    <input type="hidden" id="active-comment-moment-id">
                    <input 
                        type="text" 
                        id="comment-input-text" 
                        maxlength="500" 
                        placeholder="Tulis komentar kamu..." 
                        class="flex-1 bg-white/10 border border-white/15 rounded-full px-4 py-2 text-xs text-white placeholder-white/40 focus:outline-none focus:border-sky-400 focus:bg-white/15 transition"
                        autocomplete="off"
                    >
                    <button type="submit" id="comment-submit-btn" class="w-9 h-9 rounded-full bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white flex items-center justify-center shrink-0 shadow-md active:scale-95 transition">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: GUEST AUTHENTICATION PROMPT -->
    <!-- ============================================================== -->
    <div id="guest-auth-modal" class="hidden fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl space-y-4 border border-slate-100">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-2xl shadow-inner">
                <i class="fa-solid fa-user-lock"></i>
            </div>
            <div class="space-y-1">
                <h3 class="font-black text-lg text-slate-900">Masuk ke Rentify</h3>
                <p class="text-xs text-slate-500">Masuk untuk menyukai, menyimpan, dan berbagi pengalaman sewa barangmu!</p>
            </div>
            <div class="space-y-2 pt-2">
                <a href="{{ route('login') }}" class="block w-full py-3 rounded-2xl bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold text-xs uppercase tracking-wider shadow-md transition transform active:scale-95">
                    Masuk Sekarang
                </a>
                <a href="{{ route('register') }}" class="block w-full py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Daftar Akun Baru
                </a>
            </div>
            <button onclick="closeGuestAuthModal()" class="text-xs text-slate-400 hover:text-slate-600 font-medium">
                Nanti Saja
            </button>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: UPLOAD MOMEN BARU (SIMPEL & INTUITIF) -->
    <!-- ============================================================== -->
    <div id="upload-modal" class="hidden fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 my-8 border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-clapperboard"></i>
                    </div>
                    <h3 class="font-black text-base text-slate-900">Bagikan Momen</h3>
                </div>
                <button onclick="closeUploadModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Upload Form -->
            <form id="upload-moment-form" onsubmit="submitMomentUpload(event)" class="space-y-3.5">
                <!-- Media Picker & Live Preview -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Foto (Multi-Foto / Carousel) atau Video *</label>
                    <div id="drop-area" onclick="document.getElementById('media-file-input').click()" class="border-2 border-dashed border-sky-200 hover:border-sky-400 rounded-2xl p-6 text-center cursor-pointer transition bg-sky-50/40 hover:bg-sky-50/80">
                        <input type="file" id="media-file-input" accept="image/*,video/*,.heic,.heif,.*" multiple class="hidden" onchange="handleFileSelection(event)">
                        <div id="upload-prompt" class="space-y-2">
                            <div class="w-12 h-12 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center mx-auto text-xl shadow-xs">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-800">Pilih foto (bisa banyak / carousel) atau video</p>
                            <p class="text-[10px] text-slate-400">Pilih hingga 10 foto untuk postingan carousel bergaya Instagram atau 1 video tanpa batas ukuran</p>
                        </div>
                        <div id="media-preview-container" class="hidden space-y-2">
                            <!-- Multi-Photo Grid Thumbnails -->
                            <div id="multi-image-preview-grid" class="hidden grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-56 overflow-y-auto p-1.5 rounded-xl bg-slate-50/70 border border-slate-100 scrollbar-none"></div>
                            <!-- Single Preview (1 Foto atau 1 Video) -->
                            <div id="single-media-preview" class="hidden">
                                <img id="image-preview" class="hidden max-h-56 mx-auto rounded-xl object-contain shadow-xs">
                                <video id="video-preview" class="hidden max-h-56 mx-auto rounded-xl shadow-xs" controls></video>
                            </div>
                            <div class="flex items-center justify-between text-[11px] pt-1">
                                <span id="file-info-text" class="text-sky-700 font-bold"></span>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="event.stopPropagation(); document.getElementById('media-file-input').click()" class="text-[10px] font-bold text-sky-600 hover:text-sky-800 bg-sky-50 hover:bg-sky-100 px-2.5 py-1 rounded-full border border-sky-200 transition">
                                        <i class="fa-solid fa-plus text-[9px] mr-1"></i> Tambah Foto
                                    </button>
                                    <button type="button" onclick="event.stopPropagation(); resetSelectedMedia()" class="text-[10px] font-bold text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded-full border border-rose-200 transition">
                                        <i class="fa-solid fa-trash text-[9px] mr-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- HEIC conversion status -->
                    <div id="heic-converting-status" class="hidden mt-2 p-2 rounded-xl bg-sky-50 text-sky-700 text-xs flex items-center gap-2 font-medium">
                        <i class="fa-solid fa-circle-notch fa-spin text-sky-600"></i> Mengonversi foto iPhone (HEIC)...
                    </div>
                </div>

                <!-- Caption -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="caption-input" class="text-xs font-bold text-slate-700">Cerita / Caption</label>
                        <span id="caption-counter" class="text-[10px] text-slate-400">0/1000</span>
                    </div>
                    <textarea id="caption-input" rows="3" maxlength="1000" placeholder="Ceritakan pengalaman seru sewa barang ini..." class="w-full text-xs p-3 rounded-2xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none transition resize-none" oninput="updateCaptionCount(this)"></textarea>
                </div>

                <!-- Product Tag Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tandai Barang yang Disewa (Opsional)</label>
                    <select id="product-tag-select" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none transition mb-2" onchange="addProductTag(this)">
                        <option value="">-- Pilih barang rental --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" data-nama="{{ $prod->nama }}" data-harga="{{ number_format($prod->harga_sewa_harian ?? 0, 0, ',', '.') }}">
                                {{ $prod->nama }} (Rp {{ number_format($prod->harga_sewa_harian ?? 0, 0, ',', '.') }}/hari)
                            </option>
                        @endforeach
                    </select>
                    <div id="selected-tags-container" class="flex flex-wrap gap-1.5"></div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" id="submit-moment-btn" class="w-full py-3 rounded-2xl bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold text-xs uppercase tracking-wider shadow-md transition flex items-center justify-center gap-2">
                        <span id="submit-btn-text">Publikasikan Momen</span>
                        <i id="submit-btn-spinner" class="fa-solid fa-circle-notch fa-spin hidden"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: LAPORKAN POSTINGAN -->
    <!-- ============================================================== -->
    <div id="report-modal" class="hidden fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4 border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-flag text-rose-500"></i> Laporkan Postingan
                </h3>
                <button onclick="closeReportModal()" class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
            <form id="report-form" onsubmit="submitReport(event)" class="space-y-3">
                <input type="hidden" id="report-moment-id">
                <div class="space-y-2 text-xs">
                    <label class="block font-bold text-slate-700">Pilih Alasan Laporan *</label>
                    <label class="flex items-center gap-2.5 p-2 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="report_category" value="spam" required class="text-sky-600 focus:ring-0">
                        <span>Spam / Iklan Mengganggu</span>
                    </label>
                    <label class="flex items-center gap-2.5 p-2 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="report_category" value="inappropriate" class="text-sky-600 focus:ring-0">
                        <span>Konten Tidak Pantas</span>
                    </label>
                    <label class="flex items-center gap-2.5 p-2 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="report_category" value="fraud" class="text-sky-600 focus:ring-0">
                        <span>Penipuan / Barang Palsu</span>
                    </label>
                    <label class="flex items-center gap-2.5 p-2 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="report_category" value="copyright" class="text-sky-600 focus:ring-0">
                        <span>Pelanggaran Hak Cipta</span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Tambahan (Opsional)</label>
                    <textarea id="report-reason-text" rows="2" maxlength="1000" placeholder="Jelaskan secara singkat..." class="w-full text-xs p-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none transition resize-none"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" id="submit-report-btn" class="w-full py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition">
                        Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    </div> <!-- END WRAPPER KONTEN TAB MOMEN -->

    <!-- BOTTOM NAV (3 TAB RAMPING: BERANDA, MOMEN AKTIF, AKUN) -->
    <x-bottom-navigation-bar active="momen" />

<!-- ============================================================== -->
<!-- JAVASCRIPT: FEED LOGIC, FULLSCREEN SNAP REELS, AUDIO & GESTURES -->
<!-- ============================================================== -->
<script>
    const IS_AUTHENTICATED = @json(Auth::check());
    const CURRENT_USER_ID = @json(Auth::id());
    const CURRENT_USER_ROLE = @json(Auth::user()->role ?? null);
    const CSRF_TOKEN = '{{ csrf_token() }}';

    let nextCursor = null;
    let hasMore = true;
    let isLoading = false;
    let selectedFiles = [];
    let selectedFile = null;
    let selectedVideoDuration = null;
    let taggedProductIds = [];

    // Global Audio State (muted by default for browser autoplay policy)
    let isGlobalMuted = true;
    let currentPlayingVideo = null;

    // Gesture Tracking for Double Tap
    let lastTapTime = 0;
    let tapTimeout = null;

    // Intersection Observer untuk Autoplay Video saat masuk viewport
    const reelObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const card = entry.target;
            const video = card.querySelector('video');
            if (!video) return;

            if (entry.isIntersecting && entry.intersectionRatio >= 0.6) {
                if (currentPlayingVideo && currentPlayingVideo !== video) {
                    currentPlayingVideo.pause();
                }
                currentPlayingVideo = video;
                video.muted = isGlobalMuted;
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.catch(() => {
                        video.muted = true;
                        video.play().catch(() => {});
                    });
                }
            } else {
                if (currentPlayingVideo === video) {
                    currentPlayingVideo = null;
                }
                video.pause();
                video.currentTime = 0;
            }
        });
    }, { threshold: [0.6] });

    // Expose controller global untuk transisi mulus antar-tab tanpa reload
    window.RentifyMoments = {
        pause: function() {
            if (currentPlayingVideo) {
                try { currentPlayingVideo.pause(); } catch(e){}
            }
            document.querySelectorAll('#moments-app video').forEach(v => {
                try { v.pause(); } catch(e){}
            });
        },
        resume: function() {
            if (currentPlayingVideo) {
                try { currentPlayingVideo.play().catch(() => {}); } catch(e){}
            } else {
                const firstVideo = document.querySelector('#feed-container video');
                if (firstVideo) {
                    try { firstVideo.play().catch(() => {}); } catch(e){}
                }
            }
        },
        init: function() {
            if (window.__momentsInitialized) {
                window.RentifyMoments.resume();
                return;
            }
            window.__momentsInitialized = true;
            loadFeedBatch(true);
            setupInfiniteScroll();
            setupNavBarScrollBehavior();
            setupHorizontalSwipeNavigation();

            // Cek URL parameter ?upload=1
            if (new URLSearchParams(window.location.search).get('upload') === '1') {
                handleFabClick();
            }
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", window.RentifyMoments.init);
    } else {
        window.RentifyMoments.init();
    }

    /**
     * Memuat kumpulan momen dari endpoint API.
     */
    async function loadFeedBatch(isInitial = false) {
        if (isLoading || (!hasMore && !isInitial)) return;
        isLoading = true;

        if (!isInitial) {
            document.getElementById('scroll-loading')?.classList.remove('hidden');
        }

        try {
            let result = null;
            // Gunakan pre-cache jika tersedia dari prefetch idle tab bar
            if (isInitial && window.__rentifyInitialMomentsCache) {
                result = window.__rentifyInitialMomentsCache;
            } else {
                let url = '/api/moments?limit=8';
                if (nextCursor) {
                    url += `&cursor=${encodeURIComponent(nextCursor)}`;
                }

                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                result = await response.json();
            }

            if (result.success) {
                if (isInitial) {
                    document.getElementById('feed-skeleton')?.remove();
                    if (!result.data || result.data.length === 0) {
                        renderEmptyState();
                    }
                }

                renderMoments(result.data);
                nextCursor = result.next_cursor;
                hasMore = result.has_more;

                // Deep linking: jika pengguna datang dari profil memilih momen tertentu
                if (isInitial) {
                    const targetHash = window.location.hash;
                    const targetParam = new URLSearchParams(window.location.search).get('moment_id');
                    const targetId = targetHash ? targetHash.replace('#', '') : (targetParam ? `moment-card-${targetParam}` : null);
                    if (targetId) {
                        setTimeout(() => {
                            const targetEl = document.getElementById(targetId);
                            if (targetEl) {
                                targetEl.scrollIntoView({ behavior: 'smooth' });
                            }
                        }, 250);
                    }
                }

                if (!hasMore && result.data && result.data.length > 0) {
                    document.getElementById('end-of-feed')?.classList.remove('hidden');
                    setTimeout(() => {
                        document.getElementById('end-of-feed')?.classList.add('hidden');
                    }, 3500);
                }
            }
        } catch (error) {
            console.error('Gagal memuat feed momen:', error);
        } finally {
            isLoading = false;
            document.getElementById('scroll-loading')?.classList.add('hidden');
        }
    }

    /**
     * Tampilan saat feed kosong.
     */
    function renderEmptyState() {
        const container = document.getElementById('feed-container');
        container.innerHTML = `
            <div class="w-full h-[100dvh] flex flex-col items-center justify-center p-6 text-center text-white snap-start relative">
                <!-- Top Right Upload (+) button in empty state -->
                <div class="absolute top-4 right-4 z-20 pointer-events-auto">
                    <button onclick="handleFabClick()" class="glass-rail-btn w-9 h-9 rounded-full flex items-center justify-center text-white hover:text-sky-300 active:scale-95 transition shadow-lg group" title="Upload Momen" aria-label="Upload Momen">
                        <i class="fa-solid fa-plus text-xs group-hover:scale-110 transition-transform"></i>
                    </button>
                </div>
                <div class="w-20 h-20 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-3xl text-sky-400 mb-4 shadow-2xl">
                    <i class="fa-solid fa-clapperboard"></i>
                </div>
                <h3 class="text-lg font-black mb-1">Belum Ada Momen</h3>
                <p class="text-xs text-slate-400 max-w-xs mb-6">Jadilah yang pertama membagikan foto atau video pengalaman sewa barang di Rentify!</p>
                <button onclick="handleFabClick()" class="px-6 py-3 rounded-full bg-gradient-to-r from-sky-400 to-sky-600 text-white font-black text-xs uppercase tracking-wider shadow-lg hover:from-sky-500 hover:to-sky-700 transition transform active:scale-95">
                    Bagikan Momen Pertama
                </button>
            </div>
        `;
    }

    /**
     * Render kartu-kartu momen ke dalam DOM dengan gaya TikTok / Instagram Reels Fullscreen.
     */
    function renderMoments(moments) {
        if (!moments || moments.length === 0) return;
        const container = document.getElementById('feed-container');

        moments.forEach(m => {
            if (document.getElementById(`moment-card-${m.id}`)) return;

            const card = document.createElement('article');
            card.id = `moment-card-${m.id}`;
            card.className = "moment-reel relative w-full h-[100dvh] snap-start snap-always shrink-0 overflow-hidden bg-black flex items-center justify-center select-none";

            const isAuthorOrAdmin = (IS_AUTHENTICATED && (m.author.id === CURRENT_USER_ID || CURRENT_USER_ROLE === 'admin'));

            // Media section
            let mediaHtml = '';
            if (m.media_type === 'video') {
                mediaHtml = `
                    <div class="relative w-full h-full bg-black flex items-center justify-center overflow-hidden">
                        <video 
                            src="${m.media_url}" 
                            poster="${m.thumbnail_url || ''}" 
                            playsinline 
                            loop 
                            preload="metadata"
                            muted
                            class="w-full h-full object-cover pointer-events-none"
                        ></video>
                        <!-- Center Play/Pause Indicator -->
                        <div class="play-pause-indicator pointer-events-none absolute inset-0 flex items-center justify-center">
                            <div class="play-pause-pulse hidden w-16 h-16 rounded-full bg-black/60 backdrop-blur-md text-white text-2xl flex items-center justify-center shadow-2xl">
                                <i class="fa-solid fa-play ml-1"></i>
                            </div>
                        </div>
                        <!-- Video Bottom Progress Bar (2px) -->
                        <div class="absolute bottom-0 left-0 right-0 h-[2.5px] bg-white/20 z-20 pointer-events-none">
                            <div class="video-progress-bar h-full bg-sky-400 w-0 transition-all duration-100 ease-linear"></div>
                        </div>
                    </div>
                `;
            } else if (m.media_urls && Array.isArray(m.media_urls) && m.media_urls.length > 1) {
                // Carousel Post (Multi-Foto Instagram Reels Style)
                const totalSlides = m.media_urls.length;
                const fallbackMomentImg = 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1792881951/rentify/moments/naikdt1lgug5qgylrk1l.jpg';
                const slides = m.media_urls.map((url, sIdx) => `
                    <div class="carousel-slide relative w-full h-full shrink-0 snap-center flex items-center justify-center overflow-hidden" data-slide-index="${sIdx}">
                        <img src="${url}" class="absolute inset-0 w-full h-full object-cover filter blur-2xl opacity-35 scale-110 pointer-events-none" onerror="this.onerror=null; this.src='${fallbackMomentImg}';">
                        <img src="${url}" alt="${m.caption ? m.caption.replace(/"/g, '&quot;') : 'Momen'}" loading="lazy" class="relative z-10 w-full h-full object-contain pointer-events-none" onerror="this.onerror=null; this.src='${fallbackMomentImg}';">
                    </div>
                `).join('');

                const dots = m.media_urls.map((_, dIdx) => `
                    <div class="carousel-dot-${m.id} transition-all duration-300 rounded-full ${dIdx === 0 ? 'w-4 h-1.5 bg-sky-400' : 'w-1.5 h-1.5 bg-white/50'}" data-dot-index="${dIdx}"></div>
                `).join('');

                mediaHtml = `
                    <div class="relative w-full h-full bg-black flex items-center justify-center overflow-hidden group/carousel" id="carousel-wrap-${m.id}">
                        <!-- Track Slide Horizontal Snap -->
                        <div class="carousel-track flex w-full h-full overflow-x-auto snap-x snap-mandatory scrollbar-none" id="carousel-track-${m.id}" onscroll="handleCarouselScroll(event, ${m.id}, ${totalSlides})" style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
                            ${slides}
                        </div>

                        <!-- Badge Counter Top-Right (e.g. 1/3) -->
                        <div class="absolute top-16 right-4 z-20 glass-pill px-2.5 py-1 rounded-full text-[11px] font-black text-white/95 shadow-xl pointer-events-none flex items-center gap-1.5 border border-white/20">
                            <i class="fa-solid fa-images text-sky-400 text-xs"></i>
                            <span id="carousel-counter-${m.id}">1/${totalSlides}</span>
                        </div>

                        <!-- Prev & Next Arrows (Desktop & Hover) -->
                        <button type="button" onclick="event.stopPropagation(); navigateCarousel(${m.id}, -1)" class="carousel-prev-${m.id} absolute left-3 top-1/2 -translate-y-1/2 z-30 w-9 h-9 rounded-full bg-black/50 hover:bg-black/80 text-white flex items-center justify-center opacity-0 group-hover/carousel:opacity-100 hover:scale-110 active:scale-95 transition-all shadow-xl pointer-events-auto backdrop-blur-md border border-white/20" aria-label="Foto Sebelumnya">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </button>
                        <button type="button" onclick="event.stopPropagation(); navigateCarousel(${m.id}, 1)" class="carousel-next-${m.id} absolute right-3 top-1/2 -translate-y-1/2 z-30 w-9 h-9 rounded-full bg-black/50 hover:bg-black/80 text-white flex items-center justify-center opacity-0 group-hover/carousel:opacity-100 hover:scale-110 active:scale-95 transition-all shadow-xl pointer-events-auto backdrop-blur-md border border-white/20" aria-label="Foto Berikutnya">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </button>

                        <!-- Dots Pagination Bottom -->
                        <div class="absolute bottom-16 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1.5 pointer-events-none" id="carousel-dots-${m.id}">
                            ${dots}
                        </div>
                    </div>
                `;
            } else {
                const fallbackMomentImg = 'https://res.cloudinary.com/fnf8f1pm/image/upload/v1792881951/rentify/moments/naikdt1lgug5qgylrk1l.jpg';
                mediaHtml = `
                    <div class="relative w-full h-full bg-black flex items-center justify-center overflow-hidden">
                        <!-- Ambient Blurred Backdrop -->
                        <img src="${m.media_url}" class="absolute inset-0 w-full h-full object-cover filter blur-2xl opacity-35 scale-110 pointer-events-none" onerror="this.onerror=null; this.src='${fallbackMomentImg}';">
                        <!-- High-res Sharp Image -->
                        <img src="${m.media_url}" alt="${m.caption ? m.caption.replace(/"/g, '&quot;') : 'Momen Rentify'}" loading="lazy" class="relative z-10 w-full h-full object-contain pointer-events-none" onerror="this.onerror=null; this.src='${fallbackMomentImg}';">
                    </div>
                `;
            }

            // Tagged Rental Products (Subtle Compact Badges)
            let productsHtml = '';
            if (m.tagged_products && m.tagged_products.length > 0) {
                productsHtml = `
                    <div class="pt-1 flex flex-wrap gap-1.5 max-w-[270px] pointer-events-auto">
                        ${m.tagged_products.map(tp => `
                            <a href="/customer/barang/${tp.slug || tp.id}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/50 hover:bg-black/75 backdrop-blur-md border border-white/20 hover:border-sky-400 text-white shadow-md transition-all group">
                                <i class="fa-solid fa-tag text-[9px] text-sky-400 group-hover:scale-110 transition-transform"></i>
                                <span class="font-bold text-[10px] text-white/90 truncate max-w-[110px] leading-tight">${tp.nama}</span>
                                <span class="font-black text-[10px] text-amber-300 whitespace-nowrap">Rp ${Number(tp.harga_sewa).toLocaleString('id-ID')}</span>
                                <i class="fa-solid fa-chevron-right text-[8px] text-white/40 group-hover:translate-x-0.5 transition-transform"></i>
                            </a>
                        `).join('')}
                    </div>
                `;
            }

            // Caption formatting with inline read more
            let captionHtml = '';
            if (m.caption) {
                const isLong = m.caption.length > 85;
                if (isLong) {
                    captionHtml = `
                        <div class="text-xs text-white/95 leading-relaxed drop-shadow-md font-medium pointer-events-auto">
                            <span id="caption-short-${m.id}" class="line-clamp-2">${m.caption}</span>
                            <span id="caption-full-${m.id}" class="hidden whitespace-pre-wrap">${m.caption}</span>
                            <button onclick="toggleCaptionExpand(${m.id})" id="caption-btn-${m.id}" class="text-sky-300 hover:text-sky-200 text-[11px] font-bold mt-0.5 inline-block">
                                selengkapnya
                            </button>
                        </div>
                    `;
                } else {
                    captionHtml = `
                        <p class="text-xs text-white/95 leading-relaxed drop-shadow-md font-medium">
                            ${m.caption}
                        </p>
                    `;
                }
            }

            card.innerHTML = `
                <!-- Media Content -->
                ${mediaHtml}

                <!-- Dark Vignette Gradients (Top & Bottom for text clarity) -->
                <div class="absolute inset-x-0 bottom-0 h-80 bg-gradient-to-t from-black via-black/55 to-transparent pointer-events-none z-10"></div>
                <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-black/50 to-transparent pointer-events-none z-10"></div>

                <!-- TOP-LEFT: AUTHOR PILL (Akun Terletak di Atas) -->
                <div class="absolute top-4 left-4 z-20 pointer-events-auto">
                    <a href="${m.author.id ? '/customer' : '#'}" class="flex items-center gap-2 bg-black/45 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/20 shadow-lg text-white hover:bg-black/60 transition group">
                        ${m.author.foto_profil
                            ? `<img src="${m.author.foto_profil}" class="w-7 h-7 rounded-full object-cover border border-sky-400">`
                            : `<div class="w-7 h-7 rounded-full bg-gradient-to-tr from-sky-400 to-sky-600 text-white flex items-center justify-center font-bold text-[11px]">${(m.author.name || 'U').charAt(0).toUpperCase()}</div>`
                        }
                        <span class="font-bold text-xs text-white group-hover:text-sky-300 transition">@${m.author.username || 'user' + m.author.id}</span>
                    </a>
                </div>

                <!-- TOP-RIGHT: ACTIONS (Upload + dan Menu Titik 3) -->
                <div class="absolute top-4 right-4 z-20 pointer-events-auto flex items-center gap-2">
                    <!-- Tombol Upload (+) Sebelah Titik 3 -->
                    <button onclick="handleFabClick()" class="glass-rail-btn w-9 h-9 rounded-full flex items-center justify-center text-white hover:text-sky-300 active:scale-95 transition shadow-lg group" title="Upload Momen" aria-label="Upload Momen">
                        <i class="fa-solid fa-plus text-xs group-hover:scale-110 transition-transform"></i>
                    </button>

                    <!-- Menu Titik 3 -->
                    <div class="relative">
                        <button onclick="toggleCardMenu(${m.id})" class="glass-rail-btn w-9 h-9 rounded-full flex items-center justify-center text-white hover:text-sky-300 transition shadow-lg" aria-label="Menu Opsi">
                            <i class="fa-solid fa-ellipsis text-xs"></i>
                        </button>
                        <div id="card-menu-${m.id}" class="hidden absolute right-0 top-11 w-40 bg-slate-900/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 py-2 z-40 text-xs text-white">
                            <button onclick="openReportModal(${m.id})" class="w-full px-4 py-2.5 text-left text-slate-200 hover:bg-white/10 flex items-center gap-2.5 font-bold transition">
                                <i class="fa-solid fa-flag text-rose-400"></i> Laporkan
                            </button>
                            ${isAuthorOrAdmin ? `
                                <button onclick="deleteMoment(${m.id})" class="w-full px-4 py-2.5 text-left text-rose-400 hover:bg-rose-500/20 flex items-center gap-2.5 font-bold transition">
                                    <i class="fa-solid fa-trash-can"></i> Hapus Momen
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>

                <!-- RIGHT RAIL: ENGAGEMENT BUTTONS (TikTok / Reels Style) -->
                <div class="absolute right-3.5 bottom-32 z-20 flex flex-col items-center gap-3 pointer-events-auto">
                    <!-- Like Button (Tanpa tanda + coin di bawah hati) -->
                    <div class="flex flex-col items-center group">
                        <button onclick="handleLikeToggle(${m.id})" id="like-btn-${m.id}" class="glass-rail-btn w-11 h-11 rounded-full flex items-center justify-center text-white active:scale-125 transition-transform shadow-md">
                            <i class="${m.is_liked ? 'fa-solid fa-heart text-rose-500' : 'fa-regular fa-heart text-white group-hover:text-rose-400'} text-xl transition-colors"></i>
                        </button>
                        <span id="like-count-${m.id}" class="text-[11px] font-black text-white mt-0.5 drop-shadow-md ${m.is_liked ? 'text-rose-400' : 'text-white'}">${m.likes_count}</span>
                    </div>

                    <!-- Comment Button (Sistem Komentar Momen) -->
                    <div class="flex flex-col items-center group">
                        <button onclick="openCommentsDrawer(${m.id})" id="comment-btn-${m.id}" class="glass-rail-btn w-11 h-11 rounded-full flex items-center justify-center text-white active:scale-125 transition-transform shadow-md" aria-label="Buka Komentar">
                            <i class="fa-solid fa-comment-dots text-lg text-white group-hover:text-sky-300 transition-colors"></i>
                        </button>
                        <span id="comment-count-${m.id}" class="text-[11px] font-black text-white mt-0.5 drop-shadow-md">${m.comments_count || 0}</span>
                    </div>

                    <!-- Bookmark / Save Button -->
                    <div class="flex flex-col items-center group">
                        <button onclick="handleBookmarkToggle(${m.id})" id="bookmark-btn-${m.id}" class="glass-rail-btn w-11 h-11 rounded-full flex items-center justify-center text-white active:scale-125 transition-transform shadow-md">
                            <i class="${m.is_bookmarked ? 'fa-solid fa-bookmark text-sky-400' : 'fa-regular fa-bookmark text-white group-hover:text-sky-300'} text-lg transition-colors"></i>
                        </button>
                        <span id="bookmark-count-${m.id}" class="text-[11px] font-black text-white mt-0.5 drop-shadow-md">${m.bookmarks_count}</span>
                    </div>

                    <!-- Share Button -->
                    <div class="flex flex-col items-center group">
                        <button onclick="handleShare(${m.id}, '${m.caption ? m.caption.replace(/'/g, "\\'") : 'Momen Rentify'}')" class="glass-rail-btn w-11 h-11 rounded-full flex items-center justify-center text-white active:scale-125 transition-transform shadow-md">
                            <i class="fa-solid fa-share text-lg group-hover:text-sky-300 transition-colors"></i>
                        </button>
                        <span class="text-[10px] font-bold text-white mt-0.5 drop-shadow-md">Share</span>
                    </div>

                    <!-- Video Sound Mute/Unmute Toggle Button -->
                    ${m.media_type === 'video' ? `
                        <div class="flex flex-col items-center">
                            <button onclick="toggleAudio(event)" class="glass-rail-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-95 transition-transform shadow-md" aria-label="Toggle Suara">
                                <i class="${isGlobalMuted ? 'fa-solid fa-volume-xmark' : 'fa-solid fa-volume-high text-sky-400'} text-sm sound-btn-icon transition-colors"></i>
                            </button>
                        </div>
                    ` : ''}
                </div>

                <!-- BOTTOM-LEFT: CAPTION & COMPACT RENTAL PRODUCT TAG (Nama akun di atas) -->
                <div class="absolute left-3.5 right-20 bottom-16 z-20 space-y-1.5 pointer-events-none">
                    <!-- Caption Section -->
                    ${captionHtml}

                    <!-- Compact Tagged Products Showcase -->
                    ${productsHtml}
                </div>
            `;

            // Gesture Listeners on Reel Card
            card.addEventListener('click', (e) => handleReelInteraction(e, m.id));

            // Video progress tracker
            if (m.media_type === 'video') {
                const vid = card.querySelector('video');
                const prog = card.querySelector('.video-progress-bar');
                if (vid && prog) {
                    vid.addEventListener('timeupdate', () => {
                        if (vid.duration) {
                            const pct = (vid.currentTime / vid.duration) * 100;
                            prog.style.width = `${pct}%`;
                        }
                    });
                }
                reelObserver.observe(card);
            }

            container.appendChild(card);
        });
    }

    /**
     * Penanganan klik / ketukan pada kartu Reel (Single Tap = Play/Pause, Double Tap = Heart Like).
     */
    function handleReelInteraction(event, momentId) {
        // Jangan interupsi jika pengguna menekan tombol, tautan, atau menu interaktif
        if (event.target.closest('button, a, input, select, textarea, .glass-product-card, [id^="card-menu-"]')) {
            return;
        }

        const currentTime = new Date().getTime();
        const tapLength = currentTime - lastTapTime;

        if (tapLength < 320 && tapLength > 0) {
            // Double Tap Detected!
            clearTimeout(tapTimeout);
            spawnHeartBurst(event, momentId);
            event.preventDefault();
        } else {
            // Single Tap
            tapTimeout = setTimeout(() => {
                const card = document.getElementById(`moment-card-${momentId}`);
                if (!card) return;
                const video = card.querySelector('video');
                if (video) {
                    toggleVideoPlayPause(video, card);
                }
            }, 320);
        }
        lastTapTime = currentTime;
    }

    /**
     * Animasi Double-Tap Heart Burst khas TikTok & Instagram Reels.
     */
    function spawnHeartBurst(event, momentId) {
        const heart = document.createElement('div');
        heart.className = 'fixed pointer-events-none z-50 text-rose-500 text-6xl drop-shadow-2xl animate-heart-burst';
        heart.style.left = `${event.clientX}px`;
        heart.style.top = `${event.clientY}px`;
        heart.innerHTML = '<i class="fa-solid fa-heart"></i>';

        document.body.appendChild(heart);
        setTimeout(() => heart.remove(), 750);

        // Jika belum disukai, aktifkan like
        const btn = document.getElementById(`like-btn-${momentId}`);
        const icon = btn?.querySelector('i');
        if (icon && !icon.classList.contains('fa-solid')) {
            handleLikeToggle(momentId);
        }
    }

    /**
     * Toggle Play / Pause Video dengan animasi ikon di tengah layar.
     */
    function toggleVideoPlayPause(video, card) {
        const pulse = card.querySelector('.play-pause-pulse');
        if (video.paused) {
            video.play().catch(() => {});
            if (pulse) {
                pulse.innerHTML = '<i class="fa-solid fa-play ml-1"></i>';
                pulse.classList.remove('hidden', 'animate-pulse-center');
                void pulse.offsetWidth;
                pulse.classList.add('animate-pulse-center');
                setTimeout(() => pulse.classList.add('hidden'), 450);
            }
        } else {
            video.pause();
            if (pulse) {
                pulse.innerHTML = '<i class="fa-solid fa-pause"></i>';
                pulse.classList.remove('hidden', 'animate-pulse-center');
                void pulse.offsetWidth;
                pulse.classList.add('animate-pulse-center');
                setTimeout(() => pulse.classList.add('hidden'), 450);
            }
        }
    }

    /**
     * Toggle Suara Global (Mute / Unmute).
     */
    function toggleAudio(event) {
        event.stopPropagation();
        isGlobalMuted = !isGlobalMuted;

        document.querySelectorAll('video').forEach(vid => {
            vid.muted = isGlobalMuted;
        });

        document.querySelectorAll('.sound-btn-icon').forEach(icon => {
            icon.className = isGlobalMuted 
                ? 'fa-solid fa-volume-xmark text-sm sound-btn-icon transition-colors' 
                : 'fa-solid fa-volume-high text-sm sound-btn-icon text-sky-400 transition-colors';
        });

        // Toast feedback audio
        Swal.fire({
            icon: isGlobalMuted ? 'info' : 'success',
            text: isGlobalMuted ? 'Suara dimatikan' : 'Suara diaktifkan',
            timer: 1200,
            showConfirmButton: false,
            toast: true,
            position: 'top'
        });
    }

    /**
     * Expand / Collapse Teks Caption Panjang.
     */
    function toggleCaptionExpand(momentId) {
        const shortEl = document.getElementById(`caption-short-${momentId}`);
        const fullEl = document.getElementById(`caption-full-${momentId}`);
        const btn = document.getElementById(`caption-btn-${momentId}`);

        if (shortEl.classList.contains('hidden')) {
            shortEl.classList.remove('hidden');
            fullEl.classList.add('hidden');
            btn.textContent = 'selengkapnya';
        } else {
            shortEl.classList.add('hidden');
            fullEl.classList.remove('hidden');
            btn.textContent = 'lebih sedikit';
        }
    }

    /**
     * Infinite scroll listener terhubung langsung pada snap container.
     */
    function setupInfiniteScroll() {
        const container = document.getElementById('feed-container');
        if (!container) return;

        container.addEventListener('scroll', () => {
            if (container.scrollTop + container.clientHeight >= container.scrollHeight - container.clientHeight * 1.5) {
                loadFeedBatch();
            }
        });
    }

    /**
     * Toggle Like dengan Optimistic UI dan integrasi Reward Koin Rentify.
     */
    async function handleLikeToggle(momentId) {
        if (!IS_AUTHENTICATED) {
            openGuestAuthModal();
            return;
        }

        const btn = document.getElementById(`like-btn-${momentId}`);
        const icon = btn?.querySelector('i');
        const countSpan = document.getElementById(`like-count-${momentId}`);
        if (!btn || !icon || !countSpan) return;

        const wasLiked = icon.classList.contains('fa-solid');
        const prevCount = parseInt(countSpan.textContent, 10) || 0;

        // Optimistic UI update
        if (wasLiked) {
            icon.className = 'fa-regular fa-heart text-white group-hover:text-rose-400 text-xl transition-colors';
            countSpan.textContent = Math.max(0, prevCount - 1);
            countSpan.className = 'text-[11px] font-black text-white mt-1 drop-shadow-md';
        } else {
            icon.className = 'fa-solid fa-heart text-rose-500 text-xl transition-colors';
            countSpan.textContent = prevCount + 1;
            countSpan.className = 'text-[11px] font-black text-rose-400 mt-1 drop-shadow-md';
        }

        try {
            const response = await fetch(`/api/moments/${momentId}/like`, {
                method: wasLiked ? 'DELETE' : 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                }
            });
            const result = await response.json();

            if (!result.success) {
                countSpan.textContent = prevCount;
                icon.className = wasLiked ? 'fa-solid fa-heart text-rose-500 text-xl' : 'fa-regular fa-heart text-white text-xl';
                Swal.fire({ icon: 'warning', text: result.message || 'Gagal mengubah status suka.' });
            } else {
                countSpan.textContent = result.likes_count;
                if (result.minted) {
                    Swal.fire({
                        icon: 'success',
                        title: '1 Koin Didapatkan!',
                        text: 'Pembuat momen mendapatkan 1 Koin Rentify dari apresiasi Anda!',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            }
        } catch (error) {
            countSpan.textContent = prevCount;
            icon.className = wasLiked ? 'fa-solid fa-heart text-rose-500 text-xl' : 'fa-regular fa-heart text-white text-xl';
        }
    }

    /**
     * Toggle Bookmark dengan Optimistic UI.
     */
    async function handleBookmarkToggle(momentId) {
        if (!IS_AUTHENTICATED) {
            openGuestAuthModal();
            return;
        }

        const btn = document.getElementById(`bookmark-btn-${momentId}`);
        const icon = btn?.querySelector('i');
        const countSpan = document.getElementById(`bookmark-count-${momentId}`);
        if (!btn || !icon || !countSpan) return;

        const wasBookmarked = icon.classList.contains('fa-solid');
        const prevCount = parseInt(countSpan.textContent, 10) || 0;

        // Optimistic UI update
        if (wasBookmarked) {
            icon.className = 'fa-regular fa-bookmark text-white group-hover:text-sky-300 text-lg transition-colors';
            countSpan.textContent = Math.max(0, prevCount - 1);
            countSpan.className = 'text-[11px] font-black text-white mt-1 drop-shadow-md';
        } else {
            icon.className = 'fa-solid fa-bookmark text-sky-400 text-lg transition-colors';
            countSpan.textContent = prevCount + 1;
            countSpan.className = 'text-[11px] font-black text-sky-400 mt-1 drop-shadow-md';
        }

        try {
            const response = await fetch(`/api/moments/${momentId}/bookmark`, {
                method: wasBookmarked ? 'DELETE' : 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                }
            });
            const result = await response.json();

            if (!result.success) {
                countSpan.textContent = prevCount;
                icon.className = wasBookmarked ? 'fa-solid fa-bookmark text-sky-400 text-lg' : 'fa-regular fa-bookmark text-white text-lg';
                Swal.fire({ icon: 'warning', text: result.message || 'Gagal mengubah simpanan momen.' });
            } else {
                countSpan.textContent = result.bookmarks_count;
            }
        } catch (error) {
            countSpan.textContent = prevCount;
            icon.className = wasBookmarked ? 'fa-solid fa-bookmark text-sky-400 text-lg' : 'fa-regular fa-bookmark text-white text-lg';
        }
    }

    /**
     * Web Share API / Copy Link.
     */
    function handleShare(momentId, caption) {
        const shareUrl = `${window.location.origin}/moments#moment-${momentId}`;
        if (navigator.share) {
            navigator.share({
                title: 'Rentify Moments',
                text: caption,
                url: shareUrl
            }).catch(() => {});
        } else {
            navigator.clipboard.writeText(shareUrl).then(() => {
                Swal.fire({
                    icon: 'success',
                    text: 'Tautan momen disalin ke papan klip!',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });
        }
    }

    /**
     * Hapus Momen oleh Author/Admin.
     */
    async function deleteMoment(momentId) {
        closeCardMenus();
        const confirm = await Swal.fire({
            title: 'Hapus Momen?',
            text: 'Momen ini akan dihapus dari feed publik dan koin apresiasi akan ditarik kembali.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        });

        if (!confirm.isConfirmed) return;

        try {
            const response = await fetch(`/api/moments/${momentId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            });
            const result = await response.json();

            if (result.success) {
                document.getElementById(`moment-card-${momentId}`)?.remove();
                Swal.fire({ icon: 'success', text: 'Momen berhasil dihapus.' });
            } else {
                Swal.fire({ icon: 'error', text: result.message || 'Gagal menghapus momen.' });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', text: 'Terjadi kesalahan jaringan.' });
        }
    }

    /**
     * Menu dropdown tiga titik.
     */
    function toggleCardMenu(momentId) {
        const menu = document.getElementById(`card-menu-${momentId}`);
        if (!menu) return;
        const isHidden = menu.classList.contains('hidden');
        closeCardMenus();
        if (isHidden) {
            menu.classList.remove('hidden');
        }
    }

    function closeCardMenus() {
        document.querySelectorAll('[id^="card-menu-"]').forEach(el => el.classList.add('hidden'));
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('[id^="card-menu-"]') && !e.target.closest('button[onclick^="toggleCardMenu"]')) {
            closeCardMenus();
        }
    });

    /**
     * FAB Click Handler.
     */
    function handleFabClick() {
        if (!IS_AUTHENTICATED) {
            openGuestAuthModal();
        } else {
            window.location.href = "{{ route('customer.moments.create') }}";
        }
    }

    function openGuestAuthModal() {
        document.getElementById('guest-auth-modal')?.classList.remove('hidden');
    }

    function closeGuestAuthModal() {
        document.getElementById('guest-auth-modal')?.classList.add('hidden');
    }

    function openUploadModal() {
        document.getElementById('upload-modal')?.classList.remove('hidden');
    }

    function closeUploadModal() {
        document.getElementById('upload-modal')?.classList.add('hidden');
        resetUploadForm();
    }

    function resetUploadForm() {
        selectedFiles = [];
        selectedFile = null;
        selectedVideoDuration = null;
        taggedProductIds = [];
        document.getElementById('upload-moment-form')?.reset();
        document.getElementById('upload-prompt')?.classList.remove('hidden');
        document.getElementById('media-preview-container')?.classList.add('hidden');
        document.getElementById('multi-image-preview-grid')?.classList.add('hidden');
        document.getElementById('single-media-preview')?.classList.add('hidden');
        document.getElementById('image-preview')?.classList.add('hidden');
        document.getElementById('video-preview')?.classList.add('hidden');
        const grid = document.getElementById('multi-image-preview-grid');
        if (grid) grid.innerHTML = '';
        const container = document.getElementById('selected-tags-container');
        if (container) container.innerHTML = '';
        const counter = document.getElementById('caption-counter');
        if (counter) counter.textContent = '0/1000';
    }

    function resetSelectedMedia() {
        selectedFiles = [];
        selectedFile = null;
        selectedVideoDuration = null;
        const input = document.getElementById('media-file-input');
        if (input) input.value = '';
        document.getElementById('upload-prompt')?.classList.remove('hidden');
        document.getElementById('media-preview-container')?.classList.add('hidden');
        document.getElementById('multi-image-preview-grid')?.classList.add('hidden');
        document.getElementById('single-media-preview')?.classList.add('hidden');
        document.getElementById('image-preview')?.classList.add('hidden');
        document.getElementById('video-preview')?.classList.add('hidden');
        const grid = document.getElementById('multi-image-preview-grid');
        if (grid) grid.innerHTML = '';
    }

    /**
     * Pemilihan file (bisa multi-foto hingga 10 foto atau 1 video) dengan HEIC auto-transcoding.
     */
    async function handleFileSelection(event) {
        const rawFiles = Array.from(event.target.files || []);
        if (rawFiles.length === 0) return;

        const imageExts = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'gif', 'bmp', 'tiff', 'tif', 'svg', 'avif', 'ico', 'raw', 'cr2', 'nef', 'arw', 'dng', 'psd', 'eps', 'ai'];
        const videoExts = ['mp4', 'mov', 'quicktime', 'webm', 'mkv', 'avi', 'wmv', 'flv', '3gp', '3g2', 'm4v', 'ts', 'mpeg', 'mpg', 'ogv', 'vob', 'm2ts', 'divx', 'rm', 'rmvb', 'asf', 'm4p', 'f4v'];

        // Cek jika terdapat file video
        const hasVideo = rawFiles.some(f => {
            const m = f.type || '';
            const e = (f.name || '').split('.').pop().toLowerCase();
            return m.startsWith('video/') || videoExts.includes(e);
        });

        if (hasVideo) {
            if (rawFiles.length > 1 || selectedFiles.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Postingan video hanya mendukung 1 berkas video. Untuk multi-upload, silakan pilih foto (maksimal 10 foto).'
                });
            }
            const videoFile = rawFiles.find(f => {
                const m = f.type || '';
                const e = (f.name || '').split('.').pop().toLowerCase();
                return m.startsWith('video/') || videoExts.includes(e);
            }) || rawFiles[0];

            selectedFiles = [videoFile];
            selectedFile = videoFile;

            const videoUrl = URL.createObjectURL(videoFile);
            const tempVideo = document.createElement('video');
            tempVideo.preload = 'metadata';
            tempVideo.src = videoUrl;

            tempVideo.onloadedmetadata = function () {
                URL.revokeObjectURL(videoUrl);
                selectedVideoDuration = tempVideo.duration;
                showSingleMediaPreview(videoFile, true);
            };

            tempVideo.onerror = function () {
                selectedVideoDuration = null;
                showSingleMediaPreview(videoFile, true);
            };
            return;
        }

        // Penanganan multi-foto
        const statusEl = document.getElementById('heic-converting-status');
        const processedPhotos = [];

        async function loadHeicConverter() {
            if (typeof heic2any === 'function') return true;
            return new Promise((resolve) => {
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js';
                s.onload = () => resolve(true);
                s.onerror = () => {
                    console.warn('Gagal memuat heic2any CDN');
                    resolve(false);
                };
                document.head.appendChild(s);
            });
        }

        for (const file of rawFiles) {
            const mime = file.type || '';
            const ext = (file.name || '').split('.').pop().toLowerCase();
            const isImage = mime.startsWith('image/') || imageExts.includes(ext);

            if (!isImage) continue;

            if (['heic', 'heif'].includes(ext) || mime.includes('heic') || mime.includes('heif')) {
                statusEl?.classList.remove('hidden');
                try {
                    await loadHeicConverter();
                    if (typeof heic2any === 'function') {
                        const convertedBlob = await heic2any({
                            blob: file,
                            toType: 'image/jpeg',
                            quality: 0.9
                        });
                        const blob = Array.isArray(convertedBlob) ? convertedBlob[0] : convertedBlob;
                        processedPhotos.push(new File([blob], file.name.replace(/\.(heic|heif)$/i, '.jpg'), { type: 'image/jpeg' }));
                    } else {
                        processedPhotos.push(file);
                    }
                } catch (err) {
                    console.warn('heic2any failed, fallback to file:', err);
                    processedPhotos.push(file);
                } finally {
                    statusEl?.classList.add('hidden');
                }
            } else {
                processedPhotos.push(file);
            }
        }

        const currentImages = selectedFiles.filter(f => !(f.type || '').startsWith('video/'));
        const totalNew = currentImages.concat(processedPhotos);

        if (totalNew.length > 10) {
            Swal.fire({
                icon: 'warning',
                text: 'Maksimal 10 foto per momen. Hanya 10 foto pertama yang disimpan.'
            });
            selectedFiles = totalNew.slice(0, 10);
        } else {
            selectedFiles = totalNew;
        }

        selectedFile = selectedFiles[0] || null;
        selectedVideoDuration = null;

        renderMultiPhotoPreview();
    }

    function removePhotoByIndex(index) {
        selectedFiles.splice(index, 1);
        selectedFile = selectedFiles[0] || null;
        if (selectedFiles.length === 0) {
            resetSelectedMedia();
        } else {
            renderMultiPhotoPreview();
        }
    }

    function renderMultiPhotoPreview() {
        document.getElementById('upload-prompt')?.classList.add('hidden');
        document.getElementById('media-preview-container')?.classList.remove('hidden');
        document.getElementById('single-media-preview')?.classList.add('hidden');

        const grid = document.getElementById('multi-image-preview-grid');
        const fileInfo = document.getElementById('file-info-text');

        if (selectedFiles.length === 1) {
            showSingleMediaPreview(selectedFiles[0], false);
            return;
        }

        if (grid) {
            grid.innerHTML = '';
            grid.classList.remove('hidden');

            let totalBytes = 0;
            selectedFiles.forEach((f, idx) => {
                totalBytes += f.size;
                const url = URL.createObjectURL(f);
                const item = document.createElement('div');
                item.className = 'relative aspect-square rounded-xl overflow-hidden bg-slate-100 group border border-slate-200 shadow-xs';
                item.innerHTML = `
                    <img src="${url}" class="w-full h-full object-cover">
                    <span class="absolute top-1 left-1 bg-black/70 backdrop-blur-md text-white text-[9px] font-black px-1.5 py-0.5 rounded-md">#${idx + 1}</span>
                    <button type="button" onclick="event.stopPropagation(); removePhotoByIndex(${idx})" class="absolute top-1 right-1 w-5 h-5 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center text-[10px] shadow-sm transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;
                grid.appendChild(item);
            });

            if (fileInfo) {
                fileInfo.textContent = `${selectedFiles.length} Foto Dipilih (${(totalBytes / (1024 * 1024)).toFixed(1)} MB) — Format Carousel`;
            }
        }
    }

    function showSingleMediaPreview(file, isVideo) {
        document.getElementById('upload-prompt')?.classList.add('hidden');
        document.getElementById('media-preview-container')?.classList.remove('hidden');
        document.getElementById('multi-image-preview-grid')?.classList.add('hidden');
        document.getElementById('single-media-preview')?.classList.remove('hidden');

        const fileInfo = document.getElementById('file-info-text');
        if (fileInfo) fileInfo.textContent = `${file.name} (${(file.size / (1024 * 1024)).toFixed(1)} MB)`;

        if (isVideo) {
            const videoPreview = document.getElementById('video-preview');
            if (videoPreview) {
                videoPreview.src = URL.createObjectURL(file);
                videoPreview.classList.remove('hidden');
            }
            document.getElementById('image-preview')?.classList.add('hidden');
        } else {
            const imgPreview = document.getElementById('image-preview');
            if (imgPreview) {
                imgPreview.src = URL.createObjectURL(file);
                imgPreview.classList.remove('hidden');
            }
            document.getElementById('video-preview')?.classList.add('hidden');
        }
    }

    function updateCaptionCount(textarea) {
        const counter = document.getElementById('caption-counter');
        if (counter) counter.textContent = `${textarea.value.length}/1000`;
    }

    function addProductTag(select) {
        const id = select.value;
        if (!id) return;

        if (taggedProductIds.length >= 3) {
            Swal.fire({ icon: 'warning', text: 'Maksimal 3 produk yang dapat ditandai.' });
            select.value = '';
            return;
        }

        if (taggedProductIds.includes(Number(id))) {
            Swal.fire({ icon: 'warning', text: 'Produk ini sudah ditandai.' });
            select.value = '';
            return;
        }

        taggedProductIds.push(Number(id));
        const selectedOpt = select.options[select.selectedIndex];
        const nama = selectedOpt.getAttribute('data-nama');

        const tagBadge = document.createElement('div');
        tagBadge.id = `tag-badge-${id}`;
        tagBadge.className = 'bg-sky-50 text-sky-800 border border-sky-200 px-2.5 py-1 rounded-full text-xs flex items-center gap-1.5 shadow-xs';
        tagBadge.innerHTML = `
            <span>${nama}</span>
            <button type="button" onclick="removeProductTag(${id})" class="text-sky-500 hover:text-sky-700 font-bold">&times;</button>
        `;
        document.getElementById('selected-tags-container')?.appendChild(tagBadge);
        select.value = '';
    }

    function removeProductTag(id) {
        taggedProductIds = taggedProductIds.filter(itemId => itemId !== id);
        document.getElementById(`tag-badge-${id}`)?.remove();
    }

    /**
     * Submit formulir unggah Momen (Mendukung Multi-Foto / Carousel dan Video).
     */
    async function submitMomentUpload(event) {
        event.preventDefault();
        if (!selectedFiles || selectedFiles.length === 0) {
            Swal.fire({ icon: 'warning', text: 'Silakan pilih foto atau video terlebih dahulu.' });
            return;
        }

        const submitBtn = document.getElementById('submit-moment-btn');
        const btnText = document.getElementById('submit-btn-text');
        const spinner = document.getElementById('submit-btn-spinner');

        submitBtn.disabled = true;
        const isMulti = selectedFiles.length > 1;
        btnText.textContent = isMulti ? `Mengunggah ${selectedFiles.length} foto...` : 'Mengunggah momen...';
        spinner?.classList.remove('hidden');

        const formData = new FormData();
        const firstFile = selectedFiles[0];
        const ext = (firstFile.name || '').split('.').pop().toLowerCase();
        const mime = firstFile.type || '';
        const videoExts = ['mp4', 'mov', 'quicktime', 'webm', 'mkv', 'avi', 'wmv', 'flv', '3gp', '3g2', 'm4v', 'ts', 'mpeg', 'mpg', 'ogv', 'vob', 'm2ts', 'divx', 'rm', 'rmvb', 'asf', 'm4p', 'f4v'];
        const isVideo = mime.startsWith('video/') || videoExts.includes(ext);

        if (isVideo) {
            formData.append('media', firstFile);
            formData.append('media_type', 'video');
            if (selectedVideoDuration) {
                formData.append('video_duration_seconds', selectedVideoDuration.toFixed(2));
            }
        } else if (isMulti) {
            selectedFiles.forEach((file, idx) => {
                formData.append(`media[${idx}]`, file);
            });
            formData.append('media_type', 'image');
        } else {
            formData.append('media', firstFile);
            formData.append('media_type', 'image');
        }

        formData.append('caption', document.getElementById('caption-input')?.value || '');

        taggedProductIds.forEach((prodId, idx) => {
            formData.append(`product_ids[${idx}]`, prodId);
        });

        try {
            const response = await fetch('/api/moments', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                closeUploadModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Momen Berhasil Dibagikan!',
                    text: isMulti ? `${selectedFiles.length} foto berhasil dibagikan dalam format carousel.` : 'Postingan Anda telah aktif di komunitas Rentify.',
                    timer: 2500,
                    showConfirmButton: false
                });

                document.getElementById('feed-container').innerHTML = '';
                nextCursor = null;
                hasMore = true;
                loadFeedBatch(true);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengunggah',
                    text: result.message || Object.values(result.errors || {}).flat().join(', ')
                });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', text: 'Terjadi kesalahan saat mengunggah momen.' });
        } finally {
            submitBtn.disabled = false;
            btnText.textContent = 'Publikasikan Momen';
            spinner?.classList.add('hidden');
        }
    }

    /**
     * Modal Pelaporan Konten.
     */
    function openReportModal(momentId) {
        if (!IS_AUTHENTICATED) {
            openGuestAuthModal();
            return;
        }
        closeCardMenus();
        const inputId = document.getElementById('report-moment-id');
        if (inputId) inputId.value = momentId;
        document.getElementById('report-form')?.reset();
        document.getElementById('report-modal')?.classList.remove('hidden');
    }

    function closeReportModal() {
        document.getElementById('report-modal')?.classList.add('hidden');
    }

    async function submitReport(event) {
        event.preventDefault();
        const momentId = document.getElementById('report-moment-id')?.value;
        const categoryInput = document.querySelector('input[name="report_category"]:checked');
        const reasonText = document.getElementById('report-reason-text')?.value || '';

        if (!categoryInput) {
            Swal.fire({ icon: 'warning', text: 'Pilih salah satu kategori laporan.' });
            return;
        }

        try {
            const response = await fetch(`/api/moments/${momentId}/report`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    category: categoryInput.value,
                    reason_description: reasonText
                })
            });
            const result = await response.json();

            if (result.success) {
                closeReportModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Laporan Diterima',
                    text: 'Terima kasih atas laporan Anda. Tim moderator akan meninjau postingan ini.'
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    text: result.message || 'Gagal mengirim laporan.'
                });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', text: 'Terjadi kesalahan jaringan saat mengirim laporan.' });
        }
    }

    /**
     * ==============================================================
     * SISTEM KOMENTAR MOMEN
     * ==============================================================
     */
    async function openCommentsDrawer(momentId) {
        const drawer = document.getElementById('comments-drawer');
        const activeInput = document.getElementById('active-comment-moment-id');
        const list = document.getElementById('comments-list');
        const countSpan = document.getElementById('drawer-comments-count');

        if (!drawer || !activeInput || !list) return;

        activeInput.value = momentId;
        drawer.classList.remove('hidden');
        list.innerHTML = `
            <div class="py-12 flex flex-col items-center justify-center text-slate-400">
                <i class="fa-solid fa-circle-notch fa-spin text-2xl text-sky-400 mb-2"></i>
                <p class="text-xs">Memuat komentar...</p>
            </div>
        `;

        try {
            const response = await fetch(`/api/moments/${momentId}/comments`, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                if (countSpan) countSpan.textContent = result.comments_count || 0;
                renderCommentsList(result.data, momentId);
            } else {
                list.innerHTML = `<div class="py-8 text-center text-rose-400 text-xs">${result.message || 'Gagal memuat komentar.'}</div>`;
            }
        } catch (error) {
            list.innerHTML = `<div class="py-8 text-center text-rose-400 text-xs">Terjadi kesalahan jaringan saat memuat komentar.</div>`;
        }
    }

    function closeCommentsDrawer() {
        document.getElementById('comments-drawer')?.classList.add('hidden');
        document.getElementById('comment-form')?.reset();
    }

    function renderCommentsList(comments, momentId) {
        const list = document.getElementById('comments-list');
        if (!list) return;

        if (!comments || comments.length === 0) {
            list.innerHTML = `
                <div class="py-12 flex flex-col items-center justify-center text-slate-400 text-center">
                    <div class="w-12 h-12 rounded-full bg-white/5 flex items-center justify-center text-xl text-slate-500 mb-2">
                        <i class="fa-regular fa-comment-dots"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-300">Belum ada komentar</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Jadilah orang pertama yang mengomentari momen ini!</p>
                </div>
            `;
            return;
        }

        list.innerHTML = comments.map(c => `
            <div id="comment-item-${c.id}" class="flex items-start justify-between gap-3 group">
                <div class="flex items-start gap-2.5 min-w-0">
                    ${c.user_avatar
                        ? `<img src="${c.user_avatar}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-white/20">`
                        : `<div class="w-8 h-8 rounded-full bg-gradient-to-tr from-sky-400 to-sky-600 text-white flex items-center justify-center font-bold text-xs shrink-0">${(c.user_name || 'U').charAt(0).toUpperCase()}</div>`
                    }
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xs text-white">@${c.user_username}</span>
                            <span class="text-[10px] text-slate-400">${c.created_at_human}</span>
                        </div>
                        <p class="text-xs text-slate-200 mt-0.5 break-words leading-relaxed">${escapeHtml(c.comment)}</p>
                    </div>
                </div>
                ${c.can_delete ? `
                    <button onclick="deleteComment(${momentId}, ${c.id})" class="text-slate-500 hover:text-rose-400 p-1 opacity-60 hover:opacity-100 transition shrink-0" title="Hapus komentar">
                        <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                ` : ''}
            </div>
        `).join('');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function submitComment(event) {
        event.preventDefault();
        if (!IS_AUTHENTICATED) {
            closeCommentsDrawer();
            openGuestAuthModal();
            return;
        }

        const momentId = document.getElementById('active-comment-moment-id')?.value;
        const input = document.getElementById('comment-input-text');
        const submitBtn = document.getElementById('comment-submit-btn');
        const commentText = (input?.value || '').trim();

        if (!commentText) return;

        submitBtn.disabled = true;

        try {
            const response = await fetch(`/api/moments/${momentId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({ comment: commentText })
            });
            const result = await response.json();

            if (result.success) {
                input.value = '';
                const countDrawer = document.getElementById('drawer-comments-count');
                if (countDrawer) countDrawer.textContent = result.comments_count;
                const countCard = document.getElementById(`comment-count-${momentId}`);
                if (countCard) countCard.textContent = result.comments_count;

                openCommentsDrawer(momentId);
            } else {
                Swal.fire({ icon: 'warning', text: result.message || 'Gagal mengirim komentar.' });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', text: 'Terjadi kesalahan saat mengirim komentar.' });
        } finally {
            submitBtn.disabled = false;
        }
    }

    async function deleteComment(momentId, commentId) {
        const confirm = await Swal.fire({
            title: 'Hapus komentar?',
            text: 'Komentar ini akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        });

        if (!confirm.isConfirmed) return;

        try {
            const response = await fetch(`/api/moments/${momentId}/comments/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                }
            });
            const result = await response.json();

            if (result.success) {
                document.getElementById(`comment-item-${commentId}`)?.remove();
                const countDrawer = document.getElementById('drawer-comments-count');
                if (countDrawer) countDrawer.textContent = result.comments_count;
                const countCard = document.getElementById(`comment-count-${momentId}`);
                if (countCard) countCard.textContent = result.comments_count;

                const list = document.getElementById('comments-list');
                if (list && list.children.length === 0) {
                    renderCommentsList([], momentId);
                }
            } else {
                Swal.fire({ icon: 'error', text: result.message || 'Gagal menghapus komentar.' });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', text: 'Terjadi kesalahan jaringan.' });
        }
    }

    /**
     * Scroll Behavior: Navbar mengecil & transparan saat scroll, pulih saat diam.
     */
    function setupNavBarScrollBehavior() {
        const feed = document.getElementById('feed-container');
        const navBar = document.getElementById('bottom-navigation-bar');
        if (!feed || !navBar) return;

        let scrollTimer = null;
        feed.addEventListener('scroll', () => {
            navBar.classList.add('opacity-30', 'scale-90', 'translate-y-2');
            navBar.classList.remove('opacity-100', 'scale-100', 'translate-y-0');

            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(() => {
                navBar.classList.remove('opacity-30', 'scale-90', 'translate-y-2');
                navBar.classList.add('opacity-100', 'scale-100', 'translate-y-0');
            }, 800);
        }, { passive: true });
    }

    /**
     * Horizontal Swipe Gesture ditangani secara terpusat dan mulus oleh bottom-navigation-bar
     * (Beranda <-> Momen <-> Akun) dengan efek transisi swap dan animasi air mengalir.
     */
    function setupHorizontalSwipeNavigation() {
        // Dikelola secara harmonis oleh modul gestur navigasi x-bottom-navigation-bar
    }
</script>
@endsection
