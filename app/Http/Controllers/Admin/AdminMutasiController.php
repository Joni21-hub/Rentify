<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mutasi;
use App\Services\MutasiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMutasiController extends Controller
{
    protected MutasiService $mutasiService;

    public function __construct(MutasiService $mutasiService)
    {
        $this->mutasiService = $mutasiService;
    }

    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'all');

        $query = Mutasi::with('order')->orderBy('created_at', 'desc');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $mutasis = $query->paginate(25);

        // Hitung statistik
        $totalMutasi     = Mutasi::count();
        $countMatched    = Mutasi::where('status', 'MATCHED')->count();
        $countUnmatched  = Mutasi::where('status', 'UNMATCHED')->count();
        $totalNominalMasuk = Mutasi::where('status', 'MATCHED')->sum('nominal');

        // Ambil daftar pesanan pending untuk opsi manual match
        $pendingOrders = DB::table('orders')
            ->whereIn('status', ['PENDING_PAYMENT', 'Menunggu Pembayaran', 'Menunggu Konfirmasi'])
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();

        $webhookUrl = url('/api/webhook/mutasi');
        $webhookSecret = config('mutasi.webhook_secret', env('MUTASI_WEBHOOK_SECRET', 'rentify_mutasi_secret_2026'));

        return view('admin.mutasi.index', compact(
            'mutasis',
            'statusFilter',
            'totalMutasi',
            'countMatched',
            'countUnmatched',
            'totalNominalMasuk',
            'pendingOrders',
            'webhookUrl',
            'webhookSecret'
        ));
    }

    /**
     * Jalankan simulasi notifikasi masuk (Testing langsung dari Admin)
     */
    public function simulate(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string',
            'nominal'  => 'required|numeric|min:1',
        ]);

        $app = $request->app_name;
        $nominal = (float) $request->nominal;
        $sender = $request->sender ?: 'Customer Test';
        $formattedNominal = number_format($nominal, 0, ',', '.');

        $content = match ($app) {
            'DANA'      => "Kamu menerima uang Rp {$formattedNominal} dari {$sender} via QRIS.",
            'BCA'       => "m-Transfer: Rp {$formattedNominal},00 dari {$sender} BERHASIL",
            'GOPAY'     => "Penerimaan saldo GoPay Rp {$formattedNominal} dari {$sender}",
            'SHOPEEPAY' => "Kamu menerima pembayaran sebesar Rp {$formattedNominal} dari {$sender}",
            default     => "Transfer masuk sebesar Rp {$formattedNominal} dari {$sender}",
        };

        $payload = [
            'app_name'     => $app,
            'package_name' => 'id.dana',
            'title'        => $app,
            'content'      => $content,
            'nominal'      => $nominal,
            'sender'       => $sender,
            'secret'       => config('mutasi.webhook_secret'),
        ];

        $result = $this->mutasiService->processIncoming($payload);

        if (!empty($result['matched'])) {
            return back()->with('success', "🎉 Simulasi Sukses! Mutasi Rp {$formattedNominal} berhasil dicocokkan otomatis dengan Pesanan #INV-{$result['order_id']} dan status berubah menjadi LUNAS!");
        }

        return back()->with('warning', "ℹ️ Simulasi tercatat (ID: {$result['mutasi_id']}), tetapi tidak ada pesanan pending dengan nominal Rp {$formattedNominal}. Pastikan sudah ada order dengan total tersebut.");
    }

    /**
     * Hubungkan mutasi yang unmatched secara manual ke sebuah pesanan
     */
    public function manualMatch(Request $request, $id)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        /** @var Mutasi $mutasi */
        $mutasi = Mutasi::findOrFail($id);
        $order = DB::table('orders')->where('id', $request->order_id)->first();

        if (!$order) {
            return back()->with('error', 'Pesanan tidak ditemukan.');
        }

        try {
            $this->mutasiService->confirmOrderPayment($order, $mutasi);

            return back()->with('success', "✅ Mutasi ID #{$mutasi->id} berhasil dihubungkan ke Pesanan #INV-{$order->id} dan diverifikasi lunas!");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghubungkan mutasi: ' . $e->getMessage());
        }
    }
}
