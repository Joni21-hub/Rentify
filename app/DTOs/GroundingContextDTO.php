<?php

namespace App\DTOs;

class GroundingContextDTO
{
    /**
     * @param int|null $userId
     * @param string|null $userName
     * @param string|null $phoneNumber
     * @param string|null $email
     * @param array<int, string> $roles
     * @param string $primaryRole
     * @param bool $isAuthenticated
     * @param array<int, array<string, mixed>> $activeOrders
     * @param array<int, array<string, mixed>> $orderHistory
     * @param array<string, mixed>|null $queriedOrder
     * @param bool $orderFound
     * @param string|null $missingRecordNotice
     * @param bool $isOrderGuardTriggered
     * @param array<string, mixed>|null $leadProfile
     * @param string|null $sessionToken
     * @param string $channel
     */
    public function __construct(
        public ?int $userId = null,
        public ?string $userName = null,
        public ?string $phoneNumber = null,
        public ?string $email = null,
        public array $roles = ['guest'],
        public string $primaryRole = 'guest',
        public bool $isAuthenticated = false,
        public array $activeOrders = [],
        public array $orderHistory = [],
        public ?array $queriedOrder = null,
        public bool $orderFound = true,
        public ?string $missingRecordNotice = null,
        public bool $isOrderGuardTriggered = false,
        public ?array $leadProfile = null,
        public ?string $sessionToken = null,
        public string $channel = 'web'
    ) {
    }

    /**
     * Check if customer has any active ongoing orders.
     */
    public function hasActiveOrders(): bool
    {
        return !empty($this->activeOrders);
    }

    /**
     * Check if user is an unauthenticated guest.
     */
    public function isGuest(): bool
    {
        return !$this->isAuthenticated;
    }

    /**
     * Check whether anti-hallucination guard is triggered for missing order inquiry.
     */
    public function hasOrderGuard(): bool
    {
        return $this->isOrderGuardTriggered || ($this->missingRecordNotice !== null);
    }

    /**
     * Retrieve missing record notice if guard triggered.
     */
    public function getMissingRecordNotice(): ?string
    {
        return $this->missingRecordNotice;
    }

    /**
     * Retrieve active orders.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActiveOrders(): array
    {
        return $this->activeOrders;
    }

    /**
     * Retrieve queried order if exists.
     *
     * @return array<string, mixed>|null
     */
    public function getQueriedOrder(): ?array
    {
        return $this->queriedOrder;
    }

