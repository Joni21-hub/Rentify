/**
 * Rentify Momen Creator - UI Controller (creator-ui.js)
 * Mengontrol seluruh interaksi antarmuka kamera (Familiar IG Reels / TikTok):
 * - Segmented progress bar multi-klip
 * - Large record button dengan animated SVG circular ring
 * - Mode switcher: FOTO | VIDEO
 * - Durasi switcher: 15s | 60s | 3m
 * - Gestur: swipe filter, pinch zoom, tap to focus, double-tap flip
 * - Toolbar kanan: Flip, Flash, Timer, Speed, Filter, Beauty, Grid
 * - Pill Tambah Musik di tengah atas
 * - Hapus klip terakhir & tombol Lanjut
 */
(function(window) {
    'use strict';

    class MomentCreatorUI {
        constructor() {
            this.state = window.MomentCreatorState;
            this.engine = window.MomentCameraEngine;
            this.permission = window.MomentPermissionManager;

            // DOM elements
            this.viewfinderEl = null;
            this.videoEl = null;
            this.recordBtn = null;
            this.recordRing = null;
            this.progressBarContainer = null;
            this.filterToastEl = null;

            // Gestures
            this.touchStartX = 0;
            this.touchStartY = 0;
            this.touchStartTime = 0;
            this.lastTapTime = 0;
            this.initialPinchDistance = 0;
            this.currentZoom = 1.0;
            this.isPressRecording = false;
            this.pressRecordTimer = null;

            // Filter presets
            this.filterList = ['normal', 'warm', 'cool', 'film', 'mono', 'vivid', 'cyberpunk'];
            this.filterLabels = {
                normal: 'Normal',
                warm: 'Warm Vibes',
                cool: 'Cool Breeze',
                film: 'Vintage Film',
                mono: 'Hitam Putih',
                vivid: 'Vivid Pop',
                cyberpunk: 'Cyber Neon'
            };
        }

        /**
         * Inisialisasi UI dan pasang event listener
         */
        init() {
            this.cacheDom();
            this.bindEvents();
            this.bindStateListeners();
            this.renderProgressBar();
            this.updateModeUI(this.state.get('mode'));
            this.updateDurationUI(this.state.get('durationLimit'));
            this.updateSpeedUI(this.state.get('activeSpeed'));
            this.updateTimerUI(this.state.get('timerSeconds'));
        }

        cacheDom() {
            this.viewfinderEl = document.getElementById('camera-viewfinder-container');
            this.videoEl = document.getElementById('camera-video-preview');
            this.recordBtn = document.getElementById('record-action-btn');
            this.recordRing = document.getElementById('record-progress-circle');
            this.progressBarContainer = document.getElementById('multi-clip-progress-bar');
            this.filterToastEl = document.getElementById('filter-name-toast');
        }

        bindEvents() {
            // Viewfinder Gestures: Tap focus, Double-tap flip, Swipe filter, Pinch zoom
            if (this.viewfinderEl) {
                this.setupViewfinderGestures(this.viewfinderEl);
            }

            // Record Button interactions
            if (this.recordBtn) {
                this.setupRecordButtonInteractions(this.recordBtn);
            }

            // Mode Selector (FOTO | VIDEO)
            const modeBtns = document.querySelectorAll('[data-creator-mode]');
            modeBtns.forEach(btn => {
                btn.onclick = () => {
                    const mode = btn.getAttribute('data-creator-mode');
                    this.state.setMode(mode);
                };
            });

            // Duration Selector (15s | 60s | 3m)
            const durationBtns = document.querySelectorAll('[data-creator-duration]');
            durationBtns.forEach(btn => {
                btn.onclick = () => {
                    const duration = parseInt(btn.getAttribute('data-creator-duration'), 10);
                    this.state.setDurationLimit(duration);
                };
            });

            // Toolbar: Flip Camera
            const flipBtn = document.getElementById('tool-flip-camera');
            if (flipBtn) {
                flipBtn.onclick = () => this.engine.switchCamera();
            }

            // Toolbar: Torch/Flash
            const torchBtn = document.getElementById('tool-torch');
            if (torchBtn) {
                torchBtn.onclick = async () => {
                    const nextTorch = !this.state.get('torchActive');
                    const success = await this.engine.toggleTorch(nextTorch);
                    if (success) {
                        this.state.set('torchActive', nextTorch);
                        torchBtn.classList.toggle('text-amber-400', nextTorch);
                        torchBtn.classList.toggle('bg-amber-400/20', nextTorch);
                    }
                };
            }

            // Toolbar: Speed Toggle
            const speedBtn = document.getElementById('tool-speed-btn');
            const speedPopover = document.getElementById('tool-speed-popover');
            if (speedBtn && speedPopover) {
                speedBtn.onclick = (e) => {
                    e.stopPropagation();
                    speedPopover.classList.toggle('hidden');
                };
                const speedOptions = speedPopover.querySelectorAll('[data-speed-val]');
                speedOptions.forEach(opt => {
                    opt.onclick = (e) => {
                        e.stopPropagation();
                        const val = parseFloat(opt.getAttribute('data-speed-val'));
                        this.state.setSpeed(val);
                        speedPopover.classList.add('hidden');
                    };
                });
            }

            // Toolbar: Timer Toggle
            const timerBtn = document.getElementById('tool-timer-btn');
            const timerPopover = document.getElementById('tool-timer-popover');
            if (timerBtn && timerPopover) {
                timerBtn.onclick = (e) => {
                    e.stopPropagation();
                    timerPopover.classList.toggle('hidden');
                };
                const timerOptions = timerPopover.querySelectorAll('[data-timer-val]');
                timerOptions.forEach(opt => {
                    opt.onclick = (e) => {
                        e.stopPropagation();
                        const val = parseInt(opt.getAttribute('data-timer-val'), 10);
                        this.state.setTimer(val);
                        timerPopover.classList.add('hidden');
                    };
                });
            }

            // Toolbar: Grid 3x3 Overlay
            const gridBtn = document.getElementById('tool-grid-btn');
            const gridOverlay = document.getElementById('camera-grid-overlay');
            if (gridBtn) {
                gridBtn.onclick = () => {
                    const active = this.state.toggleGrid();
                    gridBtn.classList.toggle('text-sky-400', active);
                    gridBtn.classList.toggle('bg-sky-500/20', active);
                    if (gridOverlay) gridOverlay.classList.toggle('hidden', !active);
                };
            }

            // Toolbar: Beauty / Smooth Filter
            const beautyBtn = document.getElementById('tool-beauty-btn');
            if (beautyBtn) {
                beautyBtn.onclick = () => {
                    const active = this.state.toggleBeauty();
                    beautyBtn.classList.toggle('text-rose-400', active);
                    beautyBtn.classList.toggle('bg-rose-500/20', active);
                    this.applyFilter(this.state.get('activeFilter'));
                    this.showFilterToast(active ? 'Beauty Mode: Aktif' : 'Beauty Mode: Nonaktif');
                };
            }

            // Toolbar: Filter Switcher Button
            const filterToolBtn = document.getElementById('tool-filter-btn');
            if (filterToolBtn) {
                filterToolBtn.onclick = () => {
                    this.cycleFilter(1);
                };
            }

            // Music Pill Header
            const musicPill = document.getElementById('top-music-pill');
            if (musicPill) {
                musicPill.onclick = () => {
                    this.openMusicSelector();
                };
            }

            const removeMusicBtn = document.getElementById('remove-music-btn');
            if (removeMusicBtn) {
                removeMusicBtn.onclick = (e) => {
                    e.stopPropagation();
                    this.state.removeMusic();
                };
            }

            // Close Button (X)
            const closeBtn = document.getElementById('close-creator-btn');
            if (closeBtn) {
                closeBtn.onclick = () => this.handleCloseCreator();
            }

            // Undo / Delete Last Clip Button
            const deleteClipBtn = document.getElementById('delete-last-clip-btn');
            if (deleteClipBtn) {
                deleteClipBtn.onclick = () => {
                    this.state.removeLastClip();
                    this.engine.vibrate(20);
                };
            }

            // Next / Done Button -> Ke Editor (Fase 2)
            const nextStepBtn = document.getElementById('next-step-btn');
            if (nextStepBtn) {
                nextStepBtn.onclick = () => {
                    this.handleNextStep();
                };
            }

            // Gallery picker trigger
            const galleryThumbnailBtn = document.getElementById('gallery-picker-btn');
            if (galleryThumbnailBtn) {
                galleryThumbnailBtn.onclick = () => this.openFilePicker();
            }

            const hiddenFileInput = document.getElementById('creator-hidden-file-input');
            if (hiddenFileInput) {
                hiddenFileInput.onchange = (e) => this.handleFileSelection(e);
            }

            // Close any open popovers on click outside
            document.addEventListener('click', () => {
                if (speedPopover) speedPopover.classList.add('hidden');
                if (timerPopover) timerPopover.classList.add('hidden');
            });
        }

        bindStateListeners() {
            // Reaktif terhadap durasi klip dan recording
            this.state.on('change:isRecording', ({ value }) => {
                this.updateRecordingUI(value);
            });

            this.state.on('change:recordingElapsed', () => {
                this.updateProgressBar();
                this.updateCircularProgressRing();
            });

            this.state.on('clips:updated', () => {
                this.renderProgressBar();
                this.updateActionButtonsVisibility();
            });

            this.state.on('change:mode', ({ value }) => {
                this.updateModeUI(value);
            });

            this.state.on('change:durationLimit', ({ value }) => {
                this.updateDurationUI(value);
                this.renderProgressBar();
            });

            this.state.on('change:activeSpeed', ({ value }) => {
                this.updateSpeedUI(value);
            });

            this.state.on('change:timerSeconds', ({ value }) => {
                this.updateTimerUI(value);
            });

            this.state.on('change:activeFilter', ({ value }) => {
                this.applyFilter(value);
                this.showFilterToast(`Filter: ${this.filterLabels[value] || value}`);
            });

            this.state.on('change:selectedMusic', ({ value }) => {
                this.updateMusicPillUI(value);
            });

            this.state.on('photo:captured', () => {
                this.updateActionButtonsVisibility();
                this.handleNextStep();
            });
        }

        // ==========================================
        // Viewfinder Touch Gestures
        // ==========================================

        setupViewfinderGestures(container) {
            // Double-Tap to Flip & Single-Tap to Focus
            container.addEventListener('click', (e) => {
                // Ignore if clicked on toolbar or controls
                if (e.target.closest('#camera-right-toolbar, #camera-top-header, #camera-bottom-controls')) {
                    return;
                }

                const currentTime = Date.now();
                const tapGap = currentTime - this.lastTapTime;

                if (tapGap < 300) {
                    // Double tap: Flip camera
                    this.engine.switchCamera();
                    this.lastTapTime = 0;
                } else {
                    // Single tap: Focus
                    this.lastTapTime = currentTime;
                    setTimeout(() => {
                        if (this.lastTapTime === currentTime) {
                            this.engine.triggerFocus(e.clientX, e.clientY);
                        }
                    }, 310);
                }
            });

            // Touchstart for Swipe & Pinch
            container.addEventListener('touchstart', (e) => {
                if (e.target.closest('#camera-right-toolbar, #camera-top-header, #camera-bottom-controls')) return;

                if (e.touches.length === 1) {
                    this.touchStartX = e.touches[0].clientX;
                    this.touchStartY = e.touches[0].clientY;
                    this.touchStartTime = Date.now();
                } else if (e.touches.length === 2) {
                    // Pinch zoom start
                    this.initialPinchDistance = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    this.currentZoom = this.state.get('zoom') || 1.0;
                }
            }, { passive: true });

            // Touchmove for Pinch Zoom
            container.addEventListener('touchmove', (e) => {
                if (e.touches.length === 2 && this.initialPinchDistance > 0) {
                    const currentDistance = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    const diff = (currentDistance - this.initialPinchDistance) / 100;
                    const nextZoom = Math.max(1.0, Math.min(5.0, this.currentZoom + diff));
                    this.engine.applyZoom(nextZoom);
                }
            }, { passive: true });

            // Touchend for Horizontal Swipe (Ganti Filter IG Style)
            container.addEventListener('touchend', (e) => {
                if (e.target.closest('#camera-right-toolbar, #camera-top-header, #camera-bottom-controls')) return;

                if (e.changedTouches.length === 1 && this.touchStartX !== 0) {
                    const deltaX = e.changedTouches[0].clientX - this.touchStartX;
                    const deltaY = e.changedTouches[0].clientY - this.touchStartY;
                    const deltaTime = Date.now() - this.touchStartTime;

                    // Swipe horizontal signifikan jika jarak horizontal > 50px dan lebih besar dari vertikal
                    if (Math.abs(deltaX) > 50 && Math.abs(deltaX) > Math.abs(deltaY) * 1.5 && deltaTime < 400) {
                        if (deltaX > 0) {
                            // Swipe ke kanan -> filter sebelumnya
                            this.cycleFilter(-1);
                        } else {
                            // Swipe ke kiri -> filter berikutnya
                            this.cycleFilter(1);
                        }
                    }
                }
                this.touchStartX = 0;
                this.touchStartY = 0;
                this.initialPinchDistance = 0;
            }, { passive: true });
        }

        // ==========================================
        // Record Button Interactions
        // ==========================================

        setupRecordButtonInteractions(btn) {
            // Mode Photo: Klik -> Langsung jepret foto
            // Mode Video:
            // 1. Tap untuk mulai/stop
            // 2. Tahan (Hold) untuk merekam, lepas untuk stop
            let pointerDownTime = 0;
            let isHoldTriggered = false;

            const handlePointerDown = (e) => {
                e.preventDefault();
                pointerDownTime = Date.now();
                isHoldTriggered = false;

                const mode = this.state.get('mode');
                if (mode === 'photo') {
                    this.capturePhotoWithTimer();
                    return;
                }

                // Mode Video: Jika sedang merekam via tap sebelumnya, tap lagi untuk stop
                if (this.state.get('isRecording')) {
                    this.engine.stopRecording();
                    return;
                }

                // Deteksi hold setelah 280ms
                this.pressRecordTimer = setTimeout(() => {
                    isHoldTriggered = true;
                    this.isPressRecording = true;
                    this.startRecordingWithTimer();
                }, 280);
            };

            const handlePointerUp = (e) => {
                e.preventDefault();
                clearTimeout(this.pressRecordTimer);

                const mode = this.state.get('mode');
                if (mode === 'photo') return;

                const elapsed = Date.now() - pointerDownTime;

                if (isHoldTriggered && this.isPressRecording) {
                    // Hold dilepas -> stop recording
                    this.isPressRecording = false;
                    this.engine.stopRecording();
                } else if (elapsed < 280 && !this.state.get('isRecording')) {
                    // Short tap -> mulai recording (tap to record)
                    this.startRecordingWithTimer();
                }
            };

            btn.addEventListener('touchstart', handlePointerDown, { passive: false });
            btn.addEventListener('touchend', handlePointerUp, { passive: false });
            btn.addEventListener('mousedown', handlePointerDown);
            btn.addEventListener('mouseup', handlePointerUp);
        }

        /**
         * Mulai rekam dengan countdown timer jika timer aktif
         */
        async startRecordingWithTimer() {
            const timer = this.state.get('timerSeconds');
            if (timer > 0) {
                await this.runCountdown(timer);
            }
            this.engine.startRecording();
        }

        /**
         * Jepret foto dengan countdown timer jika timer aktif
         */
        async capturePhotoWithTimer() {
            const timer = this.state.get('timerSeconds');
            if (timer > 0) {
                await this.runCountdown(timer);
            }
            this.engine.capturePhoto();
        }

        /**
         * Animasi hitungan mundur 3... 2... 1...
         * @param {number} seconds 
         */
        runCountdown(seconds) {
            return new Promise((resolve) => {
                let overlay = document.getElementById('camera-countdown-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.id = 'camera-countdown-overlay';
                    overlay.className = 'fixed inset-0 z-40 bg-black/40 backdrop-blur-xs flex items-center justify-center pointer-events-none';
                    overlay.innerHTML = `<span id="countdown-number" class="text-white text-8xl font-black drop-shadow-2xl animate-pulse"></span>`;
                    document.body.appendChild(overlay);
                }

                overlay.classList.remove('hidden');
                const numEl = overlay.querySelector('#countdown-number');
                let remaining = seconds;

                const tick = () => {
                    if (remaining > 0) {
                        numEl.textContent = remaining;
                        this.engine.playBeep(remaining === 1 ? 880 : 440, 0.15);
                        this.engine.vibrate(20);
                        remaining--;
                        setTimeout(tick, 1000);
                    } else {
                        overlay.classList.add('hidden');
                        resolve();
                    }
                };
                tick();
            });
        }

        // ==========================================
        // UI Renderers & Updaters
        // ==========================================

        updateModeUI(mode) {
            const isVideo = (mode === 'video');
            const videoControls = document.getElementById('video-duration-selector');
            if (videoControls) {
                videoControls.classList.toggle('hidden', !isVideo);
            }

            // Update tab styles
            document.querySelectorAll('[data-creator-mode]').forEach(btn => {
                const btnMode = btn.getAttribute('data-creator-mode');
                const isActive = (btnMode === mode);
                btn.className = isActive 
                    ? 'px-3.5 py-1 rounded-full text-xs font-black text-white bg-white/20 backdrop-blur-md shadow-xs transition'
                    : 'px-3.5 py-1 rounded-full text-xs font-bold text-white/50 hover:text-white transition';
            });

            // Update record button center look
            const centerIcon = document.getElementById('record-btn-inner-shape');
            if (centerIcon) {
                if (mode === 'photo') {
                    centerIcon.className = 'w-14 h-14 rounded-full bg-white transition-all transform';
                } else {
                    centerIcon.className = 'w-14 h-14 rounded-full bg-rose-500 transition-all transform';
                }
            }
        }

        updateDurationUI(duration) {
            document.querySelectorAll('[data-creator-duration]').forEach(btn => {
                const durVal = parseInt(btn.getAttribute('data-creator-duration'), 10);
                const isActive = (durVal === duration);
                btn.className = isActive
                    ? 'px-2.5 py-0.5 rounded-full text-[11px] font-black text-sky-400 bg-sky-500/20 border border-sky-400/40 shadow-xs'
                    : 'px-2.5 py-0.5 rounded-full text-[11px] font-bold text-white/60 hover:text-white';
            });
        }

        updateSpeedUI(speed) {
            const badge = document.getElementById('tool-speed-badge');
            if (badge) {
                badge.textContent = `${speed}x`;
            }
            document.querySelectorAll('[data-speed-val]').forEach(opt => {
                const val = parseFloat(opt.getAttribute('data-speed-val'));
                const isActive = (val === speed);
                opt.classList.toggle('text-sky-400', isActive);
                opt.classList.toggle('font-black', isActive);
            });
        }

        updateTimerUI(seconds) {
            const badge = document.getElementById('tool-timer-badge');
            if (badge) {
                badge.textContent = seconds > 0 ? `${seconds}s` : 'Mati';
            }
            document.querySelectorAll('[data-timer-val]').forEach(opt => {
                const val = parseInt(opt.getAttribute('data-timer-val'), 10);
                const isActive = (val === seconds);
                opt.classList.toggle('text-sky-400', isActive);
                opt.classList.toggle('font-black', isActive);
            });
        }

        updateMusicPillUI(music) {
            const titleEl = document.getElementById('music-pill-title');
            const removeBtn = document.getElementById('remove-music-btn');
            if (!titleEl) return;

            if (music && music.title) {
                titleEl.textContent = `${music.title} • ${music.artist || 'Musik'}`;
                if (removeBtn) removeBtn.classList.remove('hidden');
            } else {
                titleEl.textContent = 'Tambah Musik';
                if (removeBtn) removeBtn.classList.add('hidden');
            }
        }

        updateRecordingUI(isRecording) {
            const innerShape = document.getElementById('record-btn-inner-shape');
            const outerCircle = document.getElementById('record-btn-outer-wrapper');

            if (isRecording) {
                // Morf ke kotak merah saat merekam
                if (innerShape) {
                    innerShape.className = 'w-7 h-7 rounded-lg bg-rose-500 animate-pulse transition-all transform';
                }
                if (outerCircle) {
                    outerCircle.classList.add('scale-110');
                }
                // Sembunyikan elemen non-rekam
                document.getElementById('camera-right-toolbar')?.classList.add('opacity-30', 'pointer-events-none');
                document.getElementById('video-duration-selector')?.classList.add('opacity-0');
            } else {
                if (innerShape) {
                    const mode = this.state.get('mode');
                    innerShape.className = mode === 'photo' 
                        ? 'w-14 h-14 rounded-full bg-white transition-all transform'
                        : 'w-14 h-14 rounded-full bg-rose-500 transition-all transform';
                }
                if (outerCircle) {
                    outerCircle.classList.remove('scale-110');
                }
                document.getElementById('camera-right-toolbar')?.classList.remove('opacity-30', 'pointer-events-none');
                document.getElementById('video-duration-selector')?.classList.remove('opacity-0');
            }
        }

        /**
         * Render ulang segmented progress bar di atas layar
         */
        renderProgressBar() {
            if (!this.progressBarContainer) return;
            this.progressBarContainer.innerHTML = '';

            const clips = this.state.get('clips');
            const limit = this.state.get('durationLimit');

            clips.forEach((clip, index) => {
                const widthPercent = (clip.duration / limit) * 100;
                const seg = document.createElement('div');
                seg.className = 'h-1.5 rounded-full bg-sky-400 relative overflow-hidden transition-all';
                seg.style.width = `${widthPercent}%`;
                this.progressBarContainer.appendChild(seg);
            });

            // Active recording segment placeholder
            const activeSeg = document.createElement('div');
            activeSeg.id = 'active-clip-segment';
            activeSeg.className = 'h-1.5 rounded-full bg-rose-500 relative transition-all duration-75';
            activeSeg.style.width = '0%';
            this.progressBarContainer.appendChild(activeSeg);
        }

        updateProgressBar() {
            const activeSeg = document.getElementById('active-clip-segment');
            if (!activeSeg) return;

            const isRecording = this.state.get('isRecording');
            if (!isRecording) {
                activeSeg.style.width = '0%';
                return;
            }

            const elapsed = this.state.get('recordingElapsed') || 0;
            const limit = this.state.get('durationLimit');
            const widthPercent = (elapsed / limit) * 100;
            activeSeg.style.width = `${widthPercent}%`;
        }

        updateCircularProgressRing() {
            if (!this.recordRing) return;
            const limit = this.state.get('durationLimit');
            const totalRecorded = this.state.getTotalRecordedDuration();
            const elapsed = this.state.get('isRecording') ? (this.state.get('recordingElapsed') || 0) : 0;
            const currentTotal = totalRecorded + elapsed;

            const radius = 38; // Radius SVG
            const circumference = 2 * Math.PI * radius;
            const progress = Math.min(1.0, currentTotal / limit);
            const offset = circumference - (progress * circumference);

            this.recordRing.style.strokeDashoffset = offset;
        }

        updateActionButtonsVisibility() {
            const clips = this.state.get('clips');
            const hasClips = clips.length > 0;
            const hasPhoto = this.state.get('capturedPhoto') !== null;
            const hasContent = hasClips || hasPhoto;

            const deleteClipBtn = document.getElementById('delete-last-clip-btn');
            const nextStepBtn = document.getElementById('next-step-btn');

            if (deleteClipBtn) {
                deleteClipBtn.classList.toggle('hidden', !hasClips);
            }
            if (nextStepBtn) {
                nextStepBtn.classList.toggle('hidden', !hasContent);
            }
        }

        // ==========================================
        // Filter System
        // ==========================================

        cycleFilter(direction = 1) {
            const currentFilter = this.state.get('activeFilter');
            let idx = this.filterList.indexOf(currentFilter);
            if (idx === -1) idx = 0;

            let nextIdx = (idx + direction + this.filterList.length) % this.filterList.length;
            const nextFilter = this.filterList[nextIdx];
            this.state.setFilter(nextFilter);
            this.engine.vibrate(15);
        }

        applyFilter(filterName) {
            if (!this.videoEl) return;
            let filterCss = this.engine.getFilterCss(filterName);

            // Tambahkan efek beauty jika aktif
            if (this.state.get('isBeautyActive')) {
                filterCss = filterCss === 'none' 
                    ? 'brightness(1.06) contrast(0.96) blur(0.4px)' 
                    : `${filterCss} brightness(1.06) contrast(0.96) blur(0.4px)`;
            }

            this.videoEl.style.filter = filterCss;
        }

        showFilterToast(text) {
            if (!this.filterToastEl) return;
            this.filterToastEl.textContent = text;
            this.filterToastEl.classList.remove('opacity-0', '-translate-y-2');
            this.filterToastEl.classList.add('opacity-100', 'translate-y-0');

            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => {
                this.filterToastEl.classList.remove('opacity-100', 'translate-y-0');
                this.filterToastEl.classList.add('opacity-0', '-translate-y-2');
            }, 1200);
        }

        // ==========================================
        // Navigation & Handlers
        // ==========================================

        handleCloseCreator() {
            if (this.state.hasRecordedContent()) {
                Swal.fire({
                    title: 'Buang rekaman?',
                    text: 'Klip atau foto yang baru kamu rekam akan hilang jika keluar sekarang.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Buang & Keluar',
                    cancelButtonText: 'Tetap di Sini',
                    customClass: {
                        popup: 'rounded-3xl p-6 bg-slate-900 text-white border border-white/10 shadow-2xl',
                        confirmButton: 'bg-rose-500 hover:bg-rose-600 text-white font-extrabold py-2.5 px-6 rounded-2xl mr-2',
                        cancelButton: 'bg-white/10 hover:bg-white/20 text-white font-semibold py-2.5 px-6 rounded-xl'
                    },
                    buttonsStyling: false
                }).then((res) => {
                    if (res.isConfirmed) {
                        this.state.reset();
                        this.engine.stopStream();
                        if (window.history.length > 1) {
                            window.history.back();
                        } else {
                            window.location.href = '/moments';
                        }
                    }
                });
            } else {
                this.engine.stopStream();
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = '/moments';
                }
            }
        }

        openFilePicker() {
            const input = document.getElementById('creator-hidden-file-input');
            if (input) input.click();
        }

        async handleFileSelection(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) return;

            const mime = file.type || '';
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            const videoExts = ['mp4', 'mov', 'quicktime', 'webm', 'mkv', 'avi', 'wmv', 'flv', '3gp', '3g2', 'm4v', 'ts', 'mpeg', 'mpg', 'ogv', 'vob', 'm2ts', 'divx', 'rm', 'rmvb', 'asf', 'm4p', 'f4v'];
            const isVideo = mime.startsWith('video/') || videoExts.includes(ext);

            if (isVideo) {
                // Buat klip dari file video tanpa batasan format/ukuran
                const url = URL.createObjectURL(file);
                const tempVideo = document.createElement('video');
                tempVideo.preload = 'metadata';
                tempVideo.src = url;
                tempVideo.onloadedmetadata = () => {
                    this.state.addClip({
                        blob: file,
                        duration: tempVideo.duration || 5,
                        speed: 1.0,
                        filter: 'normal'
                    });
                    this.handleNextStep();
                };
                tempVideo.onerror = () => {
                    this.state.addClip({
                        blob: file,
                        duration: 5,
                        speed: 1.0,
                        filter: 'normal'
                    });
                    this.handleNextStep();
                };
            } else {
                // Foto dari galeri (mendukung konversi HEIC jika tersedia)
                let targetFile = file;
                if (['heic', 'heif'].includes(ext) && typeof heic2any === 'function') {
                    try {
                        const convertedBlob = await heic2any({
                            blob: file,
                            toType: 'image/jpeg',
                            quality: 0.9
                        });
                        const blob = Array.isArray(convertedBlob) ? convertedBlob[0] : convertedBlob;
                        targetFile = new File([blob], file.name.replace(/\.(heic|heif)$/i, '.jpg'), { type: 'image/jpeg' });
                    } catch (err) {
                        console.warn('heic2any failed in creator-ui, fallback to original:', err);
                    }
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.state.setCapturedPhoto(targetFile, e.target.result);
                    this.handleNextStep();
                };
                reader.readAsDataURL(targetFile);
            }
        }

        openMusicSelector() {
            if (window.MomentEditorEngine) {
                window.MomentEditorEngine.openMusicModal();
            }
        }

        handleNextStep() {
            // Lanjut ke Editor (Fase 2)
            if (window.MomentEditorEngine) {
                window.MomentEditorEngine.open();
            }
        }
    }

    window.MomentCreatorUI = new MomentCreatorUI();
})(window);
