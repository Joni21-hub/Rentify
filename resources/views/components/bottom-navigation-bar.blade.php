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

    // Rute Akun menuju route('customer.account') yang menangani guest & auth
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

    /* GPU-Accelerated Smooth Tab Transition Classes */
    .rentify-tab-page {
        width: 100%;
        min-height: 100vh;
        will-change: transform, opacity;
    }

    .rentify-slide-out-left {
        animation: rentifySlideOutLeft 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
    }
    .rentify-slide-in-right {
        animation: rentifySlideInRight 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
    }
    .rentify-slide-out-right {
        animation: rentifySlideOutRight 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
    }
    .rentify-slide-in-left {
        animation: rentifySlideInLeft 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
    }

    @keyframes rentifySlideOutLeft {
        0% { transform: translate3d(0, 0, 0); opacity: 1; }
        100% { transform: translate3d(-32px, 0, 0); opacity: 0; }
    }
    @keyframes rentifySlideInRight {
        0% { transform: translate3d(32px, 0, 0); opacity: 0; }
        100% { transform: translate3d(0, 0, 0); opacity: 1; }
    }
    @keyframes rentifySlideOutRight {
        0% { transform: translate3d(0, 0, 0); opacity: 1; }
        100% { transform: translate3d(32px, 0, 0); opacity: 0; }
    }
    @keyframes rentifySlideInLeft {
        0% { transform: translate3d(-32px, 0, 0); opacity: 0; }
        100% { transform: translate3d(0, 0, 0); opacity: 1; }
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

<!-- CLIENT-SIDE ULTRA-SMOOTH INSTANT TAB ENGINE & HARDWARE-ACCELERATED SWAPPING -->
<script>
    (function() {
        const currentActiveTab = "{{ $activeTab }}";
        const routes = {
            beranda: "{{ route('customer.home') }}",
            momen: "{{ route('customer.moments') }}",
            akun: "{{ $accountUrl }}"
        };
        const TAB_ORDER = { beranda: 0, momen: 1, akun: 2 };

        // Pastikan store & cache global tersedia
        window.__rentifyTabs = window.__rentifyTabs || {};
        window.__rentifyInitialMomentsCache = window.__rentifyInitialMomentsCache || null;

        let currentTab = currentActiveTab;
        let isSwitching = false;

        function triggerProgressBar() {
            const bar = document.getElementById('rentify-tab-progress');
            if (!bar) return;
            bar.style.transition = 'width 0.28s cubic-bezier(0.1, 0.8, 0.2, 1), opacity 0.15s ease';
            bar.style.opacity = '1';
            bar.style.width = '75%';
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

        // Daftarkan tab saat ini ke memori
        function registerCurrentTab() {
            const pageContent = document.getElementById('rentify-page-content');
            if (pageContent) {
                pageContent.id = 'rentify-page-content-' + currentTab;
                window.__rentifyTabs[currentTab] = {
                    element: pageContent,
                    title: document.title,
                    scrollY: window.scrollY || window.pageYOffset || 0
                };
            }
        }

        // Prefetch tab di background saat idle
        async function prefetchTab(tabKey) {
            if (window.__rentifyTabs[tabKey] || !routes[tabKey]) return;
            try {
                const res = await fetch(routes[tabKey], {
                    headers: { 'X-Rentify-Prefetch': '1', 'Accept': 'text/html' }
                });
                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const contentEl = doc.getElementById('rentify-page-content');
                if (!contentEl) return;

                contentEl.id = 'rentify-page-content-' + tabKey;
                contentEl.style.display = 'none';

                const navWrapper = document.getElementById('bottom-navigation-wrapper');
                if (navWrapper && navWrapper.parentNode) {
                    navWrapper.parentNode.insertBefore(contentEl, navWrapper);
                } else {
                    document.body.appendChild(contentEl);
                }

                window.__rentifyTabs[tabKey] = {
                    element: contentEl,
                    title: doc.title,
                    scrollY: 0
                };

                // Sisipkan dan jalankan skrip inline esensial
                doc.querySelectorAll('script').forEach(oldScript => {
                    if (oldScript.src) {
                        if (!document.querySelector(`script[src="${oldScript.src}"]`)) {
                            const s = document.createElement('script');
                            s.src = oldScript.src;
                            document.body.appendChild(s);
                        }
                    } else if (
                        oldScript.textContent.includes('window.RentifyMoments') || 
                        oldScript.textContent.includes('initHomeScripts') || 
                        oldScript.textContent.includes('switchTab')
                    ) {
                        const s = document.createElement('script');
                        s.textContent = oldScript.textContent;
                        document.body.appendChild(s);
                    }
                });

                // Pre-cache feed Momen dari API agar saat tab Momen dibuka tidak ada skeleton/spinner
                if (tabKey === 'momen' && !window.__rentifyInitialMomentsCache) {
                    fetch('/api/moments?limit=8', { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(data => { window.__rentifyInitialMomentsCache = data; })
                        .catch(() => {});
                }
            } catch (e) {
                // Background prefetch gagal silently, tidak mengganggu navigasi normal
            }
        }

        function scheduleBackgroundPrefetch() {
            const tabsToPrefetch = ['beranda', 'momen', 'akun'].filter(t => t !== currentTab);
            const run = () => tabsToPrefetch.forEach(t => prefetchTab(t));
            if ('requestIdleCallback' in window) {
                requestIdleCallback(run, { timeout: 1200 });
            } else {
                setTimeout(run, 350);
            }
        }

        function updateBottomNavVisuals(targetTab) {
            const navBar = document.getElementById('bottom-navigation-bar');
            if (!navBar) return;

            const isTargetMomen = (targetTab === 'momen');

            if (isTargetMomen) {
                navBar.className = 'bg-slate-950/85 backdrop-blur-2xl border border-white/20 text-white shadow-2xl px-6 py-2 transition-all duration-300 relative overflow-hidden pointer-events-auto rounded-full flex justify-between items-center gap-9';
                navBar.classList.add('water-surge-active');
                setTimeout(() => navBar.classList.remove('water-surge-active'), 500);
            } else {
                navBar.className = 'rentify-card bg-white/90 backdrop-blur-xl border border-white/80 px-7 py-2 shadow-xl shadow-sky-900/10 transition-all duration-300 relative overflow-hidden pointer-events-auto rounded-full flex justify-between items-center gap-9';
            }

            navBar.querySelectorAll('a[data-tab]').forEach(a => {
                const tab = a.getAttribute('data-tab');
                const isActive = (tab === targetTab);

                if (isActive) {
                    a.className = isTargetMomen 
                        ? 'relative z-10 flex flex-col items-center px-2 py-0.5 transition-transform duration-200 text-sky-400 font-black active scale-105' 
                        : 'relative z-10 flex flex-col items-center px-2 py-0.5 transition-transform duration-200 text-sky-600 font-black active scale-105';
                } else {
                    a.className = isTargetMomen
                        ? 'relative z-10 flex flex-col items-center px-2 py-0.5 transition-transform duration-200 text-white/60 hover:text-white transition-colors font-bold'
                        : 'relative z-10 flex flex-col items-center px-2 py-0.5 transition-transform duration-200 text-slate-400 hover:text-sky-600 transition-colors font-bold';
                }
            });
        }

        function updateBodyTheme(targetTab) {
            if (targetTab === 'momen') {
                document.documentElement.style.backgroundColor = '#000000';
                document.documentElement.style.overflow = 'hidden';
                document.body.style.backgroundColor = '#000000';
                document.body.style.background = '#000000';
                document.body.style.overflow = 'hidden';
            } else if (targetTab === 'akun') {
                document.documentElement.style.backgroundColor = '';
                document.documentElement.style.overflow = 'auto';
                document.body.style.background = 'radial-gradient(circle at center, #fff9ef 0%, #bad6eb 100%) fixed';
                document.body.style.backgroundColor = '';
                document.body.style.overflow = 'auto';
            } else {
                document.documentElement.style.backgroundColor = '';
                document.documentElement.style.overflow = 'auto';
                document.body.style.background = '#f8fafc';
                document.body.style.backgroundColor = '#f8fafc';
                document.body.style.overflow = 'auto';
            }
        }

        function executeTabSwap(targetTab, direction) {
            const currentEl = window.__rentifyTabs[currentTab]?.element;
            const targetData = window.__rentifyTabs[targetTab];
            const targetEl = targetData?.element;

            if (!currentEl || !targetEl) {
                isSwitching = false;
                window.location.href = routes[targetTab];
                return;
            }

            const outClass = (direction === 'left') ? 'rentify-slide-out-left' : 'rentify-slide-out-right';
            const inClass = (direction === 'left') ? 'rentify-slide-in-right' : 'rentify-slide-in-left';

            // Bersihkan sisa kelas animasi
            targetEl.classList.remove('rentify-slide-out-left', 'rentify-slide-out-right', 'rentify-slide-in-left', 'rentify-slide-in-right');
            targetEl.style.display = 'block';
            targetEl.classList.add(inClass);

            currentEl.classList.remove('rentify-slide-out-left', 'rentify-slide-out-right', 'rentify-slide-in-left', 'rentify-slide-in-right');
            currentEl.classList.add(outClass);

            // Perbarui visual bar & tema warna
            updateBottomNavVisuals(targetTab);
            updateBodyTheme(targetTab);

            // Perbarui URL browser & title tanpa reload
            if (window.history && window.history.pushState) {
                window.history.pushState({ tab: targetTab }, targetData.title || document.title, routes[targetTab]);
            }
            if (targetData.title) {
                document.title = targetData.title;
            }

            // Selesai transisi geser (220ms GPU)
            setTimeout(() => {
                currentEl.style.display = 'none';
                currentEl.classList.remove(outClass);
                targetEl.classList.remove(inClass);

                // Pulihkan scroll / inisialisasi media tab
                if (targetTab === 'momen') {
                    window.scrollTo(0, 0);
                    if (window.RentifyMoments && typeof window.RentifyMoments.init === 'function') {
                        window.RentifyMoments.init();
                    } else if (window.RentifyMoments && typeof window.RentifyMoments.resume === 'function') {
                        window.RentifyMoments.resume();
                    }
                } else {
                    const savedY = targetData.scrollY || 0;
                    window.scrollTo({ top: savedY, behavior: 'instant' });
                    if (targetTab === 'beranda' && typeof window.initHomeScripts === 'function') {
                        window.initHomeScripts();
                    }
                }

                currentTab = targetTab;
                isSwitching = false;
            }, 220);
        }

        function switchToTab(targetTab, forcedDirection) {
            if (!targetTab || !routes[targetTab]) return;
            if (targetTab === currentTab) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }
            if (isSwitching) return;
            isSwitching = true;

            // Haptic feedback instan
            if ('vibrate' in navigator) {
                try { navigator.vibrate(10); } catch(e){}
            }

            const direction = forcedDirection || (TAB_ORDER[targetTab] > TAB_ORDER[currentTab] ? 'left' : 'right');

            // Simpan scroll tab asal
            if (window.__rentifyTabs[currentTab]) {
                window.__rentifyTabs[currentTab].scrollY = window.scrollY || window.pageYOffset || 0;
            }

            // Hentikan video & audio jika meninggalkan Momen
            if (currentTab === 'momen') {
                if (window.RentifyMoments && typeof window.RentifyMoments.pause === 'function') {
                    window.RentifyMoments.pause();
                } else {
                    document.querySelectorAll('#moments-app video').forEach(v => {
                        try { v.pause(); } catch(e){}
                    });
                }
            }

            const targetData = window.__rentifyTabs[targetTab];

            // Jika tab target belum ada di memory cache (misal disentuh sangat cepat)
            if (!targetData || !targetData.element) {
                triggerProgressBar();
                fetch(routes[targetTab], { headers: { 'Accept': 'text/html' } })
                    .then(res => res.text())
                    .then(html => {
                        completeProgressBar();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const contentEl = doc.getElementById('rentify-page-content');
                        if (contentEl) {
                            contentEl.id = 'rentify-page-content-' + targetTab;
                            contentEl.style.display = 'none';
                            const navWrapper = document.getElementById('bottom-navigation-wrapper');
                            if (navWrapper && navWrapper.parentNode) {
                                navWrapper.parentNode.insertBefore(contentEl, navWrapper);
                            } else {
                                document.body.appendChild(contentEl);
                            }
                            window.__rentifyTabs[targetTab] = {
                                element: contentEl,
                                title: doc.title,
                                scrollY: 0
                            };
                            executeTabSwap(targetTab, direction);
                        } else {
                            window.location.href = routes[targetTab];
                        }
                    })
                    .catch(() => {
                        window.location.href = routes[targetTab];
                    });
                return;
            }

            executeTabSwap(targetTab, direction);
        }

        // Penanganan Gestur Horizontal Swipe yang Simple & Mulus Tanpa Beban
        function setupHorizontalSwipeGesture() {
            let startX = 0;
            let startY = 0;
            let startTime = 0;
            let isSwiping = false;

            function shouldIgnore(e) {
                const t = e.target;
                if (!t) return false;
                // Form input atau tombol
                if (t.closest('input, textarea, select, button, a:not(#bottom-navigation-bar a)')) return true;
                // Modal atau sheet aktif
                if (document.querySelector('#comments-drawer:not(.hidden), #upload-modal:not(.hidden), #report-modal:not(.hidden), #guest-auth-modal:not(.hidden), #drawer-settings:not(.hidden)')) return true;
                // Carousel foto atau slider horizontal
                if (t.closest('.carousel-track, #carousel-wrap, #banner-slider, .productSwiper, .swiper, .overflow-x-auto, .overflow-x-scroll')) return true;
                return false;
            }

            window.addEventListener('touchstart', function(e) {
                if (e.touches.length !== 1 || shouldIgnore(e)) {
                    isSwiping = false;
                    return;
                }
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                startTime = Date.now();
                isSwiping = true;
            }, { passive: true });

            window.addEventListener('touchend', function(e) {
                if (!isSwiping || isSwitching) return;
                isSwiping = false;

                const touch = e.changedTouches[0];
                if (!touch) return;

                const diffX = touch.clientX - startX;
                const diffY = touch.clientY - startY;
                const elapsed = Date.now() - startTime;

                // Gestur swap sederhana: jarak horizontal minimal 40px, dominan horizontal, dan < 600ms
                if (Math.abs(diffX) >= 40 && Math.abs(diffX) > Math.abs(diffY) * 1.2 && elapsed <= 600) {
                    if (diffX < 0) {
                        // SWIPE KIRI -> Tab berikutnya
                        if (currentTab === 'beranda') {
                            switchToTab('momen', 'left');
                        } else if (currentTab === 'momen') {
                            switchToTab('akun', 'left');
                        }
                    } else {
                        // SWIPE KANAN -> Tab sebelumnya
                        if (currentTab === 'akun') {
                            switchToTab('momen', 'right');
                        } else if (currentTab === 'momen') {
                            switchToTab('beranda', 'right');
                        }
                    }
                }
            }, { passive: true });

            window.addEventListener('touchcancel', () => { isSwiping = false; }, { passive: true });
        }

        // Inisialisasi event listener tab navigation bar
        function initNavBarEvents() {
            registerCurrentTab();

            const navBar = document.getElementById('bottom-navigation-bar');
            if (navBar) {
                navBar.querySelectorAll('a[data-tab]').forEach(function(anchor) {
                    const tab = anchor.getAttribute('data-tab');
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        switchToTab(tab);
                    });
                });
            }

            setupHorizontalSwipeGesture();
            scheduleBackgroundPrefetch();
        }

        // Tangani navigasi History Back / Forward browser Android & iOS
        window.addEventListener('popstate', function(e) {
            let tab = e.state?.tab;
            if (!tab) {
                const path = window.location.pathname;
                if (path.includes('moment')) tab = 'momen';
                else if (path.includes('account') || path.includes('dashboard')) tab = 'akun';
                else tab = 'beranda';
            }
            if (tab && tab !== currentTab) {
                const direction = TAB_ORDER[tab] > TAB_ORDER[currentTab] ? 'left' : 'right';
                switchToTab(tab, direction);
            }
        });

        // Eksekusi inisialisasi
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initNavBarEvents);
        } else {
            initNavBarEvents();
        }

        // Ekspos ke window untuk kebutuhan integrasi
        window.RentifyTabEngine = {
            switchTo: switchToTab,
            getCurrentTab: () => currentTab
        };
    })();
</script>
