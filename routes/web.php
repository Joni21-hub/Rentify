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

// ─── 1. KATALOG PUBLIK (BISA DIAKSES SIAPA SAJA TANPA LOGIN) ───
Route::name('customer.')->group(function () {
    Route::get('/', [CustomerHomeController::class, 'index'])->name('home');
    Route::get('/tentang', [CustomerHomeController::class, 'about'])->name('about');
    Route::get('/moments', [\App\Http\Controllers\Customer\MomentPageController::class, 'index'])->name('moments');
    Route::get('/moments/create', [\App\Http\Controllers\Customer\MomentPageController::class, 'create'])->name('moments.create');
    Route::get('/search', [MarketplaceController::class, 'search'])->name('search');
    Route::get('/kategori/{id}', [MarketplaceController::class, 'kategori'])->name('kategori');
    Route::get('/customer/kategori/{id}', [MarketplaceController::class, 'kategori']);
    
    Route::get('/customer/barang/{slug}', [BarangDetailController::class, 'show'])->name('barang.show');
    Route::get('/customer/barang/{id}/booked-dates', [BarangDetailController::class, 'getBookedDates'])->name('barang.booked_dates');
    
    // Rute Profil Toko Publik (Katalog Barang Toko & Voucher)
    Route::get('/toko/{id}', [\App\Http\Controllers\Customer\StoreController::class, 'show'])->name('toko.show');
    Route::get('/customer/toko/{id}', [\App\Http\Controllers\Customer\StoreController::class, 'show']);
    
    Route::get('/lokasi', [App\Http\Controllers\Customer\LokasiController::class, 'index'])->name('lokasi');
    Route::post('/lokasi', [App\Http\Controllers\Customer\LokasiController::class, 'store'])->name('lokasi.store');
    Route::get('/customer/lokasi', [App\Http\Controllers\Customer\LokasiController::class, 'index']);
    Route::post('/customer/lokasi', [App\Http\Controllers\Customer\LokasiController::class, 'store']);
    Route::get('/wallet/coins', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'walletCoins'])->middleware('auth')->name('wallet.coins.direct');
    
    // Rute Akun Publik (Menangani Guest Landing State & Profil Terautentikasi)
    Route::get('/account', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'account'])->name('account');
    Route::get('/customer/account', [\App\Http\Controllers\Customer\CustomerDashboardController::class, 'account'])->name('account.alias');
});


// ─── 2. AUTHENTICATION ROUTES ─────────────────────────────────────
Route::get('/redirect-role', [AuthController::class, 'redirectByRole']); // Fallback redirect

// PERBAIKAN: Menangkap lemparan default Laravel saat user yang sudah login mengakses /login
Route::get('/home', [AuthController::class, 'redirectByRole']); 

Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
// ─── LUPA & RESET KATA SANDI ─────────────────────────────────────────────────
use App\Http\Controllers\ForgotPasswordController;
Route::get('/forgot-password',       [ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password',      [ForgotPasswordController::class, 'sendOtp'])->name('password.send.otp');
Route::get('/forgot-password/otp',   [ForgotPasswordController::class, 'showOtpForm'])->name('password.reset.otp.form');
Route::post('/forgot-password/otp',  [ForgotPasswordController::class, 'verifyOtp'])->name('password.reset.otp.verify');
Route::get('/forgot-password/baru',  [ForgotPasswordController::class, 'showNewPasswordForm'])->name('password.reset.new.form');
Route::post('/forgot-password/baru', [ForgotPasswordController::class, 'saveNewPassword'])->name('password.reset.new.save');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── GOOGLE SOCIALITE ROUTES ──────────────────────────────────────────────────
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
});

// ─── DUAL ROLE SELECTION & SWITCHING ──────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/select-role', [AuthController::class, 'showSelectRole'])->name('role.select');
    Route::post('/select-role', [AuthController::class, 'selectRole'])->name('role.select.post');
    Route::get('/switch-role/{role}', [AuthController::class, 'switchRole'])->name('role.switch');
});


// ─── 3. ADMIN ROUTES (TERKUNCI) ──────────────────────────────────────────────
Route::get('/admin/vendors-validation', [AdminVendorController::class, 'validasiVendor'])->name('admin.vendors.validation');
Route::post('/admin/vendors/{id}/approve-validation', [AdminVendorController::class, 'approveVendor'])->name('admin.vendors.approve-validation');
Route::post('/admin/vendors/{id}/reject-validation', [AdminVendorController::class, 'rejectVendor'])->name('admin.vendors.reject');

