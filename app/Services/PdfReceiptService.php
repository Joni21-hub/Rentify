<?php
namespace App\Services;

use App\Models\Penyewaan;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfReceiptService
{
    public function generate(Penyewaan $penyewaan, string $action = 'download')
    {
        $penyewaan->load([
            'customer.user',
            'details.barang.vendor',
            'pembayaran',
            'cabang',
        ]);

        $pdf = Pdf::loadView('pdf.receipt', compact('penyewaan'))
            ->setPaper('a4', 'portrait');

        $filename = "RENTIFY-{$penyewaan->kode_booking}.pdf";

        return match($action) {
            'stream'   => $pdf->stream($filename),
            'download' => $pdf->download($filename),
            'save'     => $pdf->save(storage_path("app/receipts/{$filename}")),
            default    => $pdf->download($filename),
        };
    }
}