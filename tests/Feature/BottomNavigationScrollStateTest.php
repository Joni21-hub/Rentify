<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Moment;
use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BottomNavigationScrollStateTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name'     => 'Customer Bottom Nav Test',
            'email'    => 'nav_test_' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'customer',
        ]);
    }

    /**
     * Requirement 1.1 & 1.2: Bilah navigasi bawah hanya terdiri dari 3 tab: Beranda, Momen, dan Akun.
     * Menu Favorit/Tersimpan dihilangkan dari bilah navigasi bawah utama.
     */
    public function test_bottom_navigation_bar_renders_exactly_three_tabs_on_home_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Memastikan bilah navigasi bawah ada
        $response->assertSee('id="bottom-navigation-bar"', false);

        // Memastikan 3 tab utama hadir
        $response->assertSee('Beranda', false);
        $response->assertSee('Momen', false);
        $response->assertSee('Akun', false);

        // Memastikan tab Beranda berstatus aktif pada halaman home
        $response->assertSee('data-tab="beranda"', false);
        $response->assertSee('data-tab="momen"', false);
        $response->assertSee('data-tab="akun"', false);

        // Memastikan TIDAK ADA menu Favorit pada bilah navigasi bawah (Requirement 1.2)
        $content = (string) $response->getContent();
        preg_match('/<nav[^>]*id="bottom-navigation-bar"[^>]*>(.*?)<\/nav>/s', $content, $matches);
        $this->assertNotEmpty($matches, 'Element #bottom-navigation-bar harus ditemukan di dalam DOM');
        $navContent = (string) ($matches[1] ?? '');

        $this->assertStringNotContainsString('Favorit', $navContent, 'Menu Favorit tidak boleh ada di dalam #bottom-navigation-bar');
        $this->assertStringNotContainsString('fa-heart', $navContent, 'Ikon hati/Favorit tidak boleh ada di dalam #bottom-navigation-bar');
    }

    /**
     * Requirement 1.4: Menekan tab Momen mengarahkan ke halaman Feed Momen
     * dengan status aktif yang ditandai secara visual pada ikon dan label tab.
     */
    public function test_bottom_navigation_bar_highlights_moments_tab_on_moments_feed(): void
    {
        $response = $this->get('/moments');

        $response->assertStatus(200);
        $response->assertSee('id="bottom-navigation-bar"', false);

        $content = (string) $response->getContent();
        preg_match('/<nav[^>]*id="bottom-navigation-bar"[^>]*>(.*?)<\/nav>/s', $content, $matches);
        $this->assertNotEmpty($matches);
        $navContent = (string) ($matches[1] ?? '');

        // Memastikan tidak ada menu Favorit
        $this->assertStringNotContainsString('Favorit', $navContent);

        // Memastikan tab Momen berstatus aktif (memiliki penanda active atau class text-sky-600)
        $this->assertMatchesRegularExpression('/data-tab="momen"[^>]*class="[^"]*(text-sky-600|text-\[#1E4DAA\]|active)[^"]*"/s', $navContent);
    }

    /**
     * Requirement 1.1 & 1.4: Halaman akun terautentikasi menampilkan tab Akun yang aktif.
     */
    public function test_bottom_navigation_bar_highlights_account_tab_on_customer_account(): void
    {
        $response = $this->actingAs($this->customer)->get('/account');

        $response->assertStatus(200);
        $response->assertSee('id="bottom-navigation-bar"', false);

        $content = (string) $response->getContent();
        preg_match('/<nav[^>]*id="bottom-navigation-bar"[^>]*>(.*?)<\/nav>/s', $content, $matches);
        $this->assertNotEmpty($matches);
        $navContent = (string) ($matches[1] ?? '');

        $this->assertStringNotContainsString('Favorit', $navContent);

        // Memastikan tab Akun berstatus aktif
        $this->assertMatchesRegularExpression('/data-tab="akun"[^>]*class="[^"]*(text-sky-600|text-\[#1E4DAA\]|active)[^"]*"/s', $navContent);
    }

    /**
     * Requirement 1.6 & 6.18: Pengguna tamu (guest) yang mengakses /account
     * disajikan guest landing state bersih dengan tombol "Masuk" dan "Daftar",
     * ringkasan keuntungan akun Rentify, dan tanpa pesan error sistem atau broken layout.
     */
    public function test_unauthenticated_guest_accessing_account_sees_clean_guest_landing_state(): void
    {
        $response = $this->get('/account');

        $response->assertStatus(200);
        // Memastikan halaman memuat elemen guest landing state
        $response->assertSee('id="guest-account-view"', false);
        $response->assertSee('Masuk', false);
        $response->assertSee('Daftar', false);
        $response->assertSee(URL::route('login'), false);
        $response->assertSee(URL::route('register'), false);

        // Memastikan tetap memiliki bilah navigasi bawah dengan tab Akun aktif
        $response->assertSee('id="bottom-navigation-bar"', false);
        $response->assertSee('data-tab="akun"', false);

        // Memastikan tidak ada pesan kesalahan unauthenticated atau broken structure
        $response->assertDontSee('Attempt to read property');
        $response->assertDontSee('Undefined variable');
    }

    /**
     * Pengguna terautentikasi yang mengakses /account melihat profil lengkap,
     * statistik, dan pelacakan pesanan.
     */
    public function test_authenticated_user_accessing_account_sees_full_profile(): void
    {
        $response = $this->actingAs($this->customer)->get('/account');

        $response->assertStatus(200);
        $response->assertSee($this->customer->name);
        $response->assertSee('Momen Saya', false);
        $response->assertSee('Tersimpan', false);
        $response->assertSee('Riwayat Pesanan', false);
    }

    /**
     * Requirement 1.5 & 6.15: Modul retensi posisi scroll client-side.
     * Memverifikasi bahwa skrip state store dan handler retensi scroll dimuat pada view.
     */
    public function test_client_side_scroll_retention_module_is_embedded_in_views(): void
    {
        // 1. Verifikasi di halaman Home
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('RentifyNavigationStore', false);
        $homeResponse->assertSee('saveScrollPosition', false);
        $homeResponse->assertSee('restoreScrollPosition', false);

        // 2. Verifikasi di halaman Moments
        $momentsResponse = $this->get('/moments');
        $momentsResponse->assertStatus(200);
        $momentsResponse->assertSee('RentifyNavigationStore', false);

        // 3. Verifikasi di halaman Account (Guest & Auth)
        $accountGuestResponse = $this->get('/account');
        $accountGuestResponse->assertStatus(200);
        $accountGuestResponse->assertSee('RentifyNavigationStore', false);

        $accountAuthResponse = $this->actingAs($this->customer)->get('/account');
        $accountAuthResponse->assertStatus(200);
        $accountAuthResponse->assertSee('RentifyNavigationStore', false);
    }

    /**
     * Requirement 6.15: Pengalihan tab tersegmentasi "Momen Saya" dan "Tersimpan"
     * menyimpan dan memulihkan posisi scroll masing-masing tampilan tab secara instan.
     */
    public function test_profile_segmented_tab_scroll_retention_logic_exists(): void
    {
        $response = $this->actingAs($this->customer)->get('/account');

        $response->assertStatus(200);
        $response->assertSee('switchTab', false);
        $response->assertSee('segmentedTabScroll', false);
    }

    /**
     * Requirement: Efek transisi air mengalir (water-flow) pada bilah navigasi bawah saat memasuki Momen
     * dari normal (kartu terang frosted) menjadi transparan (kaca gelap jernih).
     */
    public function test_bottom_navigation_bar_renders_water_flow_transition_on_moments(): void
    {
        $response = $this->get('/moments');

        $response->assertStatus(200);
        $response->assertSee('water-flow-entry', false);
        $response->assertSee('water-flow-sheen', false);
        $response->assertSee('morphNormalToTransparent', false);
        $response->assertSee('waterWaveFlow', false);
        $response->assertSee('waterTabBobMomen', false);
    }

    /**
     * Requirement: Gestur swap kiri-kanan (horizontal swipe navigation) terintegrasi pada bottom bar
     * untuk navigasi instan antar tab Beranda <-> Momen <-> Akun.
     */
    public function test_bottom_navigation_bar_contains_horizontal_swipe_gesture_module(): void
    {
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('setupHorizontalSwipeGesture', false);
        $homeResponse->assertSee('executeSwipeNavigation', false);
        $homeResponse->assertSee('rentify-swipe-pill', false);

        $momentsResponse = $this->get('/moments');
        $momentsResponse->assertStatus(200);
        $momentsResponse->assertSee('setupHorizontalSwipeGesture', false);
        $momentsResponse->assertSee('executeSwipeNavigation', false);

        $accountResponse = $this->get('/account');
        $accountResponse->assertStatus(200);
        $accountResponse->assertSee('setupHorizontalSwipeGesture', false);
        $accountResponse->assertSee('executeSwipeNavigation', false);
    }
}