    /**
     * Convert DTO to array structure.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'phone_number' => $this->phoneNumber,
            'email' => $this->email,
            'roles' => $this->roles,
            'primary_role' => $this->primaryRole,
            'is_authenticated' => $this->isAuthenticated,
            'active_orders' => $this->activeOrders,
            'order_history' => $this->orderHistory,
            'queried_order' => $this->queriedOrder,
            'order_found' => $this->orderFound,
            'missing_record_notice' => $this->missingRecordNotice,
            'is_order_guard_triggered' => $this->isOrderGuardTriggered,
            'lead_profile' => $this->leadProfile,
            'session_token' => $this->sessionToken,
            'channel' => $this->channel,
        ];
    }

    /**
     * Format grounded database context into an LLM system prompt / instruction block.
     * Enforces anti-hallucination order checks strictly.
     */
    public function toPromptContext(): string
    {
        $lines = [];
        $lines[] = '=== [RENTIFY DYNAMIC CONTEXT GROUNDING (VERIFIED LIVE DB)] ===';

        // Identity & Role Grounding
        $identity = $this->isAuthenticated
            ? "Terautentikasi (ID: {$this->userId}, Nama: {$this->userName}, Kontak: {$this->phoneNumber}, Role: {$this->primaryRole})"
            : "Tamu Anonim (Guest/Prospek Lead, Saluran: {$this->channel})";
        $lines[] = "Status Pengguna: {$identity}";
        $lines[] = 'Role Terdaftar: ' . implode(', ', $this->roles);

        // Anti-Hallucination Order Query Guard
        if ($this->hasOrderGuard()) {
            $lines[] = '';
            $lines[] = '⚠️ [PERINGATAN SISTEM: ORDER QUERY GUARD AKTIF - ANTI-HALUSINASI] ⚠️';
            $lines[] = "Pemberitahuan Sistem: {$this->missingRecordNotice}";
            $lines[] = 'ATURAN KETAT CHATBOT:';
            $lines[] = '1. Dilarang keras mengarang, berasumsi, atau membuat data tiruan tentang pesanan yang tidak ditemukan.';
            $lines[] = '2. Sampaikan dengan jelas, sopan, dan transparan bahwa nomor pesanan tersebut tidak terdaftar di sistem Rentify.';
            $lines[] = '3. Minta pengguna untuk memeriksa kembali dan memberikan ID Pesanan / Kode Booking yang valid (misal: RNT...) atau nomor telepon terdaftar.';
        } elseif ($this->queriedOrder !== null) {
            $lines[] = '';
            $lines[] = '✅ [DETAIL PESANAN SPESIFIK TERVERIFIKASI DARI DATABASE]';
            $lines[] = "- Kode Booking: " . ($this->queriedOrder['kode_booking'] ?? '-');
            $lines[] = "- Status Pesanan: " . ($this->queriedOrder['status'] ?? '-');
            $lines[] = "- Status Pembayaran: " . ($this->queriedOrder['payment_state'] ?? '-');
            $lines[] = "- Metode Pembayaran: " . ($this->queriedOrder['payment_method'] ?? '-');
            $lines[] = "- Total Biaya: Rp " . number_format((float) ($this->queriedOrder['total_biaya'] ?? 0), 0, ',', '.');
            $lines[] = "- Periode Sewa: " . ($this->queriedOrder['tanggal_mulai'] ?? '-') . ' s/d ' . ($this->queriedOrder['tanggal_selesai'] ?? '-');
            if (!empty($this->queriedOrder['items'])) {
                $lines[] = "- Daftar Barang: " . implode(', ', array_map(fn($item) => ($item['nama'] ?? 'Item') . ' (x' . ($item['jumlah'] ?? 1) . ')', $this->queriedOrder['items']));
            }
        }

        // Active Orders Grounding
        if (!empty($this->activeOrders)) {
            $lines[] = '';
            $lines[] = '📋 [DAFTAR PESANAN AKTIF BERJALAN (' . count($this->activeOrders) . ' Pesanan)]';
            foreach ($this->activeOrders as $idx => $order) {
                $itemNames = !empty($order['items'])
                    ? implode(', ', array_map(fn($it) => $it['nama'] ?? 'Item', $order['items']))
                    : 'Barang Sewa';
                $num = $idx + 1;
                $orderCode = $order['kode_booking'] ?? '-';
                $orderStatus = $order['status'] ?? '-';
                $payState = $order['payment_state'] ?? '-';
                $totalFormatted = number_format((float) ($order['total_biaya'] ?? 0), 0, ',', '.');
                $lines[] = "  {$num}. Kode: {$orderCode} | Status: {$orderStatus} | Pembayaran: {$payState} | Biaya: Rp {$totalFormatted} | Item: {$itemNames}";
            }
        } elseif (!$this->hasOrderGuard()) {
            $lines[] = 'Pesanan Aktif: Tidak ada pesanan aktif saat ini.';
        }

        // Progressive Lead Profile Grounding
        if (!empty($this->leadProfile)) {
            $lines[] = '';
            $lines[] = '🎯 [PROFIL PROSPEK LEAD TERTANGKAP (PROGRESSIVE PROFILING)]';
            foreach ($this->leadProfile as $key => $val) {
                if (is_scalar($val) && $key !== 'last_captured_at') {
                    $lines[] = "- " . ucfirst(str_replace('_', ' ', $key)) . ": {$val}";
                }
            }
        }

        $lines[] = '=== [AKHIR GROUNDING DATA BASIS DATA] ===';

        return implode("\n", $lines);
    }

    /**
     * Alias for toPromptContext() for system prompt injection.
     */
    public function toSystemPrompt(): string
    {
        return $this->toPromptContext();
    }
}
