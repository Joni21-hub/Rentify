{{-- Rentify — Homepage (perkenalan). Tema mengikuti halaman login: langit biru → putih, kaca putih, tombol gradasi biru. --}}
@php
    $go = fn (string $name, string $fallback, array $params = []) =>
        \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : url($fallback);

    $links = [
        'search' => $go('customer.search', '/search'),
        'shop'   => $go('register', '/register?peran=pemilik', ['peran' => 'pemilik']),
        'login'  => $go('login', '/login'),
        'home'   => $go('customer.home', '/'),
    ];

    // Isi di config/rentify.php ('app_download_url' => '...') bila aplikasi sudah rilis.
    $appUrl = config('rentify.app_download_url');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#D3EAF8">
    <title>Rentify — Sewa-menyewa antar teman, kini seluas kotamu</title>
    <meta name="description" content="Rentify adalah marketplace sewa-menyewa untuk semua jenis barang. Barang nganggur jadi penghasilan, butuh sebentar tinggal sewa.">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Rentify">
    <meta property="og:title" content="Rentify — Sewa-menyewa antar teman, kini seluas kotamu">
    <meta property="og:description" content="Barang nganggur jadi penghasilan. Butuh sebentar, tinggal sewa.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:image:alt" content="Rentify — Marketplace Sewa-Menyewa">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('images/logo.png') }}">

    
    <!-- Favicon & Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

    <!-- Schema.org JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "WebSite",
          "@id": "{{ url('/') }}/#website",
          "url": "{{ url('/') }}",
          "name": "Rentify",
          "description": "Marketplace Rental — Sewa Apapun yang Kamu Butuhkan. Sewa-menyewa antar teman, kini seluas kotamu.",
          "potentialAction": {
            "@type": "SearchAction",
            "target": "{{ route('customer.search') }}?q={search_term_string}",
            "query-input": "required name=search_term_string"
          }
        },
        {
          "@type": "Organization",
          "@id": "{{ url('/') }}/#organization",
          "name": "Rentify",
          "url": "{{ url('/') }}",
          "logo": "{{ asset('images/logo.png') }}",
          "slogan": "Sewa gampang, cuan datang!"
        }
      ]
    }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>document.documentElement.classList.add('js');</script>

    <style>
        :root {
            --blue: #0EA6E9;
            --blue-d: #199DDD;
            --sky: #22A6E4;
            --sky-l: #E6F3FB;
            --ink: #0F2A43;
            --muted: #4A6378;
            --soft: #7890A4;
            --grad: linear-gradient(135deg, #22A6E4 0%, #199DDD 100%);
            --accent: #0EA6E9;
            --ice: #E6F3FB;
            --shadow: 0 24px 60px -30px rgba(15, 42, 67, .22);
            --shadow-s: 0 16px 34px -18px rgba(15, 42, 67, .24);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; overflow-x: hidden; }
        main { overflow-x: clip; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--ink);
            background: linear-gradient(180deg, #DCEBF7 0, #E9F2FA 640px, #FFFFFF 1600px);
            min-height: 100vh;
            overflow-x: clip;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        button { font: inherit; color: inherit; background: none; border: 0; cursor: pointer; }
        ::selection { background: #D3EAF8; }
        .lucide { width: 18px; height: 18px; stroke-width: 1.75; }
        .container { width: 100%; max-width: 1180px; margin: 0 auto; padding: 0 20px; }
        section { scroll-margin-top: 80px; }

        .brand { display: inline-flex; align-items: center; gap: 10px; font-weight: 800; font-size: 24px; letter-spacing: -.035em; color: var(--blue); line-height: 1; text-decoration: none; }
        .brand-img { height: 36px; width: auto; object-fit: contain; }
        .glass {
            background: rgba(255, 255, 255, .74);
            -webkit-backdrop-filter: blur(18px) saturate(1.3);
            backdrop-filter: blur(18px) saturate(1.3);
            border: 1px solid rgba(255, 255, 255, .95);
            border-radius: 28px;
            box-shadow: var(--shadow);
        }
        .eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 14px; border-radius: 999px;
            background: rgba(255, 255, 255, .8); border: 1px solid #fff;
            font-size: 13px; font-weight: 700; color: var(--blue);
            box-shadow: 0 8px 20px -14px rgba(14, 166, 233, .5);
        }
        .eyebrow .lucide { width: 15px; height: 15px; }
        .sec-title { font-size: clamp(28px, 4vw, 44px); font-weight: 800; letter-spacing: -.03em; line-height: 1.1; margin-top: 16px; }
        .sec-sub { color: var(--muted); font-size: 16.5px; line-height: 1.65; margin-top: 14px; max-width: 560px; }
        .grad-text { color: var(--accent); }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            height: 52px; padding: 0 26px; border-radius: 999px;
            font-weight: 700; font-size: 15px; white-space: nowrap; position: relative;
            transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease;
        }
        .btn-primary { background: var(--grad); color: #fff; box-shadow: 0 10px 24px -12px rgba(14, 166, 233, .55); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 30px -14px rgba(14, 166, 233, .6); }
        .btn-light { background: #fff; color: var(--blue); border: 1px solid #E6F3FB; box-shadow: 0 8px 20px -12px rgba(14, 166, 233, .35); }
        .btn-light:hover { transform: translateY(-2px); background-color: #F3F9FD; }
        .btn:active { transform: translateY(0) scale(.98); }
        .btn-sm { height: 42px; padding: 0 18px; font-size: 14px; }
        a:focus-visible, button:focus-visible { outline: 3px solid rgba(14, 166, 233, .7); outline-offset: 3px; border-radius: 999px; }

        /* Nav */
        .nav { position: sticky; top: 0; z-index: 50; padding: 14px 0; transition: background-color .25s ease, box-shadow .25s ease; }
        .nav.scrolled { background: rgba(255, 255, 255, .8); -webkit-backdrop-filter: blur(16px); backdrop-filter: blur(16px); box-shadow: 0 10px 30px -22px rgba(15, 42, 67, .5); }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .nav-links { display: flex; gap: 30px; font-weight: 600; font-size: 14.5px; color: var(--muted); }
        .nav-links a { transition: color .18s ease; }
        .nav-links a:hover { color: var(--blue); }
        .nav-actions { display: flex; gap: 10px; align-items: center; }
        .burger { display: none; width: 44px; height: 44px; border-radius: 14px; background: #fff; border: 1px solid #E6F3FB; align-items: center; justify-content: center; color: var(--blue); }
        .nav-progress { position: absolute; left: 0; bottom: 0; height: 3px; width: 100%; background: var(--grad); transform-origin: left; transform: scaleX(0); }
        .mobile-menu { display: none; }

        /* Hero */
        .hero { padding: 56px 0 30px; }
        .hero-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 40px; align-items: center; }
        .dot-live { width: 8px; height: 8px; border-radius: 50%; background: #0EA6E9; animation: ping 2s infinite; }
        .hero-title { font-size: clamp(38px, 6vw, 68px); line-height: 1.05; letter-spacing: -.04em; font-weight: 800; margin-top: 22px; }
        .word { display: inline-block; opacity: 0; transform: translateY(30px) rotate(3deg); animation: wordIn .8s cubic-bezier(.2, .8, .2, 1) forwards; animation-delay: calc(var(--i) * 80ms + 120ms); }
        .uline { position: relative; }
        .squiggle { position: absolute; left: 0; bottom: -12px; width: 100%; height: 18px; overflow: visible; }
        .squiggle path { stroke-dasharray: 320; stroke-dashoffset: 320; animation: draw 1.1s 1s ease forwards; }
        .hero-sub { font-size: clamp(16px, 2vw, 19px); color: var(--muted); margin-top: 26px; max-width: 520px; line-height: 1.6; opacity: 0; animation: fadeUp .8s .7s ease forwards; }
        .hero-cta { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 30px; opacity: 0; animation: fadeUp .8s .85s ease forwards; }
        .hero-note { margin-top: 16px; font-size: 13.5px; color: var(--soft); display: flex; align-items: center; gap: 6px; opacity: 0; animation: fadeUp .8s 1s ease forwards; }
        .hero-note .lucide { width: 15px; height: 15px; color: #0EA6E9; }

        .hero-visual { position: relative; width: 100%; max-width: 470px; margin-left: auto; padding: 24px 0; opacity: 0; animation: fadeIn 1s .4s ease forwards; }
        .map-card { padding: 16px; border-radius: 32px; }
        .map-title { text-align: center; font-size: 17px; font-weight: 800; color: var(--ink); margin: 4px 0 14px; letter-spacing: -.01em; }
        .map { position: relative; aspect-ratio: 1; border-radius: 24px; overflow: hidden; background: #EEF5FA; }
        .map > svg { position: absolute; inset: 0; width: 100%; height: 100%; }
        .mring { position: absolute; left: 50%; top: 50%; aspect-ratio: 1; border-radius: 50%; transform: translate(-50%, -50%); background: radial-gradient(circle, rgba(14, 166, 233, .12) 0%, rgba(14, 166, 233, .05) 72%, rgba(14, 166, 233, 0) 100%); border: 1px solid rgba(14, 166, 233, .22); }
        .mring.r1 { width: 90%; } .mring.r2 { width: 62%; } .mring.r3 { width: 34%; }
        .mwave { position: absolute; left: 50%; top: 50%; width: 30%; aspect-ratio: 1; border-radius: 50%; border: 2px solid rgba(14, 166, 233, .4); transform: translate(-50%, -50%) scale(.4); animation: wave 4.2s ease-out infinite; animation-delay: var(--d); }
        .mcenter { position: absolute; left: 50%; top: 50%; width: 22px; height: 22px; margin: -11px 0 0 -11px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 8px rgba(14, 166, 233, .18), 0 0 26px 8px rgba(14, 166, 233, .3); }
        .mdot { position: absolute; width: 12px; height: 12px; margin: -6px 0 0 -6px; border-radius: 50%; background: var(--accent); opacity: .85; animation: dotPulse 2.8s ease-out infinite; animation-delay: var(--d); }
        .mdot.s { width: 8px; height: 8px; margin: -4px 0 0 -4px; opacity: .6; }
        .item-pos { position: absolute; z-index: 2; transition: transform .6s cubic-bezier(.2, .8, .2, 1); }
        .item-pos.p1 { left: -12%; top: 30%; } .item-pos.p2 { right: -10%; top: 15%; } .item-pos.p3 { right: -6%; bottom: 7%; }
        .item-card {
            display: flex; width: max-content; align-items: center; gap: 12px; padding: 12px 18px 12px 12px; border-radius: 20px;
            background: rgba(255, 255, 255, .84); -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, .95); box-shadow: 0 18px 40px -20px rgba(15, 42, 67, .3);
            animation: float 5.5s ease-in-out infinite; animation-delay: var(--dl);
        }
        .item-ic { width: 50px; height: 50px; border-radius: 15px; background: var(--ice); color: var(--accent); display: grid; place-items: center; flex: none; }
        .item-ic .lucide { width: 26px; height: 26px; stroke-width: 1.5; }
        .item-card small { display: block; font-size: 12px; color: var(--muted); font-weight: 500; }
        .item-card strong { display: block; font-size: 14.5px; font-weight: 600; color: var(--ink); margin-top: 1px; }
        .item-card b { display: block; font-size: 14.5px; font-weight: 800; color: var(--ink); margin-top: 3px; }
        .item-card b span { font-weight: 500; color: var(--muted); }
        .scroll-cue { display: flex; flex-direction: column; align-items: center; gap: 10px; margin-top: 44px; color: var(--soft); font-size: 13px; font-weight: 600; }
        .mouse { width: 24px; height: 38px; border: 2px solid #A9D8F3; border-radius: 14px; position: relative; }
        .mouse::after { content: ''; position: absolute; left: 50%; top: 7px; width: 4px; height: 8px; margin-left: -2px; border-radius: 2px; background: var(--blue); animation: wheel 1.6s ease-in-out infinite; }

        /* Story (scroll-linked) */
        .story { position: relative; height: 340vh; }
        .story-sticky { position: sticky; top: 0; height: 100vh; height: 100svh; display: flex; align-items: center; padding-top: 60px; }
        .story-grid { display: grid; grid-template-columns: .82fr 1.18fr; gap: 44px; align-items: center; width: 100%; }
        .steps { list-style: none; display: grid; gap: 10px; margin-top: 26px; }
        .step { display: flex; gap: 14px; padding: 16px 18px; border-radius: 20px; opacity: .42; transition: opacity .35s ease, background-color .35s ease, transform .35s ease, box-shadow .35s ease; }
        .step.active { opacity: 1; background: rgba(255, 255, 255, .85); box-shadow: var(--shadow-s); transform: translateX(8px); }
        .step-num { width: 34px; height: 34px; border-radius: 50%; background: var(--sky-l); color: var(--blue); font-weight: 800; display: grid; place-items: center; flex: none; font-size: 14px; transition: background-color .3s ease, color .3s ease; }
        .step.active .step-num { background: var(--grad); color: #fff; }
        .step h3 { font-size: 17px; font-weight: 800; }
        .step p { font-size: 14px; color: var(--muted); margin-top: 4px; line-height: 1.55; }
        .story-track { height: 4px; border-radius: 4px; background: #E6F3FB; margin-top: 20px; overflow: hidden; }
        .story-track span { display: block; height: 100%; background: var(--grad); transform-origin: left; transform: scaleX(0); }
        .stage-card { padding: 12px; border-radius: 34px; }
        .stage { position: relative; width: 100%; aspect-ratio: 800 / 640; }
        .stage > svg { position: absolute; inset: 0; width: 100%; height: 100%; }
        .route-pin { fill: rgba(14, 166, 233, .35); transform-box: fill-box; transform-origin: center; animation: pinPulse 2s ease-out infinite; }
        .money-card { position: absolute; left: 3%; top: 38%; display: flex; gap: 10px; align-items: center; padding: 12px 14px; border-radius: 18px; background: #fff; box-shadow: var(--shadow-s); opacity: 0; }
        .money-card .mc-ic { width: 38px; height: 38px; border-radius: 12px; background: #E6F3FB; color: #0EA6E9; display: grid; place-items: center; flex: none; }
        .money-card small { display: block; font-size: 11.5px; color: var(--soft); font-weight: 600; }
        .money-card strong { display: block; font-size: clamp(15px, 2vw, 22px); font-weight: 800; color: #0EA6E9; font-variant-numeric: tabular-nums; letter-spacing: -.02em; }
        .money-card em { display: block; font-style: normal; font-size: 10.5px; color: #9FB6C8; }
        .toko-ov { position: absolute; left: 3%; top: 58%; opacity: 0; pointer-events: none; }
        .got-badge { position: absolute; right: 4%; top: 57%; display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 999px; background: #fff; color: #0EA6E9; font-weight: 700; font-size: 13px; box-shadow: var(--shadow-s); opacity: 0; transform-origin: center; }
        .got-badge .lucide { width: 15px; height: 15px; }

        /* Coins */
        #coin-layer { position: fixed; inset: 0; pointer-events: none; z-index: 100; }
        .coin {
            position: fixed; width: 26px; height: 26px; border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, #FFFFFF, #E6F3FB 55%, #A9D8F3);
            border: 2px solid #0EA6E9; color: #0EA6E9; font-size: 9px; font-weight: 800;
            display: grid; place-items: center; box-shadow: 0 6px 14px -6px rgba(14, 166, 233, .5);
        }

        /* Radar */
        .radar-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .radar-wrap { padding: 30px; }
        .radar { position: relative; aspect-ratio: 1; width: 100%; max-width: 460px; margin: 0 auto; }
        .radar-bg { position: absolute; inset: 0; border-radius: 50%; background: radial-gradient(circle, #fff 0%, #EEF6FB 55%, rgba(234, 245, 254, 0) 72%); }
        .ring { position: absolute; border-radius: 50%; border: 1.5px solid rgba(14, 166, 233, .14); }
        .ring.r1 { inset: 8%; } .ring.r2 { inset: 22%; } .ring.r3 { inset: 36%; }
        .pulse { position: absolute; inset: 38%; border-radius: 50%; border: 2px solid rgba(14, 166, 233, .6); animation: pulse 3.6s cubic-bezier(.2, .6, .3, 1) infinite; animation-delay: var(--d); }
        .sweep { position: absolute; inset: 8%; border-radius: 50%; background: conic-gradient(from 0deg, rgba(14, 166, 233, 0) 0deg, rgba(14, 166, 233, .26) 55deg, rgba(14, 166, 233, 0) 57deg); animation: spin 5s linear infinite; }
        .me { position: absolute; left: 50%; top: 50%; width: 78px; height: 78px; transform: translate(-50%, -50%); border-radius: 50%; background: var(--grad); color: #fff; display: grid; place-items: center; font-weight: 800; font-size: 14px; box-shadow: 0 0 0 8px rgba(255, 255, 255, .85), 0 18px 40px -10px rgba(14, 166, 233, .6); z-index: 2; }
        .blip { position: absolute; width: 10px; height: 10px; margin: -5px 0 0 -5px; border-radius: 50%; background: var(--sky); animation: blip 2.4s ease-out forwards; }
        .orbit { position: absolute; inset: 0; animation: spin 44s linear infinite; z-index: 3; }
        .orbit-item { position: absolute; transform: translate(-50%, -50%); }
        .counter { animation: spin 44s linear infinite reverse; }
        .float-card { display: flex; align-items: center; gap: 10px; padding: 10px 16px 10px 10px; background: #fff; border-radius: 18px; box-shadow: var(--shadow-s); white-space: nowrap; animation: float 4s ease-in-out infinite; animation-delay: var(--dl); }
        .float-card .ic { width: 40px; height: 40px; border-radius: 13px; display: grid; place-items: center; background: var(--ice); color: var(--accent); }
        .float-card .ic .lucide { stroke-width: 1.5; width: 20px; height: 20px; }
        .float-card strong { display: block; font-size: 14px; }
        .float-card small { display: flex; align-items: center; gap: 5px; font-size: 11.5px; color: var(--soft); font-weight: 600; }
        .float-card small::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--accent); }

        /* Categories */
        .cats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-top: 30px; }
        .cat { position: relative; display: flex; flex-direction: column; gap: 12px; padding: 18px; border-radius: 22px; background: rgba(255, 255, 255, .85); border: 1px solid #fff; box-shadow: 0 10px 24px -18px rgba(15, 42, 67, .5); transition: transform .2s ease, box-shadow .2s ease; }
        .cat:hover { transform: translateY(-4px); box-shadow: var(--shadow-s); }
        .cat .ci { width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; background: var(--ice); color: var(--accent); transition: transform .3s cubic-bezier(.3, 1.6, .5, 1); }
        .cat .ci .lucide { width: 22px; height: 22px; stroke-width: 1.5; }
        .cat:hover .ci { transform: rotate(-10deg) scale(1.1); }
        .cat strong { display: block; font-size: 15px; }
        .cat div span { display: block; margin-top: 3px; font-size: 12.5px; color: var(--soft); line-height: 1.4; }
        .cat .arrow { position: absolute; top: 18px; right: 16px; color: var(--blue); opacity: 0; transform: translateX(-6px); transition: opacity .2s ease, transform .2s ease; }
        .cat:hover .arrow { opacity: 1; transform: none; }

        /* How it works */
        .center { text-align: center; }
        .center .sec-sub { margin-left: auto; margin-right: auto; }
        .seg { position: relative; display: inline-grid; grid-template-columns: 1fr 1fr; padding: 6px; border-radius: 999px; background: #fff; border: 1px solid #E6F3FB; box-shadow: var(--shadow-s); margin-top: 28px; }
        .seg button { position: relative; z-index: 1; padding: 12px 26px; font-weight: 700; font-size: 15px; color: var(--muted); border-radius: 999px; display: flex; gap: 8px; align-items: center; justify-content: center; transition: color .25s ease; }
        .seg button[aria-selected="true"] { color: #fff; }
        .seg-thumb { position: absolute; top: 6px; bottom: 6px; left: 6px; width: calc(50% - 6px); border-radius: 999px; background: var(--grad); box-shadow: 0 8px 20px -8px rgba(14, 166, 233, .7); transition: transform .45s cubic-bezier(.3, 1.4, .5, 1); }
        .seg[data-role="pemilik"] .seg-thumb { transform: translateX(100%); }
        .how-steps { position: relative; display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 44px; perspective: 1200px; text-align: left; }
        .how-line { position: absolute; top: 58px; left: 18%; right: 18%; height: 2px; background: repeating-linear-gradient(90deg, #A9D8F3 0 8px, transparent 8px 16px); }
        .how-card { position: relative; padding: 28px; transition: transform .22s ease, opacity .22s ease; }
        .how-card.flip { transform: rotateY(90deg); opacity: .2; }
        .how-ic { width: 60px; height: 60px; border-radius: 20px; background: var(--ice); color: var(--accent); display: grid; place-items: center; }
        .how-ic .lucide { width: 28px; height: 28px; stroke-width: 1.5; }
        .how-num { position: absolute; top: 20px; right: 24px; font-size: 46px; font-weight: 800; color: #E6F3FB; line-height: 1; }
        .how-card h3 { font-size: 20px; font-weight: 800; margin-top: 22px; }
        .how-card p { font-size: 14.5px; color: var(--muted); margin-top: 8px; line-height: 1.6; }

        /* Safety */
        .aman { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 40px; }
        .aman-card { padding: 16px 16px 26px; }
        .aman-art { height: 170px; border-radius: 20px; background: linear-gradient(180deg, #F3F9FD, #E6F3FB); display: grid; place-items: center; overflow: hidden; }
        .aman-art svg { width: 210px; height: 150px; overflow: visible; }
        .aman-art * { animation-play-state: paused !important; }
        .aman-card.in .aman-art * { animation-play-state: running !important; }
        .aman-card h3 { font-size: 19px; font-weight: 800; margin: 20px 10px 0; }
        .aman-card p { font-size: 14.5px; color: var(--muted); margin: 8px 10px 0; line-height: 1.6; }
        .tb { transform-box: fill-box; transform-origin: center; }
        .a-coin { animation: coinDrop 3s ease-in infinite; }
        .a-shackle { animation: shackle 3s ease infinite; }
        .a-check { animation: checkPop 3s ease infinite; }
        .a-scan { animation: scan 2.4s ease-in-out infinite alternate; }
        .a-idcheck { animation: idCheck 4.8s ease infinite; }
        .a-flash { animation: flash 3.2s ease infinite; }
        .a-photo { animation: photoOut 3.2s ease infinite; }

        /* Final CTA + footer */
        .final-card { position: relative; overflow: hidden; text-align: center; padding: 76px 28px; }
        .final-card h2 { font-size: clamp(30px, 4.6vw, 54px); font-weight: 800; letter-spacing: -.035em; line-height: 1.08; }
        .final-card p { color: var(--muted); font-size: 17px; margin: 16px auto 0; max-width: 520px; line-height: 1.6; }
        .final-cta { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 32px; position: relative; z-index: 1; }
        .fi { position: absolute; color: rgba(14, 166, 233, .16); animation: drift 9s ease-in-out infinite alternate; animation-delay: var(--dl); }
        .fi .lucide { width: 44px; height: 44px; stroke-width: 1.8; }
        footer { padding: 30px 0 40px; }
        .foot { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 30px; padding: 32px; }
        .foot p { color: var(--muted); font-size: 14px; margin-top: 12px; line-height: 1.6; max-width: 320px; }
        .foot h4 { font-size: 12px; text-transform: uppercase; letter-spacing: .12em; color: var(--soft); }
        .foot ul { list-style: none; margin-top: 12px; display: grid; gap: 10px; font-size: 14.5px; font-weight: 600; }
        .foot ul a, .foot ul button { color: var(--ink); transition: color .18s ease; text-align: left; }
        .foot ul a:hover, .foot ul button:hover { color: var(--blue); }
        .copy { text-align: center; font-size: 13px; color: var(--soft); margin-top: 20px; }

        /* Download dialog */
        dialog.dl { border: 0; padding: 0; background: transparent; color: var(--ink); width: calc(100% - 32px); max-width: 420px; margin: auto; }
        dialog.dl::backdrop { background: rgba(15, 42, 67, .35); -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px); }
        dialog.dl[open] .dl-card { animation: popIn .35s cubic-bezier(.3, 1.4, .5, 1); }
        .dl-card { padding: 30px 26px 26px; text-align: center; background: #fff; }
        .dl-ic { width: 64px; height: 64px; margin: 0 auto; border-radius: 22px; background: var(--ice); color: var(--accent); display: grid; place-items: center; }
        .dl-ic .lucide { width: 30px; height: 30px; stroke-width: 1.5; }
        .dl-card h3 { font-size: 21px; font-weight: 800; margin-top: 18px; }
        .dl-card p { font-size: 14.5px; color: var(--muted); margin-top: 8px; line-height: 1.6; }
        .dl-actions { display: grid; gap: 10px; margin-top: 22px; }

        /* Reveal */
        .js [data-reveal] { opacity: 0; transform: translateY(28px); transition: opacity .8s ease, transform .8s cubic-bezier(.2, .8, .2, 1); transition-delay: var(--d, 0ms); }
        .js [data-reveal].is-in { opacity: 1; transform: none; }

        /* Keyframes */
        @keyframes wordIn { to { opacity: 1; transform: none; } }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
        @keyframes fadeIn { to { opacity: 1; } }
        @keyframes draw { to { stroke-dashoffset: 0; } }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes bob { 0%, 100% { transform: translate(-50%, -50%); } 50% { transform: translate(-50%, calc(-50% - 10px)); } }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        @keyframes ping { 0% { box-shadow: 0 0 0 0 rgba(14, 166, 233, .55); } 100% { box-shadow: 0 0 0 10px rgba(14, 166, 233, 0); } }
        @keyframes wheel { 0% { opacity: 1; transform: translateY(0); } 100% { opacity: 0; transform: translateY(12px); } }
        @keyframes pinPulse { 0% { transform: scale(.6); opacity: 1; } 100% { transform: scale(2.4); opacity: 0; } }
        @keyframes pulse { 0% { transform: scale(.5); opacity: .9; } 100% { transform: scale(3.4); opacity: 0; } }
        @keyframes blip { 0% { transform: scale(0); opacity: 1; } 30% { transform: scale(1.2); opacity: 1; } 100% { transform: scale(2.6); opacity: 0; } }
        @keyframes coinDrop { 0% { transform: translateY(-26px); opacity: 0; } 12% { opacity: 1; } 42% { transform: translateY(32px); opacity: 1; } 50%, 100% { transform: translateY(40px); opacity: 0; } }
        @keyframes shackle { 0%, 48% { transform: translateY(-7px); } 58%, 100% { transform: translateY(0); } }
        @keyframes checkPop { 0%, 60% { transform: scale(0); } 70% { transform: scale(1.2); } 76%, 92% { transform: scale(1); } 100% { transform: scale(0); } }
        @keyframes scan { from { transform: translateY(0); } to { transform: translateY(80px); } }
        @keyframes idCheck { 0%, 45% { transform: scale(0); } 52% { transform: scale(1.2); } 58%, 90% { transform: scale(1); } 100% { transform: scale(0); } }
        @keyframes flash { 0%, 20% { opacity: 0; } 24% { opacity: .95; } 36%, 100% { opacity: 0; } }
        @keyframes photoOut { 0%, 24% { transform: translateY(-38px) rotate(0); } 50%, 88% { transform: translateY(0) rotate(-6deg); } 100% { transform: translateY(-38px) rotate(0); } }
        @keyframes drift { from { transform: translate(0, 0) rotate(-8deg); } to { transform: translate(14px, -18px) rotate(8deg); } }
        @keyframes popIn { from { transform: scale(.9); opacity: 0; } to { transform: none; opacity: 1; } }
        @keyframes wave { 0% { transform: translate(-50%, -50%) scale(.4); opacity: .9; } 100% { transform: translate(-50%, -50%) scale(3.2); opacity: 0; } }
        @keyframes dotPulse { 0% { box-shadow: 0 0 0 0 rgba(14, 166, 233, .45); } 70% { box-shadow: 0 0 0 12px rgba(14, 166, 233, 0); } 100% { box-shadow: 0 0 0 0 rgba(14, 166, 233, 0); } }

        /* Responsive */
        @media (max-width: 960px) {
            .nav-links, .nav-actions .hide-m { display: none; }
            .burger { display: inline-flex; }
            .mobile-menu {
                display: grid; gap: 4px; position: absolute; left: 16px; right: 16px; top: calc(100% + 4px); padding: 14px;
                opacity: 0; transform: translateY(-8px) scale(.98); pointer-events: none; transition: opacity .2s ease, transform .2s ease;
            }
            .mobile-menu.open { opacity: 1; transform: none; pointer-events: auto; }
            .mobile-menu > a, .mobile-menu > button { padding: 13px 14px; border-radius: 14px; font-weight: 700; font-size: 15px; display: flex; align-items: center; gap: 10px; color: var(--ink); text-align: left; }
            .mobile-menu > a:hover, .mobile-menu > button:hover { background: var(--sky-l); }
            .mobile-menu > a .lucide, .mobile-menu > button .lucide { color: var(--accent); }
            .mobile-menu .mm-cta { display: grid; gap: 8px; margin-top: 8px; padding-top: 12px; border-top: 1px solid #E6F3FB; }
            .mobile-menu .mm-cta .btn { width: 100%; }
            .hero { padding-top: 28px; }
            .hero-grid, .radar-grid, .story-grid { grid-template-columns: 1fr; }
            .hero-copy { text-align: center; }
            .hero-copy .hero-sub { margin-left: auto; margin-right: auto; }
            .hero-cta, .hero-note { justify-content: center; }
            .hero-visual { max-width: 340px; margin: 6px auto 0; padding: 30px 0 10px; }
            .map-card { padding: 12px; border-radius: 26px; }
            .map-title { font-size: 15px; margin-bottom: 10px; }
            .item-pos.p1 { left: -4%; top: 44%; } .item-pos.p2 { right: -4%; top: 19%; } .item-pos.p3 { right: -3%; bottom: 3%; }
            .hero-copy .eyebrow { font-size: 12px; padding: 7px 12px; }
            .item-card { gap: 8px; padding: 8px 12px 8px 8px; border-radius: 16px; }
            .item-ic { width: 38px; height: 38px; border-radius: 12px; }
            .item-ic .lucide { width: 21px; height: 21px; }
            .item-card small { font-size: 10.5px; }
            .item-card strong, .item-card b { font-size: 12px; }
            .story { height: 300vh; }
            .story-sticky { padding-top: 64px; }
            .story-grid { gap: 14px; }
            .story-head .sec-title { font-size: 26px; margin-top: 10px; }
            .story-head .eyebrow { display: none; }
            .steps { margin-top: 12px; }
            .step:not(.active) { display: none; }
            .step.active { transform: none; }
            .story-track { margin-top: 10px; }
            .stage-card { padding: 6px; border-radius: 24px; }
            .money-card { padding: 8px 10px; gap: 8px; border-radius: 14px; }
            .money-card .mc-ic { width: 28px; height: 28px; border-radius: 9px; }
            .money-card .mc-ic .lucide { width: 15px; height: 15px; }
            .money-card small { font-size: 10px; }
            .money-card em { font-size: 9px; }
            .toko-ov { height: 34px; padding: 0 12px; font-size: 12.5px; }
            .got-badge { font-size: 11px; padding: 6px 9px; }
            .radar-grid { gap: 10px; }
            .radar-wrap { padding: 34px 20px; order: 2; }
            .radar { max-width: 270px; }
            .float-card { padding: 7px 12px 7px 7px; gap: 8px; border-radius: 14px; }
            .float-card .ic { width: 32px; height: 32px; border-radius: 10px; }
            .float-card strong { font-size: 12.5px; }
            .float-card small { font-size: 10px; }
            .me { width: 62px; height: 62px; font-size: 12.5px; }
            .cats { grid-template-columns: repeat(2, 1fr); }
            .how-steps, .aman { grid-template-columns: 1fr; }
            .how-line { display: none; }
            .foot { grid-template-columns: 1fr 1fr; padding: 24px; }
            .foot > div:first-child { grid-column: 1 / -1; }
        }
        @media (max-width: 420px) {
            .seg button { padding: 11px 16px; font-size: 14px; }
            .hero-cta .btn { flex: 1 1 0; padding: 0 12px; }
            .final-card { padding: 56px 20px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .001ms !important; animation-iteration-count: 1 !important; transition-duration: .001ms !important; }
            html { scroll-behavior: auto; }
            .js [data-reveal] { opacity: 1; transform: none; }
        }
    </style>
</head>
<body data-app-url="{{ $appUrl }}">

{{-- ===================== NAV ===================== --}}
<header class="nav" id="nav" data-testid="site-nav">
    <div class="container nav-inner">
        <a href="{{ url('/') }}" class="brand" data-testid="nav-brand">
            <img src="{{ asset('images/logo.png') }}" onerror="this.src='https://res.cloudinary.com/fnf8f1pm/image/upload/v1793387471/gambar_logo_trerjo.png'" alt="Rentify Logo" class="brand-img">
            <span>Rentify</span>
        </a>

        <nav class="nav-links" aria-label="Navigasi utama">
            <a href="#perjalanan" data-testid="nav-link-perjalanan">Konsep</a>
            <a href="#kategori" data-testid="nav-link-kategori">Kategori</a>
            <a href="#cara-kerja" data-testid="nav-link-cara-kerja">Cara Kerja</a>
            <a href="#rasa-aman" data-testid="nav-link-rasa-aman">Rasa Aman</a>
        </nav>

        <div class="nav-actions">
            <button type="button" class="btn btn-light btn-sm hide-m" data-download data-testid="nav-download-btn"><i data-lucide="download"></i> Download Aplikasi</button>
            @auth
                <a href="{{ $links['home'] }}" class="btn btn-primary btn-sm hide-m" data-testid="nav-account-btn"><i data-lucide="shopping-bag"></i> Katalog Belanja</a>
            @else
                <a href="{{ $links['login'] }}" class="btn btn-primary btn-sm hide-m" data-testid="nav-login-btn">Masuk</a>
            @endauth
            <button type="button" class="burger" id="burger" aria-label="Buka menu" aria-expanded="false" aria-controls="mobile-menu" data-testid="nav-burger-btn"><i data-lucide="menu"></i></button>
        </div>
    </div>

    <div class="mobile-menu glass" id="mobile-menu" data-testid="mobile-menu">
        <a href="#perjalanan" data-testid="mobile-link-perjalanan"><i data-lucide="sparkles"></i> Konsep</a>
        <a href="#kategori" data-testid="mobile-link-kategori"><i data-lucide="layout-grid"></i> Kategori</a>
        <a href="#cara-kerja" data-testid="mobile-link-cara-kerja"><i data-lucide="route"></i> Cara Kerja</a>
        <a href="#rasa-aman" data-testid="mobile-link-rasa-aman"><i data-lucide="shield-check"></i> Rasa Aman</a>
        <button type="button" data-download data-testid="mobile-download-btn"><i data-lucide="download"></i> Download Aplikasi</button>
        <div class="mm-cta">
            <a href="{{ $links['search'] }}" class="btn btn-primary" data-testid="mobile-rent-btn">Mulai Menyewa</a>
            @auth
                <a href="{{ $links['home'] }}" class="btn btn-light" data-testid="mobile-account-btn"><i data-lucide="shopping-bag"></i> Katalog Belanja</a>
            @else
                <a href="{{ $links['login'] }}" class="btn btn-light" data-testid="mobile-login-btn">Masuk</a>
            @endauth
        </div>
    </div>
    <div class="nav-progress" id="nav-progress"></div>
</header>

<main>
{{-- ===================== HERO ===================== --}}
<section class="hero" data-testid="hero-section">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow" style="opacity:0;animation:fadeUp .7s .05s ease forwards"><span class="dot-live"></span> Marketplace sewa-menyewa untuk semua barang</span>
            <h1 class="hero-title" data-testid="hero-title">
                <span class="word" style="--i:0">Sewa-menyewa</span>
                <span class="word" style="--i:1">antar</span>
                <span class="word" style="--i:2">teman,</span>
                <span class="word" style="--i:3">kini</span>
                <span class="word" style="--i:4">seluas</span>
                <span class="word uline" style="--i:5"><span class="grad-text">kotamu.</span><svg class="squiggle" viewBox="0 0 200 18" preserveAspectRatio="none" aria-hidden="true"><path d="M3 11 C 40 3, 70 16, 100 9 S 160 3, 197 10" fill="none" stroke="#0EA6E9" stroke-width="4" stroke-linecap="round" opacity=".55"/></svg></span>
            </h1>
            <p class="hero-sub" data-testid="hero-subtitle">Barang nganggur jadi penghasilan. Butuh sebentar, tinggal sewa.</p>
            <div class="hero-cta">
                <a href="{{ $links['search'] }}" class="btn btn-primary" data-testid="hero-rent-btn">Mulai Menyewa</a>
                <a href="{{ $links['shop'] }}" class="btn btn-primary" data-coins data-testid="hero-shop-btn">Buka Toko</a>
            </div>
            <p class="hero-note"><i data-lucide="check-circle-2"></i> Lihat-lihat dan mulai cari barang tanpa perlu login.</p>
        </div>

        <div class="hero-visual" id="hero-visual" aria-hidden="true" data-testid="hero-visual">
            <div class="glass map-card">
                <p class="map-title">Tersedia di Sekitarmu</p>
                <div class="map" data-testid="hero-radar-map">
                    <svg viewBox="0 0 400 400" preserveAspectRatio="xMidYMid slice">
                        <rect width="400" height="400" fill="#EEF5FA"/>
                        <path d="M-20 70 C 80 40, 140 120, 230 90 S 360 30, 420 60" stroke="#D3EAF8" stroke-width="22" fill="none"/>
                        <g fill="#E6EEF4">
                            <rect x="18" y="120" width="70" height="56" rx="8"/><rect x="104" y="120" width="84" height="56" rx="8"/><rect x="210" y="132" width="76" height="60" rx="8"/><rect x="306" y="112" width="80" height="70" rx="8"/>
                            <rect x="18" y="196" width="70" height="80" rx="8"/><rect x="104" y="196" width="84" height="44" rx="8"/><rect x="306" y="204" width="80" height="62" rx="8"/>
                            <rect x="18" y="296" width="96" height="86" rx="8"/><rect x="134" y="262" width="62" height="54" rx="8"/><rect x="216" y="290" width="92" height="90" rx="8"/><rect x="326" y="288" width="60" height="94" rx="8"/>
                        </g>
                        <g stroke="#FFFFFF" fill="none" stroke-linecap="round">
                            <path d="M0 106 H400" stroke-width="9"/><path d="M0 186 H400" stroke-width="7"/><path d="M0 284 H400" stroke-width="9"/>
                            <path d="M96 100 V400" stroke-width="8"/><path d="M200 100 V400" stroke-width="10"/><path d="M296 100 V400" stroke-width="7"/>
                            <path d="M0 400 L 200 186 L 400 0" stroke-width="6" opacity=".8"/><path d="M120 250 C 160 230, 250 250, 296 230" stroke-width="5" opacity=".8"/>
                        </g>
                    </svg>
                    <div class="mring r1"></div><div class="mring r2"></div><div class="mring r3"></div>
                    <div class="mwave" style="--d:0s"></div><div class="mwave" style="--d:1.4s"></div><div class="mwave" style="--d:2.8s"></div>
                    <span class="mdot" style="left:50%;top:20%;--d:0s"></span>
                    <span class="mdot" style="left:71%;top:31%;--d:.5s"></span>
                    <span class="mdot" style="left:26%;top:40%;--d:1s"></span>
                    <span class="mdot" style="left:78%;top:58%;--d:1.5s"></span>
                    <span class="mdot" style="left:34%;top:73%;--d:.8s"></span>
                    <span class="mdot" style="left:64%;top:77%;--d:2s"></span>
                    <span class="mdot s" style="left:42%;top:36%;--d:1.2s"></span>
                    <span class="mdot s" style="left:60%;top:42%;--d:.3s"></span>
                    <span class="mdot s" style="left:56%;top:62%;--d:1.8s"></span>
                    <span class="mdot s" style="left:22%;top:62%;--d:2.4s"></span>
                    <span class="mdot s" style="left:84%;top:42%;--d:.9s"></span>
                    <span class="mcenter"></span>
                </div>
            </div>
            <div class="item-pos p1" data-depth=".8">
                <div class="item-card" style="--dl:0s" data-testid="hero-card-guitar"><span class="item-ic"><i data-lucide="guitar"></i></span><div><small>Acoustic Guitar</small><strong>Fender Acoustasonic</strong><b>Rp25.000<span>/hari</span></b></div></div>
            </div>
            <div class="item-pos p2" data-depth="1.1">
                <div class="item-card" style="--dl:-1.8s" data-testid="hero-card-phone"><span class="item-ic"><i data-lucide="smartphone"></i></span><div><small>Smartphone 4G</small><strong>Google Pixel 7</strong><b>Rp75.000<span>/hari</span></b></div></div>
            </div>
            <div class="item-pos p3" data-depth=".6">
                <div class="item-card" style="--dl:-3.6s" data-testid="hero-card-camera"><span class="item-ic"><i data-lucide="camera"></i></span><div><small>Kamera Mirrorless</small><strong>Sony Alpha A7III</strong><b>Rp150.000<span>/hari</span></b></div></div>
            </div>
        </div>
    </div>
    <a href="#perjalanan" class="scroll-cue" data-testid="hero-scroll-cue"><span class="mouse"></span>Gulir untuk melihat perjalanan sebuah gitar</a>
</section>

{{-- ===================== STORY: perjalanan gitar (scroll-linked) ===================== --}}
<section class="story" id="perjalanan" data-testid="story-section">
    <div class="story-sticky">
        <div class="container story-grid">
            <div class="story-head">
                <span class="eyebrow"><i data-lucide="sparkles"></i> Begini Rentify bekerja</span>
                <h2 class="sec-title">Dari kamarmu, <span class="grad-text">ke seluruh kota.</span></h2>
                <ol class="steps" data-testid="story-steps">
                    <li class="step active"><span class="step-num">1</span><div><h3>Gitar nganggur di kamar</h3><p>Jarang dimainkan, cuma jadi pajangan. Pemiliknya memasangnya di Rentify.</p></div></li>
                    <li class="step"><span class="step-num">2</span><div><h3>Melintasi kotamu</h3><p>Seseorang di sudut lain kota butuh gitar untuk akhir pekan — dan menemukannya.</p></div></li>
                    <li class="step"><span class="step-num">3</span><div><h3>Uang sewa masuk</h3><p>Gitar dipakai, dikembalikan, dan pemiliknya dapat penghasilan dari barang yang tadinya diam.</p></div></li>
                    <li class="step"><span class="step-num">4</span><div><h3>Sekarang giliranmu</h3><p>Barang apa di rumahmu yang bisa ikut menghasilkan?</p></div></li>
                </ol>
                <div class="story-track"><span id="story-bar"></span></div>
            </div>

            <div class="glass stage-card">
                <div class="stage" data-testid="story-stage">
                    <svg viewBox="0 0 800 640" aria-hidden="true">
                        <defs>
                            <linearGradient id="gBlue" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#22A6E4"/><stop offset="1" stop-color="#0EA6E9"/></linearGradient>
                            <linearGradient id="gWood" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#E6F3FB"/><stop offset="1" stop-color="#9FD4F2"/></linearGradient>
                            <filter id="soft" x="-20%" y="-20%" width="140%" height="150%"><feDropShadow dx="0" dy="10" stdDeviation="12" flood-color="#0F2A43" flood-opacity=".16"/></filter>
                            <mask id="routeMask" maskUnits="userSpaceOnUse" x="0" y="0" width="800" height="640">
                                <path id="routeMaskPath" d="M205 140 C 360 120, 430 220, 400 300 S 330 440, 460 455 S 610 430, 660 520" fill="none" stroke="#fff" stroke-width="20" stroke-linecap="round"/>
                            </mask>
                        </defs>

                        {{-- city map --}}
                        <rect width="800" height="640" rx="26" fill="#EEF6FB"/>
                        <path d="M-20 590 C 150 530, 260 630, 420 590 S 680 500, 820 550" stroke="#D3EAF8" stroke-width="36" fill="none"/>
                        <g fill="#E1EEF7">
                            <rect x="352" y="22" width="60" height="96" rx="10"/><rect x="420" y="22" width="62" height="96" rx="10"/>
                            <rect x="352" y="128" width="130" height="104" rx="10"/>
                            <rect x="520" y="22" width="104" height="100" rx="10"/><rect x="520" y="132" width="104" height="100" rx="10"/>
                            <rect x="660" y="22" width="120" height="210" rx="10"/>
                            <rect x="22" y="270" width="140" height="102" rx="10"/><rect x="178" y="270" width="134" height="102" rx="10"/>
                            <rect x="352" y="270" width="130" height="102" rx="10"/><rect x="520" y="270" width="260" height="102" rx="10"/>
                            <rect x="22" y="410" width="140" height="96" rx="10"/><rect x="178" y="410" width="134" height="96" rx="10"/>
                            <rect x="352" y="410" width="130" height="96" rx="10"/>
                        </g>
                        <g fill="#DCEBF7"><circle cx="572" cy="182" r="16"/><circle cx="598" cy="166" r="12"/><circle cx="420" cy="350" r="13"/><circle cx="110" cy="470" r="14"/><circle cx="270" cy="300" r="11"/><circle cx="720" cy="110" r="15"/></g>
                        <g stroke="#fff" stroke-width="14" stroke-linecap="round" fill="none" opacity=".95">
                            <path d="M0 251 H800"/><path d="M0 391 H800"/><path d="M332 0 V640"/><path d="M501 0 V640"/><path d="M642 0 V251"/>
                        </g>

                        {{-- route --}}
                        <path id="route" d="M205 140 C 360 120, 430 220, 400 300 S 330 440, 460 455 S 610 430, 660 520" fill="none" stroke="#A9D8F3" stroke-width="6" stroke-linecap="round" stroke-dasharray="0 16"/>
                        <path d="M205 140 C 360 120, 430 220, 400 300 S 330 440, 460 455 S 610 430, 660 520" fill="none" stroke="#0EA6E9" stroke-width="8" stroke-linecap="round" stroke-dasharray="0 16" mask="url(#routeMask)"/>
                        <g id="pins"></g>

                        {{-- owner room --}}
                        <rect x="22" y="22" width="254" height="200" rx="24" fill="#fff" filter="url(#soft)"/>
                        <rect x="44" y="50" width="72" height="62" rx="12" fill="#D3EAF8"/>
                        <circle cx="98" cy="70" r="9" fill="#FFFFFF"/>
                        <path d="M80 50 V112 M44 81 H116" stroke="#fff" stroke-width="4"/>
                        <rect x="44" y="164" width="140" height="40" rx="12" fill="#E6F3FB"/>
                        <rect x="52" y="152" width="44" height="20" rx="9" fill="#fff" stroke="#E6F3FB" stroke-width="3"/>
                        <rect x="136" y="96" width="44" height="6" rx="3" fill="#D3E0EA"/>
                        <rect x="140" y="78" width="8" height="18" rx="2" fill="#A9D8F3"/><rect x="151" y="82" width="8" height="14" rx="2" fill="#CFE6F5"/><rect x="162" y="76" width="8" height="20" rx="2" fill="#E6F3FB"/>
                        <rect x="236" y="182" width="24" height="24" rx="6" fill="#A9D8F3"/>
                        <ellipse cx="242" cy="172" rx="7" ry="14" fill="#A9D8F3" transform="rotate(-20 242 172)"/><ellipse cx="254" cy="170" rx="7" ry="14" fill="#0EA6E9" transform="rotate(20 254 170)"/>
                        <rect x="146" y="34" width="116" height="24" rx="12" fill="#E6F3FB"/>
                        <text x="204" y="50" text-anchor="middle" font-size="12" font-weight="700" fill="#0EA6E9" font-family="Plus Jakarta Sans, sans-serif">Kamar pemilik</text>
                        <circle cx="205" cy="84" r="3" fill="#9FB6C8"/>

                        {{-- renter --}}
                        <rect x="516" y="420" width="262" height="198" rx="24" fill="#fff" filter="url(#soft)"/>
                        <rect x="540" y="496" width="46" height="96" rx="10" fill="#E6F3FB"/>
                        <circle cx="577" cy="546" r="3.5" fill="#A9D8F3"/>
                        <rect x="602" y="470" width="44" height="40" rx="9" fill="#D3EAF8"/>
                        <circle cx="712" cy="484" r="20" fill="#D3EAF8"/>
                        <path d="M692 480 C 692 460, 732 456, 733 480 C 726 470, 704 468, 692 480 Z" fill="#0F2A43"/>
                        <rect x="684" y="508" width="56" height="84" rx="24" fill="url(#gBlue)"/>
                        <path d="M688 530 C 672 540, 664 528, 660 520" stroke="#D3EAF8" stroke-width="9" stroke-linecap="round" fill="none"/>
                        <rect x="530" y="434" width="88" height="24" rx="12" fill="#E6F3FB"/>
                        <text x="574" y="450" text-anchor="middle" font-size="12" font-weight="700" fill="#0EA6E9" font-family="Plus Jakarta Sans, sans-serif">Penyewa</text>

                        {{-- trail + guitar --}}
                        <g id="trail" fill="#22A6E4"><circle class="trail" r="6"/><circle class="trail" r="5"/><circle class="trail" r="4.2"/><circle class="trail" r="3.5"/><circle class="trail" r="2.8"/><circle class="trail" r="2.2"/></g>
                        <ellipse id="guitar-shadow" rx="28" ry="6" fill="#0F2A43" opacity="0"/>
                        <g id="guitar" transform="translate(205 140) rotate(-20)">
                            <g transform="translate(0 22)">
                                <rect x="-3.5" y="-70" width="7" height="58" rx="3" fill="#4A6378"/>
                                <rect x="-7.5" y="-88" width="15" height="22" rx="5" fill="#0F2A43"/>
                                <g fill="#E6EEF4"><circle cx="-10" cy="-82" r="2.4"/><circle cx="10" cy="-82" r="2.4"/><circle cx="-10" cy="-73" r="2.4"/><circle cx="10" cy="-73" r="2.4"/></g>
                                <path d="M0 -18 C -20 -18 -23 -4 -14 4 C -30 12 -30 42 0 44 C 30 42 30 12 14 4 C 23 -4 20 -18 0 -18 Z" fill="url(#gWood)" stroke="#0EA6E9" stroke-width="1.6"/>
                                <circle cx="0" cy="8" r="7.5" fill="#0F2A43"/>
                                <rect x="-11" y="27" width="22" height="5" rx="2.5" fill="#0F2A43"/>
                                <g stroke="#FFFFFF" stroke-width=".8"><line x1="-2.2" y1="-86" x2="-2.2" y2="29"/><line x1="0" y1="-86" x2="0" y2="29"/><line x1="2.2" y1="-86" x2="2.2" y2="29"/></g>
                            </g>
                        </g>
                    </svg>

                    <div class="money-card" id="money-card" data-testid="story-money-card">
                        <span class="mc-ic"><i data-lucide="wallet"></i></span>
                        <div><small>Uang sewa masuk</small><strong id="money-amt" data-testid="story-money-amount">Rp 0</strong><em>contoh ilustrasi</em></div>
                    </div>
                    <span class="got-badge" id="got-badge"><i data-lucide="check-circle-2"></i> Gitar sampai</span>
                    <a href="{{ $links['shop'] }}" class="btn btn-primary btn-sm toko-ov" id="toko-ov" data-coins data-testid="story-shop-btn"><i data-lucide="store"></i> Buka Toko</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== RADAR + KATEGORI ===================== --}}
<section id="kategori" style="padding:90px 0 40px" data-testid="radar-section">
    <div class="container">
        <div class="radar-grid">
            <div class="radar-wrap">
                <div class="radar" id="radar" aria-hidden="true" data-testid="radar-visual">
                    <div class="radar-bg"></div>
                    <div class="ring r1"></div><div class="ring r2"></div><div class="ring r3"></div>
                    <div class="sweep"></div>
                    <div class="pulse" style="--d:0s"></div><div class="pulse" style="--d:1.2s"></div><div class="pulse" style="--d:2.4s"></div>
                    <div class="me">Kamu</div>
                    <div class="orbit">
                        <div class="orbit-item" style="left:50%;top:4%"><div class="counter"><div class="float-card" style="--dl:0s"><span class="ic"><i data-lucide="guitar"></i></span><div><strong>Gitar</strong><small>Siap disewa</small></div></div></div></div>
                        <div class="orbit-item" style="left:92%;top:70%"><div class="counter"><div class="float-card" style="--dl:-1.3s"><span class="ic"><i data-lucide="smartphone"></i></span><div><strong>HP</strong><small>Siap disewa</small></div></div></div></div>
                        <div class="orbit-item" style="left:9%;top:72%"><div class="counter"><div class="float-card" style="--dl:-2.6s"><span class="ic"><i data-lucide="camera"></i></span><div><strong>Kamera</strong><small>Siap disewa</small></div></div></div></div>
                    </div>
                </div>
            </div>
            <div data-reveal>
                <span class="eyebrow"><i data-lucide="radar"></i> Ada di sekitarmu</span>
                <h2 class="sec-title">Bukan cuma alat camping. <span class="grad-text">Semua bisa disewa.</span></h2>
                <p class="sec-sub">Gitar tetangga, kamera teman kantor, bor milik warga satu kompleks. Rentify mempertemukan orang yang punya barang dengan orang yang butuh — di kota yang sama, jadi serah terimanya dekat dan mudah.</p>
            </div>
        </div>

        <div class="cats" data-reveal data-testid="category-grid">
            <a class="cat" href="{{ $links['search'] }}?category=musik" data-testid="category-card-musik"><span class="ci"><i data-lucide="guitar"></i></span><i data-lucide="arrow-up-right" class="arrow"></i><div><strong>Musik</strong><span>Gitar, keyboard, speaker</span></div></a>
            <a class="cat" href="{{ $links['search'] }}?category=elektronik" data-testid="category-card-elektronik"><span class="ci"><i data-lucide="smartphone"></i></span><i data-lucide="arrow-up-right" class="arrow"></i><div><strong>Elektronik</strong><span>HP, laptop, konsol game</span></div></a>
            <a class="cat" href="{{ $links['search'] }}?category=kamera" data-testid="category-card-kamera"><span class="ci"><i data-lucide="camera"></i></span><i data-lucide="arrow-up-right" class="arrow"></i><div><strong>Kamera</strong><span>Kamera, lensa, drone</span></div></a>
            <a class="cat" href="{{ $links['search'] }}?category=outdoor" data-testid="category-card-outdoor"><span class="ci"><i data-lucide="tent"></i></span><i data-lucide="arrow-up-right" class="arrow"></i><div><strong>Outdoor</strong><span>Tenda, carrier, kompor</span></div></a>
            <a class="cat" href="{{ $links['search'] }}?category=kendaraan" data-testid="category-card-kendaraan"><span class="ci"><i data-lucide="bike"></i></span><i data-lucide="arrow-up-right" class="arrow"></i><div><strong>Kendaraan</strong><span>Sepeda, motor, mobil</span></div></a>
            <a class="cat" href="{{ $links['search'] }}?category=acara" data-testid="category-card-acara"><span class="ci"><i data-lucide="party-popper"></i></span><i data-lucide="arrow-up-right" class="arrow"></i><div><strong>Acara</strong><span>Sound system, dekorasi</span></div></a>
            <a class="cat" href="{{ $links['search'] }}?category=perkakas" data-testid="category-card-perkakas"><span class="ci"><i data-lucide="drill"></i></span><i data-lucide="arrow-up-right" class="arrow"></i><div><strong>Perkakas</strong><span>Bor, tangga, alat kebun</span></div></a>
            <a class="cat" href="{{ $links['search'] }}?category=olahraga" data-testid="category-card-olahraga"><span class="ci"><i data-lucide="dumbbell"></i></span><i data-lucide="arrow-up-right" class="arrow"></i><div><strong>Olahraga</strong><span>Raket, papan, alat fitness</span></div></a>
            <a class="cat" href="{{ $links['search'] }}?category=bayi-anak" data-testid="category-card-bayi-anak"><span class="ci"><i data-lucide="baby"></i></span><i data-lucide="arrow-up-right" class="arrow"></i><div><strong>Bayi &amp; Anak</strong><span>Stroller, car seat, mainan</span></div></a>
            <a class="cat" href="{{ $links['search'] }}?category=rumah-tangga" data-testid="category-card-rumah-tangga"><span class="ci"><i data-lucide="sofa"></i></span><i data-lucide="arrow-up-right" class="arrow"></i><div><strong>Rumah Tangga</strong><span>Proyektor, vacuum, furnitur</span></div></a>
        </div>
    </div>
</section>

{{-- ===================== CARA KERJA ===================== --}}
<section id="cara-kerja" style="padding:90px 0 40px" data-testid="how-section">
    <div class="container center">
        <div data-reveal>
            <span class="eyebrow"><i data-lucide="route"></i> Cara Kerja</span>
            <h2 class="sec-title">Tiga langkah, dari dua sisi.</h2>
            <p class="sec-sub">Pilih peranmu. Kamu bisa jadi penyewa hari ini dan pemilik toko besok.</p>
        </div>
        <div class="seg" id="how-seg" data-role="penyewa" role="tablist" aria-label="Pilih peran" data-testid="how-role-toggle">
            <span class="seg-thumb" aria-hidden="true"></span>
            <button type="button" role="tab" data-role="penyewa" aria-selected="true" data-testid="how-role-penyewa"><i data-lucide="shopping-bag"></i> Penyewa</button>
            <button type="button" role="tab" data-role="pemilik" aria-selected="false" data-testid="how-role-pemilik"><i data-lucide="store"></i> Pemilik</button>
        </div>

        <div data-reveal>
            <div class="how-steps" data-testid="how-steps">
                <div class="how-line" aria-hidden="true"></div>
                <article class="glass how-card" data-testid="how-card-1"><span class="how-num">01</span><span class="how-ic"><i data-lucide="search"></i></span><h3>Cari</h3><p>Temukan barang yang kamu butuhkan dari orang-orang di kotamu.</p></article>
                <article class="glass how-card" data-testid="how-card-2"><span class="how-num">02</span><span class="how-ic"><i data-lucide="shield-check"></i></span><h3>Bayar aman</h3><p>Bayar lewat Rentify. Uangmu ditahan aman sampai serah terima beres.</p></article>
                <article class="glass how-card" data-testid="how-card-3"><span class="how-num">03</span><span class="how-ic"><i data-lucide="repeat"></i></span><h3>Ambil &amp; kembalikan</h3><p>Ambil barangnya, pakai seperlunya, lalu kembalikan sesuai jadwal.</p></article>
            </div>
        </div>
        <a href="{{ $links['search'] }}" class="btn btn-primary" id="how-cta" style="margin-top:36px" data-search="{{ $links['search'] }}" data-shop="{{ $links['shop'] }}" data-testid="how-cta-btn"><span>Mulai Menyewa</span> <i data-lucide="arrow-right"></i></a>
    </div>
</section>

{{-- ===================== RASA AMAN ===================== --}}
<section id="rasa-aman" style="padding:90px 0 40px" data-testid="safety-section">
    <div class="container">
        <div data-reveal>
            <span class="eyebrow"><i data-lucide="shield-check"></i> Rasa Aman</span>
            <h2 class="sec-title">Sewa ke orang baru, <span class="grad-text">tetap tenang.</span></h2>
            <p class="sec-sub">Tiga lapis perlindungan supaya pemilik dan penyewa sama-sama nyaman.</p>
        </div>
        <div class="aman">
            <article class="glass aman-card" data-testid="safety-card-escrow">
                <div class="aman-art">
                    <svg viewBox="0 0 210 150" aria-hidden="true">
                        <g class="a-coin tb"><circle cx="105" cy="26" r="13" fill="#FFFFFF" stroke="#0EA6E9" stroke-width="3"/><text x="105" y="30" text-anchor="middle" font-size="10" font-weight="800" fill="#4A6378" font-family="Plus Jakarta Sans, sans-serif">Rp</text></g>
                        <rect x="55" y="60" width="100" height="78" rx="18" fill="#FFFFFF" stroke="#0EA6E9" stroke-width="3"/>
                        <rect x="88" y="66" width="34" height="6" rx="3" fill="#0EA6E9" opacity=".3"/>
                        <path class="a-shackle tb" d="M93 100 v-9 a12 12 0 0 1 24 0 v9" stroke="#0EA6E9" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                        <rect x="87" y="98" width="36" height="28" rx="7" fill="#E6F3FB" stroke="#0EA6E9" stroke-width="2.5"/>
                        <circle cx="105" cy="110" r="4" fill="#0EA6E9"/><rect x="103.5" y="111" width="3" height="8" rx="1.5" fill="#0EA6E9"/>
                        <g class="a-check tb"><circle cx="154" cy="62" r="15" fill="#0EA6E9"/><path d="M147 62 l5 5 l9 -10" stroke="#fff" stroke-width="3.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></g>
                    </svg>
                </div>
                <h3>Uang ditahan aman (escrow)</h3>
                <p>Pembayaran penyewa disimpan dulu oleh Rentify dan baru diteruskan ke pemilik setelah serah terima selesai.</p>
            </article>
            <article class="glass aman-card" data-testid="safety-card-identity">
                <div class="aman-art">
                    <svg viewBox="0 0 210 150" aria-hidden="true">
                        <rect x="38" y="32" width="134" height="88" rx="14" fill="#fff" stroke="#D3E7F5" stroke-width="2"/>
                        <rect x="52" y="48" width="38" height="46" rx="8" fill="#E6F3FB"/>
                        <circle cx="71" cy="64" r="8" fill="#A9D8F3"/><path d="M57 92 c2 -12 26 -12 28 0" fill="#A9D8F3"/>
                        <rect x="100" y="52" width="58" height="7" rx="3.5" fill="#D3E0EA"/><rect x="100" y="66" width="44" height="7" rx="3.5" fill="#E6EEF4"/><rect x="100" y="80" width="52" height="7" rx="3.5" fill="#E6EEF4"/>
                        <rect x="52" y="102" width="60" height="6" rx="3" fill="#E6EEF4"/>
                        <rect class="a-scan" x="34" y="32" width="142" height="4" rx="2" fill="#22A6E4" opacity=".85"/>
                        <g class="a-idcheck tb"><circle cx="170" cy="118" r="15" fill="#0EA6E9"/><path d="M163 118 l5 5 l9 -10" stroke="#fff" stroke-width="3.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></g>
                    </svg>
                </div>
                <h3>Identitas terverifikasi</h3>
                <p>Pengguna memverifikasi identitasnya, jadi kamu tahu dengan siapa kamu bertransaksi.</p>
            </article>
            <article class="glass aman-card" data-testid="safety-card-photo">
                <div class="aman-art">
                    <svg viewBox="0 0 210 150" aria-hidden="true">
                        <g class="a-photo tb">
                            <rect x="76" y="86" width="58" height="56" rx="6" fill="#fff" stroke="#E6EEF4" stroke-width="2"/>
                            <rect x="82" y="92" width="46" height="34" rx="4" fill="#D3EAF8"/>
                            <path d="M82 126 l14 -14 l10 9 l8 -7 l14 12 z" fill="#A9D8F3"/>
                            <circle cx="118" cy="101" r="4" fill="#FFFFFF"/>
                        </g>
                        <rect x="68" y="30" width="20" height="12" rx="4" fill="#E6F3FB" stroke="#0EA6E9" stroke-width="2.5"/>
                        <rect x="58" y="38" width="94" height="62" rx="14" fill="#FFFFFF" stroke="#0EA6E9" stroke-width="3"/>
                        <circle cx="105" cy="69" r="20" fill="#E6F3FB" stroke="#0EA6E9" stroke-width="2.5"/><circle cx="105" cy="69" r="10" fill="none" stroke="#0EA6E9" stroke-width="2.5"/><circle cx="100" cy="64" r="3" fill="#0EA6E9" opacity=".35"/>
                        <circle cx="138" cy="50" r="4" fill="#0EA6E9" opacity=".5"/>
                        <rect class="a-flash" x="-20" y="-20" width="250" height="190" fill="#fff" opacity="0"/>
                    </svg>
                </div>
                <h3>Foto serah terima</h3>
                <p>Kondisi barang difoto saat diserahkan dan dikembalikan, jadi semuanya jelas dan adil untuk kedua pihak.</p>
            </article>
        </div>
    </div>
</section>

{{-- ===================== AJAKAN AKHIR ===================== --}}
<section style="padding:90px 0 30px" data-testid="final-cta-section">
    <div class="container">
        <div class="glass final-card" data-reveal>
            <span class="fi" style="left:6%;top:16%;--dl:0s"><i data-lucide="guitar"></i></span>
            <span class="fi" style="right:8%;top:12%;--dl:-2s"><i data-lucide="camera"></i></span>
            <span class="fi" style="left:12%;bottom:14%;--dl:-4s"><i data-lucide="tent"></i></span>
            <span class="fi" style="right:12%;bottom:16%;--dl:-6s"><i data-lucide="bike"></i></span>
            <span class="eyebrow"><i data-lucide="heart-handshake"></i> Mulai hari ini</span>
            <h2 style="margin-top:18px">Punya barang? Butuh barang?<br><span class="grad-text">Rentify jawabannya.</span></h2>
            <p>Sewa barang yang kamu butuhkan sebentar, atau buka toko dari barang yang jarang kamu pakai.</p>
            <div class="final-cta">
                <a href="{{ $links['search'] }}" class="btn btn-primary" data-testid="final-rent-btn"><i data-lucide="search"></i> Mulai Menyewa</a>
                <a href="{{ $links['shop'] }}" class="btn btn-light" data-coins data-testid="final-shop-btn"><i data-lucide="store"></i> Buka Toko</a>
                <button type="button" class="btn btn-light" data-download data-testid="final-download-btn"><i data-lucide="download"></i> Download Aplikasi</button>
            </div>
        </div>
    </div>
</section>
</main>

<footer data-testid="site-footer">
    <div class="container">
        <div class="glass foot">
            <div>
                <a href="{{ url('/') }}" class="brand" style="margin-bottom: 4px;">
                    <img src="{{ asset('images/logo.png') }}" onerror="this.src='https://res.cloudinary.com/fnf8f1pm/image/upload/v1793387471/gambar_logo_trerjo.png'" alt="Rentify Logo" class="brand-img">
                    <span>Rentify</span>
                </a>
                <p>Marketplace sewa-menyewa untuk semua jenis barang. Barang nganggur jadi penghasilan, butuh sebentar tinggal sewa.</p>
            </div>
            <div>
                <h4>Mulai</h4>
                <ul>
                    <li><a href="{{ $links['search'] }}" data-testid="footer-rent-link">Mulai Menyewa</a></li>
                    <li><a href="{{ $links['shop'] }}" data-testid="footer-shop-link">Buka Toko</a></li>
                    <li><a href="{{ $links['login'] }}" data-testid="footer-login-link">Masuk</a></li>
                    <li><button type="button" data-download data-testid="footer-download-btn">Download Aplikasi</button></li>
                </ul>
            </div>
            <div>
                <h4>Kenali Rentify</h4>
                <ul>
                    <li><a href="#perjalanan">Konsep</a></li>
                    <li><a href="#kategori">Kategori</a></li>
                    <li><a href="#cara-kerja">Cara Kerja</a></li>
                    <li><a href="#rasa-aman">Rasa Aman</a></li>
                </ul>
            </div>
        </div>
        <p class="copy">&copy; {{ date('Y') }} Rentify. Sewa-menyewa antar teman, kini seluas kotamu.</p>
    </div>
</footer>

{{-- Download dialog (aplikasi belum rilis) --}}
<dialog class="dl" id="download-dialog" aria-labelledby="dl-title" data-testid="download-dialog">
    <div class="glass dl-card">
        <span class="dl-ic"><i data-lucide="smartphone"></i></span>
        <h3 id="dl-title">Aplikasi Rentify segera hadir</h3>
        <p>Kami sedang menyiapkan aplikasinya. Sementara itu, semua fitur Rentify sudah bisa dipakai langsung dari browser di HP maupun laptop.</p>
        <div class="dl-actions">
            <a href="{{ $links['search'] }}" class="btn btn-primary" data-testid="download-dialog-web-btn">Pakai versi web</a>
            <button type="button" class="btn btn-light" data-close-download data-testid="download-dialog-close-btn">Tutup</button>
        </div>
    </div>
</dialog>

<div id="coin-layer" aria-hidden="true"></div>

<script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>
<script>
(() => {
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => [...r.querySelectorAll(s)];
    const clamp = (v, a = 0, b = 1) => Math.min(b, Math.max(a, v));
    const ease = t => 1 - Math.pow(1 - t, 3);
    const easeIO = t => t < .5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
    const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    const icons = () => window.lucide && window.lucide.createIcons();
    icons();

    /* Mobile menu */
    const burger = $('#burger'), menu = $('#mobile-menu');
    const setMenu = open => {
        menu.classList.toggle('open', open);
        burger.setAttribute('aria-expanded', String(open));
        burger.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
        burger.innerHTML = `<i data-lucide="${open ? 'x' : 'menu'}"></i>`;
        icons();
    };
    burger.addEventListener('click', () => setMenu(!menu.classList.contains('open')));
    $$('a', menu).forEach(a => a.addEventListener('click', () => setMenu(false)));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') setMenu(false); });
    document.addEventListener('click', e => {
        if (!menu.classList.contains('open')) return;
        if (!e.composedPath().some(n => n === menu || n === burger)) setMenu(false);
    });

    /* Coin burst */
    const layer = $('#coin-layer');
    const burst = (el, n = 18) => {
        if (reduce) return;
        const r = el.getBoundingClientRect();
        const cx = r.left + r.width / 2, cy = r.top + r.height / 2;
        for (let i = 0; i < n; i++) {
            const c = document.createElement('span');
            c.className = 'coin'; c.textContent = 'Rp';
            c.style.left = (cx - 13) + 'px'; c.style.top = (cy - 13) + 'px';
            layer.appendChild(c);
            const a = -Math.PI / 2 + (Math.random() - .5) * Math.PI * 1.4;
            const v = 70 + Math.random() * 130;
            const dx = Math.cos(a) * v, dy = Math.sin(a) * v;
            c.animate([
                { transform: 'translate(0,0) scale(.3) rotateY(0deg)', opacity: 1 },
                { transform: `translate(${dx}px, ${dy}px) scale(1) rotateY(360deg)`, opacity: 1, offset: .5 },
                { transform: `translate(${dx * 1.3}px, ${dy + 170}px) scale(.7) rotateY(720deg)`, opacity: 0 }
            ], { duration: 1000 + Math.random() * 500, easing: 'cubic-bezier(.2,.7,.4,1)' }).onfinish = () => c.remove();
        }
    };
    let lastHover = 0;
    document.addEventListener('mouseover', e => {
        const el = e.target.closest('[data-coins]');
        if (!el || el.contains(e.relatedTarget)) return;
        const now = Date.now();
        if (now - lastHover > 700) { lastHover = now; burst(el, 8); }
    });
    document.addEventListener('click', e => {
        const el = e.target.closest('[data-coins]');
        if (!el || reduce || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
        e.preventDefault();
        burst(el, 24);
        setTimeout(() => { window.location.href = el.href; }, 450);
    });

    /* Download Aplikasi: link ke app bila sudah ada, kalau belum tampilkan info */
    const appUrl = document.body.dataset.appUrl;
    const dlg = $('#download-dialog');
    const closeDlg = () => (dlg.close ? dlg.close() : dlg.removeAttribute('open'));
    document.addEventListener('click', e => {
        if (!e.target.closest('[data-download]')) return;
        e.preventDefault();
        setMenu(false);
        if (appUrl) { window.open(appUrl, '_blank', 'noopener'); return; }
        dlg.showModal ? dlg.showModal() : dlg.setAttribute('open', '');
    });
    $$('[data-close-download]').forEach(b => b.addEventListener('click', closeDlg));
    dlg.addEventListener('click', e => { if (e.target === dlg) closeDlg(); });

    /* Hero parallax */
    const cloud = $('#hero-visual');
    if (cloud && !reduce && matchMedia('(pointer: fine)').matches) {
        const items = $$('.item-pos', cloud);
        window.addEventListener('mousemove', e => {
            const x = e.clientX / innerWidth - .5, y = e.clientY / innerHeight - .5;
            items.forEach(it => { const d = +it.dataset.depth; it.style.transform = `translate(${x * d * -22}px, ${y * d * -22}px)`; });
        }, { passive: true });
    }

    /* Story: perjalanan gitar */
    const story = $('#perjalanan');
    const route = $('#route'), maskPath = $('#routeMaskPath');
    const L = route.getTotalLength();
    maskPath.style.strokeDasharray = L;
    maskPath.style.strokeDashoffset = L;
    const guitar = $('#guitar'), shadow = $('#guitar-shadow'), trail = $$('.trail');
    const money = $('#money-card'), amt = $('#money-amt'), toko = $('#toko-ov'), got = $('#got-badge');
    const steps = $$('.step', story), storyBar = $('#story-bar');
    const pins = $('#pins');
    [.3, .64].forEach(t => {
        const p = route.getPointAtLength(L * t);
        pins.insertAdjacentHTML('beforeend', `<circle class="route-pin" cx="${p.x}" cy="${p.y}" r="10"/><circle cx="${p.x}" cy="${p.y}" r="6" fill="#fff" stroke="#0EA6E9" stroke-width="3"/>`);
    });
    let burstDone = false;
    const renderStory = p => {
        const tr = easeIO(clamp((p - .16) / .48));
        const pt = route.getPointAtLength(L * tr);
        const lift = Math.sin(Math.PI * tr);
        const flying = tr > 0 && tr < 1;
        const idle = p < .16 ? Math.sin(p * 120) * 7 : 0;
        const rot = -20 + 45 * tr + Math.sin(tr * Math.PI * 4) * 14 * lift + idle;
        guitar.setAttribute('transform', `translate(${pt.x} ${pt.y - lift * 46}) rotate(${rot}) scale(${1 + lift * .35})`);
        shadow.setAttribute('transform', `translate(${pt.x} ${pt.y + 52}) scale(${1 - lift * .45})`);
        shadow.setAttribute('opacity', flying ? (.22 - lift * .1).toFixed(3) : 0);
        trail.forEach((c, i) => {
            const tt = Math.max(0, tr - (i + 1) * .02);
            const q = route.getPointAtLength(L * tt);
            c.setAttribute('cx', q.x);
            c.setAttribute('cy', q.y - Math.sin(Math.PI * tt) * 46);
            c.setAttribute('opacity', flying ? ((1 - (i + 1) / (trail.length + 1)) * .8).toFixed(2) : 0);
        });
        maskPath.style.strokeDashoffset = L * (1 - tr);

        const g = clamp((p - .62) / .06);
        got.style.opacity = g;
        got.style.transform = `scale(${.6 + .4 * ease(g)})`;

        const m = clamp((p - .66) / .16);
        const mIn = ease(clamp(m * 3));
        money.style.opacity = mIn;
        money.style.transform = `translateY(${(1 - mIn) * 16}px)`;
        amt.textContent = rupiah(Math.round(75000 * ease(m) / 500) * 500);

        const k = clamp((p - .84) / .05);
        toko.style.opacity = k;
        toko.style.transform = `translateY(${(1 - k) * 12}px) scale(${.9 + .1 * k})`;
        toko.style.pointerEvents = k > .5 ? 'auto' : 'none';
        if (p > .9 && p < 1 && !burstDone) { burstDone = true; burst(toko, 22); }
        if (p < .8) burstDone = false;

        const idx = p < .16 ? 0 : p < .64 ? 1 : p < .84 ? 2 : 3;
        steps.forEach((s, i) => s.classList.toggle('active', i === idx));
        storyBar.style.transform = `scaleX(${p})`;
    };

    /* Scroll loop */
    const nav = $('#nav'), navBar = $('#nav-progress');
    let ticking = false;
    const onScroll = () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            ticking = false;
            const y = window.scrollY;
            nav.classList.toggle('scrolled', y > 10);
            const docH = document.documentElement.scrollHeight - innerHeight;
            navBar.style.transform = `scaleX(${docH > 0 ? y / docH : 0})`;
            const r = story.getBoundingClientRect();
            const total = story.offsetHeight - innerHeight;
            renderStory(clamp(-r.top / total));
        });
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    onScroll();

    /* Radar blips */
    const radar = $('#radar');
    let radarOn = false;
    new IntersectionObserver(([e]) => { radarOn = e.isIntersecting; }).observe(radar);
    if (!reduce) setInterval(() => {
        if (!radarOn || document.hidden) return;
        const a = Math.random() * Math.PI * 2, d = 12 + Math.random() * 26;
        const b = document.createElement('span');
        b.className = 'blip';
        b.style.left = (50 + Math.cos(a) * d) + '%';
        b.style.top = (50 + Math.sin(a) * d) + '%';
        radar.appendChild(b);
        setTimeout(() => b.remove(), 2400);
    }, 650);

    /* Cara kerja toggle */
    const HOW = {
        penyewa: [
            { icon: 'search', title: 'Cari', text: 'Temukan barang yang kamu butuhkan dari orang-orang di kotamu.' },
            { icon: 'shield-check', title: 'Bayar aman', text: 'Bayar lewat Rentify. Uangmu ditahan aman sampai serah terima beres.' },
            { icon: 'repeat', title: 'Ambil & kembalikan', text: 'Ambil barangnya, pakai seperlunya, lalu kembalikan sesuai jadwal.' },
        ],
        pemilik: [
            { icon: 'store', title: 'Buka toko', text: 'Daftar dan buka tokomu sendiri dalam beberapa menit.' },
            { icon: 'package-plus', title: 'Pasang barang', text: 'Foto barang yang jarang kamu pakai, lalu atur harga sewa dan jadwalnya.' },
            { icon: 'wallet', title: 'Terima sewa', text: 'Serahkan barangnya, dan uang sewa diteruskan ke kamu setelah transaksi selesai.' },
        ],
    };
    const seg = $('#how-seg'), cards = $$('.how-card'), howCta = $('#how-cta');
    const setRole = role => {
        if (seg.dataset.role === role) return;
        seg.dataset.role = role;
        $$('button', seg).forEach(b => b.setAttribute('aria-selected', String(b.dataset.role === role)));
        cards.forEach((c, i) => setTimeout(() => {
            c.classList.add('flip');
            setTimeout(() => {
                const d = HOW[role][i];
                $('.how-ic', c).innerHTML = `<i data-lucide="${d.icon}"></i>`;
                $('h3', c).textContent = d.title;
                $('p', c).textContent = d.text;
                icons();
                c.classList.remove('flip');
            }, 220);
        }, i * 90));
        const owner = role === 'pemilik';
        howCta.href = owner ? howCta.dataset.shop : howCta.dataset.search;
        $('span', howCta).textContent = owner ? 'Buka Toko' : 'Mulai Menyewa';
        howCta.toggleAttribute('data-coins', owner);
    };
    $$('button', seg).forEach(b => b.addEventListener('click', () => setRole(b.dataset.role)));

    /* Reveal on scroll */
    const io = new IntersectionObserver(entries => entries.forEach(e => {
        if (!e.isIntersecting) return;
        e.target.classList.add('is-in', 'in');
        io.unobserve(e.target);
    }), { threshold: .15, rootMargin: '0px 0px -40px 0px' });
    $$('[data-reveal], .aman-card').forEach(el => io.observe(el));
})();
</script>
</body>
</html>