// Admin Masuk ke Toko Vendor (Impersonate & Leave)
Route::get('/admin/vendors/{id}/impersonate', [AdminVendorController::class, 'impersonate'])->middleware('auth')->name('admin.vendors.impersonate');
Route::post('/admin/vendors/{id}/impersonate', [AdminVendorController::class, 'impersonate'])->middleware('auth');
Route::get('/admin/impersonate/leave', [AdminVendorController::class, 'leaveImpersonate'])->middleware('auth')->name('admin.impersonate.leave');

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
    Route::patch('barang/{id}/quick-stock', [AdminBarangController::class, 'quickStock'])->name('barang.quickStock');
    Route::delete('barang/{id}/fotos/{fotoId}', [AdminBarangController::class, 'destroyFoto'])->name('barang.fotos.destroy');
    Route::resource('barang', AdminBarangController::class)->except(['approve', 'reject']);
    Route::resource('kategori', AdminKategoriController::class);
    Route::resource('cabang', AdminCabangController::class);

    Route::get('pembayaran', [AdminPembayaranController::class, 'index'])->name('pembayaran');
    Route::patch('pembayaran/{id}/verify', [AdminPembayaranController::class, 'verify'])->name('pembayaran.verify');
    Route::patch('pembayaran/{id}/refund', [AdminPembayaranController::class, 'refund'])->name('pembayaran.refund');

    // Monitoring Mutasi Rekening & Simulasi Auto-Detect Android
    Route::get('mutasi', [\App\Http\Controllers\Admin\AdminMutasiController::class, 'index'])->name('mutasi.index');
    Route::post('mutasi/simulate', [\App\Http\Controllers\Admin\AdminMutasiController::class, 'simulate'])->name('mutasi.simulate');
    Route::post('mutasi/{id}/match', [\App\Http\Controllers\Admin\AdminMutasiController::class, 'manualMatch'])->name('mutasi.match');

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

    // Rute Banned Sementara Vendor
    Route::patch('/vendors/{id}/suspend', [AdminDashboardController::class, 'suspendVendor']);
    Route::patch('/vendors/{id}/activate', [AdminDashboardController::class, 'activateVendor']);
    
    // PERBAIKAN: Rute Kill-Switch Voucher Vendor
    Route::patch('/vouchers/{id}/suspend', [AdminDashboardController::class, 'suspendVoucher']);
    Route::patch('/vouchers/{id}/activate', [AdminDashboardController::class, 'activateVoucher']);

    // --- WHATSAPP GATEWAY (MANDIRI & BEBAS BIAYA)
    Route::get('whatsapp', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::get('whatsapp/status', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'checkStatus'])->name('whatsapp.status');
    Route::post('whatsapp/test-send', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'testSend'])->name('whatsapp.test');
    Route::post('whatsapp/reset-sessions', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'resetSessions'])->name('whatsapp.reset-sessions');
    Route::post('whatsapp/logout', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'logout'])->name('whatsapp.logout');
});


// ─── ROUTE OTP ──────────────────────────────────────────────────────────────────
Route::get('/otp/verify', [\App\Http\Controllers\OtpController::class, 'showVerifyForm'])->name('otp.verify');
Route::post('/otp/verify', [\App\Http\Controllers\OtpController::class, 'verify']);
Route::post('/otp/resend', [\App\Http\Controllers\OtpController::class, 'resend'])->name('otp.resend');

// ─── 4. VENDOR ROUTES (TERKUNCI) ──────────────────────────────────────────────
Route::get('/vendor/register', [VendorController::class, 'showRegisterForm'])->name('vendor.register');
Route::post('/vendor/register', [VendorController::class, 'register']);
Route::view('/vendor/registration-success', 'auth.vendor-success')->name('vendor.register.success');

Route::prefix('vendor')->name('vendor.')
    ->middleware(['auth', 'role:vendor', 'verified'])
    ->group(function () {

    Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');

    Route::resource('barang', VendorBarangController::class);
    Route::post('barang/{id}/fotos', [VendorFotoBarangController::class, 'upload'])->name('barang.fotos.upload');
    Route::patch('barang/{id}/fotos/{foto}/cover', [VendorFotoBarangController::class, 'setCover'])->name('barang.fotos.cover');
    Route::delete('fotos/{foto}', [VendorFotoBarangController::class, 'destroy'])->name('fotos.destroy');
    Route::post('barang/{id}/generate-copywriting', [VendorBarangController::class, 'generateMarketingCopywriting'])->name('barang.marketing_copywriting');

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

    // --- VOUCHER TOKO
    Route::get('voucher', [\App\Http\Controllers\Vendor\VendorVoucherController::class, 'index'])->name('voucher.index');
    Route::post('voucher', [\App\Http\Controllers\Vendor\VendorVoucherController::class, 'store'])->name('voucher.store');
    Route::delete('voucher/{id}', [\App\Http\Controllers\Vendor\VendorVoucherController::class, 'destroy'])->name('voucher.destroy');
});


