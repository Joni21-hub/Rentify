/**
 * Rentify Momen Editor Engine (editor-engine.js)
 * Fase 2: Filter Efek Visual, Penyesuaian Klip, Teks Overlay & Musik
 * Mengelola preview video/foto 9:16, real-time CSS filter, overlay teks/stiker interaktif,
 * penggabungan audio & submit payload ke backend API.
 */
(function(window) {
    'use strict';

    class MomentEditorEngine {
        constructor() {
            this.state = window.MomentCreatorState;
            this.musicProvider = window.MomentMusicProvider;

            this.mediaType = 'video'; // 'video' | 'photo'
            this.mediaUrl = null;
            this.mediaBlob = null;
            this.mediaItems = []; // Carousel items: [{ id, type: 'photo'|'video', blob, url, dataUrl, duration, name }]
            this.activeSlideIndex = 0;
            this.isMirrored = false;
            this.activeFilter = 'normal';
            this.overlays = []; // Array of { id, type: 'text'|'sticker', text, emoji, color, bgColor, x, y, size }
            this.selectedOverlayId = null;

            this.volumeOriginal = 1.0;
            this.volumeMusic = 0.8;
            this.selectedMusic = null;

            this.currentClipIndex = 0;
            this.isPlaying = false;
            this.videoEl = null;
            this.imageEl = null;
            this.overlayContainer = null;

            this.filterPresets = {
                'normal': { name: 'Normal', css: 'none' },
                'warm': { name: 'Warm Sunset', css: 'sepia(0.28) saturate(1.35) contrast(1.05)' },
                'cool': { name: 'Cool Breeze', css: 'saturate(1.1) hue-rotate(185deg) brightness(1.05)' },
                'vintage': { name: 'Vintage 90s', css: 'sepia(0.55) contrast(1.2) brightness(0.92) saturate(1.2)' },
                'cyberpunk': { name: 'Cyber Neon', css: 'contrast(1.3) saturate(1.8) hue-rotate(290deg)' },
                'noir': { name: 'Classic Noir', css: 'grayscale(1) contrast(1.35) brightness(0.95)' },
                'cinematic': { name: 'Teal & Orange', css: 'contrast(1.2) saturate(1.25) brightness(1.0) hue-rotate(345deg)' },
                'vivid': { name: 'Vivid Pop', css: 'saturate(1.6) contrast(1.15) brightness(1.05)' }
            };

            this.dragState = null;
        }

        init() {
            this.videoEl = document.getElementById('editor-video-preview');
            this.imageEl = document.getElementById('editor-image-preview');
            this.overlayContainer = document.getElementById('editor-overlays-container');

            this.bindEvents();
            this.setupViewportSwipeGestures();
            this.renderFilterDrawer();
            this.renderMusicCatalog();

            const addMediaInput = document.getElementById('editor-add-media-input');
            if (addMediaInput) {
                addMediaInput.addEventListener('change', (e) => this.handleAddMoreMedia(e));
            }
        }

        bindEvents() {
            // Video play/pause on tap
            const previewViewport = document.getElementById('editor-viewport');
            if (previewViewport) {
                previewViewport.addEventListener('click', (e) => {
                    // Jangan toggle video jika yang di-klik adalah overlay
                    if (e.target.closest('.interactive-overlay') || e.target.closest('button')) return;
                    this.togglePlayPause();
                });
            }

            // Drag overlay support (touch & mouse)
            if (this.overlayContainer) {
                this.overlayContainer.addEventListener('mousedown', (e) => this.onDragStart(e));
                window.addEventListener('mousemove', (e) => this.onDragMove(e));
                window.addEventListener('mouseup', () => this.onDragEnd());

                this.overlayContainer.addEventListener('touchstart', (e) => this.onDragStart(e), { passive: false });
                window.addEventListener('touchmove', (e) => this.onDragMove(e), { passive: false });
                window.addEventListener('touchend', () => this.onDragEnd());
            }

            // Volume sliders
            const sliderOrig = document.getElementById('volume-original-slider');
            if (sliderOrig) {
                sliderOrig.addEventListener('input', (e) => {
                    this.volumeOriginal = parseFloat(e.target.value);
                    if (this.videoEl) this.videoEl.volume = this.volumeOriginal;
                });
            }

            const sliderMusic = document.getElementById('volume-music-slider');
            if (sliderMusic) {
                sliderMusic.addEventListener('input', (e) => {
                    this.volumeMusic = parseFloat(e.target.value);
                    if (this.musicProvider) this.musicProvider.setVolume(this.volumeMusic);
                });
            }
        }

        /**
         * Buka mode Editor dari data hasil rekam kamera atau galeri
         */
        open() {
            // Hentikan stream kamera
            if (window.MomentCameraEngine) {
                window.MomentCameraEngine.stopStream();
            }

            const mediaItems = this.state.getCapturedMediaItems();
            const capturedPhotos = this.state.get('capturedPhotos');
            const capturedPhoto = this.state.get('capturedPhoto');
            const clips = this.state.get('clips');

            if (mediaItems && mediaItems.length > 0) {
                this.mediaItems = mediaItems;
            } else if (capturedPhotos && capturedPhotos.length > 0) {
                this.mediaItems = capturedPhotos.map((p, idx) => ({
                    id: 'photo_' + idx,
                    type: 'photo',
                    blob: p.blob,
                    dataUrl: p.dataUrl || URL.createObjectURL(p.blob),
                    url: p.dataUrl || URL.createObjectURL(p.blob),
                    filter: p.filter || 'normal'
                }));
            } else if (capturedPhoto) {
                this.mediaItems = [{
                    id: 'photo_0',
                    type: 'photo',
                    blob: capturedPhoto.blob,
                    dataUrl: capturedPhoto.dataUrl || URL.createObjectURL(capturedPhoto.blob),
                    url: capturedPhoto.dataUrl || URL.createObjectURL(capturedPhoto.blob),
                    filter: capturedPhoto.filter || 'normal'
                }];
            } else if (clips && clips.length > 0) {
                this.mediaItems = clips.map((c, idx) => ({
                    id: c.id || ('clip_' + idx),
                    type: 'video',
                    blob: c.blob,
                    url: c.url,
                    duration: c.duration,
                    filter: c.filter || 'normal'
                }));
            } else {
                if (window.swalRentify) {
                    window.swalRentify.fire({ icon: 'warning', text: 'Belum ada media yang dipilih atau direkam.' });
                }
                return;
            }

            // Tampilkan layar editor
            document.getElementById('screen-camera')?.classList.add('hidden');
            document.getElementById('screen-editor')?.classList.remove('hidden');

            this.activeSlideIndex = 0;
            this.loadSlide(0);

            // Terapkan rasio aspek (9:16 / 3:4 / 1:1)
            this.applyAspectRatioStyle();

            // Set filter awal
            const initialFilter = this.state.get('activeFilter') || 'normal';
            this.applyFilter(initialFilter);

            // Perbarui UI Klip & Filmstrip Carousel
            this.renderClipsManager();
            this.updateFilmstripThumbnails();
        }

        /**
         * Muat slide media tertentu pada pratinjau editor
         */
        loadSlide(index) {
            if (!this.mediaItems || this.mediaItems.length === 0) return;
            this.activeSlideIndex = Math.max(0, Math.min(this.mediaItems.length - 1, index));
            const item = this.mediaItems[this.activeSlideIndex];

            this.mediaType = item.type;
            this.mediaBlob = item.blob;
            this.mediaUrl = item.url || item.dataUrl;
            this.isMirrored = !!item.isMirrored;

            if (typeof item.zoom !== 'number') item.zoom = 1.0;
            if (typeof item.panX !== 'number') item.panX = 0;
            if (typeof item.panY !== 'number') item.panY = 0;
            const btnEl = document.getElementById('editor-zoom-level-btn');
            if (btnEl) btnEl.textContent = `${item.zoom.toFixed(1)}x`;

            if (item.type === 'video') {
                this.setupVideoPreview();
            } else {
                this.setupPhotoPreview();
            }

            this.updateCarouselUI();
            this.updateFilmstripThumbnails();
            this.applyCurrentTransforms();
            this.applyFilter(item.filter || this.state.get('activeFilter') || 'normal');
        }

        nextSlide() {
            if (this.mediaItems && this.activeSlideIndex < this.mediaItems.length - 1) {
                this.loadSlide(this.activeSlideIndex + 1);
            }
        }

        prevSlide() {
            if (this.mediaItems && this.activeSlideIndex > 0) {
                this.loadSlide(this.activeSlideIndex - 1);
            }
        }

        updateCarouselUI() {
            const total = this.mediaItems ? this.mediaItems.length : 0;
            const isMulti = total > 1;

            const counterEl = document.getElementById('editor-carousel-counter');
            const counterText = document.getElementById('editor-carousel-counter-text');
            const prevBtn = document.getElementById('editor-carousel-prev-btn');
            const nextBtn = document.getElementById('editor-carousel-next-btn');
            const dotsEl = document.getElementById('editor-carousel-dots');

            if (counterEl) counterEl.classList.toggle('hidden', !isMulti);
            if (counterText) counterText.textContent = `${this.activeSlideIndex + 1}/${total}`;
            if (prevBtn) prevBtn.classList.toggle('hidden', !isMulti || this.activeSlideIndex === 0);
            if (nextBtn) nextBtn.classList.toggle('hidden', !isMulti || this.activeSlideIndex === total - 1);

            if (dotsEl) {
                dotsEl.classList.toggle('hidden', !isMulti);
                dotsEl.innerHTML = '';
                for (let i = 0; i < total; i++) {
                    const dot = document.createElement('div');
                    dot.className = `transition-all duration-200 rounded-full ${i === this.activeSlideIndex ? 'w-4 h-1.5 bg-sky-400' : 'w-1.5 h-1.5 bg-white/50'}`;
                    dotsEl.appendChild(dot);
                }
            }
        }

        /**
         * Render strip thumbnail carousel bergaya Instagram di bawah layar editor
         */
        updateFilmstripThumbnails() {
            const track = document.getElementById('editor-filmstrip-track');
            if (!track) return;
            track.innerHTML = '';

            const total = this.mediaItems ? this.mediaItems.length : 0;
            if (total === 0) return;

            this.mediaItems.forEach((item, idx) => {
                const isActive = (idx === this.activeSlideIndex);
                const thumb = document.createElement('div');
                thumb.className = `relative w-12 h-12 rounded-xl shrink-0 cursor-pointer overflow-hidden transition-all duration-200 border-2 select-none ${isActive ? 'border-sky-400 scale-105 shadow-lg shadow-sky-500/30' : 'border-white/20 opacity-70 hover:opacity-100'}`;

                const src = item.url || item.dataUrl;
                const isVideo = item.type === 'video';
                const rot = item.rotation || 0;
                const mirrorScale = item.isMirrored ? -1 : 1;

                thumb.innerHTML = `
                    <div class="w-full h-full bg-slate-900 flex items-center justify-center overflow-hidden">
                        ${isVideo 
                            ? `<video src="${src}" class="w-full h-full object-cover pointer-events-none" style="transform: rotate(${rot}deg) scaleX(${mirrorScale});" muted></video>
                               <div class="absolute inset-0 bg-black/35 flex items-center justify-center text-white text-[11px]"><i class="fa-solid fa-video"></i></div>`
                            : `<img src="${src}" class="w-full h-full object-cover pointer-events-none" style="transform: rotate(${rot}deg) scaleX(${mirrorScale});">`
                        }
                    </div>
                    <span class="absolute bottom-0.5 left-1 text-[8px] font-black text-white drop-shadow-md leading-none bg-black/50 px-1 py-0.2 rounded">${idx + 1}</span>
                    ${total > 1 ? `
                        <button type="button" class="delete-slide-btn absolute top-0.5 right-0.5 w-4 h-4 rounded-full bg-black/85 hover:bg-rose-500 text-white flex items-center justify-center text-[8px] transition shadow-md" title="Hapus slide ini">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    ` : ''}
                `;

                thumb.onclick = (e) => {
                    if (e.target.closest('.delete-slide-btn')) return;
                    this.loadSlide(idx);
                };

                const delBtn = thumb.querySelector('.delete-slide-btn');
                if (delBtn) {
                    delBtn.onclick = (e) => {
                        e.stopPropagation();
                        this.removeSlide(idx);
                    };
                }

                track.appendChild(thumb);
            });
        }

        removeSlide(index) {
            if (this.mediaItems.length <= 1) {
                this.backToCamera();
                return;
            }
            this.mediaItems.splice(index, 1);
            this.state.setCapturedMediaItems(this.mediaItems);
            const nextIdx = Math.max(0, Math.min(this.mediaItems.length - 1, index));
            this.loadSlide(nextIdx);
            this.updateFilmstripThumbnails();
            this.updateCarouselUI();
            this.showToast('Slide berhasil dihapus');
        }

        triggerAddMedia() {
            const input = document.getElementById('editor-add-media-input');
            if (input) input.click();
        }

        async handleAddMoreMedia(event) {
            const files = event.target.files ? Array.from(event.target.files) : [];
            if (files.length === 0) return;

            const remainingQuota = 10 - this.mediaItems.length;
            if (remainingQuota <= 0) {
                this.showToast('Maksimal 10 media per momen');
                return;
            }

            const targetFiles = files.slice(0, remainingQuota);
            const videoExts = ['mp4', 'mov', 'quicktime', 'webm', 'mkv', 'avi', 'wmv', 'flv', '3gp', '3g2', 'm4v', 'ts', 'mpeg', 'mpg', 'ogv', 'vob', 'm2ts', 'divx', 'rm', 'rmvb', 'asf', 'm4p', 'f4v'];
            const processedItems = [];

            for (const f of targetFiles) {
                const mime = f.type || '';
                const ext = (f.name.split('.').pop() || '').toLowerCase();
                const isVideo = mime.startsWith('video/') || videoExts.includes(ext);

                if (isVideo) {
                    const videoUrl = URL.createObjectURL(f);
                    const dur = await new Promise(resolve => {
                        const tempVideo = document.createElement('video');
                        tempVideo.preload = 'metadata';
                        tempVideo.src = videoUrl;
                        tempVideo.onloadedmetadata = () => resolve(tempVideo.duration || 5);
                        tempVideo.onerror = () => resolve(5);
                    });

                    processedItems.push({
                        id: 'item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6),
                        type: 'video',
                        blob: f,
                        url: videoUrl,
                        duration: dur,
                        speed: 1.0,
                        filter: 'normal',
                        isMirrored: false,
                        rotation: 0,
                        name: f.name
                    });
                } else {
                    let targetF = f;
                    if (['heic', 'heif'].includes(ext) && typeof heic2any === 'function') {
                        try {
                            const convertedBlob = await heic2any({
                                blob: f,
                                toType: 'image/jpeg',
                                quality: 0.95
                            });
                            const blob = Array.isArray(convertedBlob) ? convertedBlob[0] : convertedBlob;
                            targetF = new File([blob], f.name.replace(/\.(heic|heif)$/i, '.jpg'), { type: 'image/jpeg' });
                        } catch (err) {
                            console.warn('heic2any fallback to original:', err);
                        }
                    }

                    const dataUrl = await new Promise(resolve => {
                        const reader = new FileReader();
                        reader.onload = ev => resolve(ev.target.result);
                        reader.readAsDataURL(targetF);
                    });

                    processedItems.push({
                        id: 'item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6),
                        type: 'photo',
                        blob: targetF,
                        dataUrl: dataUrl,
                        url: dataUrl,
                        filter: 'normal',
                        isMirrored: false,
                        rotation: 0,
                        timestamp: Date.now(),
                        name: targetF.name
                    });
                }
            }

            if (processedItems.length > 0) {
                this.mediaItems = this.mediaItems.concat(processedItems);
                this.state.setCapturedMediaItems(this.mediaItems);
                this.updateFilmstripThumbnails();
                this.updateCarouselUI();
                this.loadSlide(this.mediaItems.length - 1);
                this.showToast(`${processedItems.length} media ditambahkan`);
            }
            if (event.target) event.target.value = '';
        }

        setupViewportSwipeGestures() {
            const viewport = document.getElementById('editor-viewport');
            if (!viewport) return;

            let touchStartX = 0;
            let touchStartY = 0;
            let touchStartTime = 0;
            let lastTapTime = 0;
            let initialPinchDist = 0;
            let initialPinchZoom = 1.0;
            let startPanX = 0;
            let startPanY = 0;

            viewport.addEventListener('touchstart', (e) => {
                if (e.target.closest('.interactive-overlay, button, input, textarea')) return;

                if (e.touches.length === 2) {
                    // Multi-touch Pinch to Zoom
                    initialPinchDist = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    const currentItem = this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null;
                    initialPinchZoom = currentItem ? (currentItem.zoom || 1.0) : 1.0;
                } else if (e.touches.length === 1) {
                    touchStartX = e.touches[0].clientX;
                    touchStartY = e.touches[0].clientY;
                    touchStartTime = Date.now();
                    const currentItem = this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null;
                    startPanX = currentItem ? (currentItem.panX || 0) : 0;
                    startPanY = currentItem ? (currentItem.panY || 0) : 0;
                }
            }, { passive: false });

            viewport.addEventListener('touchmove', (e) => {
                if (e.target.closest('.interactive-overlay, button, input, textarea')) return;

                if (e.touches.length === 2 && initialPinchDist > 0) {
                    if (e.cancelable) e.preventDefault();
                    const currentDist = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    const scale = currentDist / initialPinchDist;
                    const nextZoom = Math.min(3.0, Math.max(1.0, initialPinchZoom * scale));
                    this.setZoom(nextZoom);
                } else if (e.touches.length === 1) {
                    const currentItem = this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null;
                    const curZoom = currentItem ? (currentItem.zoom || 1.0) : 1.0;
                    if (curZoom > 1.05) {
                        // Drag & Pan ketika sedang di-zoom
                        if (e.cancelable) e.preventDefault();
                        const dx = e.touches[0].clientX - touchStartX;
                        const dy = e.touches[0].clientY - touchStartY;
                        const maxPanX = (viewport.clientWidth * (curZoom - 1)) / 1.4;
                        const maxPanY = (viewport.clientHeight * (curZoom - 1)) / 1.4;
                        currentItem.panX = Math.max(-maxPanX, Math.min(maxPanX, startPanX + dx));
                        currentItem.panY = Math.max(-maxPanY, Math.min(maxPanY, startPanY + dy));
                        this.applyCurrentTransforms();
                    }
                }
            }, { passive: false });

            viewport.addEventListener('touchend', (e) => {
                if (e.target.closest('.interactive-overlay, button, input, textarea')) return;

                if (e.changedTouches.length === 1 && touchStartX !== 0) {
                    const deltaX = e.changedTouches[0].clientX - touchStartX;
                    const deltaY = e.changedTouches[0].clientY - touchStartY;
                    const deltaTime = Date.now() - touchStartTime;

                    // Double-tap zoom gesture (Ala Instagram / Shopee)
                    const now = Date.now();
                    if (now - lastTapTime < 300 && Math.abs(deltaX) < 15 && Math.abs(deltaY) < 15) {
                        const currentItem = this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null;
                        const curZoom = currentItem ? (currentItem.zoom || 1.0) : 1.0;
                        if (curZoom > 1.05) {
                            this.setZoom(1.0);
                            this.showToast('Zoom: 1.0x (Normal)');
                        } else {
                            this.setZoom(2.0);
                            this.showToast('Zoom: 2.0x');
                        }
                        lastTapTime = 0;
                        touchStartX = 0;
                        touchStartY = 0;
                        return;
                    }
                    lastTapTime = now;

                    // Swipe horizontal berganti slide bila tidak sedang zoom
                    const currentItem = this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null;
                    const curZoom = currentItem ? (currentItem.zoom || 1.0) : 1.0;
                    if (curZoom <= 1.05 && Math.abs(deltaX) > 40 && Math.abs(deltaX) > Math.abs(deltaY) * 1.3 && deltaTime < 400) {
                        if (deltaX < 0) {
                            this.nextSlide();
                        } else {
                            this.prevSlide();
                        }
                    }
                }
                initialPinchDist = 0;
                touchStartX = 0;
                touchStartY = 0;
            }, { passive: true });
        }

        // ==========================================
        // KONTROL ZOOM & PAN ENGINE (ALA SHOPEE & IG)
        // ==========================================

        setZoom(level, panX = null, panY = null) {
            const currentItem = this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null;
            if (!currentItem) return;
            const clamped = Math.max(1.0, Math.min(3.0, parseFloat(Number(level).toFixed(2))));
            currentItem.zoom = clamped;
            if (clamped <= 1.01) {
                currentItem.panX = 0;
                currentItem.panY = 0;
            } else {
                if (panX !== null) currentItem.panX = panX;
                if (panY !== null) currentItem.panY = panY;
            }
            this.applyCurrentTransforms();

            // Update badge dan teks tombol level zoom
            const badgeEl = document.getElementById('editor-zoom-badge');
            const badgeText = document.getElementById('editor-zoom-badge-text');
            const btnEl = document.getElementById('editor-zoom-level-btn');
            const labelStr = `${clamped.toFixed(1)}x`;

            if (btnEl) btnEl.textContent = labelStr;
            if (badgeText) badgeText.textContent = labelStr;

            if (badgeEl) {
                badgeEl.classList.remove('opacity-0');
                clearTimeout(this._zoomBadgeTimeout);
                this._zoomBadgeTimeout = setTimeout(() => {
                    badgeEl.classList.add('opacity-0');
                }, 1400);
            }
        }

        zoomIn(delta = 0.25) {
            const currentItem = this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null;
            const cur = currentItem ? (currentItem.zoom || 1.0) : 1.0;
            this.setZoom(cur + delta);
        }

        zoomOut(delta = 0.25) {
            const currentItem = this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null;
            const cur = currentItem ? (currentItem.zoom || 1.0) : 1.0;
            this.setZoom(cur - delta);
        }

        resetZoom() {
            this.setZoom(1.0, 0, 0);
            this.showToast('Zoom di-reset (1.0x)');
        }

        cycleQuickZoom() {
            const currentItem = this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null;
            const cur = currentItem ? (currentItem.zoom || 1.0) : 1.0;
            let next = 1.5;
            if (cur >= 2.9) next = 1.0;
            else if (cur >= 2.4) next = 3.0;
            else if (cur >= 1.9) next = 2.5;
            else if (cur >= 1.4) next = 2.0;
            else next = 1.5;
            this.setZoom(next);
            this.showToast(`Zoom: ${next.toFixed(1)}x`);
        }

        toggleZoomControls() {
            const controls = document.getElementById('editor-zoom-controls');
            if (controls) {
                controls.classList.toggle('hidden');
                this.showToast(controls.classList.contains('hidden') ? 'Kontrol Zoom disembunyikan' : 'Kontrol Zoom ditampilkan');
            }
        }

        // ==========================================
        // DRAWER & RASIO ASPEK (ALA SHOPEE & IG)
        // ==========================================

        openRatioDrawer() {
            this.closeFilterDrawer();
            this.closeStickerDrawer();
            this.closeClipsDrawer();
            const drawer = document.getElementById('editor-ratio-drawer');
            if (drawer) {
                drawer.classList.remove('translate-y-full');
            }
            this.updateRatioDrawerActiveState();
        }

        closeRatioDrawer() {
            const drawer = document.getElementById('editor-ratio-drawer');
            if (drawer) {
                drawer.classList.add('translate-y-full');
            }
        }

        selectRatio(ratioKey) {
            this.state.setAspectRatio(ratioKey);
            this.applyAspectRatioStyle();
            this.updateRatioDrawerActiveState();
            this.closeRatioDrawer();
            let label = 'Rasio: Potret (9:16)';
            if (ratioKey === 'feed') label = 'Rasio: Potret Feed (4:5)';
            else if (ratioKey === 'square') label = 'Rasio: Persegi (1:1)';
            else if (ratioKey === 'standard') label = 'Rasio: Foto Standar (3:4)';
            else if (ratioKey === 'landscape') label = 'Rasio: Layar Lebar (16:9)';
            else if (ratioKey === 'original') label = 'Rasio: Format Asli';
            this.showToast(label);
        }

        updateRatioDrawerActiveState() {
            const cur = this.state.get('aspectRatio') || 'portrait';
            document.querySelectorAll('.ratio-item-btn').forEach(btn => {
                const key = btn.getAttribute('data-ratio-key');
                if (key === cur) {
                    btn.classList.add('border-sky-400', 'bg-sky-500/20');
                    btn.classList.remove('border-white/10', 'bg-white/5');
                } else {
                    btn.classList.remove('border-sky-400', 'bg-sky-500/20');
                    btn.classList.add('border-white/10', 'bg-white/5');
                }
            });
        }

        toggleAspectRatio() {
            const next = this.state.toggleAspectRatio();
            this.applyAspectRatioStyle();
            this.updateRatioDrawerActiveState();
            let label = 'Rasio: Potret (9:16)';
            if (next === 'feed') label = 'Rasio: Feed (4:5)';
            else if (next === 'square') label = 'Rasio: Persegi (1:1)';
            else if (next === 'standard') label = 'Rasio: Foto Standar (3:4)';
            else if (next === 'landscape') label = 'Rasio: Layar Lebar (16:9)';
            else if (next === 'original') label = 'Rasio: Format Asli';
            this.showToast(label);
        }

        applyAspectRatioStyle() {
            const ratio = this.state.get('aspectRatio') || 'portrait';
            const isSquare = ratio === 'square';
            const isFeed = ratio === 'feed';
            const isStandard = ratio === 'standard';
            const isLandscape = ratio === 'landscape';
            const isOriginal = ratio === 'original';
            const isPortrait = ratio === 'portrait';

            const labelEl = document.getElementById('editor-aspect-label');
            const iconEl = document.getElementById('editor-aspect-icon');
            if (labelEl) {
                labelEl.textContent = this.state.getAspectRatioDisplay();
            }
            if (iconEl) {
                if (isSquare) iconEl.className = 'fa-regular fa-square text-sky-400 text-xs';
                else if (isFeed) iconEl.className = 'fa-solid fa-rectangle-vertical text-sky-400 text-xs';
                else if (isStandard) iconEl.className = 'fa-solid fa-camera text-sky-400 text-xs';
                else if (isLandscape) iconEl.className = 'fa-solid fa-tv text-sky-400 text-xs';
                else if (isOriginal) iconEl.className = 'fa-solid fa-crop-simple text-sky-400 text-xs';
                else iconEl.className = 'fa-solid fa-mobile-screen text-sky-400 text-xs';
            }

            const viewport = document.getElementById('editor-viewport');
            if (viewport) {
                if (isSquare) {
                    viewport.style.aspectRatio = '1 / 1';
                    viewport.style.maxHeight = '70vh';
                    viewport.style.maxWidth = '70vh';
                    viewport.style.width = '90vw';
                    viewport.classList.add('rounded-3xl', 'my-auto', 'mx-auto', 'border', 'border-white/20', 'shadow-2xl');
                    viewport.classList.remove('w-full', 'h-full');
                } else if (isFeed) {
                    viewport.style.aspectRatio = '4 / 5';
                    viewport.style.maxHeight = '72vh';
                    viewport.style.maxWidth = 'calc(72vh * 4 / 5)';
                    viewport.style.width = '90vw';
                    viewport.classList.add('rounded-3xl', 'my-auto', 'mx-auto', 'border', 'border-white/20', 'shadow-2xl');
                    viewport.classList.remove('w-full', 'h-full');
                } else if (isStandard) {
                    viewport.style.aspectRatio = '3 / 4';
                    viewport.style.maxHeight = '76vh';
                    viewport.style.maxWidth = 'calc(76vh * 3 / 4)';
                    viewport.style.width = '90vw';
                    viewport.classList.add('rounded-3xl', 'my-auto', 'mx-auto', 'border', 'border-white/20', 'shadow-2xl');
                    viewport.classList.remove('w-full', 'h-full');
                } else if (isLandscape) {
                    viewport.style.aspectRatio = '16 / 9';
                    viewport.style.maxHeight = '55vh';
                    viewport.style.maxWidth = '94vw';
                    viewport.style.width = '94vw';
                    viewport.classList.add('rounded-2xl', 'my-auto', 'mx-auto', 'border', 'border-white/20', 'shadow-2xl');
                    viewport.classList.remove('w-full', 'h-full');
                } else if (isOriginal) {
                    viewport.style.aspectRatio = 'auto';
                    viewport.style.maxHeight = '78vh';
                    viewport.style.maxWidth = '92vw';
                    viewport.style.width = 'auto';
                    viewport.classList.add('rounded-2xl', 'my-auto', 'mx-auto', 'border', 'border-white/20', 'shadow-2xl');
                    viewport.classList.remove('w-full', 'h-full');
                } else {
                    viewport.style.aspectRatio = '';
                    viewport.style.maxHeight = '';
                    viewport.style.maxWidth = '';
                    viewport.style.width = '100%';
                    viewport.style.height = '100%';
                    viewport.classList.remove('rounded-3xl', 'rounded-2xl', 'border', 'border-white/20', 'shadow-2xl');
                    viewport.classList.add('w-full', 'h-full');
                }
            }

            if (this.imageEl) {
                this.imageEl.style.objectFit = isOriginal ? 'contain' : 'cover';
            }
            if (this.videoEl) {
                this.videoEl.style.objectFit = isOriginal ? 'contain' : 'cover';
            }
            this.applyCurrentTransforms();
        }

        toggleMirror() {
            const currentItem = this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null;
            if (currentItem) {
                currentItem.isMirrored = !currentItem.isMirrored;
                this.isMirrored = currentItem.isMirrored;
            } else {
                this.isMirrored = !this.isMirrored;
            }
            this.applyCurrentTransforms();
            this.showToast(this.isMirrored ? 'Cermin: Aktif' : 'Cermin: Nonaktif');
            this.updateFilmstripThumbnails();
        }

        rotateCurrentSlide() {
            const currentItem = this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null;
            if (!currentItem) return;
            currentItem.rotation = ((currentItem.rotation || 0) + 90) % 360;
            this.applyCurrentTransforms();
            this.showToast(`Putar: ${currentItem.rotation}°`);
            this.updateFilmstripThumbnails();
        }

        applyCurrentTransforms() {
            const currentItem = this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null;
            const isMirrored = currentItem ? !!currentItem.isMirrored : this.isMirrored;
            const rotation = currentItem ? (currentItem.rotation || 0) : 0;
            const zoom = currentItem ? (currentItem.zoom || 1.0) : 1.0;
            const panX = currentItem ? (currentItem.panX || 0) : 0;
            const panY = currentItem ? (currentItem.panY || 0) : 0;

            const target = (this.mediaType === 'video') ? this.videoEl : this.imageEl;
            if (target) {
                const scaleX = isMirrored ? -1 : 1;
                const transformVal = `translate(${panX}px, ${panY}px) scale(${zoom}) rotate(${rotation}deg) scaleX(${scaleX}) translateZ(0)`;
                target.style.transform = transformVal;
                target.style.webkitTransform = transformVal;
            }
        }

        setupPhotoPreview() {
            if (this.videoEl) {
                this.videoEl.pause();
                this.videoEl.classList.add('hidden');
            }
            if (this.imageEl) {
                this.imageEl.src = this.mediaUrl;
                this.imageEl.classList.remove('hidden');
            }
            // Sembunyikan kontrol timeline video
            document.getElementById('editor-video-timeline')?.classList.add('hidden');
            document.getElementById('tool-clip-manager-btn')?.classList.add('hidden');
        }

        setupVideoPreview() {
            if (this.imageEl) {
                this.imageEl.classList.add('hidden');
            }
            if (this.videoEl) {
                this.videoEl.src = this.mediaUrl;
                this.videoEl.classList.remove('hidden');
                this.videoEl.volume = this.volumeOriginal;
                this.videoEl.loop = (this.state.get('clips').length <= 1);

                this.videoEl.onended = () => {
                    const clips = this.state.get('clips');
                    if (clips.length > 1) {
                        this.currentClipIndex = (this.currentClipIndex + 1) % clips.length;
                        this.videoEl.src = clips[this.currentClipIndex].url;
                        this.videoEl.play().catch(() => {});
                    }
                };

                this.videoEl.play().then(() => {
                    this.isPlaying = true;
                    this.updatePlayPauseIcon();
                }).catch(() => {
                    this.isPlaying = false;
                    this.updatePlayPauseIcon();
                });
            }

            document.getElementById('editor-video-timeline')?.classList.remove('hidden');
            document.getElementById('tool-clip-manager-btn')?.classList.remove('hidden');
        }

        togglePlayPause() {
            if (this.mediaType !== 'video' || !this.videoEl) return;

            if (this.videoEl.paused) {
                this.videoEl.play().then(() => {
                    this.isPlaying = true;
                    this.updatePlayPauseIcon();
                    if (this.selectedMusic && this.musicProvider) {
                        this.musicProvider.playTrack(this.selectedMusic);
                    }
                }).catch(() => {});
            } else {
                this.videoEl.pause();
                this.isPlaying = false;
                this.updatePlayPauseIcon();
                if (this.musicProvider) {
                    this.musicProvider.stopTrack();
                }
            }
        }

        updatePlayPauseIcon() {
            const icon = document.getElementById('editor-play-indicator');
            if (icon) {
                if (this.isPlaying) {
                    icon.classList.add('opacity-0');
                } else {
                    icon.classList.remove('opacity-0');
                }
            }
        }

        /**
         * Terapkan Filter Visual CSS
         */
        applyFilter(filterKey) {
            const preset = this.filterPresets[filterKey] || this.filterPresets['normal'];
            this.activeFilter = filterKey;

            const targetMedia = (this.mediaType === 'video') ? this.videoEl : this.imageEl;
            if (targetMedia) {
                targetMedia.style.filter = preset.css;
            }

            // Update badge aktif pada carousel filter
            document.querySelectorAll('.filter-item-btn').forEach(btn => {
                if (btn.getAttribute('data-filter') === filterKey) {
                    btn.classList.add('border-sky-400', 'scale-105');
                    btn.classList.remove('border-white/20');
                } else {
                    btn.classList.remove('border-sky-400', 'scale-105');
                    btn.classList.add('border-white/20');
                }
            });

            // Tampilkan toast nama filter
            this.showToast(`Filter: ${preset.name}`);
        }

        renderFilterDrawer() {
            const container = document.getElementById('filter-presets-track');
            if (!container) return;

            container.innerHTML = '';
            Object.keys(this.filterPresets).forEach(key => {
                const f = this.filterPresets[key];
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `filter-item-btn shrink-0 flex flex-col items-center gap-1.5 transition-all p-1 rounded-2xl border-2 ${key === this.activeFilter ? 'border-sky-400 scale-105' : 'border-white/20'}`;
                btn.setAttribute('data-filter', key);

                btn.innerHTML = `
                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-800 relative shadow-md">
                        <div class="w-full h-full bg-gradient-to-br from-amber-400 via-rose-500 to-sky-500" style="filter: ${f.css};"></div>
                    </div>
                    <span class="text-[10px] font-bold text-white/90 truncate max-w-[65px]">${f.name}</span>
                `;

                btn.onclick = () => this.applyFilter(key);
                container.appendChild(btn);
            });
        }

        // ==========================================
        // TEXT OVERLAY (Aa) & STICKERS
        // ==========================================

        openTextEditor(existingOverlay = null) {
            const modal = document.getElementById('editor-text-modal');
            const input = document.getElementById('overlay-text-input');
            if (!modal || !input) return;

            if (existingOverlay) {
                input.value = existingOverlay.text;
                this.activeEditingOverlayId = existingOverlay.id;
            } else {
                input.value = '';
                this.activeEditingOverlayId = null;
            }

            modal.classList.remove('hidden');
            setTimeout(() => input.focus(), 100);
        }

        saveTextOverlay() {
            const input = document.getElementById('overlay-text-input');
            const text = (input ? input.value : '').trim();
            if (!text) {
                this.closeTextEditor();
                return;
            }

            const color = this.activeTextColor || '#ffffff';
            const bgColor = this.activeTextBgColor || 'rgba(0,0,0,0.6)';

            if (this.activeEditingOverlayId) {
                const existing = this.overlays.find(o => o.id === this.activeEditingOverlayId);
                if (existing) {
                    existing.text = text;
                    existing.color = color;
                    existing.bgColor = bgColor;
                }
            } else {
                const newOverlay = {
                    id: 'overlay_' + Date.now(),
                    type: 'text',
                    text: text,
                    color: color,
                    bgColor: bgColor,
                    x: 50, // % dari lebar viewport
                    y: 40, // % dari tinggi viewport
                    size: 18
                };
                this.overlays.push(newOverlay);
            }

            this.closeTextEditor();
            this.renderOverlays();
        }

        closeTextEditor() {
            document.getElementById('editor-text-modal')?.classList.add('hidden');
            this.activeEditingOverlayId = null;
        }

        addSticker(emoji) {
            const newSticker = {
                id: 'sticker_' + Date.now(),
                type: 'sticker',
                emoji: emoji,
                x: 50,
                y: 50,
                size: 38
            };
            this.overlays.push(newSticker);
            this.closeStickerDrawer();
            this.renderOverlays();
            this.showToast(`Stiker ditambahkan!`);
        }

        renderOverlays() {
            if (!this.overlayContainer) return;
            this.overlayContainer.innerHTML = '';

            this.overlays.forEach(item => {
                const el = document.createElement('div');
                el.id = item.id;
                el.className = 'interactive-overlay absolute cursor-move select-none active:scale-95 transition-transform';
                el.style.left = `${item.x}%`;
                el.style.top = `${item.y}%`;
                el.style.transform = 'translate(-50%, -50%)';

                if (item.type === 'text') {
                    el.innerHTML = `
                        <div class="px-3.5 py-1.5 rounded-xl font-black shadow-xl flex items-center gap-2 group border border-white/20" style="background-color: ${item.bgColor}; color: ${item.color}; font-size: ${item.size}px;">
                            <span>${item.text}</span>
                            <button type="button" class="delete-overlay-btn text-white/50 hover:text-rose-400 p-0.5 text-xs transition" title="Hapus">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    `;
                } else {
                    el.innerHTML = `
                        <div class="relative group p-1" style="font-size: ${item.size}px;">
                            <span>${item.emoji}</span>
                            <button type="button" class="delete-overlay-btn absolute -top-1 -right-1 w-5 h-5 rounded-full bg-black/70 text-white hover:text-rose-400 text-[10px] flex items-center justify-center shadow-md">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    `;
                }

                // Hapus overlay
                el.querySelector('.delete-overlay-btn').onclick = (e) => {
                    e.stopPropagation();
                    this.removeOverlay(item.id);
                };

                // Double click untuk edit teks
                if (item.type === 'text') {
                    el.ondblclick = (e) => {
                        e.stopPropagation();
                        this.openTextEditor(item);
                    };
                }

                this.overlayContainer.appendChild(el);
            });
        }

        removeOverlay(id) {
            this.overlays = this.overlays.filter(o => o.id !== id);
            this.renderOverlays();
        }

        // ==========================================
        // DRAG & DROP INTERACTION FOR OVERLAYS
        // ==========================================

        onDragStart(e) {
            const overlayEl = e.target.closest('.interactive-overlay');
            if (!overlayEl || e.target.closest('.delete-overlay-btn')) return;

            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;

            const targetOverlay = this.overlays.find(o => o.id === overlayEl.id);
            if (!targetOverlay) return;

            this.dragState = {
                id: targetOverlay.id,
                startX: clientX,
                startY: clientY,
                origX: targetOverlay.x,
                origY: targetOverlay.y
            };
        }

        onDragMove(e) {
            if (!this.dragState) return;
            if (e.cancelable) e.preventDefault();

            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;

            const viewport = document.getElementById('editor-viewport');
            if (!viewport) return;

            const rect = viewport.getBoundingClientRect();
            const deltaXPercent = ((clientX - this.dragState.startX) / rect.width) * 100;
            const deltaYPercent = ((clientY - this.dragState.startY) / rect.height) * 100;

            const target = this.overlays.find(o => o.id === this.dragState.id);
            if (target) {
                target.x = Math.max(5, Math.min(95, this.dragState.origX + deltaXPercent));
                target.y = Math.max(5, Math.min(95, this.dragState.origY + deltaYPercent));

                const el = document.getElementById(target.id);
                if (el) {
                    el.style.left = `${target.x}%`;
                    el.style.top = `${target.y}%`;
                }
            }
        }

        onDragEnd() {
            this.dragState = null;
        }

        // ==========================================
        // MUSIC & AUDIO MIXER
        // ==========================================

        renderMusicCatalog() {
            const container = document.getElementById('music-tracks-list');
            if (!container || !this.musicProvider) return;

            const catalog = this.musicProvider.getCatalog();
            container.innerHTML = '';

            catalog.forEach(track => {
                const isCurrent = (this.selectedMusic && this.selectedMusic.id === track.id);
                const item = document.createElement('div');
                item.className = `p-3 rounded-2xl border transition flex items-center justify-between ${isCurrent ? 'bg-sky-500/15 border-sky-400' : 'bg-slate-900 border-white/10 hover:border-white/20'}`;

                item.innerHTML = `
                    <div class="flex items-center gap-3">
                        <img src="${track.cover}" class="w-11 h-11 rounded-xl object-cover shadow-sm">
                        <div>
                            <h4 class="text-xs font-black text-white leading-tight">${track.title}</h4>
                            <p class="text-[10px] text-slate-400">${track.artist} • ${track.genre}</p>
                            <div class="flex gap-1 mt-1">
                                ${track.tags.map(t => `<span class="text-[9px] px-1.5 py-0.2 rounded bg-white/10 text-white/70">${t}</span>`).join('')}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="preview-music-btn w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-xs transition">
                            <i class="fa-solid fa-play"></i>
                        </button>
                        <button type="button" class="select-music-btn px-3 py-1.5 rounded-full text-xs font-black uppercase transition ${isCurrent ? 'bg-sky-400 text-slate-950' : 'bg-white/20 hover:bg-white text-white hover:text-slate-950'}">
                            ${isCurrent ? 'Terpilih' : 'Gunakan'}
                        </button>
                    </div>
                `;

                // Preview audio
                const prevBtn = item.querySelector('.preview-music-btn');
                prevBtn.onclick = (e) => {
                    e.stopPropagation();
                    const isNowPlaying = this.musicProvider.toggleTrack(track);
                    prevBtn.innerHTML = isNowPlaying ? '<i class="fa-solid fa-pause"></i>' : '<i class="fa-solid fa-play"></i>';
                };

                // Select music
                const selectBtn = item.querySelector('.select-music-btn');
                selectBtn.onclick = () => {
                    this.setMusic(track);
                    this.closeMusicModal();
                };

                container.appendChild(item);
            });
        }

        setMusic(track) {
            this.selectedMusic = track;
            this.state.setMusic(track);

            const badge = document.getElementById('editor-music-pill-text');
            if (badge) badge.textContent = track ? track.title : 'Tambah Musik';

            const removeBtn = document.getElementById('editor-remove-music-btn');
            if (removeBtn) {
                if (track) removeBtn.classList.remove('hidden');
                else removeBtn.classList.add('hidden');
            }

            if (track && this.isPlaying) {
                this.musicProvider.playTrack(track);
            }
            this.showToast(`Musik: ${track ? track.title : 'Dimatikan'}`);
        }

        removeMusic() {
            this.selectedMusic = null;
            this.state.removeMusic();
            if (this.musicProvider) this.musicProvider.stopTrack();
            this.setMusic(null);
        }

        // ==========================================
        // CLIP MANAGER & TRIMMING (Multi-klip)
        // ==========================================

        renderClipsManager() {
            const list = document.getElementById('editor-clips-list');
            if (!list) return;

            const clips = this.state.get('clips');
            list.innerHTML = '';

            if (clips.length === 0) {
                list.innerHTML = `<p class="text-xs text-slate-400 text-center py-4">Hanya 1 klip atau foto.</p>`;
                return;
            }

            clips.forEach((clip, index) => {
                const item = document.createElement('div');
                item.className = 'flex items-center justify-between p-2.5 rounded-xl bg-slate-900 border border-white/15';

                item.innerHTML = `
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-sky-500/20 text-sky-400 text-xs font-black flex items-center justify-center">${index + 1}</span>
                        <div>
                            <p class="text-xs font-bold text-white">Klip #${index + 1}</p>
                            <p class="text-[10px] text-slate-400">Durasi: ${(clip.duration || 0).toFixed(1)}s • Filter: ${clip.filter || 'normal'}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" class="preview-clip-btn text-xs text-sky-400 hover:text-sky-300 p-1.5" title="Putar">
                            <i class="fa-solid fa-play"></i>
                        </button>
                        <button type="button" class="delete-clip-btn text-xs text-rose-400 hover:text-rose-300 p-1.5" title="Hapus klip">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                `;

                item.querySelector('.preview-clip-btn').onclick = () => {
                    if (this.videoEl) {
                        this.currentClipIndex = index;
                        this.videoEl.src = clip.url;
                        this.videoEl.play().catch(() => {});
                    }
                };

                item.querySelector('.delete-clip-btn').onclick = () => {
                    clips.splice(index, 1);
                    this.state.set('clips', clips);
                    this.renderClipsManager();
                    if (clips.length === 0) {
                        this.backToCamera();
                    } else {
                        this.setupVideoPreview();
                    }
                };

                list.appendChild(item);
            });
        }

        // ==========================================
        // TRANSITIONS & PUBLISH STEP
        // ==========================================

        backToCamera() {
            if (this.videoEl) this.videoEl.pause();
            if (this.musicProvider) this.musicProvider.stopTrack();

            document.getElementById('screen-editor')?.classList.add('hidden');
            document.getElementById('screen-camera')?.classList.remove('hidden');

            if (window.MomentCameraEngine) {
                window.MomentCameraEngine.startStream(this.state.get('facingMode') || 'environment');
            }
        }

        openPublishScreen() {
            if (this.videoEl) this.videoEl.pause();
            if (this.musicProvider) this.musicProvider.stopTrack();

            const modal = document.getElementById('screen-publish');
            if (!modal) return;

            // Thumbnail Preview di Publish Modal
            const thumbImg = document.getElementById('publish-preview-thumb');
            if (thumbImg) {
                if (this.mediaType === 'photo') {
                    thumbImg.src = this.mediaUrl;
                } else {
                    // Buat frame snapshot dari video
                    this.captureVideoFrame((dataUrl) => {
                        thumbImg.src = dataUrl;
                    });
                }
            }

            modal.classList.remove('hidden');
        }

        closePublishScreen() {
            document.getElementById('screen-publish')?.classList.add('hidden');
            if (this.mediaType === 'video' && this.videoEl) {
                this.videoEl.play().catch(() => {});
            }
        }

        captureVideoFrame(callback) {
            if (!this.videoEl) return;
            try {
                const canvas = document.createElement('canvas');
                canvas.width = 720;
                canvas.height = 1280;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(this.videoEl, 0, 0, canvas.width, canvas.height);
                callback(canvas.toDataURL('image/jpeg', 0.90));
            } catch (e) {
                callback('');
            }
        }

        /**
         * Menggabungkan foto dengan CSS filter, rotasi, cermin (mirror), crop rasio, dan stiker/teks overlay ke dalam canvas.
         */
        async bakePhotoWithOverlays(targetItem = null) {
            const item = targetItem || (this.mediaItems ? this.mediaItems[this.activeSlideIndex] : null) || {
                type: this.mediaType,
                blob: this.mediaBlob,
                url: this.mediaUrl,
                filter: this.activeFilter,
                isMirrored: this.isMirrored,
                rotation: 0,
                zoom: 1.0,
                panX: 0,
                panY: 0
            };

            if (item.type !== 'photo' || !item.blob) {
                return item.blob || this.mediaBlob;
            }

            const activeRatio = this.state.get('aspectRatio') || 'portrait';
            const filterKey = item.filter || this.activeFilter || 'normal';
            const hasFilter = filterKey && filterKey !== 'normal';
            const hasOverlays = this.overlays && this.overlays.length > 0 && (!targetItem || item === this.mediaItems[this.activeSlideIndex]);
            const isMirrored = !!item.isMirrored;
            const rotation = item.rotation || 0;
            const zoom = typeof item.zoom === 'number' ? item.zoom : 1.0;
            const panX = item.panX || 0;
            const panY = item.panY || 0;
            const hasZoom = zoom > 1.01 || Math.abs(panX) > 1 || Math.abs(panY) > 1;
            const needsCrop = (activeRatio !== 'original');

            // Jika tidak ada modifikasi apapun, kembalikan blob asli
            if (!hasFilter && !hasOverlays && !isMirrored && rotation === 0 && !needsCrop && !hasZoom) {
                return item.blob;
            }

            return new Promise((resolve) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                const sourceUrl = item.dataUrl || item.url || URL.createObjectURL(item.blob);

                img.onload = () => {
                    try {
                        const rawW = img.naturalWidth || 1080;
                        const rawH = img.naturalHeight || 1920;
                        const isRotated = (rotation === 90 || rotation === 270);
                        const baseW = isRotated ? rawH : rawW;
                        const baseH = isRotated ? rawW : rawH;

                        // Tentukan dimensi target canvas sesuai rasio pilihan (Shopee / IG / Standard)
                        let outW = baseW;
                        let outH = baseH;

                        if (activeRatio === 'square') {
                            const s = Math.min(baseW, baseH);
                            outW = s;
                            outH = s;
                        } else if (activeRatio === 'feed') {
                            // 4:5 Potret Feed IG/Shopee
                            if (baseW / baseH > 4 / 5) {
                                outH = baseH;
                                outW = Math.round(outH * (4 / 5));
                            } else {
                                outW = baseW;
                                outH = Math.round(outW * (5 / 4));
                            }
                        } else if (activeRatio === 'standard') {
                            // 3:4 Standar Foto
                            if (baseW / baseH > 3 / 4) {
                                outH = baseH;
                                outW = Math.round(outH * (3 / 4));
                            } else {
                                outW = baseW;
                                outH = Math.round(outW * (4 / 3));
                            }
                        } else if (activeRatio === 'landscape') {
                            // 16:9 Lanskap Layar Lebar
                            if (baseW / baseH > 16 / 9) {
                                outH = baseH;
                                outW = Math.round(outH * (16 / 9));
                            } else {
                                outW = baseW;
                                outH = Math.round(outW * (9 / 16));
                            }
                        } else if (activeRatio === 'portrait') {
                            // 9:16 Fullscreen Story/Reels
                            if (baseW / baseH > 9 / 16) {
                                outH = baseH;
                                outW = Math.round(outH * (9 / 16));
                            } else {
                                outW = baseW;
                                outH = Math.round(outW * (16 / 9));
                            }
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = outW;
                        canvas.height = outH;
                        const ctx = canvas.getContext('2d');
                        if (!ctx) return resolve(item.blob);
                        ctx.imageSmoothingEnabled = true;
                        ctx.imageSmoothingQuality = 'high';

                        // Terapkan CSS filter bila ada
                        const filterPreset = this.filterPresets[filterKey];
                        if (filterPreset && filterPreset.css && filterPreset.css !== 'none') {
                            ctx.filter = filterPreset.css;
                        }

                        // Hitung skala fitting (object-cover) dan zoom pengguna
                        const fitScale = (activeRatio === 'original') ? 1.0 : Math.max(outW / baseW, outH / baseH);
                        const finalScale = fitScale * zoom;

                        // Hitung translasi pergeseran (pan) sesuai rasio layar
                        const vp = document.getElementById('editor-viewport');
                        const vpW = vp ? vp.clientWidth : outW;
                        const vpH = vp ? vp.clientHeight : outH;
                        const canvasOffsetX = (panX / (vpW || outW)) * outW;
                        const canvasOffsetY = (panY / (vpH || outH)) * outH;

                        // Gambar foto dengan rotasi, mirror, zoom, dan pan
                        ctx.save();
                        ctx.translate(outW / 2 + canvasOffsetX, outH / 2 + canvasOffsetY);
                        if (rotation !== 0) ctx.rotate((rotation * Math.PI) / 180);
                        if (isMirrored) ctx.scale(-1, 1);
                        ctx.drawImage(img, -rawW * finalScale / 2, -rawH * finalScale / 2, rawW * finalScale, rawH * finalScale);
                        ctx.restore();
                        ctx.filter = 'none';

                        // Render teks & stiker overlay (jika ada pada slide aktif)
                        if (hasOverlays) {
                            const scale = canvas.width / (this.overlayContainer?.clientWidth || canvas.width);
                            this.overlays.forEach((ov) => {
                                const x = (ov.x / 100) * canvas.width;
                                const y = (ov.y / 100) * canvas.height;

                                if (ov.type === 'text') {
                                    const fontSize = Math.max(16, Math.round((ov.size || 18) * scale));
                                    ctx.font = `bold ${fontSize}px sans-serif`;
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';

                                    const textMetrics = ctx.measureText(ov.text);
                                    const padX = 14 * scale;
                                    const padY = 8 * scale;
                                    const rectW = textMetrics.width + padX * 2;
                                    const rectH = fontSize + padY * 2;

                                    if (ov.bgColor && ov.bgColor !== 'transparent') {
                                        ctx.fillStyle = ov.bgColor;
                                        ctx.beginPath();
                                        if (ctx.roundRect) {
                                            ctx.roundRect(x - rectW / 2, y - rectH / 2, rectW, rectH, 10 * scale);
                                        } else {
                                            ctx.rect(x - rectW / 2, y - rectH / 2, rectW, rectH);
                                        }
                                        ctx.fill();
                                    }

                                    ctx.fillStyle = ov.color || '#ffffff';
                                    ctx.fillText(ov.text, x, y);
                                } else if (ov.type === 'sticker') {
                                    const fontSize = Math.max(24, Math.round((ov.size || 38) * scale));
                                    ctx.font = `${fontSize}px sans-serif`;
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';
                                    ctx.fillText(ov.emoji, x, y);
                                }
                            });
                        }

                        canvas.toBlob((blob) => {
                            resolve(blob || item.blob);
                        }, 'image/jpeg', 0.95);
                    } catch (bakeErr) {
                        console.warn('Gagal bake overlay foto, gunakan blob asli:', bakeErr);
                        resolve(item.blob);
                    }
                };

                img.onerror = () => resolve(item.blob);
                img.src = sourceUrl;
            });
        }

        /**
         * Kirim postingan ke backend POST /api/moments
         */
        async submitPublish(event) {
            if (event) event.preventDefault();

            const submitBtn = document.getElementById('publish-submit-btn');
            const spinner = document.getElementById('publish-spinner');
            const btnText = document.getElementById('publish-btn-text');
            const progressContainer = document.getElementById('publish-progress-container');
            const progressBar = document.getElementById('publish-progress-bar');
            const progressPercent = document.getElementById('publish-progress-percent');

            if (submitBtn) submitBtn.disabled = true;
            if (spinner) spinner.classList.remove('hidden');
            if (btnText) btnText.textContent = 'Memproses & Mengunggah...';
            if (progressContainer) progressContainer.classList.remove('hidden');

            const caption = document.getElementById('publish-caption-input')?.value || '';
            const selectedProductSelect = document.getElementById('publish-product-tag-select');
            const selectedProductIds = [];
            if (selectedProductSelect && selectedProductSelect.value) {
                selectedProductIds.push(parseInt(selectedProductSelect.value, 10));
            }

            // Siapkan FormData
            const formData = new FormData();
            const hasMultipleItems = this.mediaItems && this.mediaItems.length > 1;

            if (hasMultipleItems) {
                // Multi-media upload (Carousel Foto, Video, atau Campuran seperti IG)
                let containsVideo = false;
                for (let idx = 0; idx < this.mediaItems.length; idx++) {
                    const item = this.mediaItems[idx];
                    const isVid = item.type === 'video';
                    if (isVid) containsVideo = true;

                    let blobToUpload = item.blob;
                    if (!isVid) {
                        try {
                            blobToUpload = await this.bakePhotoWithOverlays(item);
                        } catch (e) {
                            blobToUpload = item.blob;
                        }
                    }

                    const mime = (blobToUpload && blobToUpload.type) ? blobToUpload.type.toLowerCase() : '';
                    let ext = isVid ? (mime.includes('webm') ? 'webm' : 'mp4') : (mime.includes('png') ? 'png' : 'jpg');
                    const filename = `moment_${Date.now()}_${idx}.${ext}`;
                    formData.append('media[]', blobToUpload, filename);
                }
                formData.append('media_type', containsVideo ? 'video' : 'image');
            } else {
                const singleItem = (this.mediaItems && this.mediaItems.length === 1) ? this.mediaItems[0] : null;
                const isVid = singleItem ? singleItem.type === 'video' : this.mediaType === 'video';
                let blobToUpload = singleItem ? singleItem.blob : this.mediaBlob;
                if (!isVid) {
                    try {
                        blobToUpload = await this.bakePhotoWithOverlays(singleItem);
                    } catch (e) {
                        blobToUpload = singleItem ? singleItem.blob : this.mediaBlob;
                    }
                }
                const mimeType = (blobToUpload && blobToUpload.type) ? blobToUpload.type.toLowerCase() : '';
                let ext = isVid ? (mimeType.includes('webm') ? 'webm' : 'mp4') : (mimeType.includes('png') ? 'png' : 'jpg');
                const filename = `moment_${Date.now()}.${ext}`;
                formData.append('media', blobToUpload, filename);
                formData.append('media_type', isVid ? 'video' : 'image');
            }
            formData.append('caption', caption);

            if (this.mediaType === 'video') {
                const totalDur = this.state.getTotalRecordedDuration() || 15;
                formData.append('video_duration_seconds', Math.round(totalDur));
            }

            selectedProductIds.forEach((pid, idx) => {
                formData.append(`product_ids[${idx}]`, pid);
            });

            // Simulasi progress upload smooth
            let currentPct = 15;
            const progressInterval = setInterval(() => {
                if (currentPct < 90) {
                    currentPct += 7;
                    if (progressBar) progressBar.style.width = `${currentPct}%`;
                    if (progressPercent) progressPercent.textContent = `${currentPct}%`;
                }
            }, 300);

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch('/api/moments', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                clearInterval(progressInterval);
                if (progressBar) progressBar.style.width = '100%';
                if (progressPercent) progressPercent.textContent = '100%';

                const result = await response.json().catch(() => ({}));

                if (response.ok && result.success) {
                    if (window.swalRentify) {
                        await window.swalRentify.fire({
                            icon: 'success',
                            title: 'Momen Berhasil Dibagikan!',
                            text: 'Momen keren kamu kini tampil di feed komunitas Rentify.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                    window.location.href = '/moments';
                } else {
                    let errorMsg = result.message || 'Gagal membagikan momen.';
                    if (result.errors) {
                        const errorDetails = Object.values(result.errors).flat().join('<br>');
                        if (errorDetails) errorMsg += `<br><span class="text-xs text-rose-500">${errorDetails}</span>`;
                    }
                    if (window.swalRentify) {
                        window.swalRentify.fire({ icon: 'error', title: 'Gagal Unggah', html: errorMsg });
                    }
                    if (submitBtn) submitBtn.disabled = false;
                    if (spinner) spinner.classList.add('hidden');
                    if (btnText) btnText.textContent = 'Bagikan Momen';
                    if (progressContainer) progressContainer.classList.add('hidden');
                }
            } catch (err) {
                clearInterval(progressInterval);
                console.error('Error saat upload momen:', err);
                if (window.swalRentify) {
                    window.swalRentify.fire({ icon: 'error', title: 'Kesalahan Jaringan', text: 'Terjadi gangguan saat mengunggah momen. Silakan periksa koneksi internet Anda.' });
                }
                if (submitBtn) submitBtn.disabled = false;
                if (spinner) spinner.classList.add('hidden');
                if (btnText) btnText.textContent = 'Bagikan Momen';
                if (progressContainer) progressContainer.classList.add('hidden');
            }
        }

        showToast(msg) {
            const toast = document.getElementById('editor-toast');
            const toastText = document.getElementById('editor-toast-text');
            if (toast && toastText) {
                toastText.textContent = msg;
                toast.classList.remove('opacity-0', '-translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');
                setTimeout(() => {
                    toast.classList.remove('opacity-100', 'translate-y-0');
                    toast.classList.add('opacity-0', '-translate-y-2');
                }, 2000);
            }
        }

        // Drawers
        openFilterDrawer() {
            document.getElementById('editor-filter-drawer')?.classList.remove('translate-y-full');
        }
        closeFilterDrawer() {
            document.getElementById('editor-filter-drawer')?.classList.add('translate-y-full');
        }

        openStickerDrawer() {
            document.getElementById('editor-sticker-drawer')?.classList.remove('translate-y-full');
        }
        closeStickerDrawer() {
            document.getElementById('editor-sticker-drawer')?.classList.add('translate-y-full');
        }

        openMusicModal() {
            document.getElementById('editor-music-modal')?.classList.remove('hidden');
            this.renderMusicCatalog();
        }
        closeMusicModal() {
            document.getElementById('editor-music-modal')?.classList.add('hidden');
        }

        openClipsDrawer() {
            this.renderClipsManager();
            document.getElementById('editor-clips-drawer')?.classList.remove('translate-y-full');
        }
        closeClipsDrawer() {
            document.getElementById('editor-clips-drawer')?.classList.add('translate-y-full');
        }
    }

    window.MomentEditorEngine = new MomentEditorEngine();
})(window);
