<?php

namespace Tests\Feature;

use App\Models\Moment;
use App\Models\MomentComment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MomentCommentSystemTest extends TestCase
{
    use DatabaseTransactions;

    protected User $author;
    protected User $commenter;
    protected User $stranger;
    protected Moment $moment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::create([
            'name'     => 'Moment Author',
            'email'    => 'author_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        $this->commenter = User::create([
            'name'     => 'Commenter User',
            'email'    => 'commenter_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        $this->stranger = User::create([
            'name'     => 'Stranger User',
            'email'    => 'stranger_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        $this->moment = Moment::create([
            'user_id'        => $this->author->id,
            'media_url'      => 'https://res.cloudinary.com/rentify/image/upload/v1/test_comment.jpg',
            'caption'        => 'Momen seru camping',
            'status'         => 'active',
            'likes_count'    => 0,
            'comments_count' => 0,
        ]);
    }

    public function test_guest_cannot_post_comment(): void
    {
        $response = $this->postJson("/api/moments/{$this->moment->id}/comments", [
            'comment' => 'Komentar dari guest',
        ]);

        $response->assertStatus(401);
        $this->assertFalse(
            MomentComment::where('moment_id', $this->moment->id)->where('comment', 'Komentar dari guest')->exists()
        );
    }

    public function test_authenticated_user_can_post_comment(): void
    {
        $response = $this->actingAs($this->commenter)
            ->postJson("/api/moments/{$this->moment->id}/comments", [
                'comment' => 'Keren banget alat rentalnya!',
            ]);

        $response->assertStatus(201);
        $res = $response->json();
        $this->assertTrue($res['success']);
        $this->assertEquals(1, $res['comments_count']);
        $this->assertEquals($this->moment->id, $res['data']['moment_id']);
        $this->assertEquals($this->commenter->id, $res['data']['user_id']);
        $this->assertEquals('Keren banget alat rentalnya!', $res['data']['comment']);

        $this->assertTrue(
            MomentComment::where('moment_id', $this->moment->id)
                ->where('user_id', $this->commenter->id)
                ->where('comment', 'Keren banget alat rentalnya!')
                ->exists()
        );

        $this->moment->refresh();
        $this->assertEquals(1, $this->moment->comments_count);
    }

    public function test_comment_validation_rejects_empty_and_excessive_length(): void
    {
        // Komentar kosong
        $responseEmpty = $this->actingAs($this->commenter)
            ->postJson("/api/moments/{$this->moment->id}/comments", [
                'comment' => '',
            ]);
        $responseEmpty->assertStatus(422);

        // Komentar lebih dari 500 karakter
        $responseTooLong = $this->actingAs($this->commenter)
            ->postJson("/api/moments/{$this->moment->id}/comments", [
                'comment' => str_repeat('a', 501),
            ]);
        $responseTooLong->assertStatus(422);
    }

    public function test_fetch_comments_list(): void
    {
        MomentComment::create([
            'moment_id' => $this->moment->id,
            'user_id'   => $this->commenter->id,
            'comment'   => 'Komentar pertama',
        ]);

        MomentComment::create([
            'moment_id' => $this->moment->id,
            'user_id'   => $this->stranger->id,
            'comment'   => 'Komentar kedua',
        ]);

        $this->moment->update(['comments_count' => 2]);

        $response = $this->getJson("/api/moments/{$this->moment->id}/comments");

        $response->assertStatus(200);
        $res = $response->json();
        $this->assertTrue($res['success']);
        $this->assertEquals(2, $res['comments_count']);

        $data = $res['data'];
        $this->assertCount(2, $data);
        $this->assertEquals('Komentar kedua', $data[0]['comment']);
        $this->assertEquals('Komentar pertama', $data[1]['comment']);
    }

    public function test_comment_author_can_delete_own_comment(): void
    {
        $comment = MomentComment::create([
            'moment_id' => $this->moment->id,
            'user_id'   => $this->commenter->id,
            'comment'   => 'Komentar yang akan dihapus',
        ]);
        $this->moment->update(['comments_count' => 1]);

        $response = $this->actingAs($this->commenter)
            ->deleteJson("/api/moments/{$this->moment->id}/comments/{$comment->id}");

        $response->assertStatus(200);
        $res = $response->json();
        $this->assertTrue($res['success']);
        $this->assertEquals(0, $res['comments_count']);

        $this->assertNull(MomentComment::find($comment->id));

        $this->moment->refresh();
        $this->assertEquals(0, $this->moment->comments_count);
    }

    public function test_moment_owner_can_delete_comment_on_their_moment(): void
    {
        $comment = MomentComment::create([
            'moment_id' => $this->moment->id,
            'user_id'   => $this->stranger->id,
            'comment'   => 'Komentar stranger di post author',
        ]);
        $this->moment->update(['comments_count' => 1]);

        // Pemilik postingan Momen menghapus komentar stranger
        $response = $this->actingAs($this->author)
            ->deleteJson("/api/moments/{$this->moment->id}/comments/{$comment->id}");

        $response->assertStatus(200);
        $this->assertNull(MomentComment::find($comment->id));
    }

    public function test_unauthorized_user_cannot_delete_other_users_comment(): void
    {
        $comment = MomentComment::create([
            'moment_id' => $this->moment->id,
            'user_id'   => $this->commenter->id,
            'comment'   => 'Komentar terlindungi',
        ]);

        $response = $this->actingAs($this->stranger)
            ->deleteJson("/api/moments/{$this->moment->id}/comments/{$comment->id}");

        $response->assertStatus(403);
        $this->assertNotNull(MomentComment::find($comment->id));
    }

    public function test_moments_feed_api_includes_comments_count(): void
    {
        $this->moment->update(['comments_count' => 5]);

        $response = $this->getJson('/api/moments');

        $response->assertStatus(200);
        $item = collect($response->json('data'))->firstWhere('id', $this->moment->id);

        $this->assertNotNull($item);
        $this->assertEquals(5, $item['comments_count']);
    }
}
