/**
 * Rentify Momen Creator - Permission Manager (permission-manager.js)
 * Mengelola flow izin kamera & mikrofon secara ramah (friendly permission flow):
 * 1. Edukasi sebelum popup sistem browser
 * 2. Deteksi penolakan & panduan unblock di pengaturan perangkat
 * 3. Graceful fallback ke pemilih galeri jika izin ditolak
 */
(function(window) {
    'use strict';

    class MomentPermissionManager {
        constructor() {
            this.modalEl = null;
            this.deniedModalEl = null;
        }

        /**
         * Periksa status izin kamera jika Permissions API didukung
         */
        async checkStatus() {
            if (!navigator.permissions || !navigator.permissions.query) {
                return 'prompt';
            }
            try {
                const res = await navigator.permissions.query({ name: 'camera' });
                return res.state; // 'granted', 'denied', 'prompt'
            } catch (e) {
                return 'prompt';
            }
        }

        /**
         * Tampilkan modal penjelasan ramah sebelum memicu popup browser bawaan
         * @returns {Promise<boolean>} True jika pengguna menekan Izinkan
         */
        requestWithEducation() {
            return new Promise((resolve) => {
                // Buat modal DOM jika belum ada
                let modal = document.getElementById('camera-permission-modal');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'camera-permission-modal';
                    modal.className = 'fixed inset-0 z-[100] bg-black/80 backdrop-blur-md flex items-end sm:items-center justify-center p-4 transition-opacity duration-300';
                    modal.innerHTML = `
                        <div class="w-full max-w-sm bg-slate-900 border border-white/15 rounded-3xl p-6 text-white shadow-2xl space-y-4 animate-in fade-in slide-in-from-bottom duration-300">
                            <div class="w-16 h-16 rounded-2xl bg-sky-500/20 text-sky-400 border border-sky-400/30 flex items-center justify-center text-2xl mx-auto shadow-inner">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                            <div class="text-center space-y-1.5">
                                <h3 class="text-base font-black tracking-tight text-white">Izinkan Akses Kamera & Mikrofon</h3>
                                <p class="text-xs text-slate-300 leading-relaxed">
                                    Rentify membutuhkan akses kamera dan mikrofon agar kamu dapat merekam video Momen, mengambil foto, dan merekam suara secara langsung seperti di Instagram Reels & TikTok.
                                </p>
                            </div>
                            <div class="space-y-2 pt-2">
                                <button id="perm-allow-btn" class="w-full py-3 rounded-2xl bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-black text-xs uppercase tracking-wider shadow-lg active:scale-95 transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    <span>Lanjutkan & Berikan Izin</span>
                                </button>
                                <button id="perm-gallery-btn" class="w-full py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-slate-300 font-bold text-xs transition">
                                    Gunakan File dari Galeri Saja
                                </button>
                                <button id="perm-cancel-btn" class="w-full py-2 text-center text-[11px] text-slate-400 hover:text-white font-medium transition">
                                    Nanti Saja
                                </button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }

                modal.classList.remove('hidden');

                const allowBtn = modal.querySelector('#perm-allow-btn');
                const galleryBtn = modal.querySelector('#perm-gallery-btn');
                const cancelBtn = modal.querySelector('#perm-cancel-btn');

                const cleanup = () => {
                    modal.classList.add('hidden');
                };

                allowBtn.onclick = () => {
                    cleanup();
                    resolve(true);
                };

                galleryBtn.onclick = () => {
                    cleanup();
                    // Buka gallery picker fallback
                    if (window.MomentCreatorUI && window.MomentCreatorUI.openFilePicker) {
                        window.MomentCreatorUI.openFilePicker();
                    }
                    resolve(false);
                };

                cancelBtn.onclick = () => {
                    cleanup();
                    resolve(false);
                };
            });
        }

        /**
         * Tampilkan panduan pemulihan ketika browser memblokir izin kamera
         * @param {string} errorType 
         */
        showPermissionDeniedGuide(errorType = 'NotAllowedError') {
            let modal = document.getElementById('camera-denied-guide-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'camera-denied-guide-modal';
                modal.className = 'fixed inset-0 z-[110] bg-black/85 backdrop-blur-md flex items-center justify-center p-4';
                modal.innerHTML = `
                    <div class="w-full max-w-sm bg-slate-900 border border-rose-500/30 rounded-3xl p-6 text-white shadow-2xl space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-rose-500/20 text-rose-400 border border-rose-500/40 flex items-center justify-center text-2xl mx-auto">
                            <i class="fa-solid fa-video-slash"></i>
                        </div>
                        <div class="text-center space-y-1.5">
                            <h3 class="text-sm font-black text-white">Akses Kamera Terhalang</h3>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                Izin akses kamera atau mikrofon ditolak oleh browser. Jangan khawatir, kamu tetap bisa membuat Momen menggunakan file dari galerimu!
                            </p>
                        </div>
                        <div class="bg-black/40 rounded-2xl p-3 text-left border border-white/10 space-y-2 text-[11px] text-slate-300">
                            <div class="font-bold text-sky-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-info"></i> Cara Mengaktifkan Kamera:
                            </div>
                            <ol class="list-decimal list-inside space-y-1 text-slate-400 text-[10.5px]">
                                <li>Klik ikon <strong>Kunci / Pengaturan Situs</strong> di bilah alamat browser.</li>
                                <li>Ubah status <strong>Kamera & Mikrofon</strong> menjadi <strong>Izinkan (Allow)</strong>.</li>
                                <li>Muat ulang (refresh) halaman ini.</li>
                            </ol>
                        </div>
                        <div class="space-y-2 pt-1">
                            <button id="denied-gallery-fallback-btn" class="w-full py-3 rounded-2xl bg-gradient-to-r from-sky-400 to-sky-600 hover:from-sky-500 hover:to-sky-700 text-white font-black text-xs uppercase tracking-wider shadow-lg active:scale-95 transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-photo-film"></i>
                                <span>Pilih Foto/Video dari Galeri</span>
                            </button>
                            <button id="denied-close-btn" class="w-full py-2 text-center text-xs text-slate-400 hover:text-white font-semibold transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            modal.classList.remove('hidden');

            modal.querySelector('#denied-gallery-fallback-btn').onclick = () => {
                modal.classList.add('hidden');
                if (window.MomentCreatorUI && window.MomentCreatorUI.openFilePicker) {
                    window.MomentCreatorUI.openFilePicker();
                }
            };

            modal.querySelector('#denied-close-btn').onclick = () => {
                modal.classList.add('hidden');
            };
        }
    }

    window.MomentPermissionManager = new MomentPermissionManager();
})(window);
