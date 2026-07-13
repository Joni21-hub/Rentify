<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Payment\PembayaranController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Vendor\PesananController;
use App\Http\Controllers\Vendor\VendorSaldoController;

// Admin Controllers
use App\Http\Controllers\Admin\{
    AdminDashboardController, AdminUserController, AdminVendorController, 
    AdminBarangController, AdminKategoriController, AdminCabangController, 
    AdminBannerController, AdminPembayaranController, AdminPengembalianController, 
    AdminKomplainController, AdminLaporanController, AdminPenarikanController
};

// Vendor Controllers
use App\Http\Controllers\Vendor\{
    VendorController, VendorDashboardController, VendorBarangController, 
    VendorFotoBarangController, VendorStokCabangController, VendorPenyewaanController
};

// Customer Controllers
use App\Http\Controllers\Customer\{
    CustomerHomeController, CustomerDashboardController, MarketplaceController, 
    BarangDetailController, KeranjangController, CheckoutController, 
    WishlistController, PenyewaanTrackingController, UlasanController, 
};

// ─── AUTHENTICATION ROUTES ─────────────────────────────────────
Route::get('/', [AuthController::class, 'redirectByRole']);
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/forgot-password', [AuthController::class, 'forgotForm']);
Route::post('/forgot-password', [AuthController::class, 'sendReset']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Logout fallback (GET)
Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
});


// ─── ADMIN ROUTES ──────────────────────────────────────────────
Route::get('/admin/vendors-validation', [AdminVendorController::class, 'validasiVendor'])->name('admin.vendors.validation');
Route::post('/admin/vendors/{id}/approve-validation', [AdminVendorController::class, 'approveVendor'])->name('admin.vendors.approve-validation');
Route::post('/admin/vendors/{id}/reject-validation', [AdminVendorController::class, 'rejectVendor'])->name('admin.vendors.reject');

Route::prefix('admin')->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::post('/banner', [AdminDashboardController::class, 'storeBanner'])->name('banner.store');
    Route::delete('/banner/{id}', [AdminDashboardController::class, 'destroyBanner'])->name('banner.destroy');

    Route::patch('barang/{id}/approve', [AdminDashboardController::class, 'approveBarang'])->name('barang.approve');
    Route::patch('barang/{id}/reject', [AdminDashboardController::class, 'rejectBarang'])->name('barang.reject');

    Route::delete('barang/{id}/delete', [AdminDashboardController::class, 'destroyBarang'])->name('barang.destroyMaster');
    Route::delete('user/{id}/delete', [AdminDashboardController::class, 'destroyUser'])->name('user.destroyMaster');

    Route::resource('users', AdminUserController::class);
    Route::resource('vendors', AdminVendorController::class);
    Route::patch('vendors/{id}/verify', [AdminVendorController::class, 'verify'])->name('vendors.verify');
    Route::resource('barang', AdminBarangController::class)->except(['approve', 'reject']);
    Route::resource('kategori', AdminKategoriController::class);
    Route::resource('cabang', AdminCabangController::class);

    Route::get('pembayaran', [AdminPembayaranController::class, 'index'])->name('pembayaran');
    Route::patch('pembayaran/{id}/verify', [AdminPembayaranController::class, 'verify'])->name('pembayaran.verify');
    Route::patch('pembayaran/{id}/refund', [AdminPembayaranController::class, 'refund'])->name('pembayaran.refund');

    Route::get('pengembalian', [AdminPengembalianController::class, 'index'])->name('pengembalian');
    Route::patch('pengembalian/{id}/approve', [AdminPengembalianController::class, 'approve'])->name('pengembalian.approve');

    Route::resource('komplain', AdminKomplainController::class);
    Route::patch('komplain/{id}/resolve', [AdminKomplainController::class, 'resolve'])->name('komplain.resolve');

    Route::get('laporan', [AdminLaporanController::class, 'index'])->name('laporan');
    Route::post('laporan/generate', [AdminLaporanController::class, 'generate'])->name('laporan.generate');
    Route::get('laporan/{id}/pdf', [AdminLaporanController::class, 'pdf'])->name('laporan.pdf');
    Route::get('laporan/{id}/excel', [AdminLaporanController::class, 'excel'])->name('laporan.excel');

    Route::get('penarikan', [AdminPenarikanController::class, 'index'])->name('penarikan.index');
    Route::post('penarikan/{id}/approve', [AdminPenarikanController::class, 'approve'])->name('penarikan.approve');
    Route::post('penarikan/{id}/reject', [AdminPenarikanController::class, 'reject'])->name('penarikan.reject');
});


// ─── VENDOR ROUTES ──────────────────────────────────────────────
Route::get('/vendor/register', [VendorController::class, 'showRegisterForm'])->name('vendor.register');
Route::post('/vendor/register', [VendorController::class, 'register']);
Route::view('/vendor/registration-success', 'auth.vendor-success')->name('vendor.register.success');

