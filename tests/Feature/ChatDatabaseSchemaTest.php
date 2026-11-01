<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\MarketingConsent;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChatDatabaseSchemaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_chat_and_consent_tables_exist_with_required_columns(): void
    {
        $tables = [
            'chat_sessions' => [
                'id',
                'session_token',
                'user_id',
                'channel',
                'phone_number',
                'customer_name',
                'status',
                'metadata',
                'last_interaction_at',
                'created_at',
                'updated_at',
            ],
            'chat_messages' => [
                'id',
                'chat_session_id',
                'sender_type',
                'sender_id',
                'direction',
                'message_type',
                'message',
                'media_url',
                'whatsapp_message_id',
                'status',
                'raw_payload',
                'created_at',
                'updated_at',
            ],
            'marketing_consents' => [
                'id',
                'user_id',
                'phone_number',
                'consent_type',
                'is_opted_in',
                'opted_in_at',
                'opted_out_at',
                'source',
                'metadata',
                'created_at',
                'updated_at',
            ],
        ];

        foreach ($tables as $table => $columns) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} does not exist.");
            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    "Table {$table} is missing column {$column}."
                );
            }
        }
    }

    public function test_chat_sessions_indexes_and_uniqueness(): void
    {
        $token = 'GUEST_TOK_' . uniqid();

        $session1 = ChatSession::create([
            'session_token' => $token,
            'channel' => 'web',
            'phone_number' => '081234567890',
            'status' => 'active',
            'metadata' => ['utm_source' => 'google'],
        ]);

        $this->assertNotNull($session1->id);
        $this->assertEquals(['utm_source' => 'google'], $session1->metadata);

        // Expect duplicate token to fail due to unique constraint
        $this->expectException(QueryException::class);
        ChatSession::create([
            'session_token' => $token,
            'channel' => 'whatsapp',
            'phone_number' => '081234567891',
        ]);
    }

    public function test_chat_messages_relationship_and_whatsapp_id_index(): void
    {
        $user = User::create([
            'name' => 'Chat Customer',
            'email' => 'customer_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $session = ChatSession::create([
            'session_token' => 'TOK_' . uniqid(),
            'user_id' => $user->id,
            'channel' => 'whatsapp',
            'phone_number' => '083183494835',
            'status' => 'active',
        ]);

        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type' => 'customer',
            'sender_id' => $user->id,
            'direction' => 'inbound',
            'message_type' => 'text',
            'message' => 'Halo min, mau sewa kamera untuk besok.',
            'whatsapp_message_id' => 'WAMID_' . uniqid(),
            'status' => 'delivered',
            'raw_payload' => ['key' => ['id' => 'abc12345']],
        ]);

        $this->assertEquals($session->id, $message->session->id);
        $this->assertEquals($user->id, $message->sender->id);
        $this->assertTrue($session->messages->contains($message));
        $this->assertTrue($user->chatSessions->contains($session));
        $this->assertIsArray($message->raw_payload);
        $this->assertEquals('abc12345', $message->raw_payload['key']['id']);
    }

    public function test_marketing_consent_uniqueness_and_scopes(): void
    {
        $phone = '083183494835';

        $consent1 = MarketingConsent::create([
            'phone_number' => $phone,
            'consent_type' => 'promotional',
            'is_opted_in' => true,
            'opted_in_at' => now(),
            'source' => 'whatsapp_keyword',
            'metadata' => ['keyword' => 'START'],
        ]);

        $this->assertTrue($consent1->is_opted_in);
        $this->assertCount(1, MarketingConsent::where('phone_number', $phone)->promotional()->optedIn()->get());

        // Different consent_type with same phone should succeed
        $consent2 = MarketingConsent::create([
            'phone_number' => $phone,
            'consent_type' => 'social_proof',
            'is_opted_in' => true,
            'opted_in_at' => now(),
            'source' => 'checkout_checkbox',
        ]);
        $this->assertNotNull($consent2->id);

        // Duplicate phone_number + consent_type should fail unique constraint
        $this->expectException(QueryException::class);
        MarketingConsent::create([
            'phone_number' => $phone,
            'consent_type' => 'promotional',
            'is_opted_in' => false,
        ]);
    }

    public function test_chat_session_cascade_deletion(): void
    {
        $session = ChatSession::create([
            'session_token' => 'TOK_CASCADE_' . uniqid(),
            'channel' => 'web',
            'status' => 'active',
        ]);

        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender_type' => 'bot',
            'direction' => 'outbound',
            'message' => 'Bot reply',
        ]);

        $this->assertDatabaseHas('chat_messages', ['id' => $message->id]);

        $session->delete();

        $this->assertDatabaseMissing('chat_sessions', ['id' => $session->id]);
        $this->assertDatabaseMissing('chat_messages', ['id' => $message->id]);
    }
}
