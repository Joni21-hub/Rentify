@extends('layouts.app')

@section('title', 'Buat & Edit Momen — Rentify')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    html, body {
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        background-color: #000000 !important;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
    }

    .glass-pill {
        background: rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .glass-btn {
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    /* SVG Progress Ring */
    .record-progress-svg {
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }

    .record-progress-circle {
        transition: stroke-dashoffset 0.08s linear;
        stroke: #38bdf8; /* Rentify Sky Blue */
    }

    /* Grid 3x3 Overlay */
    .grid-line-h {
        border-bottom: 1px dashed rgba(255, 255, 255, 0.22);
    }
    .grid-line-v {
        border-right: 1px dashed rgba(255, 255, 255, 0.22);
    }

    /* Interactive text and sticker overlays */
    .interactive-overlay {
        touch-action: none;
        will-change: transform, left, top;
    }
</style>
@endpush

@section('content')
<div class="fixed inset-0 w-full h-[100dvh] bg-black overflow-hidden flex flex-col justify-between" id="moment-creator-app">

    <!-- ============================================================== -->
    <!-- SCREEN 1: FULLSCREEN CAMERA & RECORDER (FASE 1) -->
    <!-- ============================================================== -->
    <div id="screen-camera" class="relative w-full h-full flex flex-col justify-between transition-opacity duration-300">
        <!-- 1. Fullscreen Viewfinder -->
        <div id="camera-viewfinder-container" class="absolute inset-0 w-full h-full bg-slate-950 flex items-center justify-center overflow-hidden cursor-pointer">
            <video 
                id="camera-video-preview" 
                autoplay 
                playsinline 
                muted 
                class="w-full h-full object-cover pointer-events-none transition-transform duration-300"
            ></video>

            <!-- 3x3 Grid Overlay (Rule of Thirds) -->
            <div id="camera-grid-overlay" class="hidden absolute inset-0 grid grid-cols-3 grid-rows-3 pointer-events-none z-10">
                <div class="grid-line-h grid-line-v"></div>
                <div class="grid-line-h grid-line-v"></div>
                <div class="grid-line-h"></div>
                <div class="grid-line-h grid-line-v"></div>
                <div class="grid-line-h grid-line-v"></div>
                <div class="grid-line-h"></div>
                <div class="grid-line-v"></div>
                <div class="grid-line-v"></div>
                <div></div>
            </div>

            <!-- Filter Name Subtle Toast (Swipe Notification) -->
            <div id="filter-name-toast" class="absolute top-20 left-1/2 -translate-x-1/2 z-30 pointer-events-none opacity-0 -translate-y-2 transition-all duration-300 glass-pill px-4 py-1.5 rounded-full text-xs font-black text-white shadow-xl flex items-center gap-1.5">
                <i class="fa-solid fa-wand-magic-sparkles text-sky-400 text-[11px]"></i>
                <span id="filter-name-text">Normal</span>
            </div>
        </div>

        <!-- 2. Camera Top Header -->
        <header id="camera-top-header" class="relative z-30 pt-safe-top px-4 pt-3 space-y-2 pointer-events-auto">
            <div class="flex items-center justify-between">
                <!-- Close Button (X) -->
                <button id="close-creator-btn" class="glass-btn w-9 h-9 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-lg" aria-label="Tutup Kamera">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>

                <!-- Pill Tambah Musik -->
                <button id="top-music-pill" class="glass-pill px-3.5 py-1.5 rounded-full text-xs font-black text-white flex items-center gap-2 shadow-lg active:scale-95 transition max-w-[200px]">
                    <i class="fa-solid fa-music text-sky-400 text-xs shrink-0"></i>
                    <span id="music-pill-title" class="truncate">Tambah Musik</span>
                    <span id="remove-music-btn" class="hidden ml-0.5 text-white/60 hover:text-white p-0.5" title="Hapus Musik">
                        <i class="fa-solid fa-xmark text-[10px]"></i>
                    </span>
                </button>

                <!-- Aspect Ratio Badge -->
                <div class="glass-pill px-2.5 py-1 rounded-full text-[10px] font-black text-white/80 tracking-wider">
                    9:16
                </div>
            </div>

            <!-- Segmented Progress Bar Multi-Klip -->
            <div id="multi-clip-progress-bar" class="w-full flex items-center gap-1.5 pt-1">
                <!-- Dynamic Segments rendered by creator-ui.js -->
            </div>
        </header>

        <!-- 3. Camera Right Toolbar -->
        <aside id="camera-right-toolbar" class="absolute right-3.5 top-28 z-30 flex flex-col items-center gap-4 pointer-events-auto transition-opacity duration-300">
            <!-- 1. Flip Camera -->
            <div class="flex flex-col items-center">
                <button id="tool-flip-camera" class="glass-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-md" title="Putar Kamera">
                    <i class="fa-solid fa-camera-rotate text-sm"></i>
                </button>
                <span class="text-[9px] font-bold text-white/90 mt-1 drop-shadow-md">Balik</span>
            </div>

            <!-- 2. Torch / Flashlight -->
            <div class="flex flex-col items-center">
                <button id="tool-torch" class="glass-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-md" title="Senter / Flash">
                    <i class="fa-solid fa-bolt text-sm"></i>
                </button>
                <span class="text-[9px] font-bold text-white/90 mt-1 drop-shadow-md">Flash</span>
            </div>

            <!-- 3. Kecepatan (Speed) -->
            <div class="relative flex flex-col items-center">
                <button id="tool-speed-btn" class="glass-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-md" title="Atur Kecepatan">
                    <span id="tool-speed-badge" class="text-xs font-black">1x</span>
                </button>
                <span class="text-[9px] font-bold text-white/90 mt-1 drop-shadow-md">Speed</span>

                <!-- Speed Popover -->
                <div id="tool-speed-popover" class="hidden absolute right-12 top-0 glass-pill rounded-2xl p-1.5 shadow-2xl flex items-center gap-1 text-xs text-white z-40 border border-white/20">
                    <button type="button" data-speed-val="0.3" class="px-2.5 py-1 rounded-xl hover:bg-white/20 transition">0.3x</button>
                    <button type="button" data-speed-val="0.5" class="px-2.5 py-1 rounded-xl hover:bg-white/20 transition">0.5x</button>
                    <button type="button" data-speed-val="1.0" class="px-2.5 py-1 rounded-xl hover:bg-white/20 text-sky-400 font-black transition">1x</button>
                    <button type="button" data-speed-val="2.0" class="px-2.5 py-1 rounded-xl hover:bg-white/20 transition">2x</button>
                    <button type="button" data-speed-val="3.0" class="px-2.5 py-1 rounded-xl hover:bg-white/20 transition">3x</button>
                </div>
            </div>

            <!-- 4. Timer (Countdown 3s / 10s) -->
            <div class="relative flex flex-col items-center">
                <button id="tool-timer-btn" class="glass-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-md" title="Timer Perekaman">
                    <i class="fa-solid fa-stopwatch text-sm"></i>
                </button>
                <span id="tool-timer-badge" class="text-[9px] font-bold text-white/90 mt-1 drop-shadow-md">Timer</span>

                <!-- Timer Popover -->
                <div id="tool-timer-popover" class="hidden absolute right-12 top-0 glass-pill rounded-2xl p-1.5 shadow-2xl flex items-center gap-1 text-xs text-white z-40 border border-white/20">
                    <button type="button" data-timer-val="0" class="px-2.5 py-1 rounded-xl hover:bg-white/20 text-sky-400 font-black transition">Mati</button>
                    <button type="button" data-timer-val="3" class="px-2.5 py-1 rounded-xl hover:bg-white/20 transition">3s</button>
                    <button type="button" data-timer-val="10" class="px-2.5 py-1 rounded-xl hover:bg-white/20 transition">10s</button>
                </div>
            </div>

            <!-- 5. Filters / Visual Presets -->
            <div class="flex flex-col items-center">
                <button id="tool-filter-btn" class="glass-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-md" title="Ganti Filter">
                    <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                </button>
                <span class="text-[9px] font-bold text-white/90 mt-1 drop-shadow-md">Filter</span>
            </div>

            <!-- 6. Beauty / Smoothing -->
            <div class="flex flex-col items-center">
                <button id="tool-beauty-btn" class="glass-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-md" title="Efek Halus / Beauty">
                    <i class="fa-solid fa-face-smile-wink text-sm"></i>
                </button>
                <span class="text-[9px] font-bold text-white/90 mt-1 drop-shadow-md">Beauty</span>
            </div>

            <!-- 7. Grid 3x3 Overlay -->
            <div class="flex flex-col items-center">
                <button id="tool-grid-btn" class="glass-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-md" title="Garis Grid">
                    <i class="fa-solid fa-table-cells text-sm"></i>
                </button>
                <span class="text-[9px] font-bold text-white/90 mt-1 drop-shadow-md">Grid</span>
            </div>
        </aside>

        <!-- 4. Camera Bottom Controls -->
        <footer id="camera-bottom-controls" class="relative z-30 pb-safe-bottom pb-6 px-6 space-y-4 pointer-events-auto">
            <!-- Durasi Selector (15s | 60s | 3m) -->
            <div id="video-duration-selector" class="flex items-center justify-center gap-1.5 transition-opacity duration-200">
                <div class="glass-pill rounded-full p-1 flex items-center gap-1">
                    <button type="button" data-creator-duration="15" class="px-2.5 py-0.5 rounded-full text-[11px] font-black text-sky-400 bg-sky-500/20 border border-sky-400/40 shadow-xs">15d</button>
                    <button type="button" data-creator-duration="60" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold text-white/60 hover:text-white">60d</button>
                    <button type="button" data-creator-duration="180" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold text-white/60 hover:text-white">3m</button>
                </div>
            </div>

            <!-- Action Row -->
            <div class="flex items-center justify-between max-w-sm mx-auto">
                <!-- Gallery Picker Thumbnail -->
                <div class="w-14 flex items-center justify-center">
                    <button id="gallery-picker-btn" class="glass-btn w-12 h-12 rounded-2xl flex flex-col items-center justify-center text-white active:scale-90 transition shadow-lg overflow-hidden border border-white/25" title="Pilih dari Galeri">
                        <i class="fa-solid fa-photo-film text-lg text-white"></i>
                    </button>
                </div>

                <!-- Big Circular Record Button -->
                <div class="relative w-24 h-24 flex items-center justify-center">
                    <div id="record-btn-outer-wrapper" class="relative w-20 h-20 rounded-full flex items-center justify-center transition-transform duration-200">
                        <svg class="record-progress-svg absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 88 88">
                            <circle cx="44" cy="44" r="38" fill="none" stroke="rgba(255, 255, 255, 0.25)" stroke-width="5"/>
                            <circle id="record-progress-circle" class="record-progress-circle" cx="44" cy="44" r="38" fill="none" stroke-width="5" stroke-dasharray="238.76" stroke-dashoffset="238.76" stroke-linecap="round"/>
                        </svg>

                        <button id="record-action-btn" class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center active:scale-95 transition-all shadow-2xl focus:outline-none" aria-label="Tombol Rekam / Jepret">
                            <div id="record-btn-inner-shape" class="w-14 h-14 rounded-full bg-rose-500 transition-all transform"></div>
                        </button>
                    </div>
                </div>

                <!-- Right: Undo Clip & Next / Checkmark -->
                <div class="w-14 flex items-center justify-center gap-2">
                    <button id="delete-last-clip-btn" class="hidden glass-btn w-10 h-10 rounded-full flex items-center justify-center text-rose-400 hover:text-rose-300 active:scale-90 transition shadow-lg" title="Hapus klip terakhir">
                        <i class="fa-solid fa-delete-left text-sm"></i>
                    </button>

                    <button id="next-step-btn" class="hidden w-11 h-11 rounded-full bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white flex items-center justify-center active:scale-95 transition shadow-lg shadow-sky-500/30" title="Lanjut ke Editor">
                        <i class="fa-solid fa-check text-base font-black"></i>
                    </button>
                </div>
            </div>

            <!-- Mode Switcher (FOTO | VIDEO) -->
            <div class="flex items-center justify-center">
                <div class="glass-pill rounded-full p-1 flex items-center gap-1">
                    <button type="button" data-creator-mode="photo" class="px-3.5 py-1 rounded-full text-xs font-bold text-white/50 hover:text-white transition">FOTO</button>
                    <button type="button" data-creator-mode="video" class="px-3.5 py-1 rounded-full text-xs font-black text-white bg-white/20 backdrop-blur-md shadow-xs transition">VIDEO</button>
                </div>
            </div>
        </footer>

        <!-- Hidden Input for File Selection (Semua format foto & video tanpa batasan) -->
        <input type="file" id="creator-hidden-file-input" accept="image/*,video/*,.heic,.heif,.*" multiple class="hidden">
    </div>

    <!-- ============================================================== -->
    <!-- SCREEN 2: EDITOR MOMEN (FASE 2: FILTER, OVERLAY TEKS, STIKER & AUDIO) -->
    <!-- ============================================================== -->
    <div id="screen-editor" class="hidden absolute inset-0 w-full h-full bg-black flex flex-col justify-between overflow-hidden z-40">
        <!-- 1. 9:16 Media Viewport -->
        <div id="editor-viewport" class="relative w-full h-full bg-black flex items-center justify-center overflow-hidden cursor-pointer">
            <!-- Video Preview Element -->
            <video 
                id="editor-video-preview" 
                playsinline 
                loop 
                class="hidden w-full h-full object-cover pointer-events-none transition-all duration-300"
            ></video>

            <!-- Image Preview Element -->
            <img 
                id="editor-image-preview" 
                class="hidden w-full h-full object-cover pointer-events-none transition-all duration-300" 
                alt="Pratinjau Momen"
            >

            <!-- Interactive Overlays (Text / Stickers) Container -->
            <div id="editor-overlays-container" class="absolute inset-0 pointer-events-auto overflow-hidden"></div>

            <!-- Center Play / Pause Indicator -->
            <div id="editor-play-indicator" class="pointer-events-none absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-200">
                <div class="w-16 h-16 rounded-full bg-black/60 backdrop-blur-md text-white text-2xl flex items-center justify-center shadow-2xl">
                    <i class="fa-solid fa-play ml-1"></i>
                </div>
            </div>

            <!-- Subtle Editor Toast Notification -->
            <div id="editor-toast" class="absolute top-20 left-1/2 -translate-x-1/2 z-30 pointer-events-none opacity-0 -translate-y-2 transition-all duration-300 glass-pill px-4 py-1.5 rounded-full text-xs font-black text-white shadow-xl flex items-center gap-1.5">
                <i class="fa-solid fa-wand-magic-sparkles text-sky-400 text-xs"></i>
                <span id="editor-toast-text">Filter: Normal</span>
            </div>
        </div>

        <!-- 2. Editor Top Header -->
        <header class="absolute top-0 left-0 right-0 z-30 pt-safe-top px-4 pt-3 flex items-center justify-between pointer-events-auto">
            <!-- Back to Camera Button -->
            <button onclick="window.MomentEditorEngine.backToCamera()" class="glass-btn w-9 h-9 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-lg" title="Kembali ke Kamera">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </button>

            <!-- Pill Musik Latar -->
            <button onclick="window.MomentEditorEngine.openMusicModal()" class="glass-pill px-3.5 py-1.5 rounded-full text-xs font-black text-white flex items-center gap-2 shadow-lg active:scale-95 transition max-w-[200px]">
                <i class="fa-solid fa-music text-sky-400 text-xs shrink-0"></i>
                <span id="editor-music-pill-text" class="truncate">Tambah Musik</span>
                <span id="editor-remove-music-btn" onclick="event.stopPropagation(); window.MomentEditorEngine.removeMusic()" class="hidden ml-0.5 text-white/60 hover:text-white p-0.5" title="Hapus Musik">
                    <i class="fa-solid fa-xmark text-[10px]"></i>
                </span>
            </button>

            <!-- Lanjut ke Detail Postingan (Next Button) -->
            <button onclick="window.MomentEditorEngine.openPublishScreen()" class="px-4 py-1.5 rounded-full bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold text-xs uppercase tracking-wider shadow-lg active:scale-95 transition flex items-center gap-1.5">
                <span>Lanjut</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        </header>

        <!-- 3. Editor Right Floating Toolbar (IG / TikTok Tools) -->
        <aside class="absolute right-3.5 top-28 z-30 flex flex-col items-center gap-4 pointer-events-auto">
            <!-- 1. Teks Overlay (Aa) -->
            <div class="flex flex-col items-center">
                <button onclick="window.MomentEditorEngine.openTextEditor()" class="glass-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-md" title="Tambah Teks">
                    <span class="text-sm font-black">Aa</span>
                </button>
                <span class="text-[9px] font-bold text-white/90 mt-1 drop-shadow-md">Teks</span>
            </div>

            <!-- 2. Filter Visual (✨) -->
            <div class="flex flex-col items-center">
                <button onclick="window.MomentEditorEngine.openFilterDrawer()" class="glass-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-md" title="Pilih Filter">
                    <i class="fa-solid fa-wand-magic-sparkles text-sm text-sky-300"></i>
                </button>
                <span class="text-[9px] font-bold text-white/90 mt-1 drop-shadow-md">Filter</span>
            </div>

            <!-- 3. Stiker / Emoji (😊) -->
            <div class="flex flex-col items-center">
                <button onclick="window.MomentEditorEngine.openStickerDrawer()" class="glass-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-md" title="Tambah Stiker">
                    <i class="fa-regular fa-face-smile text-base text-amber-300"></i>
                </button>
                <span class="text-[9px] font-bold text-white/90 mt-1 drop-shadow-md">Stiker</span>
            </div>

            <!-- 4. Audio / Musik Mixer (🎵) -->
            <div class="flex flex-col items-center">
                <button onclick="window.MomentEditorEngine.openMusicModal()" class="glass-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-md" title="Atur Suara & Musik">
                    <i class="fa-solid fa-sliders text-sm text-emerald-300"></i>
                </button>
                <span class="text-[9px] font-bold text-white/90 mt-1 drop-shadow-md">Audio</span>
            </div>

            <!-- 5. Klip / Timeline Trimming (✂️) -->
            <div id="tool-clip-manager-btn" class="flex flex-col items-center">
                <button onclick="window.MomentEditorEngine.openClipsDrawer()" class="glass-btn w-10 h-10 rounded-full flex items-center justify-center text-white active:scale-90 transition shadow-md" title="Atur & Potong Klip">
                    <i class="fa-solid fa-scissors text-sm text-rose-300"></i>
                </button>
                <span class="text-[9px] font-bold text-white/90 mt-1 drop-shadow-md">Klip</span>
            </div>
        </aside>

        <!-- 4. Video Bottom Progress Timeline (Hanya Video) -->
        <div id="editor-video-timeline" class="absolute bottom-4 left-4 right-4 z-20 pointer-events-none">
            <div class="h-1 bg-white/20 rounded-full overflow-hidden">
                <div class="h-full bg-sky-400 w-full animate-pulse"></div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- DRAWER 1: FILTER VISUAL CAROUSEL -->
        <!-- ============================================================== -->
        <div id="editor-filter-drawer" class="absolute bottom-0 left-0 right-0 z-50 bg-black/85 backdrop-blur-xl border-t border-white/10 p-4 transition-transform duration-300 translate-y-full">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-black text-white flex items-center gap-1.5">
                    <i class="fa-solid fa-wand-magic-sparkles text-sky-400"></i> Filter Efek Visual
                </h4>
                <button onclick="window.MomentEditorEngine.closeFilterDrawer()" class="text-white/60 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <!-- Horizontal Filter Presets Track -->
            <div id="filter-presets-track" class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-none">
                <!-- Injected by editor-engine.js -->
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- DRAWER 2: STIKER & EMOJI DRAWER -->
        <!-- ============================================================== -->
        <div id="editor-sticker-drawer" class="absolute bottom-0 left-0 right-0 z-50 bg-black/85 backdrop-blur-xl border-t border-white/10 p-4 transition-transform duration-300 translate-y-full">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-black text-white flex items-center gap-1.5">
                    <i class="fa-regular fa-face-smile text-amber-400"></i> Stiker & Lencana Rental
                </h4>
                <button onclick="window.MomentEditorEngine.closeStickerDrawer()" class="text-white/60 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div class="grid grid-cols-6 gap-2.5 max-h-48 overflow-y-auto p-1">
                @php
                    $stickers = ['🔥', '✨', '📸', '⭐', '🚀', '🏖️', '🏕️', '⚡', '🎬', '🎯', '💯', '🤩', '🤙', '🙌', '🎉', '❤️', '🛵', '⛺'];
                @endphp
                @foreach($stickers as $stk)
                    <button type="button" onclick="window.MomentEditorEngine.addSticker('{{ $stk }}')" class="h-12 rounded-xl bg-white/10 hover:bg-white/25 active:scale-90 text-2xl flex items-center justify-center transition">
                        {{ $stk }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- DRAWER 3: CLIP MANAGER & TRIMMING -->
        <!-- ============================================================== -->
        <div id="editor-clips-drawer" class="absolute bottom-0 left-0 right-0 z-50 bg-black/85 backdrop-blur-xl border-t border-white/10 p-4 transition-transform duration-300 translate-y-full">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-black text-white flex items-center gap-1.5">
                    <i class="fa-solid fa-scissors text-rose-400"></i> Penyesuaian Klip Video
                </h4>
                <button onclick="window.MomentEditorEngine.closeClipsDrawer()" class="text-white/60 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div id="editor-clips-list" class="space-y-2 max-h-56 overflow-y-auto">
                <!-- Injected dynamically -->
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL 1: TEXT OVERLAY EDITOR (Aa) -->
        <!-- ============================================================== -->
        <div id="editor-text-modal" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex flex-col justify-between p-6">
            <div class="flex items-center justify-between pt-safe-top">
                <button onclick="window.MomentEditorEngine.closeTextEditor()" class="text-white/70 hover:text-white text-xs font-bold px-3 py-1.5 rounded-full glass-btn">
                    Batal
                </button>
                <span class="text-xs font-black text-white">Teks Momen</span>
                <button onclick="window.MomentEditorEngine.saveTextOverlay()" class="px-4 py-1.5 rounded-full bg-sky-500 hover:bg-sky-400 text-white font-extrabold text-xs shadow-md">
                    Selesai
                </button>
            </div>

            <!-- Text Input Area -->
            <div class="my-auto flex items-center justify-center">
                <textarea 
                    id="overlay-text-input" 
                    rows="3" 
                    maxlength="150" 
                    placeholder="Ketik teks di sini..." 
                    class="w-full max-w-sm text-center text-2xl font-black text-white bg-transparent border-none focus:outline-none placeholder-white/30 resize-none leading-relaxed"
                ></textarea>
            </div>

            <!-- Color Palette & Background Toggle -->
            <div class="space-y-3 pb-safe-bottom">
                <div class="flex items-center justify-center gap-3">
                    <button type="button" onclick="window.MomentEditorEngine.activeTextColor='#ffffff'; window.MomentEditorEngine.activeTextBgColor='rgba(0,0,0,0.6)'" class="w-7 h-7 rounded-full bg-white border-2 border-white/50 active:scale-95 shadow-md"></button>
                    <button type="button" onclick="window.MomentEditorEngine.activeTextColor='#38bdf8'; window.MomentEditorEngine.activeTextBgColor='rgba(14,165,233,0.2)'" class="w-7 h-7 rounded-full bg-sky-400 border-2 border-white/50 active:scale-95 shadow-md"></button>
                    <button type="button" onclick="window.MomentEditorEngine.activeTextColor='#fbbf24'; window.MomentEditorEngine.activeTextBgColor='rgba(245,158,11,0.2)'" class="w-7 h-7 rounded-full bg-amber-400 border-2 border-white/50 active:scale-95 shadow-md"></button>
                    <button type="button" onclick="window.MomentEditorEngine.activeTextColor='#f43f5e'; window.MomentEditorEngine.activeTextBgColor='rgba(244,63,94,0.2)'" class="w-7 h-7 rounded-full bg-rose-500 border-2 border-white/50 active:scale-95 shadow-md"></button>
                    <button type="button" onclick="window.MomentEditorEngine.activeTextColor='#10b981'; window.MomentEditorEngine.activeTextBgColor='rgba(16,185,129,0.2)'" class="w-7 h-7 rounded-full bg-emerald-500 border-2 border-white/50 active:scale-95 shadow-md"></button>
                    <button type="button" onclick="window.MomentEditorEngine.activeTextColor='#a855f7'; window.MomentEditorEngine.activeTextBgColor='rgba(168,85,247,0.2)'" class="w-7 h-7 rounded-full bg-purple-500 border-2 border-white/50 active:scale-95 shadow-md"></button>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL 2: MUSIC SELECTOR & AUDIO MIXER -->
        <!-- ============================================================== -->
        <div id="editor-music-modal" class="hidden fixed inset-0 z-50 bg-black/90 backdrop-blur-xl flex flex-col justify-between p-4 sm:p-6">
            <div class="flex items-center justify-between pb-3 border-b border-white/10 pt-safe-top">
                <h3 class="text-sm font-black text-white flex items-center gap-2">
                    <i class="fa-solid fa-music text-sky-400"></i> Musik Latar & Audio Mixer
                </h3>
                <button onclick="window.MomentEditorEngine.closeMusicModal()" class="w-8 h-8 rounded-full glass-btn text-white/70 hover:text-white flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Volume Mixer Sliders -->
            <div class="p-3 my-2 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                <div>
                    <div class="flex items-center justify-between text-xs font-bold text-white/90 mb-1">
                        <span>Suara Asli Video</span>
                        <span id="volume-original-val">100%</span>
                    </div>
                    <input type="range" id="volume-original-slider" min="0" max="1" step="0.05" value="1" class="w-full accent-sky-400">
                </div>
                <div>
                    <div class="flex items-center justify-between text-xs font-bold text-white/90 mb-1">
                        <span>Musik Latar</span>
                        <span id="volume-music-val">80%</span>
                    </div>
                    <input type="range" id="volume-music-slider" min="0" max="1" step="0.05" value="0.8" class="w-full accent-sky-400">
                </div>
            </div>

            <!-- Music Catalog List -->
            <div id="music-tracks-list" class="space-y-2.5 overflow-y-auto max-h-[55vh] py-1">
                <!-- Injected dynamically by editor-engine.js -->
            </div>

            <div class="pt-2">
                <button onclick="window.MomentEditorEngine.closeMusicModal()" class="w-full py-2.5 rounded-xl bg-sky-500 hover:bg-sky-400 text-white font-extrabold text-xs shadow-md">
                    Terapkan Audio
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- SCREEN 3: PUBLISH STEP (CAPTION, TAG PRODUK & SUBMIT) -->
    <!-- ============================================================== -->
    <div id="screen-publish" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-xl flex flex-col justify-end sm:items-center sm:justify-center p-0 sm:p-4">
        <div class="bg-slate-900 border border-white/15 rounded-t-3xl sm:rounded-3xl max-w-md w-full p-6 text-white shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-clapperboard"></i>
                    </div>
                    <h3 class="font-black text-sm text-white">Bagikan ke Feed Momen</h3>
                </div>
                <button onclick="window.MomentEditorEngine.closePublishScreen()" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white/60 hover:text-white flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- Thumbnail Preview & Caption -->
            <div class="flex gap-3.5">
                <div class="w-20 h-28 rounded-xl bg-black border border-white/20 overflow-hidden shrink-0 shadow-md">
                    <img id="publish-preview-thumb" class="w-full h-full object-cover" alt="Thumbnail">
                </div>
                <div class="flex-1 flex flex-col justify-between">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 mb-1">Cerita / Caption</label>
                        <textarea 
                            id="publish-caption-input" 
                            rows="3" 
                            maxlength="1000" 
                            placeholder="Ceritakan keseruan pengalaman sewa barang ini..." 
                            class="w-full text-xs p-2.5 rounded-xl bg-slate-950 border border-white/15 focus:border-sky-400 focus:outline-none transition resize-none text-white placeholder-slate-500"
                        ></textarea>
                    </div>
                    <!-- Hashtag Shortcuts -->
                    <div class="flex flex-wrap gap-1 mt-1">
                        <button type="button" onclick="document.getElementById('publish-caption-input').value += ' #Rentify'" class="text-[9px] px-2 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-sky-300 font-bold transition">#Rentify</button>
                        <button type="button" onclick="document.getElementById('publish-caption-input').value += ' #SewaKamera'" class="text-[9px] px-2 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-sky-300 font-bold transition">#SewaKamera</button>
                        <button type="button" onclick="document.getElementById('publish-caption-input').value += ' #ReviewJujur'" class="text-[9px] px-2 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-sky-300 font-bold transition">#ReviewJujur</button>
                    </div>
                </div>
            </div>

            <!-- Product Tag Selector -->
            <div>
                <label class="block text-[11px] font-bold text-slate-300 mb-1">Tandai Barang Rental yang Dipakai</label>
                <select id="publish-product-tag-select" class="w-full text-xs p-2.5 rounded-xl bg-slate-950 border border-white/15 focus:border-sky-400 focus:outline-none text-white transition">
                    <option value="">-- Pilih barang yang kamu sewa (opsional) --</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}">
                            {{ $prod->nama }} (Rp {{ number_format($prod->harga_sewa_harian ?? 0, 0, ',', '.') }}/hari)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Upload Progress Bar (Aktif saat mengirim) -->
            <div id="publish-progress-container" class="hidden space-y-1">
                <div class="flex items-center justify-between text-[10px] text-slate-400">
                    <span>Mengunggah ke Cloudinary...</span>
                    <span id="publish-progress-percent">0%</span>
                </div>
                <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                    <div id="publish-progress-bar" class="h-full bg-sky-400 w-0 transition-all duration-200"></div>
                </div>
            </div>

            <!-- Submit Action -->
            <div class="pt-2 flex items-center gap-2">
                <button type="button" onclick="window.MomentEditorEngine.closePublishScreen()" class="w-1/3 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition">
                    Kembali
                </button>
                <button type="button" id="publish-submit-btn" onclick="window.MomentEditorEngine.submitPublish(event)" class="w-2/3 py-3 rounded-2xl bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-extrabold text-xs uppercase tracking-wider shadow-lg transition flex items-center justify-center gap-2 active:scale-95">
                    <span id="publish-btn-text">Bagikan Momen</span>
                    <i id="publish-spinner" class="fa-solid fa-circle-notch fa-spin hidden"></i>
                </button>
            </div>
        </div>
    </div>

</div>

<!-- ============================================================== -->
<!-- SCRIPT LOADER MODULAR: CREATOR & EDITOR ENGINE -->
<!-- ============================================================== -->
<script src="{{ asset('js/moments/creator-state.js') }}"></script>
<script src="{{ asset('js/moments/permission-manager.js') }}"></script>
<script src="{{ asset('js/moments/camera-engine.js') }}"></script>
<script src="{{ asset('js/moments/music-provider.js') }}"></script>
<script src="{{ asset('js/moments/editor-engine.js') }}"></script>
<script src="{{ asset('js/moments/creator-ui.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', async function () {
        // Inisialisasi controller UI & Editor Engine
        window.MomentCreatorUI.init();
        window.MomentEditorEngine.init();

        const videoEl = document.getElementById('camera-video-preview');
        window.MomentCameraEngine.init(videoEl);

        // Friendly Permission Flow: periksa dan minta izin secara edukatif
        const granted = await window.MomentPermissionManager.requestWithEducation();
        if (granted) {
            await window.MomentCameraEngine.startStream('environment');
        }
    });

    // Cleanup resources saat navigasi keluar
    window.addEventListener('beforeunload', function () {
        if (window.MomentCameraEngine) {
            window.MomentCameraEngine.stopStream();
        }
        if (window.MomentMusicProvider) {
            window.MomentMusicProvider.stopTrack();
        }
    });
</script>
@endsection
