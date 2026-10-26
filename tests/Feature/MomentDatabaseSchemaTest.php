<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Moment;
use App\Models\MomentAiTagSuggestion;
use App\Models\MomentBookmark;
use App\Models\MomentCoinLedger;
use App\Models\MomentLike;
use App\Models\MomentModerationQueue;
use App\Models\MomentReport;
use App\Models\MomentTaggedProduct;
use App\Models\ProductStockWaitlist;
use App\Models\User;
use App\Models\UserCoinDeficit;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MomentDatabaseSchemaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_all_moment_and_coin_tables_exist_with_required_columns(): void
    {
        $tables = [
            'moments' => [
                'id', 'user_id', 'media_url', 'thumbnail_url', 'media_type',
                'video_duration_seconds', 'caption', 'likes_count', 'lifetime_minted_count',
                'bookmarks_count', 'reports_count', 'ranking_score', 'status', 'created_at', 'updated_at'
            ],
            'moment_likes' => [
                'id', 'moment_id', 'user_id', 'created_at'
            ],
            'moment_bookmarks' => [
                'id', 'user_id', 'moment_id', 'created_at'
            ],
            'moment_reports' => [
                'id', 'moment_id', 'user_id', 'category', 'reason_description', 'created_at'
            ],
            'moment_moderation_queue' => [
                'id', 'moment_id', 'status', 'total_reports', 'last_reported_at', 'reviewed_by', 'resolution_notes', 'created_at', 'updated_at'
            ],
            'moment_coin_ledgers' => [
                'id', 'user_id', 'interactor_id', 'moment_id', 'order_id', 'interaction_type',
                'coins_amount', 'remaining_lot_balance', 'minted_coin', 'created_at', 'updated_at'
            ],
            'user_coin_deficits' => [
                'id', 'user_id', 'deficit_coins', 'last_settled_at', 'created_at', 'updated_at'
            ],
            'moment_tagged_products' => [
                'id', 'moment_id', 'product_id', 'display_order', 'created_at', 'updated_at'
            ],
            'moment_ai_tag_suggestions' => [
                'id', 'moment_id', 'product_id', 'confidence_score', 'created_at', 'expires_at'
            ],
            'product_stock_waitlists' => [
                'id', 'user_id', 'product_id', 'status', 'created_at', 'notified_at'
            ],
        ];

        foreach ($tables as $tableName => $columns) {
            $this->assertTrue(Schema::hasTable($tableName), "Table {$tableName} does not exist.");
            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($tableName, $column),
                    "Table {$tableName} is missing column {$column}."
                );
            }
        }
    }

    public function test_moment_creation_and_user_relationships(): void
    {
        $author = User::create([
            'name' => 'Author User',
            'email' => 'author_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $moment = Moment::create([
            'user_id' => $author->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/sample.jpg',
            'thumbnail_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/sample-thumb.jpg',
            'media_type' => 'image',
            'caption' => 'Liburan seru camping di gunung!',
            'likes_count' => 0,
            'lifetime_minted_count' => 0,
            'bookmarks_count' => 0,
            'reports_count' => 0,
            'ranking_score' => 0,
            'status' => 'active',
        ]);

        $record = DB::table('moments')->where('id', $moment->id)->first();
        $this->assertNotNull($record);
        $this->assertEquals($author->id, $record->user_id);
        $this->assertEquals('Liburan seru camping di gunung!', $record->caption);

        // Test Author and User relationship
        $this->assertEquals($author->id, $moment->user->id);
        $this->assertEquals($author->id, $moment->author->id);
        $this->assertTrue($author->moments->contains($moment));
    }

    public function test_likes_interaction_and_uniqueness(): void
    {
        $author = User::create([
            'name' => 'Author User',
            'email' => 'author_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $interactor = User::create([
            'name' => 'Liker User',
            'email' => 'liker_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $moment = Moment::create([
            'user_id' => $author->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/sample.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        // Test Like
        $like = MomentLike::create([
            'moment_id' => $moment->id,
            'user_id' => $interactor->id,
        ]);

        $this->assertTrue($moment->isLikedBy($interactor));
        $this->assertFalse($moment->isLikedBy($author));

        // Test Unique Constraint on Like
        $this->expectException(QueryException::class);
        MomentLike::create([
            'moment_id' => $moment->id,
            'user_id' => $interactor->id,
        ]);
    }

    public function test_bookmarks_and_reports_uniqueness(): void
    {
        $author = User::create([
            'name' => 'Author User 2',
            'email' => 'author2_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $user = User::create([
            'name' => 'Bookmarker User',
            'email' => 'bookmarker_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $moment = Moment::create([
            'user_id' => $author->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/sample2.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        // Bookmark
        MomentBookmark::create([
            'user_id' => $user->id,
            'moment_id' => $moment->id,
        ]);

        $this->assertTrue($moment->isBookmarkedBy($user));
        $this->assertTrue($user->bookmarkedMoments->contains($moment));

        // Report
        $report = MomentReport::create([
            'moment_id' => $moment->id,
            'user_id' => $user->id,
            'category' => 'spam',
            'reason_description' => 'Spam link in post',
        ]);

        $this->assertEquals('spam', $report->category);
        $this->assertTrue($moment->reports->contains($report));

        // Moderation Queue
        $queue = MomentModerationQueue::create([
            'moment_id' => $moment->id,
            'status' => 'pending',
            'total_reports' => 1,
            'last_reported_at' => now(),
        ]);

        $this->assertEquals('pending', $queue->status);
        $this->assertEquals($moment->id, $queue->moment->id);
    }

    public function test_coin_ledger_and_deficit_management(): void
    {
        $creator = User::create([
            'name' => 'Creator User',
            'email' => 'creator_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $interactor = User::create([
            'name' => 'Fan User',
            'email' => 'fan_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $moment = Moment::create([
            'user_id' => $creator->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/sample3.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        // Mint 1 coin
        MomentCoinLedger::create([
            'user_id' => $creator->id,
            'interactor_id' => $interactor->id,
            'moment_id' => $moment->id,
            'interaction_type' => 'like_mint',
            'coins_amount' => 1,
            'remaining_lot_balance' => 1,
            'minted_coin' => true,
        ]);

        $this->assertEquals(1, $creator->fresh()->coin_balance);

        // Record deficit
        UserCoinDeficit::create([
            'user_id' => $creator->id,
            'deficit_coins' => 2,
            'last_settled_at' => now(),
        ]);

        $this->assertEquals(2, $creator->fresh()->coinDeficit->deficit_coins);
    }

    public function test_tagged_products_and_stock_waitlists(): void
    {
        $vendor = User::create([
            'name' => 'Vendor Test',
            'email' => 'vendor_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
        ]);

        $kategori = Kategori::create([
            'nama' => 'Camping Outdoor',
            'slug' => 'camping-outdoor-' . uniqid(),
            'is_active' => 1,
        ]);

        $barang = Barang::create([
            'vendor_id' => $vendor->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Tenda Dome Arpenaz 4.1',
            'slug' => 'tenda-dome-arpenaz-4-1-' . uniqid(),
            'deskripsi' => 'Tenda camping kapasitas 4 orang',
            'harga_sewa_harian' => 50000,
            'deposit' => 100000,
            'denda_per_hari' => 25000,
            'stok_total' => 0,
            'status' => 'habis',
            'is_approved' => 1,
        ]);

        $moment = Moment::create([
            'user_id' => $vendor->id,
            'media_url' => 'https://res.cloudinary.com/rentify/image/upload/v1/sample4.jpg',
            'media_type' => 'image',
            'status' => 'active',
        ]);

        // Tag product
        MomentTaggedProduct::create([
            'moment_id' => $moment->id,
            'product_id' => $barang->id,
            'display_order' => 1,
        ]);

        $this->assertTrue($moment->products->contains($barang));
        $this->assertTrue($barang->moments->contains($moment));

        // AI Tag Suggestion
        $suggestion = MomentAiTagSuggestion::create([
            'moment_id' => $moment->id,
            'product_id' => $barang->id,
            'confidence_score' => 0.9542,
            'expires_at' => now()->addDays(7),
        ]);

        $this->assertEquals(0.9542, (float) $suggestion->confidence_score);
        $this->assertEquals($barang->id, $suggestion->product->id);

        // Product Stock Waitlist
        $waitlist = ProductStockWaitlist::create([
            'user_id' => $vendor->id,
            'product_id' => $barang->id,
            'status' => 'pending',
        ]);

        $this->assertEquals('pending', $waitlist->status);
        $this->assertTrue($barang->stockWaitlists->contains($waitlist));
    }
}
