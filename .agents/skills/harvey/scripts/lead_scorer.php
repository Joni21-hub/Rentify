<?php
/**
 * Lead Scorer & Pipeline Processor (Rentify Edition - PHP CLI)
 * Adapted from EthanPlusAI Harvey for Indonesian Rental Ecosystem.
 *
 * Usage:
 *   php lead_scorer.php --demo
 *   php lead_scorer.php --input=leads.json --output=scored_leads.json --min-score=50
 */

declare(strict_types=1);

$tier1Categories = [
    'kamera'       => 35,
    'camera'       => 35,
    'outdoor'      => 35,
    'camping'      => 35,
    'kebaya'       => 30,
    'gaun'         => 30,
    'jas'          => 30,
    'sound'        => 30,
    'sound system' => 30,
    'ps5'          => 25,
    'playstation'  => 25,
    'genset'       => 25,
    'proyektor'    => 25,
];

$majorCities = [
    'jakarta', 'surabaya', 'bandung', 'malang', 'yogyakarta',
    'jogja', 'semarang', 'tangerang', 'bekasi', 'depok', 'bali', 'medan'
];

function cleanPhoneNumber(?string $rawPhone): ?string {
    if (!$rawPhone) {
        return null;
    }
    $digits = preg_replace('/\D/', '', $rawPhone);
    if (str_starts_with($digits, '08')) {
        $digits = '62' . substr($digits, 1);
    } elseif (str_starts_with($digits, '8')) {
        $digits = '62' . $digits;
    } elseif (str_starts_with($digits, '6208')) {
        $digits = '628' . substr($digits, 4);
    }

    if (str_starts_with($digits, '628') && strlen($digits) >= 11 && strlen($digits) <= 15) {
        return $digits;
    }
    return null;
}

function calculateIcpScore(array $prospect, array $tier1Categories, array $majorCities): int {
    $score = 0;
    $category = strtolower((string)($prospect['category'] ?? ''));
    $city     = strtolower((string)($prospect['city'] ?? ''));
    $signals  = (array)($prospect['signals'] ?? []);
    $rawPhone = (string)($prospect['phone'] ?? '');
    $ig       = (string)($prospect['instagram'] ?? '');
    $email    = (string)($prospect['email'] ?? '');

    // 1. Category Fit (Max 35)
    $catPoints = 15;
    foreach ($tier1Categories as $key => $pts) {
        if (str_contains($category, $key)) {
            $catPoints = max($catPoints, $pts);
        }
    }
    $score += $catPoints;

    // 2. Location Fit (Max 15)
    $inMajorCity = false;
    foreach ($majorCities as $c) {
        if (str_contains($city, $c)) {
            $inMajorCity = true;
            break;
        }
    }
    if ($inMajorCity) {
        $score += 15;
    } elseif (!empty($city)) {
        $score += 8;
    }

    // 3. Contact Quality (Max 25)
    $cleanWa = cleanPhoneNumber($rawPhone);
    if ($cleanWa) {
        $score += 20;
    }
    if (!empty($ig)) {
        $score += 5;
    }
    if (!empty($email) && !$cleanWa) {
        $score += 10;
    }

    // 4. Trigger Signals (Max 25)
    $signalWeights = [
        'MANUAL_BOOKING_SHEET'  => 15,
        'SLOW_RESPONSE_REVIEWS' => 15,
        'DOUBLE_BOOKING_RISK'   => 12,
        'WEEKDAY_IDLE_STOCK'    => 10,
        'SEASONAL_SURGE'        => 10,
        'NO_BOOKING_WEBSITE'    => 8,
        'EXPANDING_CATALOG'     => 7,
    ];
    $signalPoints = 0;
    foreach ($signals as $s) {
        $signalPoints += $signalWeights[$s] ?? 5;
    }
    $score += min($signalPoints, 25);

    // Disqualification guards
    if (!empty($prospect['is_closed'])) {
        return 0;
    }
    if (!$cleanWa && empty($ig) && empty($email)) {
        return 0;
    }
    if (!empty($prospect['is_competitor_platform'])) {
        return 5;
    }

    return min($score, 100);
}

