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
            this.renderFilterDrawer();
            this.renderMusicCatalog();
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

            const capturedPhotos = this.state.get('capturedPhotos');
            const capturedPhoto = this.state.get('capturedPhoto');
            const clips = this.state.get('clips');

            if (capturedPhotos && capturedPhotos.length > 0) {
                this.mediaType = 'photo';
                this.mediaBlobs = capturedPhotos.map(p => p.blob);
                this.mediaUrls = capturedPhotos.map(p => p.dataUrl || URL.createObjectURL(p.blob));
                this.mediaBlob = this.mediaBlobs[0];
                this.mediaUrl = this.mediaUrls[0];
                this.setupPhotoPreview();
            } else if (capturedPhoto) {
                this.mediaType = 'photo';
                this.mediaBlobs = [capturedPhoto.blob];
                this.mediaUrls = [capturedPhoto.dataUrl || URL.createObjectURL(capturedPhoto.blob)];
                this.mediaBlob = capturedPhoto.blob;
                this.mediaUrl = this.mediaUrls[0];
                this.setupPhotoPreview();
            } else if (clips && clips.length > 0) {
                this.mediaType = 'video';
                this.currentClipIndex = 0;
                this.mediaBlob = clips[0].blob;
                this.mediaUrl = clips[0].url;
                this.setupVideoPreview();
            } else {
                if (window.swalRentify) {
                    window.swalRentify.fire({ icon: 'warning', text: 'Belum ada media yang direkam.' });
                }
                return;
            }

            // Tampilkan layar editor
            document.getElementById('screen-camera')?.classList.add('hidden');
            document.getElementById('screen-editor')?.classList.remove('hidden');

            // Set filter awal
            const initialFilter = this.state.get('activeFilter') || 'normal';
            this.applyFilter(initialFilter);

            // Perbarui UI Klip
            this.renderClipsManager();
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
                canvas.width = 360;
                canvas.height = 640;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(this.videoEl, 0, 0, canvas.width, canvas.height);
                callback(canvas.toDataURL('image/jpeg', 0.85));
            } catch (e) {
                callback('');
            }
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
            if (btnText) btnText.textContent = 'Mengunggah Momen...';
            if (progressContainer) progressContainer.classList.remove('hidden');

            const caption = document.getElementById('publish-caption-input')?.value || '';
            const selectedProductSelect = document.getElementById('publish-product-tag-select');
            const selectedProductIds = [];
            if (selectedProductSelect && selectedProductSelect.value) {
                selectedProductIds.push(parseInt(selectedProductSelect.value, 10));
            }

            // Siapkan FormData
            const formData = new FormData();
            if (this.mediaType === 'photo' && this.mediaBlobs && this.mediaBlobs.length > 1) {
                // Multi-photo upload
                this.mediaBlobs.forEach((blob, idx) => {
                    formData.append(`media[${idx}]`, blob, `moment_${Date.now()}_${idx}.jpg`);
                });
                formData.append('media_type', 'image');
            } else {
                const ext = (this.mediaType === 'video') ? 'mp4' : 'jpg';
                const filename = `moment_${Date.now()}.${ext}`;
                formData.append('media', this.mediaBlob, filename);
                formData.append('media_type', this.mediaType);
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
            let currentPct = 10;
            const progressInterval = setInterval(() => {
                if (currentPct < 90) {
                    currentPct += 8;
                    if (progressBar) progressBar.style.width = `${currentPct}%`;
                    if (progressPercent) progressPercent.textContent = `${currentPct}%`;
                }
            }, 300);

            try {
                const response = await fetch('/api/moments', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: formData
                });

                clearInterval(progressInterval);
                if (progressBar) progressBar.style.width = '100%';
                if (progressPercent) progressPercent.textContent = '100%';

                const result = await response.json();

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
                    const msg = result.message || 'Gagal membagikan momen.';
                    if (window.swalRentify) {
                        window.swalRentify.fire({ icon: 'error', title: 'Gagal Unggah', text: msg });
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
                    window.swalRentify.fire({ icon: 'error', text: 'Terjadi kesalahan jaringan saat mengunggah.' });
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
