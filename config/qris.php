<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QRIS Configuration (Standar Bank Indonesia / ASPI)
    |--------------------------------------------------------------------------
    |
    | Base string QRIS Statis dari Penyedia (DANA Bisnis "Rental kursi").
    | Tag 01 diubah otomatis ke '12' (Dinamis), Tag 54 (Nominal Transaksi)
    | diinjeksi, dan CRC16 (Tag 63) dihitung ulang untuk setiap pesanan.
    |
    */
    'base_string' => env('QRIS_BASE_STRING', '00020101021126570011ID.DANA.WWW011893600915300617401802090061740180303UMI51440014ID.CO.QRIS.WWW0215ID10265872193240303UMI5204739453033605802ID5912Rental kursi600416056105316646304065A'),

    'merchant_name' => env('QRIS_MERCHANT_NAME', 'Rental kursi'),

    'nmid' => env('QRIS_NMID', 'ID1026587219324'),

    'acquirer' => env('QRIS_ACQUIRER', 'DANA Bisnis'),

    'expiry_minutes' => (int) env('QRIS_EXPIRY_MINUTES', 30),
];
