<?php

namespace App\Events;

use App\Models\Barang;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VendorBundleAlertEvent
{
    use Dispatchable, SerializesModels;

    /**
     * @var mixed
     */
    public $bundle;

    public Barang $barang;

    public string $reason;

    /**
     * @param mixed $bundle
     * @param Barang $barang
     * @param string $reason
     */
    public function __construct($bundle, Barang $barang, string $reason)
    {
        $this->bundle = $bundle;
        $this->barang = $barang;
        $this->reason = $reason;
    }
}
