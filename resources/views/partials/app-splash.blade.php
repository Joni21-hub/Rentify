<!-- ========================================================================= -->
<!-- RENTIFY NATIVE WELCOMING & SPLASH SCREEN SYSTEM                          -->
<!-- Menghadirkan transisi pembukaan aplikasi modern, hangat & profesional     -->
<!-- ========================================================================= -->
<div id="rentify-splash-screen" 
     class="fixed inset-0 z-[999999] flex flex-col items-center justify-between py-12 px-6 bg-gradient-to-b from-[#F0F9FF] via-[#FFFFFF] to-[#E0F2FE] select-none pointer-events-auto transition-all duration-500 ease-out"
     aria-hidden="true">
    
    <!-- Ambient Background Glow Circles -->
    <div class="absolute top-12 -right-16 w-64 h-64 rounded-full bg-sky-300/20 blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-16 -left-16 w-72 h-72 rounded-full bg-sky-400/15 blur-3xl pointer-events-none"></div>

    <!-- Top Space Holder for Vertical Balance -->
    <div class="h-6"></div>

    <!-- Center Brand Emblem & Greeting -->
    <div class="flex flex-col items-center text-center relative z-10 -mt-6">
        <!-- Logo Card with Subtle Floating Effect -->
        <div class="relative group">
            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-white shadow-xl shadow-sky-500/15 border border-sky-100 flex items-center justify-center p-3 relative overflow-hidden transition-transform duration-700 ease-out animate-splash-logo">
                <img src="{{ asset('icons/maskable-icon-192x192.png') }}" 
                     alt="Rentify Logo" 
                     class="w-full h-full object-contain filter drop-shadow-sm"
                     loading="eager"
                     decoding="async">
                <!-- Glossy diagonal reflection overlay -->
                <div class="absolute inset-0 bg-gradient-to-tr from-white/0 via-white/40 to-white/0 pointer-events-none"></div>
            </div>
            <!-- Outer Pulse Ring -->
            <div class="absolute -inset-2 rounded-[32px] bg-sky-400/20 blur-md -z-10 animate-pulse"></div>
        </div>

        <!-- App Name -->
        <div class="mt-5 flex items-center gap-1.5">
            <h1 class="text-3xl sm:text-4xl font-black text-sky-500 tracking-tight">Rentify</h1>
        </div>

        <!-- Tagline & Welcoming Badge -->
        <p class="text-xs sm:text-sm font-semibold text-slate-500 mt-1 max-w-[260px] leading-relaxed">
            Platform Marketplace Rental Terpercaya
        </p>

        <div class="mt-4 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/90 border border-sky-200/70 shadow-xs text-sky-600 text-xs font-bold backdrop-blur-xs">
            <i class="fa-solid fa-sparkles text-sky-400 text-[11px]"></i>
            <span>Selamat Datang!</span>
        </div>
    </div>

    <!-- Bottom Progress & Tagline -->
    <div class="flex flex-col items-center gap-3 relative z-10 w-full max-w-[200px]">
        <!-- Modern Loading Progress Track -->
        <div class="w-full h-1.5 bg-sky-100/80 rounded-full overflow-hidden relative shadow-inner">
            <div class="h-full bg-gradient-to-r from-sky-400 via-sky-500 to-sky-600 rounded-full w-2/5 animate-splash-bar"></div>
        </div>
        <span class="text-[11px] font-semibold text-slate-400 tracking-wider">#SewaGampangCuanDatang</span>
    </div>
</div>

<style>
@keyframes splashLogo {
    0% { transform: scale(0.92) translateY(6px); opacity: 0.7; }
    50% { transform: scale(1.02) translateY(-2px); opacity: 1; }
    100% { transform: scale(1) translateY(0); opacity: 1; }
}
@keyframes splashBar {
    0% { transform: translateX(-120%); }
    50% { transform: translateX(50%); }
    100% { transform: translateX(250%); }
}
.animate-splash-logo {
    animation: splashLogo 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.animate-splash-bar {
    animation: splashBar 1.4s ease-in-out infinite;
}
.rentify-splash-fade-out {
    opacity: 0 !important;
    transform: scale(1.03) !important;
    pointer-events: none !important;
    visibility: hidden;
    transition: opacity 0.45s cubic-bezier(0.16, 1, 0.3, 1), transform 0.45s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.45s ease !important;
}
</style>

<script>
(function() {
    const splash = document.getElementById('rentify-splash-screen');
    if (!splash) return;

    // Cek apakah splash sudah pernah tampil di sesi saat ini (cegah berulang saat berpindah-pindah tab biasa)
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches 
                      || (window.navigator.standalone === true) 
                      || window.location.search.includes('source=pwa');
    const hasSeenSplash = sessionStorage.getItem('rentify_splash_seen');

    // Jika navigasi internal reguler di tab yang sama dan bukan PWA launch baru, sembunyikan seketika
    if (hasSeenSplash && !window.location.search.includes('source=pwa') && !sessionStorage.getItem('rentify_force_splash')) {
        splash.style.display = 'none';
        return;
    }

    // Catat bahwa pengguna sudah disambut di sesi ini
    try {
        sessionStorage.setItem('rentify_splash_seen', '1');
    } catch(e) {}

    const startTime = performance.now();
    const MIN_SPLASH_DURATION = 650; // Durasi minimum agar animasi penyambutan terasa lembut dan elegan
    const MAX_SPLASH_TIMEOUT = 1800; // Timeout maksimal bila ada aset eksternal yang lambat

    function hideSplash() {
        if (!splash || splash.classList.contains('rentify-splash-fade-out')) return;
        const elapsed = performance.now() - startTime;
        const remaining = Math.max(0, MIN_SPLASH_DURATION - elapsed);

        setTimeout(() => {
            splash.classList.add('rentify-splash-fade-out');
            setTimeout(() => {
                if (splash && splash.parentNode) {
                    splash.style.display = 'none';
                }
            }, 480);
        }, remaining);
    }

    if (document.readyState === 'complete') {
        hideSplash();
    } else {
        window.addEventListener('load', hideSplash, { once: true });
        // Fallback safety timeout
        setTimeout(hideSplash, MAX_SPLASH_TIMEOUT);
    }
})();
</script>
