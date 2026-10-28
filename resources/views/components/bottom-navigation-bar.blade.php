@props(['active' => 'beranda'])

@php
    $activeTab = strtolower($active ?? 'beranda');
    $isBeranda = ($activeTab === 'beranda');
    $isMomen = ($activeTab === 'momen');
    $isAkun = ($activeTab === 'akun');

    $berandaClass = $isBeranda 
        ? 'text-sky-600 font-black active scale-105' 
        : ($isMomen ? 'text-white/60 hover:text-white transition-colors font-bold' : 'text-slate-400 hover:text-sky-600 transition-colors font-bold');

    $momenClass = $isMomen 
        ? 'text-sky-400 font-black active scale-105' 
        : ($isMomen ? 'text-white/60 hover:text-white transition-colors font-bold' : 'text-slate-400 hover:text-sky-600 transition-colors font-bold');

    $akunClass = $isAkun 
        ? 'text-sky-600 font-black active scale-105' 
        : ($isMomen ? 'text-white/60 hover:text-white transition-colors font-bold' : 'text-slate-400 hover:text-sky-600 transition-colors font-bold');

    // Rute Akun menuju route('customer.account') yang menangani guest & auth (Requirement 1.6 & 6.18)
    $accountUrl = \Illuminate\Support\Facades\Route::has('customer.account') 
        ? route('customer.account') 
        : (\Illuminate\Support\Facades\Route::has('customer.dashboard') ? route('customer.dashboard') : '/account');

    // Desain Bar: Kaca gelap elegan pada Momen, kaca terang frosted pada Beranda/Akun
    $navContainerClass = $isMomen
        ? 'bg-slate-950/85 backdrop-blur-2xl border border-white/20 text-white shadow-2xl px-6 py-2 transition-all duration-300 relative overflow-hidden'
        : 'rentify-card bg-white/90 backdrop-blur-xl border border-white/80 px-7 py-2 shadow-xl shadow-sky-900/10 transition-all duration-300 relative overflow-hidden';
@endphp