// ─── 5. TRANSAKSI CUSTOMER ROUTES (TERKUNCI LOGIN) ───────────────────────
Route::get('/customer', [CustomerDashboardController::class, 'index'])->middleware(['auth', 'role:customer']);

Route::prefix('customer')->name('customer.')
    ->middleware(['auth', 'role:customer'])
    ->group(function () {

    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    
    // Rute Dompet Koin Rentify
    Route::get('/wallet/coins', [CustomerDashboardController::class, 'walletCoins'])->name('wallet.coins');
    
    // Rute Pengaturan Akun Customer
    Route::get('/settings', [CustomerDashboardController::class, 'settings'])->name('settings');
    
    Route::get('/settings/profile', [CustomerDashboardController::class, 'settingsProfile'])->name('settings.profile');
    Route::post('/settings/profile', [CustomerDashboardController::class, 'updateProfile'])->name('settings.profile.update');
    
    Route::get('/settings/password', [CustomerDashboardController::class, 'settingsPassword'])->name('settings.password');
    Route::post('/settings/password', [CustomerDashboardController::class, 'updatePassword'])->name('settings.password.update');
    
    Route::get('/settings/whatsapp', [CustomerDashboardController::class, 'settingsWhatsapp'])->name('settings.whatsapp');
    Route::post('/settings/whatsapp', [CustomerDashboardController::class, 'updateWhatsapp'])->name('settings.whatsapp.update');
    Route::post('/settings/whatsapp/verify', [CustomerDashboardController::class, 'verifyWhatsapp'])->name('settings.whatsapp.verify');

    Route::get('/settings/email', [CustomerDashboardController::class, 'settingsEmail'])->name('settings.email');
    Route::post('/settings/email/request', [CustomerDashboardController::class, 'requestEmailChange'])->name('settings.email.request');
    Route::post('/settings/email/verify', [CustomerDashboardController::class, 'verifyEmailChange'])->name('settings.email.verify');
    
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

    // ── Checkout & Payment (QRIS & COD) ──────────────────────────────────────
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Pembayaran QRIS Dinamis Standar Bank Indonesia
    Route::get('/qris/{id}', [CheckoutController::class, 'qris'])->name('qris');

    // AJAX Polling & Konfirmasi: cek status pembayaran (QRIS & DB)
    Route::get('/pembayaran/check-status/{id}', [PembayaranController::class, 'checkStatus'])->name('pembayaran.check_status');
    Route::post('/pembayaran/confirm/{id}', [PembayaranController::class, 'confirmPayment'])->name('pembayaran.confirm');
    Route::post('/pembayaran/simulate/{id}', [PembayaranController::class, 'simulatePayment'])->name('pembayaran.simulate');

    // AJAX Voucher Toko
    Route::post('/checkout/cek-voucher', [CheckoutController::class, 'cekVoucher'])->name('checkout.cek_voucher');

    // Struk & COD
    Route::post('/checkout/{id}/set-cod', [CheckoutController::class, 'setCod'])->name('checkout.set_cod');
    Route::get('/struk/{id}', [CheckoutController::class, 'struk'])->name('struk');

    // Orders, Tracking, & Invoices
    Route::get('/pesanan/track/{kode}', [PenyewaanTrackingController::class, 'show'])->name('pesanan.show');
    Route::get('/pesanan/{id}/pdf', [PdfController::class, 'download'])->name('pesanan.pdf');
    Route::get('/order/{id}', [CustomerHomeController::class, 'orderDetail'])->name('order.detail');
    Route::get('/order/{id}/cancel', [CustomerHomeController::class, 'cancelOrder'])->name('order.cancel');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/{barang}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Voucher & Promo Customer
    Route::get('/voucher', [\App\Http\Controllers\Customer\CustomerVoucherController::class, 'index'])->name('voucher');

    // Chat & Reviews
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/chat', [ChatController::class, 'send'])->name('chat.send');
    Route::post('/ulasan/{penyewaan}', [UlasanController::class, 'store'])->name('ulasan.store');

    // Notifications
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'readAll']);

    // Riwayat Transaksi Customer
    Route::get('/pesanan', [\App\Http\Controllers\Customer\PesananController::class, 'index'])->name('pesanan');
    Route::post('/pesanan/{id}/selesai', [\App\Http\Controllers\Customer\PesananController::class, 'selesaikan'])->name('pesanan.selesai');
    Route::post('/pesanan/{id}/batal', [\App\Http\Controllers\Customer\PesananController::class, 'batalkan'])->name('pesanan.batal');
});