function processLeads(array $leads, int $minScore, array $tier1Categories, array $majorCities): array {
    $scored = [];
    foreach ($leads as $lead) {
        $score = calculateIcpScore($lead, $tier1Categories, $majorCities);
        if ($score < $minScore) {
            continue;
        }

        $tier = 'COLD';
        if ($score >= 80) {
            $tier = 'HOT';
        } elseif ($score >= 60) {
            $tier = 'WARM';
        }

        $cleanWa = cleanPhoneNumber($lead['phone'] ?? '');
        $lead['normalized_phone'] = $cleanWa;
        $lead['icp_score'] = $score;
        $lead['lead_tier'] = $tier;
        $lead['ready_for_outbox'] = (bool)($cleanWa || !empty($lead['instagram']));
        $scored[] = $lead;
    }

    usort($scored, fn($a, $b) => $b['icp_score'] <=> $a['icp_score']);
    return $scored;
}

// CLI argument parsing
$options = getopt('', ['demo', 'input:', 'output:', 'min-score:']);
$isDemo = isset($options['demo']) || empty($options['input']);
$minScore = isset($options['min-score']) ? (int)$options['min-score'] : 40;

if ($isDemo) {
    fwrite(STDERR, "[*] Running Harvey Lead Scorer Demo with sample Indonesian rental prospects...\n");
    $demoLeads = [
        [
            'business_name' => 'Lensa Malang Rental Kamera',
            'category'      => 'Rental Kamera & Lensa',
            'city'          => 'Malang',
            'phone'         => '081234567890',
            'instagram'     => '@lensamalang',
            'signals'       => ['MANUAL_BOOKING_SHEET', 'SLOW_RESPONSE_REVIEWS'],
            'notes'         => 'Bio ada format WA teks manual, ulasan Google Maps bintang 4.2 keluhan chat slow respon.'
        ],
        [
            'business_name' => 'Bromo Camp Outdoor Gear',
            'category'      => 'Sewa Alat Camping & Outdoor',
            'city'          => 'Surabaya',
            'phone'         => '085711223344',
            'instagram'     => '@bromocamp_sby',
            'signals'       => ['WEEKDAY_IDLE_STOCK', 'NO_BOOKING_WEBSITE'],
            'notes'         => 'Ada promo paket sewa tenda weekday, belum ada link web booking.'
        ],
        [
            'business_name' => 'Kencana Kebaya Wisuda',
            'category'      => 'Sewa Kebaya & Busana',
            'city'          => 'Yogyakarta',
            'phone'         => '087899001122',
            'instagram'     => '@kencanakebaya_jogja',
            'signals'       => ['SEASONAL_SURGE', 'DOUBLE_BOOKING_RISK'],
            'notes'         => 'Musim wisuda kampus UGM/UNY ramai, katalog 40+ kebaya.'
        ],
        [
            'business_name' => 'Toko Jual Beli HP Bekas',
            'category'      => 'Elektronik Jual Beli',
            'city'          => 'Jakarta',
            'phone'         => '089988776655',
            'signals'       => [],
            'notes'         => 'Hanya toko jual-beli, bukan rental.'
        ]
    ];

    $results = processLeads($demoLeads, $minScore, $tier1Categories, $majorCities);
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(0);
}

$inputPath = (string)$options['input'];
if (!file_exists($inputPath)) {
    fwrite(STDERR, "[!] Error: Input file not found: {$inputPath}\n");
    exit(1);
}

$content = file_get_contents($inputPath);
$leads = json_decode($content, true);
if (!is_array($leads)) {
    fwrite(STDERR, "[!] Error: Invalid JSON in input file.\n");
    exit(1);
}

$results = processLeads($leads, $minScore, $tier1Categories, $majorCities);

if (!empty($options['output'])) {
    file_put_contents((string)$options['output'], json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    fwrite(STDERR, "[✓] Scored " . count($results) . " leads. Saved to " . $options['output'] . "\n");
} else {
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