Route::prefix('vendor')->name('vendor.')
    ->middleware(['auth', 'role:vendor'])
    ->group(function () {

    Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');

    Route::resource('barang', VendorBarangController::class);
    Route::post('barang/{id}/fotos', [VendorFotoBarangController::class, 'upload'])->name('barang.fotos.upload');
    Route::patch('barang/{id}/fotos/{foto}/cover', [VendorFotoBarangController::class, 'setCover'])->name('barang.fotos.cover');
    Route::delete('fotos/{foto}', [VendorFotoBarangController::class, 'destroy'])->name('fotos.destroy');

    Route::get('stok', [VendorStokCabangController::class, 'index'])->name('stok');
    Route::put('stok', [VendorStokCabangController::class, 'update'])->name('stok.update');

    Route::get('pesanan', [PesananController::class, 'index'])->name('pesanan.index');
    Route::get('pesanan/{id}', [PesananController::class, 'show'])->name('pesanan.show');
    Route::post('pesanan/{id}/status', [PesananController::class, 'updateStatus'])->name('pesanan.status.update');

    Route::get('saldo', [VendorSaldoController::class, 'index'])->name('saldo.index');
    Route::post('saldo/tarik', [VendorSaldoController::class, 'storePenarikan'])->name('saldo.tarik');
    Route::get('saldo/export', [VendorSaldoController::class, 'export'])->name('saldo.export');

    Route::get('pengaturan', [\App\Http\Controllers\Vendor\VendorPengaturanController::class, 'index'])->name('pengaturan.index');
    Route::post('pengaturan', [\App\Http\Controllers\Vendor\VendorPengaturanController::class, 'update'])->name('pengaturan.update');
});


// ─── CUSTOMER ROUTES ────────────────────────────────────────────
Route::get('/customer/home', function () {
    return view('customer.home');
});

Route::prefix('customer')->name('customer.')
    ->middleware(['auth', 'role:customer'])
    ->group(function () {

    // Catalog & Basic Search
    Route::get('/', [CustomerHomeController::class, 'index'])->name('home');
    Route::get('/search', [MarketplaceController::class, 'search'])->name('search');
    Route::get('/barang/{slug}', [BarangDetailController::class, 'show'])->name('barang.show');
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    
    // Core Cart System
    Route::get('/cart', [CustomerHomeController::class, 'viewCart'])->name('cart.view');
    Route::post('/cart/add', [CustomerHomeController::class, 'addToCart'])->name('cart.add-old');
    Route::post('/cart/remove', [CustomerHomeController::class, 'removeFromCart'])->name('cart.remove-old');
    Route::post('/cart/checkout', [CustomerHomeController::class, 'checkout'])->name('cart.checkout');

    // Alternative Cart System
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang');
    Route::post('/keranjang', [KeranjangController::class, 'add'])->name('keranjang.add');
    Route::patch('/keranjang/{id}', [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::delete('/keranjang/{id}', [KeranjangController::class, 'remove'])->name('keranjang.remove');

    // Checkout & Payment
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pembayaran/{id}/upload', [PembayaranController::class, 'uploadForm'])->name('pembayaran.upload');
    Route::post('/pembayaran/{id}/upload', [PembayaranController::class, 'upload'])->name('pembayaran.upload.post');

    // QRIS & Struk
    Route::get('/qris/{id}', [CheckoutController::class, 'qris'])->name('qris');
    Route::get('/struk/{id}', [CheckoutController::class, 'struk'])->name('struk');

    // Orders, Tracking, & Invoices (PERBAIKAN: Rute /pesanan lama dimatikan agar tidak bentrok)
    // Route::get('/pesanan', [PenyewaanTrackingController::class, 'index'])->name('pesanan.old');
    Route::get('/pesanan/track/{kode}', [PenyewaanTrackingController::class, 'show'])->name('pesanan.show');
    Route::get('/pesanan/{id}/pdf', [PdfController::class, 'download'])->name('pesanan.pdf');
    Route::get('/order/{id}', [CustomerHomeController::class, 'orderDetail'])->name('order.detail');
    Route::get('/order/{id}/cancel', [CustomerHomeController::class, 'cancelOrder'])->name('order.cancel');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/{barang}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Chat & Reviews
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/chat', [ChatController::class, 'send'])->name('chat.send');
    Route::post('/ulasan/{penyewaan}', [UlasanController::class, 'store'])->name('ulasan.store');

    // Notifications
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'readAll']);

    // ─── RUTE RIWAYAT TRANSAKSI CUSTOMER TERBARU (DIJAMIN TIDAK BENTROK) ───
    Route::get('/pesanan', [\App\Http\Controllers\Customer\PesananController::class, 'index'])->name('pesanan');
    Route::post('/pesanan/{id}/selesai', [\App\Http\Controllers\Customer\PesananController::class, 'selesaikan'])->name('pesanan.selesai');
});