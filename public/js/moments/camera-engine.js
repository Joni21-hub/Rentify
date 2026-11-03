/**
 * Rentify Momen Creator - Camera Engine (camera-engine.js)
 * Modul inti pengontrol kamera real-time:
 * 1. WebRTC MediaStream (Front / Back / Flip)
 * 2. MediaRecorder multi-klip bersegmen
 * 3. High-res photo snapshot
 * 4. Hardware Torch & Digital / Optical Pinch Zoom
 * 5. Tap-to-focus & Double-tap camera flip
 * 6. Synthesized audio beeps & haptic feedback
 */
(function(window) {
    'use strict';

    class MomentCameraEngine {
        constructor() {
            this.videoEl = null;
            this.stream = null;
            this.mediaRecorder = null;
            this.recordedChunks = [];
            this.currentClipStartTime = 0;
            this.recordTimerInterval = null;
            this.audioCtx = null;
            this.currentMimeType = '';
            this.isInitializing = false;
        }

        /**
         * Inisialisasi engine dengan elemen video DOM
         * @param {HTMLVideoElement} videoElement 
         */
        init(videoElement) {
            this.videoEl = videoElement;
            this.setupAudioContext();
        }

        setupAudioContext() {
            try {
                const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
                if (AudioCtxClass) {
                    this.audioCtx = new AudioCtxClass();
                }
            } catch (e) {
                console.warn('[CameraEngine] AudioContext tidak didukung:', e);
            }
        }

        /**
         * Bunyikan nada beep countdown (3-2-1) menggunakan Web Audio API tanpa perlu file mp3 eksternal
         * @param {number} freq Frekuensi audio (Hz)
         * @param {number} duration Durasi detik
         */
        playBeep(freq = 440, duration = 0.12) {
            if (!this.audioCtx) return;
            try {
                if (this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume();
                }
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, this.audioCtx.currentTime);
                gain.gain.setValueAtTime(0.2, this.audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + duration);
                osc.connect(gain);
                gain.connect(this.audioCtx.destination);
                osc.start();
                osc.stop(this.audioCtx.currentTime + duration);
            } catch (e) {}
        }

        /**
         * Haptic feedback untuk sensasi native di smartphone
         * @param {number|number[]} pattern 
         */
        vibrate(pattern = 20) {
            if (navigator.vibrate) {
                try { navigator.vibrate(pattern); } catch (e) {}
            }
        }

        /**
         * Jalankan streaming kamera
         * @param {string} facingMode 'environment' atau 'user'
         */
        async startStream(facingMode = 'environment') {
            if (!this.videoEl) return false;
            this.isInitializing = true;

            // Matikan stream aktif sebelumnya
            this.stopStream();

            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

            // Cari kamera wide/utama jika ada banyak lensa belakang (hindari lensa telephoto/zoom)
            let preferredDeviceId = null;
            try {
                if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoInputs = devices.filter(d => d.kind === 'videoinput');
                    if (videoInputs.length > 1) {
                        const targetKeyword = facingMode === 'environment' ? 'back' : 'front';
                        const matching = videoInputs.filter(d => {
                            const lbl = (d.label || '').toLowerCase();
                            return lbl.includes(targetKeyword) && !lbl.includes('telephoto') && !lbl.includes('zoom') && !lbl.includes('macro');
                        });
                        const wideChoice = matching.find(d => {
                            const lbl = (d.label || '').toLowerCase();
                            return lbl.includes('wide') || lbl.includes('ultra') || lbl.includes('main') || lbl.includes('0');
                        }) || matching[0];
                        if (wideChoice && wideChoice.deviceId) {
                            preferredDeviceId = wideChoice.deviceId;
                        }
                    }
                }
            } catch (e) {}

            const baseVideoConstraint = preferredDeviceId 
                ? { deviceId: { exact: preferredDeviceId } }
                : { facingMode: { ideal: facingMode } };

            const constraintsList = isMobile ? [
                // 1. Mobile Portrait: Prioritaskan Main/Wide Lens tanpa sensor crop paksa
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1080, max: 1920 },
                        height: { ideal: 1920, max: 1920 },
                        frameRate: { ideal: 30, max: 60 }
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    }
                },
                // 2. Mobile Fallback HD 720p
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 720 },
                        height: { ideal: 1280 },
                        frameRate: { ideal: 30 }
                    },
                    audio: true
                },
                // 3. Fallback Facing Mode saja
                {
                    video: { facingMode: { ideal: facingMode } },
                    audio: true
                },
                { video: true, audio: true },
                { video: true, audio: false }
            ] : [
                // 1. Desktop / Laptop Webcam: Sensor 16:9 Widescreen Alami (Jangan crop 9:16 di driver agar tidak ngezoom)
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1280, max: 1920 },
                        height: { ideal: 720, max: 1080 },
                        aspectRatio: { ideal: 16 / 9 },
                        frameRate: { ideal: 30 }
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    }
                },
                // 2. Desktop Fallback 1080p
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1920 },
                        height: { ideal: 1080 }
                    },
                    audio: true
                },
                // 3. Desktop Fallback Facing Mode
                {
                    video: { facingMode: { ideal: facingMode } },
                    audio: true
                },
                { video: true, audio: true },
                { video: true, audio: false }
            ];

            let streamAcquired = null;
            let lastError = null;

            for (const c of constraintsList) {
                try {
                    streamAcquired = await navigator.mediaDevices.getUserMedia(c);
                    if (streamAcquired) break;
                } catch (err) {
                    lastError = err;
                    // Lanjutkan ke constraints fallback berikutnya
                }
            }

            if (!streamAcquired) {
                this.isInitializing = false;
                console.error('[CameraEngine] Gagal memperoleh stream kamera:', lastError);
                if (window.MomentPermissionManager) {
                    window.MomentPermissionManager.showPermissionDeniedGuide(lastError?.name);
                }
                return false;
            }

            this.stream = streamAcquired;

            // Reset hardware zoom level ke minimum (1x atau lensa wide, bukan telephoto)
            const videoTrack = this.stream.getVideoTracks()[0];
            if (videoTrack && videoTrack.getCapabilities) {
                try {
                    const capabilities = videoTrack.getCapabilities();
                    if (capabilities.zoom) {
                        const minZoom = capabilities.zoom.min || 1.0;
                        await videoTrack.applyConstraints({
                            advanced: [{ zoom: minZoom }]
                        });
                    }
                } catch (e) {
                    console.warn('[CameraEngine] Gagal reset zoom hardware:', e);
                }
            }

            this.videoEl.srcObject = this.stream;
            this.videoEl.playsInline = true;
            this.videoEl.muted = true; // Supaya tidak feedback saat live preview

            // Adaptif: Saat metadata video dimuat, periksa rasio video untuk mencegah zoom/crop berlebih
            this.videoEl.onloadedmetadata = () => {
                const streamWidth = this.videoEl.videoWidth || 0;
                const streamHeight = this.videoEl.videoHeight || 0;
                const isLandscapeStream = streamWidth > streamHeight;

                // Jika stream berformat landscape (misal webcam PC/laptop), tampilkan dengan object-contain
                // agar kamera tampil utuh, wide-angle, dan tidak terpotong (ngezoom) secara drastis
                if (isLandscapeStream || !isMobile) {
                    const activeRatio = window.MomentCreatorState.get('aspectRatio');
                    if (activeRatio === 'original' || activeRatio === 'landscape' || isLandscapeStream) {
                        this.videoEl.classList.remove('object-cover');
                        this.videoEl.classList.add('object-contain');
                        const fitLabel = document.getElementById('tool-fit-label');
                        const fitIcon = document.getElementById('tool-fit-icon');
                        if (fitLabel) fitLabel.textContent = 'Utuh';
                        if (fitIcon) fitIcon.className = 'fa-solid fa-compress text-sm text-sky-400';
                    }
                }
            };

            try {
                await this.videoEl.play();
            } catch (playErr) {
                console.warn('[CameraEngine] Autoplay video dicegah:', playErr);
            }

            // Update facingMode di state & terapkan transformasi mirror preview
            window.MomentCreatorState.setFacingMode(facingMode);
            window.MomentCreatorState.set('zoom', 1.0);
            this.updateMirrorTransform();
            this.isInitializing = false;
            return true;
        }

        /**
         * Sinkronkan orientasi mirror video preview dengan state
         */
        updateMirrorTransform() {
            if (!this.videoEl) return;
            const isMirrored = window.MomentCreatorState.get('isMirrored');
            const zoom = window.MomentCreatorState.get('zoom') || 1.0;
            const scaleX = isMirrored ? -zoom : zoom;
            const transformVal = `scale(${scaleX}, ${zoom}) translateZ(0)`;
            this.videoEl.style.transform = transformVal;
            this.videoEl.style.webkitTransform = transformVal;
        }

        /**
         * Toggle cermin / mirror horizontal secara manual
         */
        toggleMirror() {
            const isMirrored = window.MomentCreatorState.toggleMirror();
            this.updateMirrorTransform();
            this.vibrate(15);
            return isMirrored;
        }

        /**
         * Hentikan semua track media untuk menghemat baterai & memori
         */
        stopStream() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => {
                    try { track.stop(); } catch (e) {}
                });
                this.stream = null;
            }
            if (this.videoEl) {
                this.videoEl.srcObject = null;
            }
        }

        /**
         * Ganti kamera depan / belakang
         */
        async switchCamera() {
            if (this.isInitializing) return;
            const currentMode = window.MomentCreatorState.get('facingMode');
            const targetMode = currentMode === 'environment' ? 'user' : 'environment';

            this.vibrate(25);
            // Animasi flip 3D pada video element
            if (this.videoEl) {
                this.videoEl.classList.add('transition-transform', 'duration-300', 'scale-x-0');
            }

            const success = await this.startStream(targetMode);

            if (this.videoEl) {
                setTimeout(() => {
                    this.videoEl.classList.remove('scale-x-0');
                }, 150);
            }
            return success;
        }

        /**
         * Aktifkan / matikan senter/flash jika didukung hardware
         */
        async toggleTorch(state) {
            if (!this.stream) return false;
            const videoTrack = this.stream.getVideoTracks()[0];
            if (!videoTrack) return false;

            try {
                const capabilities = videoTrack.getCapabilities ? videoTrack.getCapabilities() : {};
                if (capabilities.torch) {
                    await videoTrack.applyConstraints({
                        advanced: [{ torch: !!state }]
                    });
                    this.vibrate(15);
                    return true;
                }
            } catch (e) {
                console.warn('[CameraEngine] Gagal mengontrol torch/flash:', e);
            }
            return false;
        }

        /**
         * Terapkan zoom hardware atau fallback digital scale
         * @param {number} level Zoom multiplier (1.0 - 5.0)
         */
        async applyZoom(level) {
            const zoomLevel = Math.max(1.0, Math.min(5.0, level));
            window.MomentCreatorState.set('zoom', zoomLevel);

            if (this.stream) {
                const videoTrack = this.stream.getVideoTracks()[0];
                if (videoTrack && videoTrack.getCapabilities) {
                    const capabilities = videoTrack.getCapabilities();
                    if (capabilities.zoom) {
                        try {
                            const min = capabilities.zoom.min || 1;
                            const max = capabilities.zoom.max || 5;
                            const target = Math.min(max, Math.max(min, zoomLevel));
                            await videoTrack.applyConstraints({
                                advanced: [{ zoom: target }]
                            });
                            return;
                        } catch (e) {}
                    }
                }
            }

            // Fallback digital zoom via CSS transform
            if (this.videoEl) {
                this.updateMirrorTransform();
            }
        }

        /**
         * Reset zoom kembali ke 1.0x (Normal / Lensa Paling Lebar)
         */
        async resetZoom() {
            return this.applyZoom(1.0);
        }

        /**
         * Animasi Tap-to-Focus pada koordinat viewport
         */
        triggerFocus(x, y) {
            this.vibrate(15);
            let ring = document.getElementById('camera-focus-ring');
            if (!ring) {
                ring = document.createElement('div');
                ring.id = 'camera-focus-ring';
                ring.className = 'fixed pointer-events-none z-30 w-16 h-16 border-2 border-amber-400 rounded-lg shadow-lg opacity-0 transform -translate-x-1/2 -translate-y-1/2 transition-opacity duration-200';
                document.body.appendChild(ring);
            }

            ring.style.left = `${x}px`;
            ring.style.top = `${y}px`;
            ring.style.opacity = '1';
            ring.style.transform = 'translate(-50%, -50%) scale(1.15)';

            setTimeout(() => {
                ring.style.transform = 'translate(-50%, -50%) scale(0.95)';
            }, 150);

            setTimeout(() => {
                ring.style.opacity = '0';
            }, 850);

            // Coba trigger hardware focus jika didukung
            if (this.stream) {
                const videoTrack = this.stream.getVideoTracks()[0];
                if (videoTrack && videoTrack.applyConstraints) {
                    try {
                        videoTrack.applyConstraints({
                            advanced: [{ focusMode: 'continuous' }]
                        }).catch(() => {});
                    } catch (e) {}
                }
            }
        }

        /**
         * Ambil foto snapshot berkualitas tinggi dan tajam (mendukung Square & Mirror)
         * @returns {Promise<{ blob: Blob, dataUrl: string }>}
         */
        async capturePhoto() {
            if (!this.videoEl) return null;
            this.vibrate([20, 40, 20]);
            this.playBeep(880, 0.08);

            // Animasi flash visual pada layar saat jepret
            this.flashScreenEffect();

            const canvas = document.createElement('canvas');
            // Gunakan resolusi sensor asli video stream
            const videoWidth = this.videoEl.videoWidth || 1080;
            const videoHeight = this.videoEl.videoHeight || 1920;
            canvas.width = videoWidth;
            canvas.height = videoHeight;

            const ctx = canvas.getContext('2d');
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';

            // Cerminkan secara horizontal sesuai state isMirrored agar sama persis dengan preview
            const isMirrored = window.MomentCreatorState.get('isMirrored');
            if (isMirrored) {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }

            // Terapkan filter aktif ke canvas jika ada
            const activeFilter = window.MomentCreatorState.get('activeFilter');
            const filterCss = this.getFilterCss(activeFilter);
            if (filterCss && filterCss !== 'none') {
                ctx.filter = filterCss;
            }

            ctx.drawImage(this.videoEl, 0, 0, canvas.width, canvas.height);

            // Potong ke Rasio Aktif: 1:1, 4:5, 3:4, 16:9, 9:16 atau Asli
            const aspectRatio = window.MomentCreatorState.get('aspectRatio') || 'portrait';
            let finalCanvas = canvas;

            const cropHelper = (ratioW, ratioH) => {
                let targetW, targetH;
                const targetRatio = ratioW / ratioH;
                if (canvas.width / canvas.height > targetRatio) {
                    targetH = canvas.height;
                    targetW = Math.round(targetH * targetRatio);
                } else {
                    targetW = canvas.width;
                    targetH = Math.round(targetW / targetRatio);
                }
                const cropped = document.createElement('canvas');
                cropped.width = targetW;
                cropped.height = targetH;
                const cCtx = cropped.getContext('2d');
                cCtx.imageSmoothingEnabled = true;
                cCtx.imageSmoothingQuality = 'high';
                const sx = (canvas.width - targetW) / 2;
                const sy = (canvas.height - targetH) / 2;
                cCtx.drawImage(canvas, sx, sy, targetW, targetH, 0, 0, targetW, targetH);
                return cropped;
            };

            if (aspectRatio === 'square') {
                finalCanvas = cropHelper(1, 1);
            } else if (aspectRatio === 'feed') {
                finalCanvas = cropHelper(4, 5);
            } else if (aspectRatio === 'standard') {
                finalCanvas = cropHelper(3, 4);
            } else if (aspectRatio === 'landscape') {
                finalCanvas = cropHelper(16, 9);
            } else if (aspectRatio === 'portrait') {
                finalCanvas = cropHelper(9, 16);
            }

            const dataUrl = finalCanvas.toDataURL('image/jpeg', 0.98);

            return new Promise((resolve) => {
                finalCanvas.toBlob((blob) => {
                    const result = { blob, dataUrl };
                    window.MomentCreatorState.setCapturedPhoto(blob, dataUrl);
                    resolve(result);
                }, 'image/jpeg', 0.98);
            });
        }

        flashScreenEffect() {
            const flash = document.createElement('div');
            flash.className = 'fixed inset-0 z-50 bg-white pointer-events-none transition-opacity duration-200';
            document.body.appendChild(flash);
            setTimeout(() => {
                flash.style.opacity = '0';
                setTimeout(() => flash.remove(), 250);
            }, 50);
        }

        /**
         * Dapatkan CSS filter string untuk filter preset
         * @param {string} filterName 
         */
        getFilterCss(filterName) {
            switch (filterName) {
                case 'warm': return 'sepia(0.3) saturate(1.3) brightness(1.05)';
                case 'cool': return 'hue-rotate(185deg) saturate(1.1) brightness(1.02)';
                case 'film': return 'contrast(1.15) brightness(0.95) saturate(0.85) sepia(0.2)';
                case 'mono': return 'grayscale(1) contrast(1.2) brightness(1.05)';
                case 'vivid': return 'saturate(1.7) contrast(1.1) brightness(1.02)';
                case 'cyberpunk': return 'hue-rotate(280deg) saturate(1.8) contrast(1.25)';
                default: return 'none';
            }
        }

        // ==========================================
        // Multi-Clip Media Recording Engine
        // ==========================================

        /**
         * Mulai perekaman klip video
         */
        startRecording() {
            if (!this.stream || window.MomentCreatorState.get('isRecording')) return false;

            const remaining = window.MomentCreatorState.getRemainingDuration();
            if (remaining <= 0.5) {
                if (window.swalRentify) {
                    window.swalRentify.fire({
                        icon: 'info',
                        title: 'Durasi Penuh',
                        text: 'Durasi maksimal momen telah tercapai! Ketuk centang untuk lanjut ke editor.',
                        timer: 2500
                    });
                }
                return false;
            }

            this.vibrate(35);
            this.recordedChunks = [];

            // Tentukan mimeType terbaik yang didukung browser
            const mimeCandidates = [
                'video/webm;codecs=vp9,opus',
                'video/webm;codecs=vp8,opus',
                'video/mp4;codecs=avc1,mp4a',
                'video/mp4',
                'video/webm'
            ];

            let selectedMime = '';
            for (const mime of mimeCandidates) {
                if (window.MediaRecorder && MediaRecorder.isTypeSupported(mime)) {
                    selectedMime = mime;
                    break;
                }
            }

            const options = {
                videoBitsPerSecond: 8000000, // 8 Mbps bitrate tinggi untuk rekaman video jernih dan tajam
                audioBitsPerSecond: 128000,
            };
            if (selectedMime) {
                options.mimeType = selectedMime;
            }
            this.currentMimeType = selectedMime;

            try {
                this.mediaRecorder = new MediaRecorder(this.stream, options);
            } catch (err) {
                console.error('[CameraEngine] Gagal menginisialisasi MediaRecorder:', err);
                return false;
            }

            this.mediaRecorder.ondataavailable = (event) => {
                if (event.data && event.data.size > 0) {
                    this.recordedChunks.push(event.data);
                }
            };

            this.mediaRecorder.onstop = () => {
                this.finalizeClip();
            };

            this.mediaRecorder.start(100); // chunk tiap 100ms
            this.currentClipStartTime = Date.now();
            window.MomentCreatorState.set('isRecording', true);
            window.MomentCreatorState.set('recordingElapsed', 0);

            // Interval tick progress bar (50ms interval untuk 60fps animasi)
            const speed = window.MomentCreatorState.get('activeSpeed') || 1.0;
            this.recordTimerInterval = setInterval(() => {
                const realElapsed = (Date.now() - this.currentClipStartTime) / 1000;
                // Durasi klip disesuaikan dengan kecepatan (misal 2x speed -> klip lebih cepat)
                const effectiveElapsed = realElapsed * speed;
                window.MomentCreatorState.set('recordingElapsed', effectiveElapsed);

                const currentRemaining = window.MomentCreatorState.getRemainingDuration() - effectiveElapsed;
                if (currentRemaining <= 0) {
                    this.stopRecording();
                }
            }, 50);

            return true;
        }

        /**
         * Berhenti merekam klip
         */
        stopRecording() {
            if (!window.MomentCreatorState.get('isRecording') || !this.mediaRecorder) return;

            clearInterval(this.recordTimerInterval);
            this.recordTimerInterval = null;
            this.vibrate([20, 20]);

            if (this.mediaRecorder.state !== 'inactive') {
                try {
                    this.mediaRecorder.stop();
                } catch (e) {}
            }

            window.MomentCreatorState.set('isRecording', false);
        }

        /**
         * Simpan klip yang baru direkam ke dalam state multi-klip
         */
        finalizeClip() {
            const rawDuration = (Date.now() - this.currentClipStartTime) / 1000;
            const speed = window.MomentCreatorState.get('activeSpeed') || 1.0;
            const finalDuration = Math.max(0.3, rawDuration * speed);

            // Jangan simpan klip yang terlalu pendek (misal accidental tap < 0.2s)
            if (rawDuration < 0.25 || this.recordedChunks.length === 0) {
                this.recordedChunks = [];
                window.MomentCreatorState.set('recordingElapsed', 0);
                return;
            }

            const mime = this.currentMimeType || 'video/webm';
            const blob = new Blob(this.recordedChunks, { type: mime });
            this.recordedChunks = [];

            window.MomentCreatorState.addClip({
                blob: blob,
                duration: finalDuration,
                speed: speed,
                filter: window.MomentCreatorState.get('activeFilter')
            });

            window.MomentCreatorState.set('recordingElapsed', 0);
        }
    }

    window.MomentCameraEngine = new MomentCameraEngine();
})(window);
