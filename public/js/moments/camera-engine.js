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

            // Enumerasi kamera untuk mendeteksi kamera utama (wide) dan kamera ultra-wide (0.5x)
            let preferredDeviceId = forceDeviceId;
            let ultraWideDeviceId = null;
            try {
                if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoInputs = devices.filter(d => d.kind === 'videoinput');
                    if (videoInputs.length > 1) {
                        const targetKeyword = facingMode === 'environment' ? 'back' : 'front';
                        const matching = videoInputs.filter(d => {
                            const lbl = (d.label || '').toLowerCase();
                            return lbl.includes(targetKeyword) && !lbl.includes('telephoto') && !lbl.includes('macro');
                        });

                        // Lensa ultra-wide (untuk mode zoom 0.5x)
                        const ultra = videoInputs.find(d => {
                            const lbl = (d.label || '').toLowerCase();
                            return lbl.includes('ultra') || lbl.includes('0.5') || lbl.includes('wide-angle');
                        });
                        if (ultra && ultra.deviceId) {
                            ultraWideDeviceId = ultra.deviceId;
                        }

                        if (!preferredDeviceId) {
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

            const baseVideoConstraint = preferredDeviceId 
                ? { deviceId: { ideal: preferredDeviceId } }
                : { facingMode: { ideal: facingMode } };

            const constraintsList = isMobile ? [
                // 1. Mobile FHD Portrait 1080x1920 (Kualitas 100% HD Tajam Langsung)
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1080, min: 720, max: 2160 },
                        height: { ideal: 1920, min: 1280, max: 3840 },
                        frameRate: { ideal: 30, max: 60 }
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    }
                },
                // 2. Mobile Landscape/Natural FHD 1920x1080
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1920, min: 1280, max: 3840 },
                        height: { ideal: 1080, min: 720, max: 2160 },
                        frameRate: { ideal: 30, max: 60 }
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    }
                },
                // 3. Mobile Fallback HD 720p
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 720 },
                        height: { ideal: 1280 },
                        frameRate: { ideal: 30 }
                    },
                    audio: true
                },
                // 4. Fallback Facing Mode FHD
                {
                    video: { facingMode: { ideal: facingMode }, width: { ideal: 1920 }, height: { ideal: 1080 } },
                    audio: true
                },
                { video: true, audio: true },
                { video: true, audio: false }
            ] : [
                // 1. Desktop / Laptop Webcam: Sensor 16:9 Widescreen Alami Kualitas 100% HD
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1920, min: 1280, max: 3840 },
                        height: { ideal: 1080, min: 720, max: 2160 },
                        aspectRatio: { ideal: 16 / 9 },
                        frameRate: { ideal: 30, max: 60 }
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    }
                },
                // 2. Desktop Fallback 720p HD
                {
                    video: {
                        ...baseVideoConstraint,
                        width: { ideal: 1280, min: 720 },
                        height: { ideal: 720, min: 480 },
                        aspectRatio: { ideal: 16 / 9 },
                        frameRate: { ideal: 30 }
                    },
                    audio: true
                },
                // 3. Desktop Fallback Facing Mode FHD
                {
                    video: { facingMode: { ideal: facingMode }, width: { ideal: 1920 }, height: { ideal: 1080 } },
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
            try { localStorage.setItem('camera_permission_granted', '1'); } catch (e) {}

            this.isHardwareZoomActive = false;

            // Reset hardware zoom level ke normal 1x dan aktifkan hardware auto-focus, auto-exposure, auto-white-balance
            const videoTrack = this.stream.getVideoTracks()[0];
            if (videoTrack && videoTrack.getCapabilities) {
                try {
                    const capabilities = videoTrack.getCapabilities();
                    const advanced = [];

                    // Reset zoom hardware ke 1.0 (atau minZoom bawaan)
                    if (capabilities.zoom) {
                        const minZoom = capabilities.zoom.min || 1.0;
                        advanced.push({ zoom: minZoom });
                        this.isHardwareZoomActive = true;
                    }

                    // Aktifkan continuous autofocus bawaan sensor kamera
                    if (capabilities.focusMode && capabilities.focusMode.includes('continuous')) {
                        advanced.push({ focusMode: 'continuous' });
                    }

                    // Aktifkan continuous exposure bawaan kamera agar pencahayaan stabil
                    if (capabilities.exposureMode && capabilities.exposureMode.includes('continuous')) {
                        advanced.push({ exposureMode: 'continuous' });
                    }

                    // Aktifkan continuous white-balance agar warna alami
                    if (capabilities.whiteBalanceMode && capabilities.whiteBalanceMode.includes('continuous')) {
                        advanced.push({ whiteBalanceMode: 'continuous' });
                    }

                    if (advanced.length > 0) {
                        await videoTrack.applyConstraints({ advanced });
                    }
                } catch (e) {
                    console.warn('[CameraEngine] Hardware camera constraints tuning:', e);
                }
            }

            this.videoEl.srcObject = this.stream;
            this.videoEl.playsInline = true;
            this.videoEl.muted = true; // Supaya tidak feedback saat live preview
            this.videoEl.style.imageRendering = '-webkit-optimize-contrast';

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
            const isMirrored = window.MomentCreatorState.get('isMirrored');
            
            // PENTING: Jika hardware zoom aktif, zoom sudah ditangani langsung oleh sensor kamera.
            // Jangan kalikan lagi dengan CSS scale agar tidak terjadi zoom ganda (double-zoom / abnormal)!
            let scaleFactor = 1.0;
            if (digitalZoomOverride !== null) {
                scaleFactor = digitalZoomOverride;
            } else if (!this.isHardwareZoomActive) {
                scaleFactor = window.MomentCreatorState.get('zoom') || 1.0;
            }

            const scaleX = isMirrored ? -scaleFactor : scaleFactor;
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
            window.MomentCreatorState.set('zoom', zoomLevel);

            let hardwareApplied = false;

            // Jika zoom 0.5x dan ada lensa ultra-wide fisik terpisah pada perangkat
            if (zoomLevel <= 0.6 && this.ultraWideDeviceId && this.currentFacingMode === 'environment') {
                if (this.stream) {
                    const currentTrack = this.stream.getVideoTracks()[0];
                    const currentSettings = currentTrack ? currentTrack.getSettings() : {};
                    if (currentSettings.deviceId !== this.ultraWideDeviceId) {
                        try {
                            await this.startStream('environment', this.ultraWideDeviceId);
                            this.isHardwareZoomActive = true;
                            this.vibrate(15);
                            return;
                        } catch (e) {
                            console.warn('[CameraEngine] Gagal beralih ke lensa ultra-wide:', e);
                        }
                    }
                }
            } else if (zoomLevel > 0.8 && this.mainCameraDeviceId && this.currentFacingMode === 'environment') {
                // Jika sebelumnya di ultra-wide dan kembali ke >= 1x, beralih ke main camera
                if (this.stream) {
                    const currentTrack = this.stream.getVideoTracks()[0];
                    const currentSettings = currentTrack ? currentTrack.getSettings() : {};
                    if (currentSettings.deviceId === this.ultraWideDeviceId) {
                        try {
                            await this.startStream('environment', this.mainCameraDeviceId);
                        } catch (e) {}
                    }
                }
            }

            // Terapkan hardware zoom pada videoTrack jika didukung
            if (this.stream) {
                const videoTrack = this.stream.getVideoTracks()[0];
                if (videoTrack && videoTrack.getCapabilities) {
                    const capabilities = videoTrack.getCapabilities();
                    if (capabilities.zoom) {
                        try {
                            const min = capabilities.zoom.min || 1.0;
                            const max = capabilities.zoom.max || 5.0;
                            if (zoomLevel >= min && zoomLevel <= max) {
                                await videoTrack.applyConstraints({
                                    advanced: [{ zoom: zoomLevel }]
                                });
                                hardwareApplied = true;
                            }
                        } catch (e) {
                            console.warn('[CameraEngine] Hardware zoom applyConstraints error:', e);
                        }
                    }
                }
            }

            this.isHardwareZoomActive = hardwareApplied;

            // PENTING: Jika hardware zoom berhasil, CSS scale = 1.0 agar tidak terjadi double-zoom!
            if (this.videoEl) {
                const digitalScale = hardwareApplied ? 1.0 : zoomLevel;
                this.updateMirrorTransform(digitalScale);
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
         * Ambil foto snapshot berkualitas tinggi dan tajam (Kamera Sensor Asli 100% HD + Hardware ISP)
         * @returns {Promise<{ blob: Blob, dataUrl: string }>}
         */
        async capturePhoto() {
            if (!this.videoEl) return null;
            this.vibrate([20, 40, 20]);
            this.playBeep(880, 0.08);

            // Animasi flash visual pada layar saat jepret
            this.flashScreenEffect();

            const isMirrored = window.MomentCreatorState.get('isMirrored');
            const activeFilter = window.MomentCreatorState.get('activeFilter');
            const aspectRatio = window.MomentCreatorState.get('aspectRatio') || 'portrait';
            const zoomLevel = window.MomentCreatorState.get('zoom') || 1.0;
            const filterCss = this.getFilterCss(activeFilter);

            let sourceImage = null;
            let naturalWidth = this.videoEl.videoWidth || 1920;
            let naturalHeight = this.videoEl.videoHeight || 1080;

            // 1. Coba ambil foto beresolusi penuh langsung dari hardware sensor menggunakan ImageCapture API
            const videoTrack = this.stream ? this.stream.getVideoTracks()[0] : null;
            if (window.ImageCapture && videoTrack && videoTrack.readyState === 'live') {
                try {
                    const imageCapture = new ImageCapture(videoTrack);
                    const photoSettings = {};
                    try {
                        const caps = await imageCapture.getPhotoCapabilities();
                        if (caps && caps.imageWidth && caps.imageWidth.max) {
                            photoSettings.imageWidth = caps.imageWidth.max;
                        }
                        if (caps && caps.imageHeight && caps.imageHeight.max) {
                            photoSettings.imageHeight = caps.imageHeight.max;
                        }
                    } catch(capErr) {}

                    const rawBlob = await imageCapture.takePhoto(photoSettings);
                    if (rawBlob && rawBlob.size > 0) {
                        const tempImg = new Image();
                        const imgUrl = URL.createObjectURL(rawBlob);
                        await new Promise((res, rej) => {
                            tempImg.onload = () => {
                                naturalWidth = tempImg.naturalWidth;
                                naturalHeight = tempImg.naturalHeight;
                                sourceImage = tempImg;
                                res();
                            };
                            tempImg.onerror = rej;
                            tempImg.src = imgUrl;
                        });
                    }
                } catch (icErr) {
                    sourceImage = null;
                }
            }

            // Fallback ke video element jika ImageCapture tidak didukung oleh browser tertentu
            if (!sourceImage) {
                sourceImage = this.videoEl;
                naturalWidth = this.videoEl.videoWidth || 1920;
                naturalHeight = this.videoEl.videoHeight || 1080;
            }

            const canvas = document.createElement('canvas');
            canvas.width = naturalWidth;
            canvas.height = naturalHeight;

            const ctx = canvas.getContext('2d');
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';

            // Cerminkan secara horizontal sesuai state isMirrored agar sama persis dengan preview
            if (isMirrored) {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }

            // Terapkan filter aktif ke canvas jika ada
            if (filterCss && filterCss !== 'none') {
                ctx.filter = filterCss;
            }

            // Jika zoom digital aktif (hardware zoom tidak aktif dan zoom bukan 1.0):
            // Lakukan crop/scale di tengah canvas agar hasil foto 100% identik dengan yang terlihat di layar
            if (!this.isHardwareZoomActive && Math.abs(zoomLevel - 1.0) > 0.05) {
                if (zoomLevel > 1.0) {
                    const cropW = naturalWidth / zoomLevel;
                    const cropH = naturalHeight / zoomLevel;
                    const cropX = (naturalWidth - cropW) / 2;
                    const cropY = (naturalHeight - cropH) / 2;
                    ctx.drawImage(sourceImage, cropX, cropY, cropW, cropH, 0, 0, canvas.width, canvas.height);
                } else {
                    // Zoom out 0.5x
                    const drawW = naturalWidth * zoomLevel;
                    const drawH = naturalHeight * zoomLevel;
                    const drawX = (naturalWidth - drawW) / 2;
                    const drawY = (naturalHeight - drawH) / 2;
                    ctx.drawImage(sourceImage, 0, 0, naturalWidth, naturalHeight, drawX, drawY, drawW, drawH);
                }
            } else {
                ctx.drawImage(sourceImage, 0, 0, canvas.width, canvas.height);
            }

            if (sourceImage instanceof HTMLImageElement && sourceImage.src) {
                try { URL.revokeObjectURL(sourceImage.src); } catch (e) {}
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
                videoBitsPerSecond: 10000000, // 10 Mbps: Kualitas 100% HD tajam, jernih, dan mulus tanpa kompresi buram/patah
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