// ─── ANDROID NOTIFICATION MUTASI WEBHOOK (AUTO-CONFIRM 24/7) ─────────────────
Route::post('/api/webhook/mutasi', [\App\Http\Controllers\Api\MutasiWebhookController::class, 'handle'])
    ->name('webhook.mutasi')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// ─── WHATSAPP WEBHOOK (SELF-HOSTED GATEWAY & AUTO-REPLY CS) ────────────────────
Route::post('/webhook/whatsapp', [\App\Http\Controllers\WhatsAppWebhookController::class, 'handle'])
    ->name('whatsapp.webhook');


// ─── EMAIL VERIFICATION ROUTES ──────────────────────────────────────────────────
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// ─── MOMENTS & LIKE REWARD API ────────────────────────────────────────────────
Route::prefix('api/moments')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\MomentController::class, 'index'])
        ->name('api.moments.index');
    Route::post('/', [\App\Http\Controllers\Api\MomentController::class, 'store'])
        ->name('api.moments.store');
    Route::get('/{id}', [\App\Http\Controllers\Api\MomentController::class, 'show'])
        ->name('api.moments.show');
    Route::match(['put', 'patch'], '/{id}', [\App\Http\Controllers\Api\MomentController::class, 'update'])
        ->name('api.moments.update');
    Route::delete('/{id}', [\App\Http\Controllers\Api\MomentController::class, 'destroy'])
        ->name('api.moments.destroy');

    Route::get('/{id}/suggestions', [\App\Http\Controllers\Api\MomentController::class, 'suggestions'])
        ->name('api.moments.suggestions');
    Route::post('/{id}/ai-tag', [\App\Http\Controllers\Api\MomentController::class, 'reprocessAiTag'])
        ->name('api.moments.ai_tag');

    Route::post('/{id}/like', [\App\Http\Controllers\Api\MomentLikeController::class, 'store'])
        ->name('api.moments.like');
    Route::delete('/{id}/like', [\App\Http\Controllers\Api\MomentLikeController::class, 'destroy'])
        ->name('api.moments.unlike');

    Route::post('/{id}/bookmark', [\App\Http\Controllers\Api\MomentController::class, 'bookmark'])
        ->name('api.moments.bookmark');
    Route::delete('/{id}/bookmark', [\App\Http\Controllers\Api\MomentController::class, 'unbookmark'])
        ->name('api.moments.unbookmark');

    Route::post('/{id}/report', [\App\Http\Controllers\Api\MomentController::class, 'report'])
        ->name('api.moments.report');

    // Komentar Momen
    Route::get('/{id}/comments', [\App\Http\Controllers\Api\MomentCommentController::class, 'index'])
        ->name('api.moments.comments.index');
    Route::post('/{id}/comments', [\App\Http\Controllers\Api\MomentCommentController::class, 'store'])
        ->name('api.moments.comments.store');
    Route::delete('/{id}/comments/{commentId}', [\App\Http\Controllers\Api\MomentCommentController::class, 'destroy'])
        ->name('api.moments.comments.destroy');
});

// ─── MOMENT CONTENT MODERATION API (ADMIN) ────────────────────────────────────
Route::prefix('api/admin/moderation')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminMomentModerationController::class, 'index'])
        ->name('api.admin.moderation.index');
    Route::post('/{id}/dismiss', [\App\Http\Controllers\Admin\AdminMomentModerationController::class, 'dismiss'])
        ->name('api.admin.moderation.dismiss');
    Route::post('/{id}/uphold', [\App\Http\Controllers\Admin\AdminMomentModerationController::class, 'uphold'])
        ->name('api.admin.moderation.uphold');
});

// ─── WALLET & COINS API ───────────────────────────────────────────────────────
Route::prefix('api/wallet')->group(function () {
    Route::get('/coins', [\App\Http\Controllers\Api\WalletController::class, 'index'])
        ->name('api.wallet.coins');
    Route::post('/coins/calculate-discount', [\App\Http\Controllers\Api\WalletController::class, 'calculateDiscount'])
        ->name('api.wallet.calculate_discount');
});

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/redirect-role');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('success', 'Tautan verifikasi telah dikirim ulang!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');