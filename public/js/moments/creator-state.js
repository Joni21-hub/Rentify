/**
 * Rentify Momen Creator - Centralized State Store (creator-state.js)
 * Mengelola state pembuatan momen secara terpusat dan reaktif:
 * klip video, durasi, filter, kecepatan, timer, musik, mode kamera.
 */
(function(window) {
    'use strict';

    class MomentCreatorState {
        constructor() {
            this.state = {
                currentStep: 'camera', // 'camera' | 'gallery' | 'editor' | 'share'
                mode: 'video',         // 'video' | 'photo'
                durationLimit: 15,     // 15 | 60 | 180 (detik)
                activeFilter: 'normal',// 'normal' | 'warm' | 'cool' | 'film' | 'mono' | 'vivid' | 'cyberpunk'
                activeSpeed: 1.0,      // 0.3 | 0.5 | 1.0 | 2.0 | 3.0
                timerSeconds: 0,       // 0 | 3 | 10 (detik countdown)
                isGridActive: false,
                isBeautyActive: false,
                torchActive: false,
                facingMode: 'environment', // 'environment' (belakang) | 'user' (depan)
                isMirrored: false,         // Mirror horizontal untuk selfie/preview/capture
                aspectRatio: 'portrait',   // 'portrait' (9:16) | 'standard' (3:4) | 'square' (1:1)
                zoom: 1.0,
                isRecording: false,
                recordingElapsed: 0,   // detik saat ini pada klip yang sedang direkam
                clips: [],             // Array of { id, blob, url, duration, speed, filter }
                capturedPhoto: null,   // { blob, dataUrl, filter }
                capturedPhotos: [],    // Array of { blob, dataUrl, filter } for multi-photo
                capturedMediaItems: [],// Unified carousel media items: [{ id, type: 'photo'|'video', blob, url, dataUrl, duration, filter }]
                selectedMusic: null,   // { id, title, artist, cover, previewUrl, startMs, duration }
                gallerySelectedMedia: null, // Dari galeri
            };

            this.listeners = new Map();
        }

        /**
         * Subscribe ke perubahan event state tertentu
         * @param {string} eventName 
         * @param {Function} callback 
         */
        on(eventName, callback) {
            if (!this.listeners.has(eventName)) {
                this.listeners.set(eventName, new Set());
            }
            this.listeners.get(eventName).add(callback);
            return () => this.off(eventName, callback);
        }

        off(eventName, callback) {
            if (this.listeners.has(eventName)) {
                this.listeners.get(eventName).delete(callback);
            }
        }

        emit(eventName, data) {
            if (this.listeners.has(eventName)) {
                this.listeners.get(eventName).forEach(cb => {
                    try {
                        cb(data, this.state);
                    } catch (err) {
                        console.error(`[CreatorState] Error in listener for ${eventName}:`, err);
                    }
                });
            }
            // Global wildcard listener
            if (this.listeners.has('*')) {
                this.listeners.get('*').forEach(cb => cb(eventName, data, this.state));
            }
        }

        get(key) {
            return this.state[key];
        }

        set(key, value) {
            const oldValue = this.state[key];
            if (oldValue !== value) {
                this.state[key] = value;
                this.emit(`change:${key}`, { value, oldValue });
                this.emit('change', { key, value, oldValue });
            }
        }

        // ==========================================
        // Helper State Mutations
        // ==========================================

        setMode(mode) {
            if (['video', 'photo'].includes(mode)) {
                this.set('mode', mode);
            }
        }

        setDurationLimit(seconds) {
            const sec = parseInt(seconds, 10);
            if ([15, 60, 180].includes(sec)) {
                this.set('durationLimit', sec);
            }
        }

        setFilter(filterId) {
            this.set('activeFilter', filterId);
        }

        setSpeed(speed) {
            const spd = parseFloat(speed);
            if ([0.3, 0.5, 1.0, 2.0, 3.0].includes(spd)) {
                this.set('activeSpeed', spd);
            }
        }

        setTimer(seconds) {
            const sec = parseInt(seconds, 10);
            if ([0, 3, 10].includes(sec)) {
                this.set('timerSeconds', sec);
            }
        }

        toggleGrid() {
            this.set('isGridActive', !this.state.isGridActive);
            return this.state.isGridActive;
        }

        toggleBeauty() {
            this.set('isBeautyActive', !this.state.isBeautyActive);
            return this.state.isBeautyActive;
        }

        toggleTorch() {
            this.set('torchActive', !this.state.torchActive);
            return this.state.torchActive;
        }

        setFacingMode(mode) {
            if (['environment', 'user'].includes(mode)) {
                this.set('facingMode', mode);
                // Sinkronisasi otomatis: kamera selfie secara default mirrored
                this.set('isMirrored', mode === 'user');
            }
        }

        toggleFacingMode() {
            const nextMode = this.state.facingMode === 'environment' ? 'user' : 'environment';
            this.setFacingMode(nextMode);
            return nextMode;
        }

        setMirrored(mirrored) {
            this.set('isMirrored', !!mirrored);
        }

        toggleMirror() {
            const next = !this.state.isMirrored;
            this.setMirrored(next);
            return next;
        }

        setAspectRatio(ratio) {
            let normalized = ratio;
            if (ratio === '9:16' || ratio === 'portrait' || ratio === 'penuh') normalized = 'portrait';
            else if (ratio === '3:4' || ratio === 'standard') normalized = 'standard';
            else if (ratio === '1:1' || ratio === 'square' || ratio === 'persegi') normalized = 'square';
            else if (ratio === '4:5' || ratio === 'feed') normalized = 'standard';
            else normalized = 'portrait';

            this.set('aspectRatio', normalized);
        }

        toggleAspectRatio() {
            // Siklus rasio kamera standar: 9:16 (Layar Penuh) -> 3:4 (Standar Foto) -> 1:1 (Persegi) -> 9:16
            const sequence = ['portrait', 'standard', 'square'];
            const cur = this.state.aspectRatio || 'portrait';
            const curIndex = sequence.indexOf(cur);
            const nextIndex = curIndex >= 0 ? (curIndex + 1) % sequence.length : 0;
            const next = sequence[nextIndex];
            this.setAspectRatio(next);
            return next;
        }

        getAspectRatioDisplay() {
            if (this.state.aspectRatio === 'square') return '1:1';
            if (this.state.aspectRatio === 'standard') return '3:4';
            return '9:16';
        }

        setZoom(zoom) {
            const clamped = Math.max(1.0, Math.min(5.0, parseFloat(zoom)));
            this.set('zoom', clamped);
        }

        setCapturedMediaItems(items) {
            this.state.capturedMediaItems = items || [];
            // Sinkronisasi backward compatibility untuk foto
            const photos = this.state.capturedMediaItems.filter(i => i.type === 'photo');
            this.setCapturedPhotos(photos);
            this.emit('media_items:updated', this.state.capturedMediaItems);
        }

        addMediaItem(item) {
            if (!this.state.capturedMediaItems) this.state.capturedMediaItems = [];
            if (this.state.capturedMediaItems.length >= 10) return false;
            this.state.capturedMediaItems.push(item);
            this.syncPhotosFromMediaItems();
            this.emit('media_items:updated', this.state.capturedMediaItems);
            return true;
        }

        removeMediaItem(idOrIndex) {
            if (!this.state.capturedMediaItems) return;
            if (typeof idOrIndex === 'number') {
                this.state.capturedMediaItems.splice(idOrIndex, 1);
            } else {
                this.state.capturedMediaItems = this.state.capturedMediaItems.filter(i => i.id !== idOrIndex);
            }
            this.syncPhotosFromMediaItems();
            this.emit('media_items:updated', this.state.capturedMediaItems);
        }

        syncPhotosFromMediaItems() {
            const photos = this.state.capturedMediaItems.filter(i => i.type === 'photo');
            this.setCapturedPhotos(photos);
        }

        getCapturedMediaItems() {
            return this.state.capturedMediaItems || [];
        }

        // Multi-clip Management
        addClip(clip) {
            // clip: { blob, duration, speed, filter }
            const newClip = {
                id: 'clip_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6),
                blob: clip.blob,
                url: URL.createObjectURL(clip.blob),
                duration: clip.duration, // detik
                speed: clip.speed || 1.0,
                filter: clip.filter || this.state.activeFilter
            };
            this.state.clips.push(newClip);
            this.emit('clip:added', newClip);
            this.emit('clips:updated', this.state.clips);
            return newClip;
        }

        removeLastClip() {
            if (this.state.clips.length === 0) return null;
            const removed = this.state.clips.pop();
            if (removed && removed.url) {
                try { URL.revokeObjectURL(removed.url); } catch (e) {}
            }
            this.emit('clip:removed', removed);
            this.emit('clips:updated', this.state.clips);
            return removed;
        }

        clearAllClips() {
            this.state.clips.forEach(clip => {
                if (clip.url) {
                    try { URL.revokeObjectURL(clip.url); } catch (e) {}
                }
            });
            this.state.clips = [];
            this.state.capturedPhoto = null;
            this.state.capturedPhotos = [];
            this.state.capturedMediaItems = [];
            this.emit('clips:cleared');
            this.emit('clips:updated', this.state.clips);
        }

        getTotalRecordedDuration() {
            return this.state.clips.reduce((acc, c) => acc + (c.duration || 0), 0);
        }

        getRemainingDuration() {
            const total = this.getTotalRecordedDuration();
            return Math.max(0, this.state.durationLimit - total);
        }

        hasRecordedContent() {
            return this.state.clips.length > 0 || 
                   this.state.capturedPhoto !== null || 
                   (this.state.capturedPhotos && this.state.capturedPhotos.length > 0) ||
                   (this.state.capturedMediaItems && this.state.capturedMediaItems.length > 0);
        }

        setCapturedPhoto(blob, dataUrl) {
            const newPhoto = {
                id: 'photo_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6),
                type: 'photo',
                blob,
                dataUrl,
                url: dataUrl,
                filter: this.state.activeFilter || 'normal',
                isMirrored: this.state.isMirrored || false,
                aspectRatio: this.state.aspectRatio || 'portrait',
                timestamp: Date.now()
            };
            this.state.capturedPhoto = newPhoto;
            if (!this.state.capturedMediaItems || this.state.capturedMediaItems.length === 0) {
                this.state.capturedMediaItems = [newPhoto];
            } else {
                this.state.capturedMediaItems.push(newPhoto);
            }
            this.state.capturedPhotos = this.state.capturedMediaItems.filter(i => i.type === 'photo');
            this.emit('photo:captured', newPhoto);
            this.emit('media_items:updated', this.state.capturedMediaItems);
        }

        setCapturedPhotos(photos) {
            this.state.capturedPhotos = photos || [];
            if (photos && photos.length > 0) {
                this.state.capturedPhoto = photos[0];
            } else {
                this.state.capturedPhoto = null;
            }
            this.emit('photos:captured', this.state.capturedPhotos);
        }

        setMusic(music) {
            this.set('selectedMusic', music);
        }

        removeMusic() {
            this.set('selectedMusic', null);
        }

        reset() {
            this.clearAllClips();
            this.state.isRecording = false;
            this.state.recordingElapsed = 0;
            this.state.currentStep = 'camera';
            this.state.selectedMusic = null;
            this.emit('reset');
        }
    }

    window.MomentCreatorState = new MomentCreatorState();
})(window);
