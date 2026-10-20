<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Gemini API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi integrasi Google Gemini API untuk AI Marketing Agent.
    | Menggunakan model gemini-1.5-flash sebagai default hemat dan cepat.
    |
    */
    'gemini' => [
        'api_key'     => env('GEMINI_API_KEY', ''),
        'model'       => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'temperature' => (float) env('GEMINI_TEMPERATURE', 0.7),
        'timeout'     => (int) env('GEMINI_TIMEOUT', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Aturan Otomasi Harian & Jadwal Bergantian (50:50 Customer vs Vendor)
    |--------------------------------------------------------------------------
    |
    | Setiap hari secara bergantian:
    | - Hari Ganjil (Odd Day): Edukasi & Promosi Menarik CUSTOMER (Penyewa)
    | - Hari Genap (Even Day): Edukasi & Promosi Menarik VENDOR (Pemilik Barang)
    |
    */
    'automation' => [
        'enabled'          => (bool) env('RENTIFY_AI_AUTOGEN_ENABLED', true),
        'schedule_time'    => env('RENTIFY_AI_SCHEDULE_TIME', '08:00'), // Jam generate otomatis harian
        'rotation_mode'    => 'odd_even_days', // 'odd_even_days'
        'auto_post_instagram' => (bool) env('RENTIFY_AI_AUTOPOST_INSTAGRAM', false),
        'auto_post_tiktok'    => (bool) env('RENTIFY_AI_AUTOPOST_TIKTOK', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Profil Merek & Web Platform Rentify
    |--------------------------------------------------------------------------
    |
    */
    'brand' => [
        'name'        => 'Rentify',
        'tagline'     => 'Sewa Mudah, Hemat, dan Dekat di Sekitar Anda',
        'website_url' => env('APP_URL', 'http://localhost:8000'),
        'cta_text'    => 'Kunjungi rentify untuk sewa atau sewakan barangmu sekarang!',
    ],

    /*
    |--------------------------------------------------------------------------
    | Target 1: Edukasi Customer (Penyewa) — Hari Ganjil
    |--------------------------------------------------------------------------
    |
    | Fokus: Edukasi orang butuh barang untuk keperluan sesekali/jarang dipakai
    | agar TIDAK PERLU BELI MAHAL, cukup sewa murah dan dekat di Rentify.
    |
    */
    'customer_target' => [
        'objective' => 'Mencegah pemborosan membeli barang yang hanya dipakai sesekali/jarang',
        'core_message' => 'Ngapain beli mahal kalau bisa sewa hemat di Rentify? Praktis, murah, dan siap pakai.',
        'sample_categories' => [
            'Alat Camping & Outdoor' => 'Tenda doom, matras, kompor portable, nesting, carrier',
            'Fotografi & Kamera Acara' => 'Kamera mirrorless, lensa tele, gimbal stabilizer, lighting, mic wireless',
            'Pertukangan & Alat Rumah' => 'Bor listrik impact, tangga lipat teleskopik, mesin las, jet cleaner',
            'Perlengkapan Bayi & Anak' => 'Stroller travelling, baby car seat, sterilizer botol, mainan playhouse',
            'Alat Pesta & Acara Komunitas' => 'Sound system portable, wireless mic, proyektor pro, layar standing, dispenser kopi',
            'Otomotif & Kendaraan Harian' => 'Motor matic harian, helm touring, action cam motor',
            'Musik & Seni' => 'Gitar akustik elektrik, keyboard piano, amplifier portable',
            'Gaming & Entertainment' => 'PlayStation 5, Nintendo Switch, VR headset, stik tambahan',
        ],
        'sample_hooks' => [
            'Mau camping sekali setahun masa harus beli tenda 2 juta?',
            'Ada acara kantor sehari, masa beli mic dan sound system baru?',
            'Mau bikin video cinematic tapi kamera 25 juta? Sewa aja 150rb di Rentify!',
            'Anak cepat besar, stroller beli mahal cuma kepake 3 bulan? Sewa aja bund!',
            'Mau benerin rumah akhir pekan, pinjem tetangga gak enak? Sewa bor 30 ribuan!',
            'Weekend pengen push rank PS5 bareng temen tapi belum punya konsol?',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Target 2: Edukasi Vendor (Pemilik Barang) — Hari Genap
    |--------------------------------------------------------------------------
    |
    | Fokus: Mengajak mahasiswa, bapak/ibu rumah tangga yang punya barang nganggur,
    | hingga pemilik toko rental besar untuk menyewakan barangnya di Rentify.
    |
    */
    'vendor_target' => [
        'objective' => 'Mengubah barang nganggur di rumah/gudang menjadi sumber passive income harian',
        'core_message' => 'Barang nganggur di rumah jangan cuma berdebu! Sewakan di Rentify, dapet cuan jutaan tiap bulan!',
        'sample_personas' => [
            'Mahasiswa' => 'Punya laptop spek dewa, kamera, Nintendo Switch, atau gitar yang jarang dipakai.',
            'Ibu Rumah Tangga' => 'Punya stroller bayi, box bayi, breastpump cadangan, alat catering/pesta yang menumpuk di gudang.',
            'Bapak-bapak / Pekerja' => 'Punya bor listrik, gerinda, tangga lipat, tenda camping, sepeda yang nganggur di garasi.',
            'Pemilik Toko Rental Fisik' => 'Punya rental kamera/outdoor/event offline yang ingin dapat pesanan online lebih banyak tanpa sewa ruko mahal.',
        ],
        'sample_hooks' => [
            'Kamera atau PS5 kamu nganggur di lemari? Sayang banget, harusnya bisa hasilin 1-2 juta per bulan!',
            'Stroller anak udah gak kepake dan menuh-menuhin kamar? Sewain di Rentify biar jadi uang belanja!',
            'Punya bor listrik atau tangga di gudang? Sewain ke tetangga sekitar via Rentify, aman dan bergaransi!',
            'Punya usaha rental offline yang sepi pengunjung? Daftarkan tokomu di Rentify, jangkau ribuan penyewa baru!',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Integrasi Media Sosial (Instagram Graph API & TikTok API)
    |--------------------------------------------------------------------------
    |
    */
    'social' => [
        'instagram' => [
            'access_token' => env('INSTAGRAM_ACCESS_TOKEN', ''),
            'account_id'   => env('INSTAGRAM_ACCOUNT_ID', ''),
            'graph_version' => env('INSTAGRAM_GRAPH_VERSION', 'v19.0'),
        ],
        'tiktok' => [
            'client_key'   => env('TIKTOK_CLIENT_KEY', ''),
            'client_secret' => env('TIKTOK_CLIENT_SECRET', ''),
            'access_token' => env('TIKTOK_ACCESS_TOKEN', ''),
        ],
    ],
];
