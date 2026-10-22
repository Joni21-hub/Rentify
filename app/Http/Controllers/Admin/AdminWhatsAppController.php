<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class AdminWhatsAppController extends Controller
{
    /**
     * Tampilkan halaman Pusat Kontrol WhatsApp Gateway
     */
    public function index()
    {
        $gatewayStatus = WhatsAppService::getGatewayStatus();

        return view('admin.whatsapp.index', [
            'status'        => $gatewayStatus['status'] ?? 'offline',
            'qr'            => $gatewayStatus['qr'] ?? null,
            'user'          => $gatewayStatus['user'] ?? null,
            'uptime'        => $gatewayStatus['uptime'] ?? 0,
            'provider'      => env('WHATSAPP_PROVIDER', 'self_hosted'),
            'gatewayUrl'    => env('WHATSAPP_GATEWAY_URL', 'http://127.0.0.1:3000'),
            'adminPhone'    => config('security.admin_phone', '083183494835'),
        ]);
    }

    /**
     * Endpoint polling status real-time untuk pemindaian QR Code (AJAX)
     */
    public function checkStatus()
    {
        return response()->json(WhatsAppService::getGatewayStatus());
    }

    /**
     * Kirim pesan uji coba ke nomor WhatsApp
     */
    public function testSend(Request $request)
    {
        $request->validate([
            'nomor' => 'required|string',
            'pesan' => 'required|string|max:1000',
        ]);

        $res = WhatsAppService::send($request->nomor, $request->pesan);

        if (is_array($res) && isset($res['status']) && $res['status'] === false) {
            $errMsg = $res['message'] ?? 'Terjadi kesalahan saat mengirim pesan.';
            return back()->with('error', "Gagal mengirim: {$errMsg}");
        }

        return back()->with('success', "Pesan uji coba berhasil dikirim ke {$request->nomor}!");
    }

    /**
     * Putuskan sesi WhatsApp aktif (Logout)
     */
    public function logout()
    {
        $success = WhatsAppService::logoutGateway();

        if ($success) {
            return back()->with('success', 'Sesi WhatsApp berhasil diputuskan. Silakan scan QR code baru untuk menghubungkan ulang.');
        }

        return back()->with('error', 'Gagal memutuskan sesi WhatsApp. Pastikan gateway sedang menyala.');
    }
}