<!-- CSS: EFEK PERPINDAHAN SUPER MULUS & GPU ACCELERATION -->
<style>
    /* View Transitions Level 2: Nav Bar tetap terkunci & tidak berkedip */
    #bottom-navigation-wrapper,
    #bottom-navigation-bar {
        view-transition-name: rentify-bottom-nav;
        will-change: transform;
        transform: translateZ(0);
    }

    /* Aliran Gelombang Air Menyeberang Bar (GPU Accelerated) */
    @keyframes waterWaveFlowGpu {
        0% {
            transform: translate3d(-140%, 0, 0) skewX(-15deg);
            opacity: 0.9;
        }
        50% {
            opacity: 1;
        }
        100% {
            transform: translate3d(140%, 0, 0) skewX(-15deg);
            opacity: 0;
        }
    }

    /* Lapisan Gelombang Air (Water-Flow Sheen) */
    .water-flow-sheen {
        position: absolute;
        inset: -50% -30%;
        background: linear-gradient(
            105deg,
            transparent 0%,
            rgba(56, 189, 248, 0.25) 25%,
            rgba(255, 255, 255, 0.9) 50%,
            rgba(14, 165, 233, 0.55) 60%,
            transparent 100%
        );
        pointer-events: none;
        z-index: 1;
        opacity: 0;
        will-change: transform, opacity;
    }

    .water-surge-active .water-flow-sheen {
        opacity: 1;
        animation: waterWaveFlowGpu 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Indikator Melayang Gestur Swap Kiri-Kanan */
    .rentify-swipe-pill {
        position: fixed;
        top: 50%;
        transform: translateY(-50%) translateZ(0);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        background: rgba(15, 23, 42, 0.92);
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.025em;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        pointer-events: none;
        transition: opacity 0.2s ease-out, transform 0.2s ease-out;
        animation: rentifyPillPop 0.2s ease-out;
        will-change: transform, opacity;
    }

    @keyframes rentifyPillPop {
        0% { transform: translateY(-50%) scale(0.85); opacity: 0; }
        100% { transform: translateY(-50%) scale(1); opacity: 1; }
    }
</style>

<!-- TOP NATIVE-LIKE LOADING PROGRESS BAR -->
<div id="rentify-tab-progress" class="fixed top-0 left-0 w-0 h-[2.5px] bg-gradient-to-r from-sky-400 via-sky-500 to-indigo-500 z-[99999] pointer-events-none opacity-0 shadow-[0_0_10px_rgba(56,189,248,0.85)]"></div>

<!-- BOTTOM NAV (3 TAB RAMPING: BERANDA, MOMEN, AKUN) -->
<div id="bottom-navigation-wrapper" class="md:hidden fixed bottom-2 left-0 w-full z-40 flex justify-center pointer-events-none px-4 transition-all duration-300" style="bottom: max(0.5rem, env(safe-area-inset-bottom, 0.5rem));">
    <nav id="bottom-navigation-bar" class="{{ $navContainerClass }} pointer-events-auto rounded-full flex justify-between items-center gap-9" style="-webkit-backdrop-filter: blur(16px);">
        <!-- Lapisan Efek Aliran Air -->
        <div class="water-flow-sheen pointer-events-none"></div>

        <!-- Tab 1: Beranda -->
        <a href="{{ route('customer.home') }}" data-tab="beranda" class="relative z-10 flex flex-col items-center px-2 py-0.5 transition-transform duration-200 {{ $berandaClass }}">
            <i class="fa-solid fa-house text-[16px] mb-0.5"></i>
            <span class="text-[9px] tracking-tight">Beranda</span>
        </a>

        <!-- Tab 2: Momen -->
        <a href="{{ route('customer.moments') }}" data-tab="momen" class="relative z-10 flex flex-col items-center px-2 py-0.5 transition-transform duration-200 {{ $momenClass }}">
            <i class="fa-solid fa-camera text-[16px] mb-0.5"></i>
            <span class="text-[9px] tracking-tight">Momen</span>
        </a>

        <!-- Tab 3: Akun -->
        <a href="{{ $accountUrl }}" data-tab="akun" class="relative z-10 flex flex-col items-center px-2 py-0.5 transition-transform duration-200 {{ $akunClass }}">
            <i class="fa-solid fa-user text-[16px] mb-0.5"></i>
            <span class="text-[9px] tracking-tight">Akun</span>
        </a>
    </nav>
</div>

<!-- SPECULATION RULES API: Prerender & Prefetch Instan di Browser Chromium / Android -->
<script type="speculationrules">
{
  "prerender": [
    {
      "source": "list",
      "urls": [
        "{{ route('customer.home') }}",
        "{{ route('customer.moments') }}",
        "{{ $accountUrl }}"
      ],
      "eagerness": "moderate"
    }
  ],
  "prefetch": [
    {
      "source": "list",
      "urls": [
        "{{ route('customer.home') }}",
        "{{ route('customer.moments') }}",
        "{{ $accountUrl }}"
      ],
      "eagerness": "conservative"
    }
  ]
}
</script>

<!-- CLIENT-SIDE ULTRA-SMOOTH NAVIGATION & SCROLL RETENTION -->
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

        let isNavigating = false;

        // Smart Prefetch Cache (Fallback untuk Safari / browser tanpa Speculation Rules)
        window.__rentifyPrefetchedUrls = window.__rentifyPrefetchedUrls || {};
        function prefetchDocument(url) {
            if (!url || window.__rentifyPrefetchedUrls[url]) return;
            try {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url;
                link.as = 'document';
                document.head.appendChild(link);
                window.__rentifyPrefetchedUrls[url] = true;
            } catch (e) {}
        }

        // Jalankan prefetch di background saat browser idle
        function scheduleIdlePrefetch() {
            const targetUrls = Object.values(routes).filter(u => u && !window.location.href.includes(u));
            if ('requestIdleCallback' in window) {
                requestIdleCallback(() => targetUrls.forEach(prefetchDocument), { timeout: 1500 });
            } else {
                setTimeout(() => targetUrls.forEach(prefetchDocument), 800);
            }
        }

        function triggerProgressBar() {
            const bar = document.getElementById('rentify-tab-progress');
            if (!bar) return;
            bar.style.transition = 'width 0.28s cubic-bezier(0.1, 0.8, 0.2, 1), opacity 0.15s ease';
            bar.style.opacity = '1';
            bar.style.width = '80%';
        }

        function completeProgressBar() {
            const bar = document.getElementById('rentify-tab-progress');
            if (!bar) return;
            bar.style.transition = 'width 0.12s ease-out, opacity 0.2s ease-out 0.05s';
            bar.style.width = '100%';
            setTimeout(() => {
                bar.style.opacity = '0';
                setTimeout(() => { bar.style.width = '0%'; }, 200);
            }, 120);
        }

        function navigateSmoothly(targetTab, targetUrl, direction) {
            if (isNavigating || !targetUrl) return;
            isNavigating = true;

            // Simpan scroll tab asal
            if (currentTab && window.RentifyNavigationStore) {
                window.RentifyNavigationStore.saveScrollPosition(currentTab);
            }

            // Haptic feedback instan pada smartphone
            if ('vibrate' in navigator) {
                try { navigator.vibrate(12); } catch (e) {}
            }

            // Efek air pada bottom bar
            const navBar = document.getElementById('bottom-navigation-bar');
            if (targetTab === 'momen' && navBar) {
                navBar.classList.add('water-surge-active');
            }

            // Indikator arah geser jika berasal dari swipe gesture
            if (direction) {
                if (targetTab === 'beranda') {
                    showSwipeFeedback('Beranda', 'fa-solid fa-house text-sky-400', 'left');
                } else if (targetTab === 'momen') {
                    showSwipeFeedback('Momen', 'fa-solid fa-camera text-sky-400', direction === 'left' ? 'right' : 'left');
                } else if (targetTab === 'akun') {
                    showSwipeFeedback('Akun', 'fa-solid fa-user text-sky-400', 'right');
                }
            }

            // Jalankan top progress bar
            triggerProgressBar();

            // Animasi konten keluar dengan hardware acceleration 60fps
            const mainEl = document.querySelector('main') || document.getElementById('moments-app') || document.body;
            if (mainEl) {
                mainEl.style.willChange = 'opacity, transform';
                mainEl.style.transition = 'transform 0.14s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.14s ease-out';
                if (direction === 'left') {
                    mainEl.style.transform = 'translate3d(-20px, 0, 0)';
                } else if (direction === 'right') {
                    mainEl.style.transform = 'translate3d(20px, 0, 0)';
                } else {
                    mainEl.style.transform = 'translate3d(0, -6px, 0)';
                }
                mainEl.style.opacity = '0.72';
            }

            // Eksekusi navigasi secepat mungkin pada frame berikutnya
            requestAnimationFrame(() => {
                window.location.href = targetUrl;
            });
        }

        function showSwipeFeedback(label, iconClass, side) {
            const pill = document.createElement('div');
            pill.className = 'rentify-swipe-pill';
            pill.style[side === 'left' ? 'left' : 'right'] = '1.25rem';
            pill.innerHTML = `<i class="${iconClass}"></i><span>${label}</span>`;
            document.body.appendChild(pill);

            setTimeout(() => {
                pill.style.opacity = '0';
                pill.style.transform = 'translateY(-50%) scale(0.9) translateZ(0)';
                setTimeout(() => pill.remove(), 200);
            }, 380);
        }

        function initNavigation() {
            completeProgressBar();

            // Pulihkan posisi scroll
            if (currentTab && window.RentifyNavigationStore) {
                requestAnimationFrame(function() {
                    window.RentifyNavigationStore.restoreScrollPosition(currentTab);
                });
            }

            const navBar = document.getElementById('bottom-navigation-bar');
            if (!navBar) return;

            // Sambungkan event listener klik tab
            navBar.querySelectorAll('a[data-tab]').forEach(function(anchor) {
                const tab = anchor.getAttribute('data-tab');
                const url = routes[tab];

                // Prefetch saat kursor menyentuh / pointer mendarat
                anchor.addEventListener('pointerenter', () => prefetchDocument(url), { passive: true });
                anchor.addEventListener('touchstart', () => prefetchDocument(url), { passive: true });

                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (tab === currentTab) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                    navigateSmoothly(tab, url, null);
                });
            });

            // Jadwalkan prefetch idle
            scheduleIdlePrefetch();

            // Inisialisasi gestur Swap Kiri-Kanan
            setupHorizontalSwipeGesture();
        }

        function setupHorizontalSwipeGesture() {
            let startX = 0;
            let startY = 0;
            let startTime = 0;
            let isSwiping = false;

            function shouldIgnoreTouch(e) {
                const target = e.target;
                if (!target) return false;

                // Abaikan jika menyentuh input form, modal, atau slider horizontal
                if (target.closest('input, textarea, select, button, a:not(#bottom-navigation-bar a)')) return true;
                if (document.querySelector('#comments-drawer:not(.hidden), #upload-modal:not(.hidden), #report-modal:not(.hidden), #guest-auth-modal:not(.hidden), #drawer-settings:not(.hidden)')) return true;
                if (target.closest('#banner-slider, .productSwiper, .swiper, .overflow-x-auto, .overflow-x-scroll')) return true;

                return false;
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

                // Gestur horizontal: jarak minimal 50px, dominan horizontal, dan durasi < 750ms
                if (Math.abs(diffX) >= 50 && Math.abs(diffX) > Math.abs(diffY) * 1.3 && elapsed <= 750) {
                    if (diffX < 0) {
                        // SWIPE LEFT (Geser ke kiri -> maju ke tab kanan)
                        if (currentTab === 'beranda') {
                            navigateSmoothly('momen', routes.momen, 'left');
                        } else if (currentTab === 'momen') {
                            navigateSmoothly('akun', routes.akun, 'left');
                        }
                    } else {
                        // SWIPE RIGHT (Geser ke kanan -> mundur ke tab kiri)
                        if (currentTab === 'akun') {
                            navigateSmoothly('momen', routes.momen, 'right');
                        } else if (currentTab === 'momen') {
                            navigateSmoothly('beranda', routes.beranda, 'right');
                        }
                    }
                }
            }, { passive: true });

            window.addEventListener('touchcancel', () => { isSwiping = false; }, { passive: true });
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
