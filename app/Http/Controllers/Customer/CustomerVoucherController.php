<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class CustomerVoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::with('vendor')
            ->where('is_active', 1)
            ->whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now())
            ->whereColumn('kuota_terpakai', '<', 'kuota_total')
            ->whereHas('vendor', function ($q) {
                $q->where('vendor_status', '!=', 'suspended')
                  ->orWhereNull('vendor_status');
            })
            ->latest()
            ->get();

        return view('customer.voucher.index', compact('vouchers'));
    }
}
