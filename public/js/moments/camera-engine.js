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
            this.isHardwareZoomActive = false;
            this.ultraWideDeviceId = null;
            this.mainCameraDeviceId = null;
            this.currentFacingMode = 'environment';
            this.recordingAudioTrack = null;
            this.audioStream = null;
        }

        /**
         * Siapkan audio track mikrofon lebih awal agar perekaman klip instan tanpa delay spin-up hardware
         * @returns {Promise<MediaStreamTrack|null>}
         */
        async initAudioTrack() {
            if (this.recordingAudioTrack && this.recordingAudioTrack.readyState === 'live') {
                return this.recordingAudioTrack;
            }
            try {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    const aStream = await navigator.mediaDevices.getUserMedia({
                        audio: {
                            echoCancellation: true,
                            noiseSuppression: true,
                            autoGainControl: true
                        }
                    });
                    this.audioStream = aStream;
                    const track = aStream.getAudioTracks()[0];
                    if (track) {
                        track.enabled = false; // Muted saat live preview
                        this.recordingAudioTrack = track;
                        return track;
                    }
                }
            } catch (err) {
                console.warn('[CameraEngine] Mikrofon tidak dapat diakses:', err);
            }
            return null;
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
         * Jalankan streaming kamera berkualitas 100% Full HD
         * @param {string} facingMode 'environment' atau 'user'
         * @param {string|null} forceDeviceId Device ID spesifik (misal ultra-wide)
         */
        async startStream(facingMode = 'environment', forceDeviceId = null) {
            if (!this.videoEl) return false;
            this.isInitializing = true;
            this.currentFacingMode = facingMode;

            // Matikan stream aktif sebelumnya
            this.stopStream();

            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

            // Enumerasi kamera untuk mendeteksi kamera ultra-wide (0.5x) jika tersedia
            let preferredDeviceId = forceDeviceId;
            let ultraWideDeviceId = null;
            try {
                if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoInputs = devices.filter(d => d.kind === 'videoinput');
                    if (videoInputs.length > 1) {
                        // Lensa ultra-wide (untuk mode zoom 0.5x)
                        const ultra = videoInputs.find(d => {
                            const lbl = (d.label || '').toLowerCase();
                            return lbl.includes('ultra') || lbl.includes('0.5') || lbl.includes('wide-angle');
                        });
                        if (ultra && ultra.deviceId) {
                            ultraWideDeviceId = ultra.deviceId;
                        }

                        // Hanya tetapkan preferredDeviceId jika bukan perangkat mobile atau jika diminta spesifik (forceDeviceId)
                        if (!isMobile && !preferredDeviceId) {
                            const targetKeyword = facingMode === 'environment' ? 'back' : 'front';
                            const matching = videoInputs.filter(d => {
                                const lbl = (d.label || '').toLowerCase();
                                return lbl.includes(targetKeyword) && !lbl.includes('telephoto') && !lbl.includes('macro');
                            });
                            const wideChoice = matching.find(d => {
                                const lbl = (d.label || '').toLowerCase();
                                return (lbl.includes('main') || lbl.includes('wide') || lbl.includes('0')) && !lbl.includes('ultra');
                            }) || matching[0];
                            if (wideChoice && wideChoice.deviceId) {
                                preferredDeviceId = wideChoice.deviceId;
                            }
                        }
                    }
                }
            } catch (e) {}

            this.ultraWideDeviceId = ultraWideDeviceId;
            this.mainCameraDeviceId = preferredDeviceId;

            // PENTING: Pada smartphone, JANGAN memaksa deviceId acak karena driver sering memilih kamera telephoto/makro
            // yang menyebabkan gambar sangat ngezoom (3-4x crop). Gunakan facingMode murni agar OS memilih kamera utama wide 1x.
            const baseVideoConstraint = (preferredDeviceId && !isMobile) || forceDeviceId
                ? { deviceId: { exact: preferredDeviceId || forceDeviceId } }
                : { facingMode: { ideal: facingMode } };

            const constraintsList = isMobile ? [
                // 1. Mobile Portrait 1080x1920 (9:16 Full HD Layar Penuh)
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1080 },
                        height: { ideal: 1920 },
                        aspectRatio: { ideal: 9 / 16 },
                        frameRate: { ideal: 30, max: 60 }
                    },
                    audio: false
                },
                // 2. Mobile Fallback HD 720p Portrait (720x1280)
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 720 },
                        height: { ideal: 1280 },
                        aspectRatio: { ideal: 9 / 16 }
                    },
                    audio: false
                },
                // 3. Mobile Sensor Asli Smartphone (3:4 High-Res Portrait)
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1080 },
                        height: { ideal: 1440 },
                        aspectRatio: { ideal: 3 / 4 }
                    },
                    audio: false
                },
                // 4. Mobile Sensor 4:3 (1440x1920)
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1440 },
                        height: { ideal: 1920 }
                    },
                    audio: false
                },
                // 5. Mobile FHD Standar
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1920 },
                        height: { ideal: 1080 }
                    },
                    audio: false
                },
                // 6. Mobile Fallback Facing Mode Murni
                {
                    video: { facingMode: { ideal: facingMode } },
                    audio: false
                },
                { video: true, audio: false }
            ] : [
                // 1. Desktop / Laptop Webcam: Sensor 16:9 Widescreen Alami Kualitas 100% HD
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1920, min: 1280 },
                        height: { ideal: 1080, min: 720 },
                        aspectRatio: { ideal: 16 / 9 },
                        frameRate: { ideal: 30, max: 60 }
                    },
                    audio: false
                },
                // 2. Desktop Fallback 720p HD
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        aspectRatio: { ideal: 16 / 9 },
                        frameRate: { ideal: 30 }
                    },
                    audio: false
                },
                // 3. Desktop Fallback Facing Mode FHD
                {
                    video: { facingMode: { ideal: facingMode }, width: { ideal: 1920 }, height: { ideal: 1080 } },
                    audio: false
                },
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
            try { localStorage.setItem('camera_permission_granted', '1'); } catch (e) {}

            this.isHardwareZoomActive = false;

            // Reset hardware zoom level ke normal 1.0 jika didukung
            const videoTrack = this.stream.getVideoTracks()[0];
            if (videoTrack && videoTrack.getCapabilities) {
                try {
                    const capabilities = videoTrack.getCapabilities();
                    if (capabilities.zoom) {
                        const min = capabilities.zoom.min || 1.0;
                        const max = capabilities.zoom.max || 1.0;
                        const defaultZoom = Math.max(min, Math.min(max, 1.0));
                        await videoTrack.applyConstraints({ advanced: [{ zoom: defaultZoom }] });
                        this.isHardwareZoomActive = true;
                    }
                } catch (e) {
                    console.warn('[CameraEngine] Hardware camera zoom reset:', e);
                }
            }

            this.videoEl.srcObject = this.stream;
            this.videoEl.playsInline = true;
            this.videoEl.muted = true; // Supaya tidak feedback saat live preview
            this.videoEl.style.imageRendering = 'auto'; // Rendering halus natural bebas pixelation

            const updatePreviewRatioClass = () => {
                const ratio = window.MomentCreatorState.get('aspectRatio') || 'portrait';
                if (ratio === 'original' || ratio === 'utuh') {
                    this.videoEl.classList.remove('object-cover');
                    this.videoEl.classList.add('object-contain');
                } else {
                    this.videoEl.classList.remove('object-contain');
                    this.videoEl.classList.add('object-cover');
                }
            };

            updatePreviewRatioClass();
            this.videoEl.onloadedmetadata = updatePreviewRatioClass;

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
         * @param {number|null} digitalZoomOverride
         */
        updateMirrorTransform(digitalZoomOverride = null) {
            if (!this.videoEl) return;
            const facingMode = window.MomentCreatorState.get('facingMode') || this.currentFacingMode;
            const isManualMirrored = window.MomentCreatorState.get('isMirrored');
            
            // Kamera depan (selfie) secara alami dicerminkan pada pratinjau (seperti cermin) kecuali dibalik manual
            const isSelfie = (facingMode === 'user');
            const shouldMirrorPreview = isSelfie ? !isManualMirrored : !!isManualMirrored;

            let scaleFactor = 1.0;
            if (digitalZoomOverride !== null) {
                scaleFactor = digitalZoomOverride;
            } else if (!this.isHardwareZoomActive) {
                scaleFactor = window.MomentCreatorState.get('zoom') || 1.0;
            }

            const scaleX = shouldMirrorPreview ? -scaleFactor : scaleFactor;
            const scaleY = scaleFactor;
            const transformVal = `scale(${scaleX}, ${scaleY}) translateZ(0)`;
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
            if (this.recordingAudioTrack) {
                try { this.recordingAudioTrack.stop(); } catch (e) {}
                this.recordingAudioTrack = null;
            }
            if (this.audioStream) {
                try {
                    this.audioStream.getTracks().forEach(track => {
                        try { track.stop(); } catch (e) {}
                    });
                } catch (e) {}
                this.audioStream = null;
            }
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
         * Terapkan zoom kamera: hardware optical/sensor zoom atau CSS transform
         * @param {number} level Zoom multiplier (0.5 - 5.0)
         */
        async applyZoom(level) {
            const zoomLevel = Math.max(0.5, Math.min(5.0, parseFloat(level)));

            // Jika zoom 0.5x diminta (ultra-wide)
            if (zoomLevel < 1.0) {
                // Periksa apakah perangkat memiliki sensor ultra-wide terpisah
                if (this.ultraWideDeviceId && this.currentFacingMode === 'environment') {
                    if (this.stream) {
                        const currentTrack = this.stream.getVideoTracks()[0];
                        const currentSettings = currentTrack ? currentTrack.getSettings() : {};
                        if (currentSettings.deviceId !== this.ultraWideDeviceId) {
                            try {
                                await this.startStream('environment', this.ultraWideDeviceId);
                                this.isHardwareZoomActive = true;
                                this.vibrate(15);
                                window.MomentCreatorState.set('zoom', 0.5);
                                return;
                            } catch (e) {
                                console.warn('[CameraEngine] Gagal beralih ke lensa ultra-wide:', e);
                            }
                        }
                    }
                }

                // Periksa apakah track kamera mendukung hardware zoom-out < 1.0
                const videoTrack = this.stream ? this.stream.getVideoTracks()[0] : null;
                const caps = (videoTrack && videoTrack.getCapabilities) ? videoTrack.getCapabilities() : {};
                const canHardwareZoomOut = caps.zoom && caps.zoom.min < 1.0;

                if (!canHardwareZoomOut && !this.ultraWideDeviceId) {
                    // Perangkat tidak memiliki sensor ultra-wide fisik
                    // Hindari CSS scale(0.5) karena akan mengecilkan video menjadi kotak di tengah layar hitam
                    if (window.MomentCreatorUI) {
                        window.MomentCreatorUI.showFilterToast('Lensa Ultra-Wide tidak tersedia di perangkat ini');
                    }
                    window.MomentCreatorState.set('zoom', 1.0);
                    this.updateMirrorTransform(1.0);
                    return;
                }
            } else if (zoomLevel >= 1.0 && this.currentFacingMode === 'environment' && this.ultraWideDeviceId) {
                // Jika sebelumnya di ultra-wide dan kembali ke >= 1x, beralih ke main camera
                if (this.stream) {
                    const currentTrack = this.stream.getVideoTracks()[0];
                    const currentSettings = currentTrack ? currentTrack.getSettings() : {};
                    if (currentSettings.deviceId === this.ultraWideDeviceId) {
                        try {
                            await this.startStream('environment', this.mainCameraDeviceId || null);
                        } catch (e) {}
                    }
                }
            }

            window.MomentCreatorState.set('zoom', zoomLevel);
            const hardwareApplied = await this.applyHardwareZoom(zoomLevel);
            this.isHardwareZoomActive = hardwareApplied;

            // PENTING: Jika hardware zoom berhasil, CSS scale = 1.0 agar tidak terjadi double-zoom!
            if (this.videoEl) {
                const digitalScale = hardwareApplied ? 1.0 : zoomLevel;
                this.updateMirrorTransform(digitalScale);
            }
        }

        /**
         * Eksekusi hardware camera zoom pada track MediaStream
         * @param {number} zoomLevel 
         * @returns {Promise<boolean>}
         */
        async applyHardwareZoom(zoomLevel) {
            if (!this.stream) return false;
            const videoTrack = this.stream.getVideoTracks()[0];
            if (videoTrack && videoTrack.getCapabilities) {
                const capabilities = videoTrack.getCapabilities();
                if (capabilities.zoom) {
                    try {
                        const min = capabilities.zoom.min || 1.0;
                        const max = capabilities.zoom.max || 5.0;
                        const clamped = Math.max(min, Math.min(max, zoomLevel));
                        await videoTrack.applyConstraints({
                            advanced: [{ zoom: clamped }]
                        });
                        return true;
                    } catch (e) {
                        console.warn('[CameraEngine] Hardware zoom applyConstraints error:', e);
                    }
                }
            }
            return false;
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
         * Ambil foto snapshot berkualitas tinggi dan tajam (Kamera Sensor Asli 100% HD + Hardware ISP)
         * @returns {Promise<{ blob: Blob, dataUrl: string }>}
         */
        async capturePhoto() {
            if (!this.videoEl) return null;
            this.vibrate([20, 40, 20]);
            this.playBeep(880, 0.08);

            // Animasi flash visual pada layar saat jepret
            this.flashScreenEffect();

            const facingMode = window.MomentCreatorState.get('facingMode') || this.currentFacingMode;
            const isManualMirrored = window.MomentCreatorState.get('isMirrored');
            const activeFilter = window.MomentCreatorState.get('activeFilter');
            const aspectRatio = window.MomentCreatorState.get('aspectRatio') || 'portrait';
            const zoomLevel = window.MomentCreatorState.get('zoom') || 1.0;
            const filterCss = this.getFilterCss(activeFilter);

            let naturalWidth = this.videoEl.videoWidth || 1080;
            let naturalHeight = this.videoEl.videoHeight || 1920;

            const canvas = document.createElement('canvas');
            canvas.width = naturalWidth;
            canvas.height = naturalHeight;

            const ctx = canvas.getContext('2d');
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';

            // Cerminkan foto agar identik dengan apa yang dilihat pengguna di pratinjau kamera (WYSIWYG)
            // Kamera depan secara alami di-mirror di layar pratinjau, kecuali tombol cermin manual diaktifkan/dinonaktifkan
            const isSelfie = (facingMode === 'user');
            const shouldMirror = isSelfie ? !isManualMirrored : !!isManualMirrored;

            if (shouldMirror) {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }

            // Terapkan filter aktif ke canvas jika ada
            if (filterCss && filterCss !== 'none') {
                ctx.filter = filterCss;
            }

            // Jika zoom digital aktif (hardware zoom tidak aktif dan zoom > 1.0):
            // Lakukan crop di tengah canvas agar hasil foto 100% identik dengan yang terlihat di layar
            if (!this.isHardwareZoomActive && zoomLevel > 1.05) {
                const cropW = naturalWidth / zoomLevel;
                const cropH = naturalHeight / zoomLevel;
                const cropX = (naturalWidth - cropW) / 2;
                const cropY = (naturalHeight - cropH) / 2;
                ctx.drawImage(this.videoEl, cropX, cropY, cropW, cropH, 0, 0, canvas.width, canvas.height);
            } else {
                ctx.drawImage(this.videoEl, 0, 0, canvas.width, canvas.height);
            }

            // Potong ke Rasio Kamera: 9:16 (Layar Penuh), 3:4 (Standar Ponsel), 1:1 (Persegi), atau Utuh (Original)
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

            if (aspectRatio === 'original' || aspectRatio === 'utuh') {
                // RASIO UTUH / ASLI SENSOR: 100% FRAME ASLI KAMERA TANPA TERPOTONG
                finalCanvas = canvas;
            } else if (aspectRatio === 'square' || aspectRatio === '1:1') {
                finalCanvas = cropHelper(1, 1);
            } else if (aspectRatio === 'standard' || aspectRatio === '3:4') {
                finalCanvas = cropHelper(3, 4);
            } else {
                // Default 9:16 (Layar Penuh Story & Reels)
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
         * Mulai perekaman klip video (Hardware H.264 Accelerated, 60fps Smooth, Anti-Patah)
         */
        async startRecording() {
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

            // Pastikan audio track tersedia pada stream untuk merekam suara video
            if (this.stream.getAudioTracks().length === 0) {
                try {
                    const audioStream = await navigator.mediaDevices.getUserMedia({
                        audio: true
                    });
                    const audioTrack = audioStream.getAudioTracks()[0];
                    if (audioTrack) {
                        this.stream.addTrack(audioTrack);
                        this.recordingAudioTrack = audioTrack;
                    }
                } catch (audioErr) {
                    console.warn('[CameraEngine] Merekam tanpa audio mikrofon:', audioErr);
                }
            }

            this.vibrate(35);
            this.recordedChunks = [];

            // Prioritas hardware-accelerated H.264/MP4 untuk perekaman mulus tanpa lag/patah
            const mimeCandidates = [
                'video/mp4;codecs=avc1,mp4a',
                'video/mp4;codecs=avc1',
                'video/mp4',
                'video/webm;codecs=h264,opus',
                'video/webm;codecs=h264',
                'video/webm;codecs=vp8,opus',
                'video/webm;codecs=vp8',
                'video/webm',
                'video/webm;codecs=vp9,opus'
            ];

            let selectedMime = '';
            for (const mime of mimeCandidates) {
                if (window.MediaRecorder && MediaRecorder.isTypeSupported(mime)) {
                    selectedMime = mime;
                    break;
                }
            }

            const options = {
                videoBitsPerSecond: 6000000, // 6 Mbps: Kualitas Full HD jernih optimal tanpa membebani encoder hardware
                audioBitsPerSecond: 128000,
            };
            if (selectedMime) {
                options.mimeType = selectedMime;
            }
            this.currentMimeType = selectedMime;

            try {
                this.mediaRecorder = new MediaRecorder(this.stream, options);
            } catch (err) {
                console.warn('[CameraEngine] Gagal dengan opsi bitrate, fallback ke MediaRecorder default:', err);
                try {
                    this.mediaRecorder = new MediaRecorder(this.stream);
                } catch (fallbackErr) {
                    console.error('[CameraEngine] MediaRecorder error:', fallbackErr);
                    return false;
                }
            }

            this.mediaRecorder.ondataavailable = (event) => {
                if (event.data && event.data.size > 0) {
                    this.recordedChunks.push(event.data);
                }
            };

            this.mediaRecorder.onstop = () => {
                this.finalizeClip();
            };

            // Timeslice 1000ms untuk encoding hardware mulus tanpa interupsi event berlebih
            this.mediaRecorder.start(1000);
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

            if (this.recordingAudioTrack) {
                try {
                    this.recordingAudioTrack.stop();
                    if (this.stream) this.stream.removeTrack(this.recordingAudioTrack);
                } catch (e) {}
                this.recordingAudioTrack = null;
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
