@props(['active' => 'beranda'])

@php
    $activeTab = strtolower($active ?? 'beranda');
    $isBeranda = ($activeTab === 'beranda');
    $isMomen = ($activeTab === 'momen');
    $isAkun = ($activeTab === 'akun');

    $berandaClass = $isBeranda 
        ? 'text-sky-600 font-black active' 
        : ($isMomen ? 'text-white/60 hover:text-white transition-colors font-bold' : 'text-slate-400 hover:text-sky-600 transition-colors font-bold');

    $momenClass = $isMomen 
        ? 'text-sky-600 font-black active' 
        : 'text-slate-400 hover:text-sky-600 transition-colors font-bold';

    $akunClass = $isAkun 
        ? 'text-sky-600 font-black active' 
        : ($isMomen ? 'text-white/60 hover:text-white transition-colors font-bold' : 'text-slate-400 hover:text-sky-600 transition-colors font-bold');

    // Rute Akun menuju route('customer.account') yang menangani guest & auth (Requirement 1.6 & 6.18)
    $accountUrl = \Illuminate\Support\Facades\Route::has('customer.account') 
        ? route('customer.account') 
        : (\Illuminate\Support\Facades\Route::has('customer.dashboard') ? route('customer.dashboard') : '/account');

    // Pada Momen, bar memiliki transisi aliran air dari normal ke transparan
    $navContainerClass = $isMomen
        ? 'water-flow-entry bg-black/40 backdrop-blur-2xl border border-white/20 text-white shadow-2xl px-6 py-1.5 transition-all duration-300 transform relative overflow-hidden'
        : 'rentify-card backdrop-blur-xl border border-white/60 px-7 py-2 shadow-xl shadow-sky-900/10 transition-all duration-300 relative overflow-hidden';
@endphp

