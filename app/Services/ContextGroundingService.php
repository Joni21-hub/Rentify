<?php

namespace App\Services;

use App\DTOs\GroundingContextDTO;
use App\Models\Barang;
use App\Models\ChatSession;
use App\Models\Kategori;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContextGroundingService
{
    /**
     * Terminated order statuses that are no longer considered active.
     */
    protected const TERMINATED_STATUSES = [
        'selesai',
        'completed',
        'dibatalkan',
        'cancelled',
        'ditolak',
        'rejected',
        'expired',
        'kadaluarsa',
    ];

    /**
     * Retrieve complete dynamic grounding context for customer or anonymous guest from live DB state.
     * Includes anti-hallucination guard against non-existent order queries.
     *
     * @param int|null $userId
     * @param string|null $phoneNumber
     * @param string|null $orderQuery
     * @param array<string, mixed> $options
     * @return GroundingContextDTO
     */
    public function getCustomerGroundingContext(
        ?int $userId = null,
        ?string $phoneNumber = null,
        ?string $orderQuery = null,
        array $options = []
    ): GroundingContextDTO {
        $channel = (string) ($options['channel'] ?? 'web');
        $sessionToken = $options['session_token'] ?? null;
        $user = null;

        // 1. Resolve User Identity
        if ($userId !== null && $userId > 0) {
            $user = User::find($userId);
        }

        $phoneVariations = [];
        if (!empty($phoneNumber)) {
            $phoneVariations = $this->normalizePhoneNumber($phoneNumber);
            if (!$user) {
                $user = User::where(function (Builder $query) use ($phoneVariations) {
                    $query->whereIn('whatsapp', $phoneVariations['variations'])
                        ->orWhereIn('whatsapp_vendor', $phoneVariations['variations']);
                    if (!empty($phoneVariations['last9'])) {
                        $query->orWhere('whatsapp', 'like', "%{$phoneVariations['last9']}");
                    }
                })->first();
            }
        }

        // Check active chat session if sessionToken or phone provided
        $chatSession = null;
        if (!empty($sessionToken)) {
            $chatSession = ChatSession::where('session_token', $sessionToken)->first();
        }
        if (!$chatSession && !empty($phoneVariations['variations'])) {
            $chatSession = ChatSession::whereIn('phone_number', $phoneVariations['variations'])
                ->orderBy('last_interaction_at', 'desc')
                ->first();
        }

        if (!$user && $chatSession && $chatSession->user_id) {
            $user = User::find($chatSession->user_id);
        }

        // 2. Resolve Roles
        $roles = [];
        $primaryRole = 'guest';
        $isAuthenticated = false;

        if ($user) {
            $isAuthenticated = true;
            $userRole = strtolower(trim((string) $user->role));
            if ($userRole === 'admin') {
                $roles[] = 'admin';
                $roles[] = 'customer';
                $primaryRole = 'admin';
            } elseif ($userRole === 'vendor' || $user->vendor_status === 'approved' || !empty($user->vendor_name)) {
                $roles[] = 'vendor';
                $roles[] = 'customer';
                $primaryRole = 'vendor';
            } else {
                $roles[] = 'customer';
                $primaryRole = 'customer';
            }

            if (!empty($user->active_role)) {
                $primaryRole = $user->active_role;
            }
        } else {
            $roles = ['guest'];
            $primaryRole = 'guest';
        }

        // 3. Resolve Customer Orders (Active and Past)
        $ordersQuery = Penyewaan::query()->with(['details.barang', 'pembayaran', 'vendor']);

        $hasTarget = false;
        if ($user) {
            $ordersQuery->where(function (Builder $q) use ($user, $phoneVariations) {
                $q->where('user_id', $user->id);
                if (!empty($phoneVariations['variations'])) {
                    $q->orWhereIn('customer_whatsapp', $phoneVariations['variations']);
                }
            });
            $hasTarget = true;
        } elseif (!empty($phoneVariations['variations'])) {
            $ordersQuery->where(function (Builder $q) use ($phoneVariations) {
                $q->whereIn('customer_whatsapp', $phoneVariations['variations']);
                if (!empty($phoneVariations['last9'])) {
                    $q->orWhere('customer_whatsapp', 'like', "%{$phoneVariations['last9']}");
                }
            });
            $hasTarget = true;
        }

        $allOrders = $hasTarget ? $ordersQuery->orderBy('created_at', 'desc')->get() : new \Illuminate\Support\Collection();

        $activeOrdersList = [];
        $orderHistoryList = [];

        foreach ($allOrders as $order) {
            $formattedOrder = $this->formatOrderRecord($order);
            $normalizedStatus = strtolower(trim((string) $order->status));

            if (!in_array($normalizedStatus, self::TERMINATED_STATUSES, true)) {
                $activeOrdersList[] = $formattedOrder;
            } else {
                $orderHistoryList[] = $formattedOrder;
            }
        }

        // 4. Order Query Guard (Anti-Hallucination Guard)
        // Detect order query from explicit parameter or message options
        $cleanOrderQuery = !empty($orderQuery)
            ? trim($orderQuery)
            : $this->extractOrderQueryFromText((string) ($options['message'] ?? $options['query'] ?? ''));

        $queriedOrder = null;
        $orderFound = true;
        $missingRecordNotice = null;
        $isOrderGuardTriggered = false;

        if (!empty($cleanOrderQuery)) {
            // Find in live DB
            $matchedOrder = Penyewaan::with(['details.barang', 'pembayaran', 'vendor'])
                ->where(function (Builder $q) use ($cleanOrderQuery) {
                    $q->where('kode_booking', $cleanOrderQuery)
                        ->orWhere('kode_booking', 'like', "%{$cleanOrderQuery}%")
                        ->orWhere('payment_group_id', $cleanOrderQuery);
                    if (is_numeric($cleanOrderQuery)) {
                        $q->orWhere('id', (int) $cleanOrderQuery);
                    }
                })
                ->first();

            if ($matchedOrder) {
                $queriedOrder = $this->formatOrderRecord($matchedOrder);
                $orderFound = true;
                $isOrderGuardTriggered = false;
                $missingRecordNotice = null;
            } else {
                // GUARD TRIGGERED: Order not found in database!
                $orderFound = false;
                $isOrderGuardTriggered = true;
                $missingRecordNotice = "Pesanan dengan ID / Kode Booking '{$cleanOrderQuery}' tidak ditemukan dalam sistem kami. Mohon pastikan kembali dan berikan ID Pesanan atau Kode Booking yang valid.";
            }
        }

        // 5. Lead Profile Data
        $leadProfile = $chatSession?->metadata['lead_profile'] ?? ($options['lead_profile'] ?? null);

        return new GroundingContextDTO(
            userId: $user?->id,
            userName: $user?->name ?? $chatSession?->customer_name,
            phoneNumber: $user?->whatsapp ?? $chatSession?->phone_number ?? $phoneNumber,
            email: $user?->email,
            roles: $roles,
            primaryRole: $primaryRole,
            isAuthenticated: $isAuthenticated,
            activeOrders: $activeOrdersList,
            orderHistory: $orderHistoryList,
            queriedOrder: $queriedOrder,
            orderFound: $orderFound,
            missingRecordNotice: $missingRecordNotice,
            isOrderGuardTriggered: $isOrderGuardTriggered,
            leadProfile: $leadProfile,
            sessionToken: $sessionToken ?? $chatSession?->session_token,
            channel: $chatSession?->channel ?? $channel
        );
    }

    /**
     * Non-blocking progressive profiling: captures and stores partial customer profile
     * attributes (name, contact, duration, budget, etc.) into ChatSession metadata.
     * Never interrupts conversation flow or forces registration.
     *
     * @param string|ChatSession $sessionOrIdentifier
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function captureLeadProgressively(string|ChatSession $sessionOrIdentifier, array $attributes): array
    {
        $session = null;

        if ($sessionOrIdentifier instanceof ChatSession) {
            $session = $sessionOrIdentifier;
        } elseif (is_string($sessionOrIdentifier)) {
            // First try finding by session token
            $session = ChatSession::where('session_token', $sessionOrIdentifier)->first();

            // Next try finding by phone number
            if (!$session && preg_match('/^[0-9+ -]{7,20}$/', $sessionOrIdentifier)) {
                $phoneVars = $this->normalizePhoneNumber($sessionOrIdentifier);
                $session = ChatSession::whereIn('phone_number', $phoneVars['variations'])->first();
            }

            // If still not found, create a new active session gracefully
            if (!$session) {
                $isPhone = preg_match('/^[0-9+ -]{7,20}$/', $sessionOrIdentifier);
                $session = ChatSession::create([
                    'session_token' => $isPhone ? 'wa_' . md5($sessionOrIdentifier . microtime()) : $sessionOrIdentifier,
                    'channel' => $isPhone ? 'whatsapp' : 'web',
                    'phone_number' => $isPhone ? $sessionOrIdentifier : null,
                    'status' => 'active',
                    'metadata' => ['lead_profile' => []],
                    'last_interaction_at' => Carbon::now(),
                ]);
            }
        }

        if (!$session) {
            throw new \InvalidArgumentException('Unable to resolve or initialize chat session for lead capture.');
        }

        // Normalize incoming profile attributes
        $normalized = [];

        // Name
        $name = $attributes['name'] ?? $attributes['customer_name'] ?? $attributes['nama'] ?? null;
        if (!empty($name) && is_string($name)) {
            $normalized['name'] = trim($name);
            $session->customer_name = $normalized['name'];
        }

        // Contact (Phone / WhatsApp / Email)
        $contact = $attributes['contact'] ?? $attributes['phone'] ?? $attributes['phone_number'] ?? $attributes['whatsapp'] ?? $attributes['no_hp'] ?? null;
        if (!empty($contact) && is_string($contact)) {
            $normalized['contact'] = trim($contact);
            $session->phone_number = $normalized['contact'];
        }

        $email = $attributes['email'] ?? null;
        if (!empty($email) && is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $normalized['email'] = strtolower(trim($email));
        }

        // Rental Duration (Days or descriptive string)
        $duration = $attributes['duration'] ?? $attributes['duration_days'] ?? $attributes['durasi'] ?? $attributes['lama_sewa'] ?? null;
        if ($duration !== null) {
            if (is_numeric($duration)) {
                $normalized['duration_days'] = (int) $duration;
                $normalized['duration_text'] = "{$duration} hari";
            } else {
                $durStr = (string) $duration;
                $normalized['duration_text'] = trim($durStr);
                if (preg_match('/(\d+)\s*(?:hari|day|days|minggu|bulan)/i', $durStr, $m)) {
                    $multiplier = str_contains(strtolower($durStr), 'minggu') ? 7 : (str_contains(strtolower($durStr), 'bulan') ? 30 : 1);
                    $normalized['duration_days'] = ((int) $m[1]) * $multiplier;
                }
            }
        }

        // Budget
        $budget = $attributes['budget'] ?? $attributes['max_budget'] ?? $attributes['anggaran'] ?? null;
        if ($budget !== null) {
            if (is_numeric($budget)) {
                $normalized['budget_amount'] = (float) $budget;
                $normalized['budget_text'] = 'Rp ' . number_format((float) $budget, 0, ',', '.');
            } else {
                $budgetStr = (string) $budget;
                $normalized['budget_text'] = trim($budgetStr);
                $cleanNumeric = preg_replace('/[^0-9]/', '', $budgetStr);
                if (!empty($cleanNumeric)) {
                    $normalized['budget_amount'] = (float) $cleanNumeric;
                }
            }
        }

        // Intent & Catalog Interest
        if (!empty($attributes['intent'])) {
            $normalized['intent'] = (string) $attributes['intent'];
        }
        if (!empty($attributes['category']) || !empty($attributes['kategori'])) {
            $normalized['category'] = (string) ($attributes['category'] ?? $attributes['kategori']);
        }
        if (!empty($attributes['item']) || !empty($attributes['item_name']) || !empty($attributes['barang'])) {
            $normalized['item'] = (string) ($attributes['item'] ?? $attributes['item_name'] ?? $attributes['barang']);
        }
        if (!empty($attributes['location']) || !empty($attributes['lokasi'])) {
            $normalized['location'] = (string) ($attributes['location'] ?? $attributes['lokasi']);
        }

        // Merge with existing lead profile without losing previous data
        $metadata = $session->metadata ?? [];
        $currentLead = $metadata['lead_profile'] ?? [];
        $mergedLead = array_merge($currentLead, $normalized, [
            'last_updated_at' => Carbon::now()->toIso8601String(),
        ]);

        $metadata['lead_profile'] = $mergedLead;
        $session->metadata = $metadata;
        $session->last_interaction_at = Carbon::now();
        $session->save();

        return [
            'success' => true,
            'session_id' => $session->id,
            'session_token' => $session->session_token,
            'customer_name' => $session->customer_name,
            'phone_number' => $session->phone_number,
            'lead_profile' => $mergedLead,
            'is_complete' => $this->isLeadProfileComplete($mergedLead),
            'captured_attributes' => array_keys($normalized),
        ];
    }

    /**
     * Generate commercial up-selling and cross-selling recommendations for a catalog item.
     * Includes multi-day package discount tiers and complementary accessories.
     *
     * @param Barang|int|string $barangOrId
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function getRecommendations(Barang|int|string $barangOrId, array $options = []): array
    {
        $barang = null;

        if ($barangOrId instanceof Barang) {
            $barang = $barangOrId;
        } elseif (is_numeric($barangOrId)) {
            $barang = Barang::with(['kategori', 'vendor'])->find((int) $barangOrId);
        } elseif (is_string($barangOrId)) {
            $barang = Barang::with(['kategori', 'vendor'])
                ->where('slug', $barangOrId)
                ->orWhere('nama', 'like', "%{$barangOrId}%")
                ->first();
        }

        if (!$barang) {
            throw new \InvalidArgumentException("Produk barang tidak ditemukan untuk rekomendasi.");
        }

        $baseDailyRate = (float) $barang->harga_sewa_harian;
        // Rentify 5% customer markup standard
        $customerDailyRate = (float) ($barang->harga_sewa_customer ?? round($baseDailyRate * 1.05));

        // 1. Up-Selling: Multi-Day Package Discounts
        $multiDayPackages = [
            [
                'package_tier' => '3_days',
                'name' => 'Paket Hemat 3 Hari (Long Weekend)',
                'duration_days' => 3,
                'discount_percent' => 5,
                'daily_rate_normal' => $customerDailyRate,
                'daily_rate_discounted' => round($customerDailyRate * 0.95),
                'normal_total' => $customerDailyRate * 3,
                'package_total' => round(($customerDailyRate * 3) * 0.95),
                'savings' => ($customerDailyRate * 3) - round(($customerDailyRate * 3) * 0.95),
                'pitch' => "Sewa 3 hari lebih santai! Hemat Rp " . number_format(($customerDailyRate * 3) - round(($customerDailyRate * 3) * 0.95), 0, ',', '.') . " dengan tarif diskon paket.",
            ],
            [
                'package_tier' => 'weekly',
                'name' => 'Paket Mingguan (7 Hari - Diskon 15%)',
                'duration_days' => 7,
                'discount_percent' => 15,
                'daily_rate_normal' => $customerDailyRate,
                'daily_rate_discounted' => round($customerDailyRate * 0.85),
                'normal_total' => $customerDailyRate * 7,
                'package_total' => round(($customerDailyRate * 7) * 0.85),
                'savings' => ($customerDailyRate * 7) - round(($customerDailyRate * 7) * 0.85),
                'pitch' => "Sewa seminggu penuh hemat 15%! Potongan total Rp " . number_format(($customerDailyRate * 7) - round(($customerDailyRate * 7) * 0.85), 0, ',', '.') . ".",
            ],
            [
                'package_tier' => 'bi_weekly',
                'name' => 'Paket 2 Minggu (14 Hari - Diskon 20%)',
                'duration_days' => 14,
                'discount_percent' => 20,
                'daily_rate_normal' => $customerDailyRate,
                'daily_rate_discounted' => round($customerDailyRate * 0.80),
                'normal_total' => $customerDailyRate * 14,
                'package_total' => round(($customerDailyRate * 14) * 0.80),
                'savings' => ($customerDailyRate * 14) - round(($customerDailyRate * 14) * 0.80),
                'pitch' => "Sewa 14 hari untuk liburan/proyek panjang! Hemat Rp " . number_format(($customerDailyRate * 14) - round(($customerDailyRate * 14) * 0.80), 0, ',', '.') . ".",
            ],
            [
                'package_tier' => 'monthly',
                'name' => 'Paket Bulanan Ultimate (30 Hari - Diskon 30%)',
                'duration_days' => 30,
                'discount_percent' => 30,
                'daily_rate_normal' => $customerDailyRate,
                'daily_rate_discounted' => round($customerDailyRate * 0.70),
                'normal_total' => $customerDailyRate * 30,
                'package_total' => round(($customerDailyRate * 30) * 0.70),
                'savings' => ($customerDailyRate * 30) - round(($customerDailyRate * 30) * 0.70),
                'pitch' => "Paket Bulanan super ekonomis diskon 30%! Hemat hingga Rp " . number_format(($customerDailyRate * 30) - round(($customerDailyRate * 30) * 0.70), 0, ',', '.') . ".",
            ],
        ];

        // 2. Up-Selling: Premium Alternatives in same category
        $premiumQuery = Barang::query()
            ->where('id', '!=', $barang->id)
            ->where('harga_sewa_harian', '>', $baseDailyRate);

        if ($barang->kategori_id) {
            $premiumQuery->where('kategori_id', $barang->kategori_id);
        }

        $premiumItems = $premiumQuery->orderBy('harga_sewa_harian', 'asc')
            ->take(3)
            ->get();

        $premiumAlternatives = $premiumItems->map(function (Barang $item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'kategori' => $item->kategori?->nama,
                'harga_sewa_harian' => (float) $item->harga_sewa_harian,
                'harga_sewa_customer' => (float) ($item->harga_sewa_customer ?? round($item->harga_sewa_harian * 1.05)),
                'kondisi' => $item->kondisi,
                'cover_photo' => $item->cover_photo,
                'alasan_upgrade' => "Performa & spesifikasi lebih tinggi untuk hasil maksimal.",
            ];
        })->toArray();

        // 3. Cross-Selling: Compatible Accessories and Add-Ons
        $accessoryKeywords = $this->getAccessoryKeywordsForProduct($barang);
        $catalogAccessories = [];

        if (!empty($accessoryKeywords)) {
            $accQuery = Barang::query()->where('id', '!=', $barang->id);
            $accQuery->where(function (Builder $q) use ($accessoryKeywords) {
                foreach ($accessoryKeywords as $kw) {
                    $q->orWhere('nama', 'like', "%{$kw}%")
                        ->orWhere('deskripsi', 'like', "%{$kw}%");
                }
            });

            $foundAcc = $accQuery->take(4)->get();
            $catalogAccessories = $foundAcc->map(function (Barang $item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'kategori' => $item->kategori?->nama,
                    'harga_sewa_harian' => (float) $item->harga_sewa_harian,
                    'harga_sewa_customer' => (float) ($item->harga_sewa_customer ?? round($item->harga_sewa_harian * 1.05)),
                    'tipe' => 'live_catalog_addon',
                ];
            })->toArray();
        }

        // Curated accessory suggestions for conversational chatbot bundling
        $curatedAddons = $this->getCuratedAddonsForProduct($barang);

        // 4. Cross-Selling: Catalog Alternatives in same category (excluding already recommended up-sell & accessories)
        $excludeIds = [$barang->id];
        foreach ($premiumAlternatives as $pa) {
            $excludeIds[] = $pa['id'];
        }
        foreach ($catalogAccessories as $ca) {
            $excludeIds[] = $ca['id'];
        }

        $altQuery = Barang::query()
            ->whereNotIn('id', array_unique($excludeIds));

        if ($barang->kategori_id) {
            $altQuery->where('kategori_id', $barang->kategori_id);
        }

        $catalogAlternatives = $altQuery->take(4)->get()->map(function (Barang $item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'kategori' => $item->kategori?->nama,
                'harga_sewa_harian' => (float) $item->harga_sewa_harian,
                'harga_sewa_customer' => (float) ($item->harga_sewa_customer ?? round($item->harga_sewa_harian * 1.05)),
                'kondisi' => $item->kondisi,
            ];
        })->toArray();

        return [
            'target_product' => [
                'id' => $barang->id,
                'nama' => $barang->nama,
                'kategori' => $barang->kategori?->nama,
                'harga_sewa_harian' => $baseDailyRate,
                'harga_sewa_customer' => $customerDailyRate,
                'stok_total' => $barang->stok_total,
                'status' => $barang->status ?? 'tersedia',
            ],
            'upselling' => [
                'multi_day_packages' => $multiDayPackages,
                'premium_alternatives' => $premiumAlternatives,
            ],
            'cross_selling' => [
                'catalog_accessories' => $catalogAccessories,
                'curated_addons' => $curatedAddons,
                'catalog_alternatives' => $catalogAlternatives,
            ],
            'commercial_pitch' => "Sewa multi-hari lebih hemat hingga 30% dengan Paket Mingguan/Bulanan. Dapatkan juga aksesoris pelengkap untuk pengalaman sewa tanpa repot!",
        ];
    }

    /**
     * Search catalog for anonymous inquiries without requiring customer login.
     *
     * @param string $keyword
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function searchCatalog(string $keyword = '', array $filters = []): array
    {
        $query = Barang::query()->with(['kategori', 'vendor']);

        $cleanKw = trim($keyword);
        if ($cleanKw !== '') {
            $query->where(function (Builder $q) use ($cleanKw) {
                $q->where('nama', 'like', "%{$cleanKw}%")
                    ->orWhere('deskripsi', 'like', "%{$cleanKw}%")
                    ->orWhere('slug', 'like', "%{$cleanKw}%");
            });
        }

        if (!empty($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('harga_sewa_harian', '>=', (float) $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('harga_sewa_harian', '<=', (float) $filters['max_price']);
        }

        $items = $query->take((int) ($filters['limit'] ?? 10))->get();

        return [
            'total_found' => $items->count(),
            'keyword' => $cleanKw,
            'items' => $items->map(function (Barang $b) {
                return [
                    'id' => $b->id,
                    'nama' => $b->nama,
                    'slug' => $b->slug,
                    'kategori' => $b->kategori?->nama,
                    'vendor_name' => $b->vendor?->vendor_name ?? $b->vendor?->name,
                    'harga_sewa_harian' => (float) $b->harga_sewa_harian,
                    'harga_sewa_customer' => (float) ($b->harga_sewa_customer ?? round($b->harga_sewa_harian * 1.05)),
                    'stok_total' => $b->stok_total,
                    'kondisi' => $b->kondisi,
                    'cover_photo' => $b->cover_photo,
                ];
            })->toArray(),
        ];
    }

    /**
     * Format a Penyewaan Eloquent record into a structured array.
     *
     * @param Penyewaan $order
     * @return array<string, mixed>
     */
    protected function formatOrderRecord(Penyewaan $order): array
    {
        $paymentState = $this->resolvePaymentState($order);

        $items = [];
        if ($order->relationLoaded('details') || $order->details()->exists()) {
            foreach ($order->details as $item) {
                $barang = $item->barang;
                $items[] = [
                    'product_id' => $item->product_id,
                    'nama' => $barang ? $barang->nama : ($item->product_name ?? 'Item Produk'),
                    'harga_sewa' => (float) ($item->harga_sewa ?? $item->price ?? 0),
                    'jumlah' => (int) ($item->jumlah ?? $item->quantity ?? 1),
                ];
            }
        }

        return [
            'id' => $order->id,
            'kode_booking' => $order->kode_booking,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'payment_state' => $paymentState,
            'is_paid' => in_array($paymentState, ['paid', 'Lunas'], true),
            'total_biaya' => (float) ($order->total_biaya ?? $order->total_price ?? 0),
            'tanggal_mulai' => $order->tanggal_mulai ? $order->tanggal_mulai->format('Y-m-d') : null,
            'tanggal_selesai' => $order->tanggal_selesai ? $order->tanggal_selesai->format('Y-m-d') : null,
            'paid_at' => $order->paid_at ? $order->paid_at->toIso8601String() : null,
            'shipping_method' => $order->shipping_method ?? $order->metode_pengambilan,
            'customer_name' => $order->customer_name,
            'customer_whatsapp' => $order->customer_whatsapp,
            'items' => $items,
        ];
    }

    /**
     * Determine accurate live payment state based on Pembayaran record and order attributes.
     *
     * @param Penyewaan $order
     * @return string
     */
    public function resolvePaymentState(Penyewaan $order): string
    {
        // 1. Check related Pembayaran row if exists
        $pembayaran = $order->pembayaran;
        if ($pembayaran) {
            $status = strtoupper(trim((string) $pembayaran->status));
            if (in_array($status, ['PAID', 'SUKSES', 'BERHASIL', 'LUNAS'], true) || !empty($pembayaran->paid_at)) {
                return 'paid';
            }
            if (in_array($status, ['MENUNGGU KONFIRMASI', 'WAITING_CONFIRMATION', 'WAITING_VERIFICATION'], true)) {
                return 'waiting_confirmation';
            }
            if (in_array($status, ['PENDING', 'PENDING_PAYMENT', 'MENUNGGU PEMBAYARAN'], true)) {
                return 'pending';
            }
            if (in_array($status, ['FAILED', 'GAGAL'], true)) {
                return 'failed';
            }
            if (in_array($status, ['EXPIRED', 'KADALUARSA'], true)) {
                return 'expired';
            }
        }

        // 2. Check Order attributes
        if (!empty($order->paid_at)) {
            return 'paid';
        }

        $method = strtoupper(trim((string) $order->payment_method));
        if ($method === 'COD') {
            return 'cod';
        }

        $orderStatus = strtoupper(trim((string) $order->status));
        $normalizedOrderStatus = str_replace('_', ' ', $orderStatus);
        if (in_array($normalizedOrderStatus, ['PENDING PAYMENT', 'MENUNGGU PEMBAYARAN', 'PENDING'], true)) {
            return 'pending';
        }
        if (in_array($normalizedOrderStatus, ['MENUNGGU KONFIRMASI', 'WAITING CONFIRMATION'], true)) {
            return 'waiting_confirmation';
        }
        if (in_array($normalizedOrderStatus, ['DISETUJUI', 'SEDANG DISEWA', 'DIPROSES', 'DIKIRIM', 'SIAP DIAMBIL'], true)) {
            return 'paid';
        }

        return strtolower($orderStatus !== '' ? $orderStatus : 'pending');
    }

    /**
     * Normalize Indonesian phone number formats (08..., 62..., +62..., spaces, dashes).
     *
     * @param string $phone
     * @return array<string, mixed>
     */
    public function normalizePhoneNumber(string $phone): array
    {
        $digits = (string) preg_replace('/[^0-9]/', '', $phone);
        if ($digits === '') {
            return ['raw' => $phone, 'variations' => [$phone], 'last9' => ''];
        }

        $standard0 = $digits;
        $standard62 = $digits;

        if (str_starts_with($digits, '62')) {
            $standard0 = '0' . substr($digits, 2);
            $standard62 = $digits;
        } elseif (str_starts_with($digits, '0')) {
            $standard0 = $digits;
            $standard62 = '62' . substr($digits, 1);
        } else {
            $standard0 = '0' . $digits;
            $standard62 = '62' . $digits;
        }

        $variations = array_unique([
            $digits,
            $standard0,
            $standard62,
            '+' . $standard62,
            trim($phone),
        ]);

        $last9 = strlen($standard0) >= 9 ? substr($standard0, -9) : $standard0;

        return [
            'raw' => $phone,
            'standard_0' => $standard0,
            'standard_62' => $standard62,
            'variations' => array_values($variations),
            'last9' => $last9,
        ];
    }

    /**
     * Extract order ID / booking code pattern from free-form text.
     *
     * @param string $text
     * @return string|null
     */
    public function extractOrderQueryFromText(string $text): ?string
    {
        $clean = trim($text);
        if ($clean === '') {
            return null;
        }

        // Match patterns like RNT12345, RNT-2026-001, ORD-9988
        if (preg_match('/\b(RNT[A-Za-z0-9_-]+)\b/i', $clean, $m)) {
            return strtoupper($m[1]);
        }
        if (preg_match('/\b(ORD[A-Za-z0-9_-]+)\b/i', $clean, $m)) {
            return strtoupper($m[1]);
        }
        // Match explicit "#12345" or "pesanan 12345"
        if (preg_match('/(?:order|pesanan|booking|kode|no\.?|nomor)\s*[:#]?\s*([A-Za-z0-9_-]{3,30})/i', $clean, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    /**
     * Determine if a progressive lead profile has captured core fields.
     *
     * @param array<string, mixed> $leadProfile
     * @return bool
     */
    public function isLeadProfileComplete(array $leadProfile): bool
    {
        $hasName = !empty($leadProfile['name']);
        $hasContact = !empty($leadProfile['contact']) || !empty($leadProfile['email']);
        $hasDuration = !empty($leadProfile['duration_days']) || !empty($leadProfile['duration_text']);
        $hasBudget = !empty($leadProfile['budget_amount']) || !empty($leadProfile['budget_text']);

        return $hasName && $hasContact && $hasDuration && $hasBudget;
    }

    /**
     * Map accessory search keywords for a given product or its category.
     *
     * @param Barang $barang
     * @return array<int, string>
     */
    protected function getAccessoryKeywordsForProduct(Barang $barang): array
    {
        $nama = strtolower($barang->nama);
        $kategoriNama = strtolower($barang->kategori?->nama ?? '');

        if (str_contains($nama, 'kamera') || str_contains($nama, 'sony') || str_contains($nama, 'canon') || str_contains($nama, 'fujifilm') || str_contains($nama, 'lumix')) {
            return ['lensa', 'lens', 'tripod', 'memory', 'sd card', 'baterai', 'battery', 'gimbal', 'flash', 'lighting'];
        }

        if (str_contains($nama, 'tenda') || str_contains($nama, 'camping') || str_contains($kategoriNama, 'outdoor')) {
            return ['sleeping bag', 'matras', 'kompor', 'gas', 'lampu tenda', 'carrier', 'kursi lipat', 'flysheet'];
        }

        if (str_contains($nama, 'proyektor') || str_contains($nama, 'projector')) {
            return ['layar', 'screen', 'hdmi', 'pointer', 'speaker', 'kabel'];
        }

        if (str_contains($nama, 'badminton') || str_contains($nama, 'raket')) {
            return ['shuttlecock', 'kok', 'grip', 'tas raket'];
        }

        if (str_contains($nama, 'gitar') || str_contains($nama, 'guitar')) {
            return ['capo', 'senar', 'pick', 'stand', 'kabel jack', 'tas gitar'];
        }

        if (str_contains($kategoriNama, 'kendaraan') || str_contains($nama, 'motor') || str_contains($nama, 'mobil')) {
            return ['helm', 'jas hujan', 'holder', 'kunci gembok'];
        }

        return ['aksesoris', 'pelengkap', 'cadangan'];
    }

    /**
     * Curated add-on suggestions to help AI chatbot provide immediate bundling proposals.
     *
     * @param Barang $barang
     * @return array<int, array<string, mixed>>
     */
    protected function getCuratedAddonsForProduct(Barang $barang): array
    {
        $nama = strtolower($barang->nama);
        $kategoriNama = strtolower($barang->kategori?->nama ?? '');

        if (str_contains($nama, 'kamera') || str_contains($nama, 'sony') || str_contains($nama, 'canon') || str_contains($kategoriNama, 'elektronik')) {
            return [
                ['name' => 'Tripod Fleksibel & Kokoh', 'estimated_rate' => 25000, 'benefit' => 'Untuk kestabilan video & foto landscape'],
                ['name' => 'Baterai Cadangan Ekstra + Charger', 'estimated_rate' => 20000, 'benefit' => 'Shooting seharian tanpa khawatir lowbat'],
                ['name' => 'SD Card SanDisk Extreme 64GB (4K Ready)', 'estimated_rate' => 15000, 'benefit' => 'Kecepatan tulis tinggi anti-dropped frames'],
            ];
        }

        if (str_contains($nama, 'tenda') || str_contains($kategoriNama, 'outdoor')) {
            return [
                ['name' => 'Sleeping Bag Polar Hangat', 'estimated_rate' => 15000, 'benefit' => 'Tidur nyaman & hangat di ketinggian'],
                ['name' => 'Kompor Portable Mini + Gas Canister', 'estimated_rate' => 20000, 'benefit' => 'Praktis untuk masak air & mie di tenda'],
                ['name' => 'Lampu Tenda LED Rechargeable', 'estimated_rate' => 10000, 'benefit' => 'Penerangan terang & tahan lama'],
            ];
        }

        return [
            ['name' => 'Paket Perlengkapan Tambahan', 'estimated_rate' => 15000, 'benefit' => 'Perlindungan & kenyamanan ekstra selama sewa'],
        ];
    }
}
