interface PembayaranDriverInterface
{
    public function createTransaction(array $data): array;
    public function verifyPayment(string $transactionId): bool;
    public function refund(string $transactionId, float $amount): bool;
}

class ManualQrisDriver implements PembayaranDriverInterface
{
    public function createTransaction(array $data): array {
        return ['status' => 'pending', 'qris_url' => config('rentify.qris_url')];
    }
    public function verifyPayment(string $transactionId): bool {
        return true; // Manual admin verification
    }
    public function refund(string $transactionId, float $amount): bool {
        return true; // Manual admin refund process
    }
}

// Future: class MidtransDriver implements PembayaranDriverInterface { ... }
// Future: class XenditDriver  implements PembayaranDriverInterface { ... }