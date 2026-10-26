/**
 * Rentify Momen Music Provider (music-provider.js)
 * Katalog musik latar (BGM) & audio player berbasis Web Audio API
 * Menghasilkan ambient & lively music loops secara instan tanpa isu CORS atau file eksternal rusak.
 */
(function(window) {
    'use strict';

    const MUSIC_CATALOG = [
        {
            id: 'track_viral_pop',
            title: 'Electric Euphoria',
            artist: 'Rentify Vibes',
            genre: 'Pop / Viral',
            duration: 30,
            bpm: 124,
            style: 'pop',
            cover: 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=150&auto=format&fit=crop&q=80',
            tags: ['Viral', 'Upbeat', 'Trend']
        },
        {
            id: 'track_lofi_chill',
            title: 'Midnight Coffee & Gear',
            artist: 'Lofi Cam',
            genre: 'Lo-Fi Chill',
            duration: 30,
            bpm: 82,
            style: 'lofi',
            cover: 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=150&auto=format&fit=crop&q=80',
            tags: ['Santai', 'Aesthetic', 'Cozy']
        },
        {
            id: 'track_cinematic_travel',
            title: 'Horizon Drone Adventure',
            artist: 'Aerial Studio',
            genre: 'Cinematic',
            duration: 30,
            bpm: 100,
            style: 'cinematic',
            cover: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=150&auto=format&fit=crop&q=80',
            tags: ['Travel', 'Drone', 'Outdoor']
        },
        {
            id: 'track_acoustic_sun',
            title: 'Golden Hour Smile',
            artist: 'Sunday Acoustic',
            genre: 'Acoustic Folk',
            duration: 30,
            bpm: 96,
            style: 'acoustic',
            cover: 'https://images.unsplash.com/photo-1465847899084-d164df4dedc6?w=150&auto=format&fit=crop&q=80',
            tags: ['Hangout', 'Vlog', 'Happy']
        },
        {
            id: 'track_cyber_tech',
            title: 'Future Neon Gear',
            artist: 'CyberSound 2026',
            genre: 'Electro / Bass',
            duration: 30,
            bpm: 130,
            style: 'synth',
            cover: 'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?w=150&auto=format&fit=crop&q=80',
            tags: ['Action', 'Gadget', 'Gaming']
        }
    ];

    class MomentMusicProvider {
        constructor() {
            this.audioCtx = null;
            this.currentTrack = null;
            this.isPlaying = false;
            this.timerNode = null;
            this.gainNode = null;
            this.volume = 0.8;
            this.step = 0;
            this.activeNodes = [];
        }

        initAudio() {
            if (!this.audioCtx) {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                this.audioCtx = new AudioContext();
                this.gainNode = this.audioCtx.createGain();
                this.gainNode.gain.setValueAtTime(this.volume, this.audioCtx.currentTime);
                this.gainNode.connect(this.audioCtx.destination);
            }
            if (this.audioCtx.state === 'suspended') {
                this.audioCtx.resume();
            }
        }

        getCatalog() {
            return MUSIC_CATALOG;
        }

        getTrackById(id) {
            return MUSIC_CATALOG.find(t => t.id === id) || null;
        }

        setVolume(val) {
            this.volume = Math.max(0, Math.min(1.0, parseFloat(val) || 0));
            if (this.gainNode && this.audioCtx) {
                this.gainNode.gain.setValueAtTime(this.volume, this.audioCtx.currentTime);
            }
        }

        playTrack(track) {
            this.initAudio();
            this.stopTrack();

            this.currentTrack = track;
            this.isPlaying = true;
            this.step = 0;

            const intervalMs = (60 / track.bpm) * 1000 / 2; // 8th notes
            this.timerNode = setInterval(() => {
                this.playSynthNote(track.style, this.step);
                this.step = (this.step + 1) % 32;
            }, intervalMs);

            return track;
        }

        playSynthNote(style, step) {
            if (!this.audioCtx || !this.isPlaying) return;

            const now = this.audioCtx.currentTime;
            const osc = this.audioCtx.createOscillator();
            const noteGain = this.audioCtx.createGain();

            // Skala pentatonik dan progresi akord
            let freq = 440;
            let type = 'sine';
            let decay = 0.25;

            if (style === 'pop') {
                const melody = [261.63, 293.66, 329.63, 392.00, 440.00, 523.25, 440.00, 392.00];
                freq = melody[step % melody.length];
                type = 'triangle';
                decay = 0.18;
                // Kick pada ketukan 0, 4, 8, 12...
                if (step % 4 === 0) {
                    this.playKick(now);
                }
            } else if (style === 'lofi') {
                const lofiNotes = [196.00, 246.94, 293.66, 369.99, 440.00];
                freq = lofiNotes[step % lofiNotes.length];
                type = 'sine';
                decay = 0.45;
                if (step % 8 === 0) this.playKick(now, 60);
                if (step % 8 === 4) this.playSnare(now);
            } else if (style === 'cinematic') {
                const droneNotes = [130.81, 164.81, 196.00, 220.00, 261.63];
                freq = droneNotes[Math.floor(step / 4) % droneNotes.length];
                type = 'sawtooth';
                decay = 0.6;
            } else if (style === 'acoustic') {
                const acousticNotes = [220.00, 261.63, 329.63, 392.00, 440.00];
                freq = acousticNotes[step % acousticNotes.length];
                type = 'sine';
                decay = 0.3;
            } else { // synth
                const synthNotes = [110.00, 146.83, 164.81, 220.00, 293.66];
                freq = synthNotes[step % synthNotes.length];
                type = 'sawtooth';
                decay = 0.2;
                if (step % 2 === 0) this.playKick(now, 110);
            }

            osc.type = type;
            osc.frequency.setValueAtTime(freq, now);

            noteGain.gain.setValueAtTime(0.35, now);
            noteGain.gain.exponentialRampToValueAtTime(0.001, now + decay);

            osc.connect(noteGain);
            noteGain.connect(this.gainNode);

            osc.start(now);
            osc.stop(now + decay);
        }

        playKick(time, startFreq = 120) {
            try {
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();
                osc.frequency.setValueAtTime(startFreq, time);
                osc.frequency.exponentialRampToValueAtTime(0.01, time + 0.25);
                gain.gain.setValueAtTime(0.7, time);
                gain.gain.exponentialRampToValueAtTime(0.001, time + 0.25);
                osc.connect(gain);
                gain.connect(this.gainNode);
                osc.start(time);
                osc.stop(time + 0.25);
            } catch(e) {}
        }

        playSnare(time) {
            try {
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(220, time);
                gain.gain.setValueAtTime(0.3, time);
                gain.gain.exponentialRampToValueAtTime(0.001, time + 0.15);
                osc.connect(gain);
                gain.connect(this.gainNode);
                osc.start(time);
                osc.stop(time + 0.15);
            } catch(e) {}
        }

        stopTrack() {
            if (this.timerNode) {
                clearInterval(this.timerNode);
                this.timerNode = null;
            }
            this.isPlaying = false;
        }

        toggleTrack(track) {
            if (this.currentTrack && this.currentTrack.id === track.id && this.isPlaying) {
                this.stopTrack();
                return false;
            } else {
                this.playTrack(track);
                return true;
            }
        }
    }

    window.MomentMusicProvider = new MomentMusicProvider();
})(window);
