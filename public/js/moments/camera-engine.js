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

            const constraintsList = [
                // Ideal: portrait 9:16 resolusi tinggi
                {
                    video: {
                        facingMode: { ideal: facingMode },
                        width: { ideal: 1080 },
                        height: { ideal: 1920 },
                        frameRate: { ideal: 30, max: 60 }
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true
                    }
                },
                // Fallback 1: video biasa + audio
                {
                    video: { facingMode: facingMode },
                    audio: true
                },
                // Fallback 2: video apa saja + audio
                {
                    video: true,
                    audio: true
                },
                // Fallback 3: video saja (jika perangkat tidak punya mikrofon)
                {
                    video: true,
                    audio: false
                }
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
            this.videoEl.srcObject = this.stream;
            this.videoEl.playsInline = true;
            this.videoEl.muted = true; // Supaya tidak feedback saat live preview

            try {
                await this.videoEl.play();
            } catch (playErr) {
                console.warn('[CameraEngine] Autoplay video dicegah:', playErr);
            }

            // Update facingMode di state
            window.MomentCreatorState.set('facingMode', facingMode);
            this.isInitializing = false;
            return true;
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
                            const target = min + (zoomLevel - 1) * ((max - min) / 4);
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
                this.videoEl.style.transform = `scale(${zoomLevel})`;
            }
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
         * Ambil foto snapshot berkualitas tinggi dari video stream
         * @returns {Promise<{ blob: Blob, dataUrl: string }>}
         */
        async capturePhoto() {
            if (!this.videoEl) return null;
            this.vibrate([20, 40, 20]);
            this.playBeep(880, 0.08);

            // Animasi flash visual pada layar saat jepret
            this.flashScreenEffect();

            const canvas = document.createElement('canvas');
            const videoWidth = this.videoEl.videoWidth || 1080;
            const videoHeight = this.videoEl.videoHeight || 1920;
            canvas.width = videoWidth;
            canvas.height = videoHeight;

            const ctx = canvas.getContext('2d');

            // Cerminkan secara horizontal jika menggunakan kamera depan (selfie)
            const isUserFacing = window.MomentCreatorState.get('facingMode') === 'user';
            if (isUserFacing) {
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

            const dataUrl = canvas.toDataURL('image/jpeg', 0.95);

            return new Promise((resolve) => {
                canvas.toBlob((blob) => {
                    const result = { blob, dataUrl };
                    window.MomentCreatorState.setCapturedPhoto(blob, dataUrl);
                    resolve(result);
                }, 'image/jpeg', 0.95);
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

            const options = selectedMime ? { mimeType: selectedMime } : {};
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
