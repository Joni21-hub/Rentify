<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\CustomerVoucher;
use App\Models\DetailPenyewaan;
use App\Models\OrderVoucherRedemption;
use App\Models\Penyewaan;
use App\Models\Voucher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MultiVendorProrationService
{
    /**
     * Platform markup rate (5%).
     */
    public const PLATFORM_MARKUP_RATE = 0.05;

    /**
     * Hitung diskon maksimal voucher platform yang memenuhi syarat untuk keranjang multi-vendor.
     *
     * @param Voucher $platformVoucher      Instance voucher platform yang digunakan.
     * @param float   $totalNetEligibleCart Total subtotal sewa bersih yang memenuhi syarat (net voucher toko).
     * @return float
     */
    public function calculateMaxPlatformDiscount(Voucher $platformVoucher, float $totalNetEligibleCart): float
    {
        $totalNetEligibleCart = round(max(0.0, $totalNetEligibleCart), 2);
        if ($totalNetEligibleCart <= 0.0) {
            return 0.0;
        }

        $minimalBelanja = (float) ($platformVoucher->minimal_belanja ?? 0.0);
        if ($totalNetEligibleCart < $minimalBelanja) {
            return 0.0;
        }

        $tipeDiskon = $platformVoucher->tipe_diskon ?? 'nominal';
        $nilaiDiskon = (float) ($platformVoucher->nilai_diskon ?? 0.0);

        if ($tipeDiskon === 'persen') {
            $rawDiscount = round($totalNetEligibleCart * ($nilaiDiskon / 100.0), 2);
            $maksimalDiskon = $platformVoucher->maksimal_diskon !== null ? (float) $platformVoucher->maksimal_diskon : null;
            if ($maksimalDiskon !== null && $maksimalDiskon > 0.0) {
                $discount = min($rawDiscount, $maksimalDiskon);
            } else {
                $discount = $rawDiscount;
            }
        } else {
            $discount = $nilaiDiskon;
        }

        // Bounded strictly by totalNetEligibleCart
        $discount = min($discount, $totalNetEligibleCart);

        return round(max(0.0, $discount), 2);
    }

    /**
     * Alokasikan diskon platform secara proporsional ke setiap vendor order dan baris item yang memenuhi whitelist.
     * Menerapkan rounding difference cascade per 0.01 cent dan deterministic tie-breaking.
     *
     * @param array         $vendorOrders          Daftar pesanan vendor dan itemnya.
     * @param float         $totalPlatformDiscount Total nominal diskon platform yang akan dialokasikan.
     * @param Voucher|null  $platformVoucher       Voucher platform untuk evaluasi whitelist.
     * @return array
     */
    public function proratePlatformDiscount(
        array $vendorOrders,
        float $totalPlatformDiscount,
        ?Voucher $platformVoucher = null
    ): array {
        $totalPlatformDiscount = round(max(0.0, $totalPlatformDiscount), 2);

        // Langkah 1: Isolasi line items yang eligible whitelist per vendor order
        $netEligibleMap = [];
        $vendorEligibleItemsMap = [];
        $vendorTotalAsliMap = [];

        foreach ($vendorOrders as $key => $order) {
            $vendorId = $this->extractVendorId($order, $key);
            $items = $this->extractOrderItems($order);

            $eligibleSubtotalVendor = 0.0;
            $eligibleStoreDiscountVendor = 0.0;
            $totalSubtotalVendor = 0.0;
            $eligibleItems = [];

            foreach ($items as $itemIdx => $item) {
                $itemId = $this->extractItemId($item, $itemIdx);
                $isEligible = $this->resolveItemEligibility($platformVoucher, $vendorId, $item);

                $itemSubtotalAsli = $this->extractItemSubtotalAsli($item);
                $itemAlokasiToko = $this->extractItemAlokasiToko($item);

                $totalSubtotalVendor += $itemSubtotalAsli;

                if ($isEligible) {
                    $eligibleSubtotalVendor += $itemSubtotalAsli;
                    $eligibleStoreDiscountVendor += $itemAlokasiToko;

                    $itemNetBase = max(0.0, round($itemSubtotalAsli - $itemAlokasiToko, 2));
                    $eligibleItems[$itemId] = [
                        'item'          => $item,
                        'index'         => $itemIdx,
                        'subtotal_asli' => $itemSubtotalAsli,
                        'alokasi_toko'  => $itemAlokasiToko,
                        'net_base'      => $itemNetBase > 0.0 ? $itemNetBase : $itemSubtotalAsli,
                    ];
                }
            }

            // Hitung potongan voucher toko yang memotong subtotal eligible
            if ($eligibleStoreDiscountVendor > 0.0) {
                $storeVoucherEligible = $eligibleStoreDiscountVendor;
            } else {
                $orderStoreVoucher = $this->extractOrderStoreVoucher($order);
                $storeVoucherEligible = min($orderStoreVoucher, $eligibleSubtotalVendor);
            }

            $netEligibleSewaVendor = max(0.0, round($eligibleSubtotalVendor - $storeVoucherEligible, 2));

            $netEligibleMap[$vendorId] = $netEligibleSewaVendor;
            $vendorEligibleItemsMap[$vendorId] = $eligibleItems;
            $vendorTotalAsliMap[$vendorId] = $totalSubtotalVendor;
        }

        $totalNetEligibleCart = round(array_sum($netEligibleMap), 2);

        // Guard clause: jika totalNetEligibleCart <= 0.0 atau totalPlatformDiscount <= 0.0
        // Kembalikan alokasi Rp0 tanpa pembagian untuk mencegah DivisionByZeroError
        if ($totalNetEligibleCart <= 0.0 || $totalPlatformDiscount <= 0.0) {
            $zeroAllocations = [];
            foreach ($vendorOrders as $key => &$orderRef) {
                $vId = $this->extractVendorId($orderRef, $key);
                $zeroAllocations[$vId] = 0.0;
                $this->setOrderPlatformDiscount($orderRef, 0.0);

                $items = $this->extractOrderItems($orderRef);
                foreach ($items as $itemIdx => &$itemRef) {
                    $this->setItemPlatformDiscount($itemRef, 0.0);
                }
                $this->setOrderItems($orderRef, $items);
            }
            unset($orderRef, $itemRef);

            return [
                'total_net_eligible_cart' => $totalNetEligibleCart,
                'total_platform_discount' => 0.0,
                'total_allocated'         => 0.0,
                'allocations'             => $zeroAllocations,
                'orders'                  => $vendorOrders,
                'vendor_orders'           => $vendorOrders,
            ];
        }

        // Batasi diskon platform dengan total net eligible cart
        $targetDiscount = min($totalPlatformDiscount, $totalNetEligibleCart);

        // Langkah 2: Hitung alokasi dasar proporsional per vendor (floored ke 2 desimal)
        $vendorAllocations = [];
        foreach ($netEligibleMap as $vId => $netEligible) {
            if ($netEligible <= 0.0) {
                $vendorAllocations[$vId] = 0.0;
                continue;
            }
            $rawBase = floor(($netEligible / $totalNetEligibleCart) * $targetDiscount * 100.0) / 100.0;
            $vendorAllocations[$vId] = min($rawBase, $netEligible);
        }

        // Langkah 3: Rounding difference cascade per 0.01 sen dengan deterministic tie-breaker
        $totalAllocated = round(array_sum($vendorAllocations), 2);
        $remainder = round($targetDiscount - $totalAllocated, 2);
        $cents = (int) round($remainder * 100);

        if ($cents > 0) {
            $sortedVendorIds = array_keys($netEligibleMap);
            usort($sortedVendorIds, function ($a, $b) use ($netEligibleMap) {
                $netA = $netEligibleMap[$a];
                $netB = $netEligibleMap[$b];
                if (abs($netA - $netB) > 0.00001) {
                    return ($netB <=> $netA); // net_eligible DESC
                }
                return is_numeric($a) && is_numeric($b)
                    ? ((int) $a <=> (int) $b)
                    : strcmp((string) $a, (string) $b); // vendor_id ASC
            });

            while ($cents > 0) {
                $allocatedAny = false;
                foreach ($sortedVendorIds as $vId) {
                    if ($cents <= 0) {
                        break;
                    }
                    $currentAlloc = $vendorAllocations[$vId];
                    $capacity = $netEligibleMap[$vId];
                    if (round($currentAlloc + 0.01, 2) <= round($capacity, 2)) {
                        $vendorAllocations[$vId] = round($currentAlloc + 0.01, 2);
                        $cents--;
                        $allocatedAny = true;
                    }
                }
                if (!$allocatedAny) {
                    // Semua vendor telah mencapai batas net eligible rental ceiling
                    break;
                }
            }
        }

        $totalAllocated = round(array_sum($vendorAllocations), 2);

        // Langkah 4: Kaskade alokasi platform voucher per vendor turun ke baris line items whitelisted
        foreach ($vendorOrders as $key => &$orderRef) {
            $vId = $this->extractVendorId($orderRef, $key);
            $vDiscount = $vendorAllocations[$vId] ?? 0.0;
            $this->setOrderPlatformDiscount($orderRef, $vDiscount);

            $items = $this->extractOrderItems($orderRef);
            $eligibleItems = $vendorEligibleItemsMap[$vId] ?? [];
            $sumEligibleNetBase = 0.0;
            foreach ($eligibleItems as $eData) {
                $sumEligibleNetBase += $eData['net_base'];
            }

            $itemAllocations = [];
            if ($vDiscount <= 0.0 || $sumEligibleNetBase <= 0.0 || empty($eligibleItems)) {
                // Semua item mendapat 0
                foreach ($items as $itemIdx => &$itemRef) {
                    $this->setItemPlatformDiscount($itemRef, 0.0);
                }
                unset($itemRef);
            } else {
                // Alokasi dasar proporsional item
                foreach ($eligibleItems as $itemId => $eData) {
                    $rawItem = floor(($eData['net_base'] / $sumEligibleNetBase) * $vDiscount * 100.0) / 100.0;
                    $itemAllocations[$itemId] = min($rawItem, $eData['net_base']);
                }

                $itemAllocTotal = round(array_sum($itemAllocations), 2);
                $itemRemainder = round($vDiscount - $itemAllocTotal, 2);
                $itemCents = (int) round($itemRemainder * 100);

                if ($itemCents > 0) {
                    $sortedItemIds = array_keys($eligibleItems);
                    usort($sortedItemIds, function ($a, $b) use ($eligibleItems) {
                        $baseA = $eligibleItems[$a]['net_base'];
                        $baseB = $eligibleItems[$b]['net_base'];
                        if (abs($baseA - $baseB) > 0.00001) {
                            return ($baseB <=> $baseA); // net_base DESC
                        }
                        return is_numeric($a) && is_numeric($b)
                            ? ((int) $a <=> (int) $b)
                            : strcmp((string) $a, (string) $b); // item_id ASC
                    });

                    while ($itemCents > 0) {
                        $allocatedItemCent = false;
                        foreach ($sortedItemIds as $iId) {
                            if ($itemCents <= 0) {
                                break;
                            }
                            $current = $itemAllocations[$iId];
                            $cap = $eligibleItems[$iId]['net_base'];
                            if (round($current + 0.01, 2) <= round($cap, 2)) {
                                $itemAllocations[$iId] = round($current + 0.01, 2);
                                $itemCents--;
                                $allocatedItemCent = true;
                            }
                        }
                        if (!$allocatedItemCent) {
                            break;
                        }
                    }
                }

                // Terapkan alokasi ke items
                foreach ($items as $itemIdx => &$itemRef) {
                    $itemId = $this->extractItemId($itemRef, $itemIdx);
                    if (isset($itemAllocations[$itemId])) {
                        $this->setItemPlatformDiscount($itemRef, round($itemAllocations[$itemId], 2));
                    } else {
                        // Non-whitelisted item
                        $this->setItemPlatformDiscount($itemRef, 0.0);
                    }
                }
                unset($itemRef);
            }

            $this->setOrderItems($orderRef, $items);
        }
        unset($orderRef);

        return [
            'total_net_eligible_cart' => $totalNetEligibleCart,
            'total_platform_discount' => $targetDiscount,
            'total_allocated'         => $totalAllocated,
            'allocations'             => $vendorAllocations,
            'orders'                  => $vendorOrders,
            'vendor_orders'           => $vendorOrders,
        ];
    }

    /**
     * Alokasikan diskon Koin Rentify (potongan_koin) secara proporsional ke setiap vendor order dan baris item.
     * Menerapkan batas maksimal 50% dari sisa harga sewa (remaining_sewa) dan deterministic penny reconciliation.
     *
     * @param array $vendorOrders       Daftar pesanan vendor dan itemnya.
     * @param float $totalCoinDiscount  Total nominal potongan koin yang akan dialokasikan.
     * @return array
     */
    public function prorateCoinDiscount(array $vendorOrders, float $totalCoinDiscount): array
    {
        $totalCoinDiscount = round(max(0.0, $totalCoinDiscount), 2);

        // Langkah 1: Hitung post-voucher remaining rental dan cap 50% per vendor order
        $remainingSewaVendorMap = [];
        $vendorMaxCoinCapMap = [];
        $vendorItemsRemainingMap = [];

        foreach ($vendorOrders as $key => $order) {
            $vendorId = $this->extractVendorId($order, $key);
            $items = $this->extractOrderItems($order);

            $orderMarkup = $this->extractOrderMarkup($order);
            $orderToko = $this->extractOrderStoreVoucher($order);
            $orderPlatform = $this->extractOrderPlatformVoucher($order);

            $remainingVendor = max(0.0, round($orderMarkup - $orderToko - $orderPlatform, 2));
            $vendorCoinCap = floor(0.50 * $remainingVendor * 100.0 + 0.00001) / 100.0;

            $remainingSewaVendorMap[$vendorId] = $remainingVendor;
            $vendorMaxCoinCapMap[$vendorId] = $vendorCoinCap;

            // Hitung remaining per item
            $itemsData = [];
            foreach ($items as $itemIdx => $item) {
                $itemId = $this->extractItemId($item, $itemIdx);
                $itemMarkup = $this->extractItemMarkup($item);
                $itemToko = $this->extractItemAlokasiToko($item);
                $itemPlatform = $this->extractItemAlokasiPlatform($item);

                $remainingItem = max(0.0, round($itemMarkup - $itemToko - $itemPlatform, 2));
                $itemCoinCap = floor(0.50 * $remainingItem * 100.0 + 0.00001) / 100.0;

                $itemsData[$itemId] = [
                    'item'           => $item,
                    'index'          => $itemIdx,
                    'remaining_sewa' => $remainingItem,
                    'coin_cap'       => $itemCoinCap,
                ];
            }
            $vendorItemsRemainingMap[$vendorId] = $itemsData;
        }

        $totalRemainingSewa = round(array_sum($remainingSewaVendorMap), 2);
        $totalMaxCoinCapacity = round(array_sum($vendorMaxCoinCapMap), 2);
        $effectiveCoinDiscount = min($totalCoinDiscount, $totalMaxCoinCapacity);

        // Guard clause: jika tidak ada sewa tersisa atau diskon koin 0
        if ($totalRemainingSewa <= 0.0 || $effectiveCoinDiscount <= 0.0) {
            $zeroCoinAllocations = [];
            foreach ($vendorOrders as $key => &$orderRef) {
                $vId = $this->extractVendorId($orderRef, $key);
                $zeroCoinAllocations[$vId] = 0.0;
                $this->setOrderCoinDiscount($orderRef, 0.0);

                $items = $this->extractOrderItems($orderRef);
                foreach ($items as $itemIdx => &$itemRef) {
                    $this->setItemCoinDiscount($itemRef, 0.0);
                    $this->updateItemSubtotalSewaBersih($itemRef);
                }
                unset($itemRef);
                $this->setOrderItems($orderRef, $items);
            }
            unset($orderRef);

            return [
                'total_remaining_sewa' => $totalRemainingSewa,
                'total_coin_discount'  => 0.0,
                'total_allocated'      => 0.0,
                'allocations'          => $zeroCoinAllocations,
                'orders'               => $vendorOrders,
                'vendor_orders'        => $vendorOrders,
            ];
        }

        // Langkah 2: Alokasi dasar proporsional koin per vendor
        $vendorCoinAllocations = [];
        foreach ($remainingSewaVendorMap as $vId => $remVendor) {
            if ($remVendor <= 0.0) {
                $vendorCoinAllocations[$vId] = 0.0;
                continue;
            }
            $rawCoin = floor(($remVendor / $totalRemainingSewa) * $effectiveCoinDiscount * 100.0) / 100.0;
            $vendorCoinAllocations[$vId] = min($rawCoin, $vendorMaxCoinCapMap[$vId]);
        }

        // Langkah 3: Rounding difference cascade per 0.01 koin dengan deterministic tie-breaker
        $totalCoinAllocated = round(array_sum($vendorCoinAllocations), 2);
        $coinRemainder = round($effectiveCoinDiscount - $totalCoinAllocated, 2);
        $coinCents = (int) round($coinRemainder * 100);

        if ($coinCents > 0) {
            $sortedVendorIds = array_keys($remainingSewaVendorMap);
            usort($sortedVendorIds, function ($a, $b) use ($remainingSewaVendorMap) {
                $remA = $remainingSewaVendorMap[$a];
                $remB = $remainingSewaVendorMap[$b];
                if (abs($remA - $remB) > 0.00001) {
                    return ($remB <=> $remA); // remaining_sewa DESC
                }
                return is_numeric($a) && is_numeric($b)
                    ? ((int) $a <=> (int) $b)
                    : strcmp((string) $a, (string) $b); // vendor_id ASC
            });

            while ($coinCents > 0) {
                $allocatedAny = false;
                foreach ($sortedVendorIds as $vId) {
                    if ($coinCents <= 0) {
                        break;
                    }
                    $current = $vendorCoinAllocations[$vId];
                    $cap = $vendorMaxCoinCapMap[$vId];
                    if (round($current + 0.01, 2) <= round($cap, 2)) {
                        $vendorCoinAllocations[$vId] = round($current + 0.01, 2);
                        $coinCents--;
                        $allocatedAny = true;
                    }
                }
                if (!$allocatedAny) {
                    break;
                }
            }
        }

        $totalCoinAllocated = round(array_sum($vendorCoinAllocations), 2);

        // Langkah 4: Kaskade alokasi koin vendor turun ke baris line items
        foreach ($vendorOrders as $key => &$orderRef) {
            $vId = $this->extractVendorId($orderRef, $key);
            $vCoin = $vendorCoinAllocations[$vId] ?? 0.0;
            $this->setOrderCoinDiscount($orderRef, $vCoin);

            $items = $this->extractOrderItems($orderRef);
            $itemsData = $vendorItemsRemainingMap[$vId] ?? [];
            $sumItemRemaining = 0.0;
            foreach ($itemsData as $iData) {
                $sumItemRemaining += $iData['remaining_sewa'];
            }

            $itemCoinAllocations = [];
            if ($vCoin <= 0.0 || $sumItemRemaining <= 0.0 || empty($itemsData)) {
                foreach ($items as $itemIdx => &$itemRef) {
                    $this->setItemCoinDiscount($itemRef, 0.0);
                    $this->updateItemSubtotalSewaBersih($itemRef);
                }
                unset($itemRef);
            } else {
                // Alokasi koin dasar per item
                foreach ($itemsData as $itemId => $iData) {
                    $rawItemCoin = floor(($iData['remaining_sewa'] / $sumItemRemaining) * $vCoin * 100.0) / 100.0;
                    $itemCoinAllocations[$itemId] = min($rawItemCoin, $iData['coin_cap']);
                }

                $itemCoinAllocTotal = round(array_sum($itemCoinAllocations), 2);
                $itemCoinRemainder = round($vCoin - $itemCoinAllocTotal, 2);
                $itemCoinCents = (int) round($itemCoinRemainder * 100);

                if ($itemCoinCents > 0) {
                    $sortedItemIds = array_keys($itemsData);
                    usort($sortedItemIds, function ($a, $b) use ($itemsData) {
                        $remA = $itemsData[$a]['remaining_sewa'];
                        $remB = $itemsData[$b]['remaining_sewa'];
                        if (abs($remA - $remB) > 0.00001) {
                            return ($remB <=> $remA); // remaining_sewa DESC
                        }
                        return is_numeric($a) && is_numeric($b)
                            ? ((int) $a <=> (int) $b)
                            : strcmp((string) $a, (string) $b); // item_id ASC
                    });

                    while ($itemCoinCents > 0) {
                        $allocatedItemCent = false;
                        foreach ($sortedItemIds as $iId) {
                            if ($itemCoinCents <= 0) {
                                break;
                            }
                            $current = $itemCoinAllocations[$iId];
                            $cap = $itemsData[$iId]['coin_cap'];
                            if (round($current + 0.01, 2) <= round($cap, 2)) {
                                $itemCoinAllocations[$iId] = round($current + 0.01, 2);
                                $itemCoinCents--;
                                $allocatedItemCent = true;
                            }
                        }
                        if (!$allocatedItemCent) {
                            break;
                        }
                    }
                }

                // Terapkan alokasi koin ke item & mutasikan subtotal_sewa_bersih
                foreach ($items as $itemIdx => &$itemRef) {
                    $itemId = $this->extractItemId($itemRef, $itemIdx);
                    $allocatedCoin = isset($itemCoinAllocations[$itemId]) ? round($itemCoinAllocations[$itemId], 2) : 0.0;
                    $this->setItemCoinDiscount($itemRef, $allocatedCoin);
                    $this->updateItemSubtotalSewaBersih($itemRef);
                }
                unset($itemRef);
            }

            $this->setOrderItems($orderRef, $items);
        }
        unset($orderRef);

        return [
            'total_remaining_sewa' => $totalRemainingSewa,
            'total_coin_discount'  => $effectiveCoinDiscount,
            'total_allocated'      => $totalCoinAllocated,
            'allocations'          => $vendorCoinAllocations,
            'orders'               => $vendorOrders,
            'vendor_orders'        => $vendorOrders,
        ];
    }

    // =========================================================================
    // HELPER & RESOLVER METHODS (Whitelist, Extractors, Mutators)
    // =========================================================================

    /**
     * Evaluasi apakah line-item memenuhi syarat whitelist voucher platform.
     */
    protected function resolveItemEligibility(?Voucher $platformVoucher, $vendorId, array|object $item): bool
    {
        // 1. Jika item memiliki flag 'is_eligible' eksplisit, hormati flag tersebut
        if (is_array($item) && isset($item['is_eligible'])) {
            return (bool) $item['is_eligible'];
        }
        if (is_object($item) && isset($item->is_eligible)) {
            return (bool) $item->is_eligible;
        }

        // 2. Jika tidak ada voucher platform, semua item dianggap eligible
        if ($platformVoucher === null) {
            return true;
        }

        // 3. Evaluasi vendor whitelist
        if ($platformVoucher->relationLoaded('vendors')) {
            if ($platformVoucher->vendors->isNotEmpty()) {
                $allowedVendors = $platformVoucher->vendors->pluck('id')->map(fn($v) => (string) $v)->all();
                if (!in_array((string) $vendorId, $allowedVendors, true)) {
                    return false;
                }
            }
        } elseif (method_exists($platformVoucher, 'vendors')) {
            try {
                $allowedVendors = $platformVoucher->vendors()->pluck('users.id')->map(fn($v) => (string) $v)->all();
                if (!empty($allowedVendors) && !in_array((string) $vendorId, $allowedVendors, true)) {
                    return false;
                }
            } catch (\Throwable $e) {
                // Ignore query exceptions for unit mocks
            }
        }

        // 4. Evaluasi cakupan produk
        $cakupan = $platformVoucher->cakupan_produk ?? 'seluruh_toko';
        if ($cakupan === 'seluruh_toko') {
            return true;
        }

        $productId = is_array($item)
            ? ($item['product_id'] ?? $item['barang_id'] ?? $item['id'] ?? null)
            : ($item->product_id ?? $item->barang_id ?? $item->id ?? null);

        if ($cakupan === 'produk_spesifik') {
            if ($productId === null) {
                return false;
            }
            if ($platformVoucher->relationLoaded('barangs')) {
                $allowedBarangs = $platformVoucher->barangs->pluck('id')->map(fn($b) => (string) $b)->all();
                return in_array((string) $productId, $allowedBarangs, true);
            }
            if (method_exists($platformVoucher, 'barangs')) {
                try {
                    $allowedBarangs = $platformVoucher->barangs()->pluck('barang.id')->map(fn($b) => (string) $b)->all();
                    return in_array((string) $productId, $allowedBarangs, true);
                } catch (\Throwable $e) {
                    return false;
                }
            }
            return false;
        }

        if ($cakupan === 'kategori_spesifik') {
            $kategoriId = null;
            if (is_array($item)) {
                $kategoriId = $item['kategori_id'] ?? $item['category_id'] ?? null;
                if ($kategoriId === null && isset($item['barang'])) {
                    $kategoriId = is_array($item['barang']) ? ($item['barang']['kategori_id'] ?? null) : ($item['barang']->kategori_id ?? null);
                }
            } else {
                $kategoriId = $item->kategori_id ?? $item->category_id ?? null;
                if ($kategoriId === null && isset($item->barang)) {
                    $kategoriId = is_array($item->barang) ? ($item->barang['kategori_id'] ?? null) : ($item->barang->kategori_id ?? null);
                }
            }

            if ($kategoriId === null && $productId !== null) {
                try {
                    $barang = Barang::find($productId);
                    $kategoriId = $barang?->kategori_id;
                } catch (\Throwable $e) {
                    // Ignore
                }
            }

            if ($kategoriId === null) {
                return false;
            }

            if ($platformVoucher->relationLoaded('kategoris')) {
                $allowedKategoris = $platformVoucher->kategoris->pluck('id')->map(fn($k) => (string) $k)->all();
                return in_array((string) $kategoriId, $allowedKategoris, true);
            }
            if (method_exists($platformVoucher, 'kategoris')) {
                try {
                    $allowedKategoris = $platformVoucher->kategoris()->pluck('kategoris.id')->map(fn($k) => (string) $k)->all();
                    return in_array((string) $kategoriId, $allowedKategoris, true);
                } catch (\Throwable $e) {
                    return false;
                }
            }
            return false;
        }

        return true;
    }

    protected function extractVendorId(array|object $order, int|string $fallbackKey): int|string
    {
        if (is_array($order)) {
            return $order['vendor_id'] ?? $fallbackKey;
        }
        return $order->vendor_id ?? $fallbackKey;
    }

    protected function extractItemId(array|object $item, int|string $fallbackKey): int|string
    {
        if (is_array($item)) {
            return $item['item_id'] ?? $item['id'] ?? $item['product_id'] ?? $fallbackKey;
        }
        return $item->item_id ?? $item->id ?? $item->product_id ?? $fallbackKey;
    }

    protected function extractOrderItems(array|object $order): array
    {
        if (is_array($order)) {
            $items = $order['items'] ?? $order['order_items'] ?? $order['details'] ?? [];
        } else {
            $items = $order->details ?? $order->items ?? $order->order_items ?? [];
        }

        if ($items instanceof Collection) {
            return $items->all();
        }
        return is_array($items) ? $items : [];
    }

    protected function setOrderItems(array|object &$order, array $items): void
    {
        if (is_array($order)) {
            if (isset($order['items'])) {
                $order['items'] = $items;
            } elseif (isset($order['order_items'])) {
                $order['order_items'] = $items;
            } elseif (isset($order['details'])) {
                $order['details'] = $items;
            } else {
                $order['items'] = $items;
            }
        } elseif (is_object($order)) {
            if (method_exists($order, 'setRelation') && $order instanceof \Illuminate\Database\Eloquent\Model) {
                $order->setRelation('details', collect($items));
            } else {
                $order->items = $items;
            }
        }
    }

    protected function extractItemSubtotalAsli(array|object $item): float
    {
        if (is_array($item)) {
            if (isset($item['subtotal_sewa_asli'])) {
                return (float) $item['subtotal_sewa_asli'];
            }
            $price = (float) ($item['price'] ?? 0.0);
            $qty = (int) ($item['quantity'] ?? $item['jumlah'] ?? 1);
            $durasi = (int) ($item['durasi_sewa'] ?? 1);
            return round($price * $qty * $durasi, 2);
        }

        if (isset($item->subtotal_sewa_asli)) {
            return (float) $item->subtotal_sewa_asli;
        }
        $price = (float) ($item->price ?? 0.0);
        $qty = (int) ($item->quantity ?? $item->jumlah ?? 1);
        $durasi = (int) ($item->durasi_sewa ?? 1);
        return round($price * $qty * $durasi, 2);
    }

    protected function extractItemAlokasiToko(array|object $item): float
    {
        if (is_array($item)) {
            return (float) ($item['alokasi_diskon_toko'] ?? $item['potongan_voucher_toko'] ?? 0.0);
        }
        return (float) ($item->alokasi_diskon_toko ?? $item->potongan_voucher_toko ?? 0.0);
    }

    protected function extractItemAlokasiPlatform(array|object $item): float
    {
        if (is_array($item)) {
            return (float) ($item['alokasi_diskon_platform'] ?? $item['potongan_voucher_platform'] ?? 0.0);
        }
        return (float) ($item->alokasi_diskon_platform ?? $item->potongan_voucher_platform ?? 0.0);
    }

    protected function extractOrderStoreVoucher(array|object $order): float
    {
        if (is_array($order)) {
            return (float) ($order['potongan_voucher_toko_eligible'] ?? $order['potongan_voucher_toko'] ?? $order['potongan_voucher'] ?? 0.0);
        }
        return (float) ($order->potongan_voucher_toko ?? $order->potongan_voucher ?? 0.0);
    }

    protected function extractOrderPlatformVoucher(array|object $order): float
    {
        if (is_array($order)) {
            return (float) ($order['potongan_voucher_platform'] ?? 0.0);
        }
        return (float) ($order->potongan_voucher_platform ?? 0.0);
    }

    protected function extractOrderMarkup(array|object $order): float
    {
        if (is_array($order)) {
            if (isset($order['subtotal_sewa_markup'])) {
                return (float) $order['subtotal_sewa_markup'];
            }
            if (isset($order['subtotal_sewa_asli'])) {
                return round((float) $order['subtotal_sewa_asli'] * (1 + self::PLATFORM_MARKUP_RATE), 2);
            }
            // Sum from items
            $items = $this->extractOrderItems($order);
            $sum = 0.0;
            foreach ($items as $item) {
                $sum += $this->extractItemMarkup($item);
            }
            return round($sum, 2);
        }

        if (isset($order->subtotal_sewa_markup)) {
            return (float) $order->subtotal_sewa_markup;
        }
        if (isset($order->subtotal_sewa_asli)) {
            return round((float) $order->subtotal_sewa_asli * (1 + self::PLATFORM_MARKUP_RATE), 2);
        }
        $items = $this->extractOrderItems($order);
        $sum = 0.0;
        foreach ($items as $item) {
            $sum += $this->extractItemMarkup($item);
        }
        return round($sum, 2);
    }

    protected function extractItemMarkup(array|object $item): float
    {
        if (is_array($item)) {
            if (isset($item['subtotal_sewa_markup'])) {
                return (float) $item['subtotal_sewa_markup'];
            }
            $asli = $this->extractItemSubtotalAsli($item);
            return round($asli * (1 + self::PLATFORM_MARKUP_RATE), 2);
        }

        if (isset($item->subtotal_sewa_markup)) {
            return (float) $item->subtotal_sewa_markup;
        }
        $asli = $this->extractItemSubtotalAsli($item);
        return round($asli * (1 + self::PLATFORM_MARKUP_RATE), 2);
    }

    protected function setOrderPlatformDiscount(array|object &$order, float $discount): void
    {
        if (is_array($order)) {
            $order['potongan_voucher_platform'] = $discount;
            $order['allocated_platform_discount'] = $discount;
        } elseif (is_object($order)) {
            $order->potongan_voucher_platform = $discount;
        }
    }

    protected function setItemPlatformDiscount(array|object &$item, float $discount): void
    {
        if (is_array($item)) {
            $item['alokasi_diskon_platform'] = $discount;
        } elseif (is_object($item)) {
            $item->alokasi_diskon_platform = $discount;
        }
    }

    protected function setOrderCoinDiscount(array|object &$order, float $coinDiscount): void
    {
        if (is_array($order)) {
            $order['potongan_koin'] = $coinDiscount;
        } elseif (is_object($order)) {
            $order->potongan_koin = $coinDiscount;
        }
    }

    protected function setItemCoinDiscount(array|object &$item, float $coinDiscount): void
    {
        if (is_array($item)) {
            $item['alokasi_diskon_koin'] = $coinDiscount;
        } elseif (is_object($item)) {
            $item->alokasi_diskon_koin = $coinDiscount;
        }
    }

    protected function updateItemSubtotalSewaBersih(array|object &$item): void
    {
        $markup = $this->extractItemMarkup($item);
        $toko = $this->extractItemAlokasiToko($item);
        $platform = $this->extractItemAlokasiPlatform($item);
        $koin = is_array($item) ? (float) ($item['alokasi_diskon_koin'] ?? 0.0) : (float) ($item->alokasi_diskon_koin ?? 0.0);

        $bersih = max(0.0, round($markup - $toko - $platform - $koin, 2));

        if (is_array($item)) {
            $item['subtotal_sewa_bersih'] = $bersih;
        } elseif (is_object($item)) {
            $item->subtotal_sewa_bersih = $bersih;
        }
    }

    /**
     * Persist allocation records into order_voucher_redemptions while consuming exactly 1 platform quota per payment_group_id.
     *
     * @param string        $paymentGroupId     ID grup pembayaran bersama multi-vendor
     * @param Voucher       $platformVoucher    Instance model Voucher platform yang digunakan
     * @param array         $vendorOrders       Daftar sub-order yang telah menerima alokasi proration diskon
     * @param int|null      $customerVoucherId  ID klaim voucher pelanggan (CustomerVoucher), jika ada
     * @return array        Daftar model OrderVoucherRedemption yang berhasil di-persist
     */
    public function persistPlatformDiscountRedemptions(
        string $paymentGroupId,
        Voucher $platformVoucher,
        array $vendorOrders,
        ?int $customerVoucherId = null
    ): array {
        return DB::transaction(function () use ($paymentGroupId, $platformVoucher, $vendorOrders, $customerVoucherId) {
            $createdRedemptions = [];
            $totalPlatformAllocated = 0.0;

            foreach ($vendorOrders as $key => $order) {
                $orderId = is_array($order)
                    ? ($order['id'] ?? $order['order_id'] ?? null)
                    : ($order->id ?? $order->order_id ?? null);

                $discountAllocated = is_array($order)
                    ? ($order['potongan_voucher_platform'] ?? $order['allocated_platform_discount'] ?? 0.0)
                    : ($order->potongan_voucher_platform ?? $order->allocated_platform_discount ?? 0.0);

                $discountAllocated = round((float) $discountAllocated, 2);

                if ($discountAllocated > 0.0 && $orderId !== null) {
                    $redemption = OrderVoucherRedemption::create([
                        'payment_group_id'    => $paymentGroupId,
                        'order_id'            => $orderId,
                        'voucher_id'          => $platformVoucher->id,
                        'customer_voucher_id' => $customerVoucherId,
                        'tipe_voucher'        => 'platform',
                        'alokasi_diskon'      => $discountAllocated,
                        'status'              => 'aktif',
                    ]);

                    $createdRedemptions[] = $redemption;
                    $totalPlatformAllocated += $discountAllocated;
                }
            }

            // Konsumsi tepat 1 kuota platform per payment_group_id (bukan per sub-order)
            $platformVoucher->increment('kuota_terpakai', 1);

            // Tambahkan anggaran terpakai jika tracking anggaran aktif
            if ($totalPlatformAllocated > 0.0 && Schema::hasColumn('vouchers', 'anggaran_terpakai')) {
                $platformVoucher->increment('anggaran_terpakai', $totalPlatformAllocated);
            }

            // Perbarui status klaim customer voucher menjadi 'terpakai'
            if ($customerVoucherId) {
                CustomerVoucher::where('id', $customerVoucherId)->update([
                    'status'  => 'terpakai',
                    'used_at' => now(),
                ]);
            }

            // Evaluasi auto_exhausted jika kuota total atau anggaran telah habis
            $platformVoucher->refresh();
            $shouldExhaust = false;
            if ($platformVoucher->kuota_total > 0 && $platformVoucher->kuota_terpakai >= $platformVoucher->kuota_total) {
                $shouldExhaust = true;
            }
            if ($platformVoucher->anggaran_maksimal_platform > 0 && $platformVoucher->anggaran_terpakai >= $platformVoucher->anggaran_maksimal_platform) {
                $shouldExhaust = true;
            }

            if ($shouldExhaust) {
                $platformVoucher->update([
                    'auto_exhausted' => true,
                    'is_active'      => false,
                ]);
            }

            return $createdRedemptions;
        });
    }

    /**
     * Alias method untuk persistPlatformDiscountRedemptions.
     */
    public function persistRedemptions(
        string $paymentGroupId,
        Voucher $platformVoucher,
        array $vendorOrders,
        ?int $customerVoucherId = null
    ): array {
        return $this->persistPlatformDiscountRedemptions($paymentGroupId, $platformVoucher, $vendorOrders, $customerVoucherId);
    }
}
