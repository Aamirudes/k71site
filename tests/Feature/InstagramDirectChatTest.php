<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstagramDirectChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/chat/get_conversations.php');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_conversations_and_messages(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        $convId = DB::table('chat_conversations')->insertGetId([
            'user_one_id' => min($u1->id, $u2->id),
            'user_two_id' => max($u1->id, $u2->id),
            'status' => 'accepted',
            'requested_by' => $u1->id,
            'last_message_at' => now(),
        ]);

        DB::table('chat_messages')->insert([
            'conversation_id' => $convId,
            'sender_id' => $u1->id,
            'body' => 'Hello from unit test!',
            'is_read' => 0,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($u1)->getJson('/api/chat/get_conversations.php');
        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'data');

        $msgResponse = $this->actingAs($u1)->getJson("/api/chat/get_messages.php?conversation_id={$convId}");
        $msgResponse->assertOk();
        $msgResponse->assertJsonPath('success', true);
        $msgResponse->assertJsonPath('recipient.id', $u2->id);
    }

    public function test_authenticated_user_can_send_message(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        $convId = DB::table('chat_conversations')->insertGetId([
            'user_one_id' => min($u1->id, $u2->id),
            'user_two_id' => max($u1->id, $u2->id),
            'status' => 'accepted',
            'requested_by' => $u1->id,
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($u1)->postJson('/api/chat/send_message.php', [
            'conversation_id' => $convId,
            'message' => 'Hey how are you?'
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message.body', 'Hey how are you?');

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $convId,
            'body' => 'Hey how are you?'
        ]);
    }
}