<!-- CSS: EFEK PERPINDAHAN SEPERTI AIR MENGALIR (WATER-FLOW TRANSITION) & GESTURE SWIPE -->
<style>
    /* Aliran Gelombang Air Menyeberang Bar */
    @keyframes waterWaveFlow {
        0% {
            transform: translateX(-130%) skewX(-15deg);
            opacity: 0.95;
        }
        30% {
            opacity: 1;
        }
        70% {
            opacity: 0.85;
        }
        100% {
            transform: translateX(140%) skewX(-15deg);
            opacity: 0;
        }
    }

    /* Morfologi Bar: Dari Normal (Kartu Terang Frosted) ke Transparan (Kaca Gelap Jernih) */
    @keyframes morphNormalToTransparent {
        0% {
            background-color: rgba(255, 255, 255, 0.95);
            border-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 25px -5px rgba(3, 105, 161, 0.16);
            color: #475569;
        }
        30% {
            background-color: rgba(224, 242, 254, 0.82);
            border-color: rgba(56, 189, 248, 0.65);
            box-shadow: 0 12px 30px -4px rgba(14, 165, 233, 0.35), 0 0 20px rgba(56, 189, 248, 0.3);
            color: #0369a1;
        }
        65% {
            background-color: rgba(15, 23, 42, 0.6);
            border-color: rgba(255, 255, 255, 0.32);
            color: #f8fafc;
        }
        100% {
            background-color: rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            color: #ffffff;
        }
    }

    /* Efek Apung Gelombang Air Pada Tiap Tab (Beranda, Momen, Akun) */
    @keyframes waterTabBobBeranda {
        0%, 100% { transform: translateY(0) scale(1); }
        35% { transform: translateY(-2.5px) scale(1.05); }
    }
    @keyframes waterTabBobMomen {
        0%, 100% { transform: translateY(0) scale(1); }
        45% { transform: translateY(-4px) scale(1.12); filter: drop-shadow(0 0 8px rgba(56, 189, 248, 0.85)); }
    }
    @keyframes waterTabBobAkun {
        0%, 100% { transform: translateY(0) scale(1); }
        55% { transform: translateY(-2.5px) scale(1.05); }
    }

    /* Lapisan Gelombang Air (Water-Flow Sheen) */
    .water-flow-sheen {
        position: absolute;
        inset: -50% -30%;
        background: linear-gradient(
            105deg,
            transparent 0%,
            rgba(56, 189, 248, 0.2) 20%,
            rgba(255, 255, 255, 0.85) 45%,
            rgba(14, 165, 233, 0.5) 55%,
            rgba(56, 189, 248, 0.25) 75%,
            transparent 100%
        );
        pointer-events: none;
        z-index: 1;
        opacity: 0;
    }

    .water-flow-entry {
        animation: morphNormalToTransparent 0.85s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    .water-flow-entry .water-flow-sheen,
    .water-surge-active .water-flow-sheen {
        opacity: 1;
        animation: waterWaveFlow 0.85s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    .water-flow-entry a[data-tab="beranda"] {
        animation: waterTabBobBeranda 0.75s ease-out 0.05s;
    }
    .water-flow-entry a[data-tab="momen"] {
        animation: waterTabBobMomen 0.85s ease-out 0.18s;
    }
    .water-flow-entry a[data-tab="akun"] {
        animation: waterTabBobAkun 0.75s ease-out 0.32s;
    }

    /* Indikator Melayang Gestur Swap Kiri-Kanan */
    .rentify-swipe-pill {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        background: rgba(15, 23, 42, 0.9);
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.025em;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.22);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        pointer-events: none;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        animation: rentifyPillPop 0.25s ease-out;
    }

    @keyframes rentifyPillPop {
        0% { transform: translateY(-50%) scale(0.85); opacity: 0; }
        100% { transform: translateY(-50%) scale(1); opacity: 1; }
    }
</style>

<!-- BOTTOM NAV (3 TAB RAMPING: BERANDA, MOMEN, AKUN) -->
<div id="bottom-navigation-wrapper" class="md:hidden fixed bottom-2 left-0 w-full z-40 flex justify-center pointer-events-none px-4 transition-all duration-300" style="bottom: max(0.5rem, env(safe-area-inset-bottom, 0.5rem));">
    <nav id="bottom-navigation-bar" class="{{ $navContainerClass }} pointer-events-auto rounded-full flex justify-between items-center gap-9" style="-webkit-backdrop-filter: blur(16px);">
        <!-- Lapisan Efek Aliran Air -->
        <div class="water-flow-sheen pointer-events-none"></div>

        <!-- Tab 1: Beranda -->
        <a href="{{ route('customer.home') }}" data-tab="beranda" class="relative z-10 flex flex-col items-center px-2 py-0.5 {{ $berandaClass }}">
            <i class="fa-solid fa-house text-[16px] mb-0.5"></i>
            <span class="text-[9px] tracking-tight">Beranda</span>
        </a>

        <!-- Tab 2: Momen -->
        <a href="{{ route('customer.moments') }}" data-tab="momen" class="relative z-10 flex flex-col items-center px-2 py-0.5 {{ $momenClass }}">
            <i class="fa-solid fa-camera text-[16px] mb-0.5"></i>
            <span class="text-[9px] tracking-tight">Momen</span>
        </a>

        <!-- Tab 3: Akun -->
        <a href="{{ $accountUrl }}" data-tab="akun" class="relative z-10 flex flex-col items-center px-2 py-0.5 {{ $akunClass }}">
            <i class="fa-solid fa-user text-[16px] mb-0.5"></i>
            <span class="text-[9px] tracking-tight">Akun</span>
        </a>
    </nav>
</div>

<!-- CLIENT-SIDE SCROLL RETENTION & GESTURE SWIPE NAVIGATION MODULE -->
<script>
    if (typeof window.RentifyNavigationStore === 'undefined') {
        window.RentifyNavigationStore = (function() {
            const STORAGE_KEY = 'rentify_tab_scroll_positions';

            function getStore() {
                try {
                    const data = sessionStorage.getItem(STORAGE_KEY);
                    return data ? JSON.parse(data) : {};
                } catch (e) {
                    return {};
                }
            }

            function setStore(data) {
                try {
                    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                } catch (e) {}
            }

            function saveScrollPosition(tabName) {
                if (!tabName) return;
                const store = getStore();
                store[tabName] = {
                    scrollX: window.scrollX || window.pageXOffset || 0,
                    scrollY: window.scrollY || window.pageYOffset || 0,
                    timestamp: Date.now()
                };
                setStore(store);
            }

            function restoreScrollPosition(tabName) {
                if (!tabName) return;
                const store = getStore();
                const pos = store[tabName];
                if (pos && typeof pos.scrollY === 'number') {
                    window.scrollTo({
                        left: pos.scrollX || 0,
                        top: pos.scrollY || 0,
                        behavior: 'instant'
                    });
                }
            }

            return {
                saveScrollPosition: saveScrollPosition,
                restoreScrollPosition: restoreScrollPosition,
                getStore: getStore
            };
        })();
    }

    (function() {
        const currentTab = "{{ $activeTab }}";
        const routes = {
            beranda: "{{ route('customer.home') }}",
            momen: "{{ route('customer.moments') }}",
            akun: "{{ $accountUrl }}"
        };

        function initNavigation() {
            // 1. Pulihkan posisi scroll
            if (currentTab && window.RentifyNavigationStore) {
                requestAnimationFrame(function() {
                    window.RentifyNavigationStore.restoreScrollPosition(currentTab);
                });
            }

            const navBar = document.getElementById('bottom-navigation-bar');
            if (!navBar) return;

            // 2. Bersihkan kelas animasi air mengalir setelah selesai berputar
            navBar.addEventListener('animationend', function cleanupAnimation(e) {
                if (e.animationName === 'morphNormalToTransparent') {
                    navBar.classList.remove('water-flow-entry');
                }
            });

            // 3. Tangani klik tab dan efek air mengalir saat menuju tab Momen
            navBar.querySelectorAll('a[data-tab]').forEach(function(anchor) {
                anchor.addEventListener('click', function(e) {
                    const targetTab = anchor.getAttribute('data-tab');

                    if (currentTab && window.RentifyNavigationStore) {
                        window.RentifyNavigationStore.saveScrollPosition(currentTab);
                    }

                    if (targetTab === currentTab) {
                        e.preventDefault();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    // Jika menuju tab Momen, langsung jalankan gelombang air mengalir
                    if (targetTab === 'momen') {
                        navBar.classList.add('water-surge-active');
                    }
                });
            });

            // 4. Inisialisasi gestur Swap Kiri-Kanan (Swipe Navigation Beranda <-> Momen <-> Akun)
            setupHorizontalSwipeGesture();
        }

        function setupHorizontalSwipeGesture() {
            let startX = 0;
            let startY = 0;
            let startTime = 0;
            let isSwiping = false;
            let isNavigating = false;

            function shouldIgnoreTouch(e) {
                const target = e.target;
                if (!target) return false;

                // Abaikan jika menyentuh input form, tombol interaktif, link di luar bottom bar
                if (target.closest('input, textarea, select, button, a:not(#bottom-navigation-bar a)')) {
                    return true;
                }

                // Abaikan jika modal atau bottom-sheet drawer sedang terbuka
                const openOverlay = document.querySelector(
                    '#comments-drawer:not(.hidden), #upload-modal:not(.hidden), #report-modal:not(.hidden), #guest-auth-modal:not(.hidden), #drawer-settings:not(.hidden)'
                );
                if (openOverlay) return true;

                // Abaikan jika menyentuh area carousel/slider horizontal yang bisa di-scroll
                const horizontalScrollArea = target.closest(
                    '#banner-slider, .productSwiper, .swiper, .overflow-x-auto, .overflow-x-scroll'
                );
                if (horizontalScrollArea) return true;

                return false;
            }

            function showSwipeFeedback(label, iconClass, side) {
                const pill = document.createElement('div');
                pill.className = 'rentify-swipe-pill';
                pill.style[side === 'left' ? 'left' : 'right'] = '1.25rem';
                pill.innerHTML = `<i class="${iconClass}"></i><span>${label}</span>`;
                document.body.appendChild(pill);

                setTimeout(() => {
                    pill.style.opacity = '0';
                    pill.style.transform = 'translateY(-50%) scale(0.9)';
                    setTimeout(() => pill.remove(), 250);
                }, 450);
            }

            function executeSwipeNavigation(targetTab, targetUrl, direction) {
                if (isNavigating) return;
                isNavigating = true;

                if (window.RentifyNavigationStore) {
                    window.RentifyNavigationStore.saveScrollPosition(currentTab);
                }

                // Tampilkan indikator visual arah geser
                if (targetTab === 'beranda') {
                    showSwipeFeedback('Beranda', 'fa-solid fa-house text-sky-400', 'left');
                } else if (targetTab === 'momen') {
                    showSwipeFeedback('Momen', 'fa-solid fa-water text-sky-400', direction === 'left' ? 'right' : 'left');
                    const navBar = document.getElementById('bottom-navigation-bar');
                    if (navBar) navBar.classList.add('water-surge-active');
                } else if (targetTab === 'akun') {
                    showSwipeFeedback('Akun', 'fa-solid fa-user text-sky-400', 'right');
                }

                // Animasi geser halus pada konten halaman (swap effect)
                const mainEl = document.querySelector('main') || document.getElementById('moments-app') || document.body;
                if (mainEl) {
                    mainEl.style.transition = 'transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.22s ease-out';
                    mainEl.style.transform = direction === 'left' ? 'translateX(-32px)' : 'translateX(32px)';
                    mainEl.style.opacity = '0.75';
                }

                setTimeout(() => {
                    window.location.href = targetUrl;
                }, 180);
            }

            window.addEventListener('touchstart', function(e) {
                if (e.touches.length !== 1 || shouldIgnoreTouch(e)) {
                    isSwiping = false;
                    return;
                }
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                startTime = Date.now();
                isSwiping = true;
            }, { passive: true });

            window.addEventListener('touchend', function(e) {
                if (!isSwiping || isNavigating) return;
                isSwiping = false;

                const touch = e.changedTouches[0];
                if (!touch) return;

                const diffX = touch.clientX - startX;
                const diffY = touch.clientY - startY;
                const elapsed = Date.now() - startTime;

                // Syarat gestur swap horizontal: jarak minimal 55px, lebih dominan horizontal dibanding vertikal, dan < 750ms
                if (Math.abs(diffX) >= 55 && Math.abs(diffX) > Math.abs(diffY) * 1.35 && elapsed <= 750) {
                    if (diffX < 0) {
                        // SWIPE LEFT (Geser ke kiri -> maju ke tab kanan)
                        if (currentTab === 'beranda') {
                            executeSwipeNavigation('momen', routes.momen, 'left');
                        } else if (currentTab === 'momen') {
                            executeSwipeNavigation('akun', routes.akun, 'left');
                        }
                    } else {
                        // SWIPE RIGHT (Geser ke kanan -> mundur ke tab kiri)
                        if (currentTab === 'akun') {
                            executeSwipeNavigation('momen', routes.momen, 'right');
                        } else if (currentTab === 'momen') {
                            executeSwipeNavigation('beranda', routes.beranda, 'right');
                        }
                    }
                }
            }, { passive: true });

            window.addEventListener('touchcancel', function() {
                isSwiping = false;
            }, { passive: true });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initNavigation);
        } else {
            initNavigation();
        }

        window.addEventListener('beforeunload', function() {
            if (currentTab && window.RentifyNavigationStore) {
                window.RentifyNavigationStore.saveScrollPosition(currentTab);
            }
        });
    })();
</script>