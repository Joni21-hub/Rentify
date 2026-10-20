<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiMarketingPost;
use App\Models\Banner;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Penyewaan;
use App\Models\Voucher;
use App\Services\AiMarketingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminAiMarketingController extends Controller
{
    protected AiMarketingService $aiService;

    public function __construct(AiMarketingService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Dashboard AI Marketing & Pusat Kendali Auto-Promosi
     */
    public function index()
    {
        $totalBarang = Barang::count();
        $totalKategori = Kategori::count();
        $totalBanners = Banner::count();
        $totalVouchers = Voucher::count();

        // Kategori terpopuler berdasarkan jumlah barang & rental
        $kategoriStats = Kategori::withCount('barang')->get();
        $activeBanners = Banner::latest()->take(6)->get();
        $isAiOnline = $this->aiService->isAiEnabled();

        // Status & Jadwal Harian AI (50:50 Customer vs Vendor)
        $today = Carbon::today();
        $dayNumber = (int) $today->format('j');
        $isOdd = ($dayNumber % 2 !== 0);
        $dayType = $isOdd ? 'ganjil' : 'genap';
        $todayDefaultTarget = $isOdd ? 'customer' : 'vendor';

        // Konten Hari Ini (atau konten terbaru yang aktif)
        $todayPost = AiMarketingPost::where('tanggal', $today->toDateString())->latest()->first();
        if (!$todayPost) {
            $todayPost = AiMarketingPost::latest()->first();
        }

        // Riwayat Konten
        $recentPosts = AiMarketingPost::latest()->take(25)->get();

        // Metrik AI Marketing
        $totalGeneratedPosts = AiMarketingPost::count();
        $totalCustomerPosts = AiMarketingPost::where('target_audience', 'customer')->count();
        $totalVendorPosts = AiMarketingPost::where('target_audience', 'vendor')->count();
        $totalPublishedSocial = AiMarketingPost::where('posted_to_instagram', true)
            ->orWhere('posted_to_tiktok', true)
            ->count();

        // Pengaturan Sistem
        $settings = [
            'gemini_api_key'      => config('rentify_ai.gemini.api_key') ?: env('GEMINI_API_KEY', ''),
            'gemini_model'        => config('rentify_ai.gemini.model') ?: env('GEMINI_MODEL', 'gemini-1.5-flash'),
            'schedule_time'       => config('rentify_ai.automation.schedule_time', '08:00'),
            'auto_post_instagram' => (bool) config('rentify_ai.automation.auto_post_instagram', false),
            'auto_post_tiktok'    => (bool) config('rentify_ai.automation.auto_post_tiktok', false),
            'instagram_token'     => config('rentify_ai.social.instagram.access_token', ''),
            'instagram_account_id'=> config('rentify_ai.social.instagram.account_id', ''),
            'tiktok_token'        => config('rentify_ai.social.tiktok.access_token', ''),
            'brand_url'           => config('rentify_ai.brand.website_url', env('APP_URL', 'http://localhost:8000')),
        ];

        return view('admin.ai-marketing.index', compact(
            'totalBarang',
            'totalKategori',
            'totalBanners',
            'totalVouchers',
            'kategoriStats',
            'activeBanners',
            'isAiOnline',
            'today',
            'dayNumber',
            'dayType',
            'todayDefaultTarget',
            'todayPost',
            'recentPosts',
            'totalGeneratedPosts',
            'totalCustomerPosts',
            'totalVendorPosts',
            'totalPublishedSocial',
            'settings'
        ));
    }

    /**
     * Trigger Pembuatan Konten Harian AI (Manual / On-Demand)
     */
    public function generateDailyContent(Request $request)
    {
        $request->validate([
            'target_audience' => 'nullable|in:auto,customer,vendor',
            'tanggal'         => 'nullable|date',
        ]);

        $targetDate = $request->tanggal ? Carbon::parse($request->tanggal) : Carbon::today();
        $forceTarget = ($request->target_audience && $request->target_audience !== 'auto') 
            ? $request->target_audience 
            : null;

        $post = $this->aiService->generateDailyVideoContent($targetDate, $forceTarget);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Konten video ' . ($post->target_audience === 'customer' ? 'Customer (Penyewa)' : 'Vendor (Pemilik Barang)') . ' berhasil dibuat!',
                'post'    => $post,
            ]);
        }

        return redirect()->route('admin.ai-marketing.index')
            ->with('success', 'Ide konten video Reels/TikTok harian berhasil dibuat oleh AI!');
    }

    /**
     * Publish Konten ke Instagram atau TikTok
     */
    public function publishSocial(Request $request, $id)
    {
        /** @var AiMarketingPost $post */
        $post = AiMarketingPost::findOrFail($id);
        $channel = $request->input('channel', 'all'); // 'instagram', 'tiktok', 'all'

        $results = [];

        if ($channel === 'instagram' || $channel === 'all') {
            $results['instagram'] = $this->aiService->publishToInstagram($post);
        }

        if ($channel === 'tiktok' || $channel === 'all') {
            $results['tiktok'] = $this->aiService->publishToTikTok($post);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'results' => $results,
                'post'    => $post->fresh(),
            ]);
        }

        return redirect()->route('admin.ai-marketing.index')
            ->with('success', 'Perintah publikasi media sosial berhasil diproses!');
    }

    /**
     * Simpan Visual 9:16 dari HTML5 Canvas Export
     */
    public function saveVisual(Request $request, $id)
    {
        $request->validate([
            'image_data' => 'required|string', // Data URL base64
        ]);

        /** @var AiMarketingPost $post */
        $post = AiMarketingPost::findOrFail($id);

        $imageData = $request->input('image_data');
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, etc.

            $imageData = base64_decode($imageData);
            if ($imageData === false) {
                return response()->json(['success' => false, 'message' => 'Invalid base64 image data'], 400);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid image format'], 400);
        }

        $dir = public_path('storage/ai_visuals');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $filename = 'reels_9_16_' . $post->id . '_' . time() . '.' . $type;
        $filePath = $dir . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($filePath, $imageData);

        $url = asset('storage/ai_visuals/' . $filename);
        $post->update([
            'visual_media_url' => $url,
        ]);

        return response()->json([
            'success'   => true,
            'image_url' => $url,
            'message'   => 'Visual 9:16 berhasil disimpan!'
        ]);
    }

    /**
     * Simpan Pengaturan AI Marketing & Integrasi
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'gemini_api_key'      => 'nullable|string|max:200',
            'gemini_model'        => 'nullable|string|max:50',
            'schedule_time'       => 'nullable|string|max:10',
            'instagram_token'     => 'nullable|string',
            'instagram_account_id'=> 'nullable|string|max:100',
            'tiktok_token'        => 'nullable|string',
        ]);

        // Simpan ke .env jika memungkinkan
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = (string) File::get($envPath);

            $replacements = [
                'GEMINI_API_KEY'            => $request->gemini_api_key,
                'GEMINI_MODEL'              => $request->gemini_model ?: 'gemini-1.5-flash',
                'RENTIFY_AI_SCHEDULE_TIME'  => $request->schedule_time ?: '08:00',
                'RENTIFY_AI_AUTOPOST_INSTAGRAM' => $request->has('auto_post_instagram') ? 'true' : 'false',
                'RENTIFY_AI_AUTOPOST_TIKTOK'    => $request->has('auto_post_tiktok') ? 'true' : 'false',
                'INSTAGRAM_ACCESS_TOKEN'    => $request->instagram_token,
                'INSTAGRAM_ACCOUNT_ID'      => $request->instagram_account_id,
                'TIKTOK_ACCESS_TOKEN'       => $request->tiktok_token,
            ];

            foreach ($replacements as $key => $val) {
                if ($val !== null) {
                    if (preg_match("/^{$key}=/m", $envContent)) {
                        $envContent = (string) preg_replace("/^{$key}=.*/m", "{$key}=\"{$val}\"", $envContent);
                    } else {
                        $envContent .= "\n{$key}=\"{$val}\"";
                    }
                }
            }

            File::put($envPath, $envContent);
        }

        return redirect()->route('admin.ai-marketing.index')
            ->with('success', 'Pengaturan AI Agent & Jadwal Otomasi berhasil disimpan!');
    }

    /**
     * Generate Kampanye Promosi Platform dengan AI (Legacy Support)
     */
    public function generateCampaign(Request $request)
    {
        $request->validate([
            'tema'              => 'required|string|max:150',
            'target_audiens'    => 'nullable|string|max:150',
            'usulan_diskon'     => 'nullable|string|max:100',
        ]);

        $campaign = $this->aiService->generateAdminCampaign(
            $request->tema,
            $request->target_audiens,
            $request->usulan_diskon
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'campaign' => $campaign,
            ]);
        }

        return back()->with('generated_campaign', $campaign);
    }

    /**
     * Cepat Buat Banner Baru dari Ide AI
     */
    public function createBannerFromAi(Request $request)
    {
        $request->validate([
            'judul_promo' => 'required|string|max:150',
            'gambar'      => 'required|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $file = $request->file('gambar');
        if (!$file) {
            return back()->withErrors(['gambar' => 'File gambar wajib diunggah.']);
        }

        $path = $file->store('banners', 'public');
        if (!$path) {
            return back()->withErrors(['gambar' => 'Gagal mengunggah file banner.']);
        }

        Banner::create([
            'judul_promo' => $request->judul_promo,
            'gambar_url'  => Storage::url($path),
        ]);

        return redirect()->route('admin.ai-marketing.index')->with('success', 'Banner promosi AI berhasil dipublikasikan!');
    }
}
