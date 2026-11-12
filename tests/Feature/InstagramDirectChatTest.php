<?php

namespace Tests\Feature;

use App\Http\Controllers\MessageController;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstagramDirectChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        unset($_SESSION['user_id'], $_SESSION['username'], $_COOKIE['k71_uid']);
    }

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

    public function test_messages_index_uses_black_text_on_sent_bubbles_in_dark_mode(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('messages.index'));

        $response->assertOk();
        $response->assertSee('color: #000000 !important;', false);
        $response->assertSee('-webkit-text-fill-color: #000000 !important;', false);
        $response->assertDontSee("const bubbleColorStyle = isSent ? 'color: #ffffff !important;'", false);
        $response->assertSee('/chat/api/conversations', false);
        $response->assertSee('/chat/api/search', false);
        $response->assertDontSee('/api/chat/get_conversations.php', false);
        $response->assertDontSee('/api/chat/search_users.php', false);
    }

    public function test_legacy_chats_sync_into_primary_inbox_and_survive_new_replies(): void
    {
        $buyer = User::factory()->create([
            'first_name' => 'Aarav',
            'last_name' => 'Mehta',
            'username' => 'aaravmehta',
        ]);
        $sellerUser = User::factory()->create([
            'first_name' => 'Sinan',
            'last_name' => 'Khan',
            'username' => 'gaddito_sinan',
        ]);
        $vendor = Vendor::create([
            'user_id' => $sellerUser->id,
            'shop_name' => 'Gaddito Store',
            'slug' => 'gaddito-store',
            'status' => 'approved',
        ]);

        $legacyConv = Conversation::create([
            'vendor_id' => $vendor->id,
            'user_id' => $buyer->id,
            'subject' => 'Order enquiry',
            'last_message_at' => now()->subMinutes(10),
        ]);
        Message::create([
            'conversation_id' => $legacyConv->id,
            'sender_type' => 'user',
            'sender_id' => $buyer->id,
            'body' => 'Old message from legacy inbox',
            'is_read' => false,
        ]);

        // 1. Sync runs automatically and returns legacy chat in /chat/api/conversations?tab=primary
        $listRes = $this->actingAs($buyer)->getJson('/chat/api/conversations?tab=primary');
        $listRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.last_message.body', 'Old message from legacy inbox');

        $chatConvId = $listRes->json('data.0.id');

        // 2. Vendor replies via legacy reply endpoint -> cache invalidated, resurfaces immediately with unread count
        $this->actingAs($sellerUser)->post(route('vendor.messages.reply', $legacyConv->id), [
            'body' => 'Legacy vendor follow-up',
        ])->assertRedirect();

        $afterReply = $this->actingAs($buyer)->getJson('/chat/api/conversations?tab=primary');
        $afterReply->assertOk()
            ->assertJsonPath('data.0.last_message.body', 'Legacy vendor follow-up')
            ->assertJsonPath('data.0.unread_count', 1);

        $unread = $this->actingAs($buyer)->getJson('/chat/api/unread_count');
        $unread->assertOk()->assertJsonPath('unread', 1);

        // Opening thread marks messages read
        $this->actingAs($buyer)->getJson("/chat/api/messages?conversation_id={$chatConvId}")
            ->assertOk()
            ->assertJsonCount(2, 'messages');

        $unreadAfterRead = $this->actingAs($buyer)->getJson('/chat/api/unread_count');
        $unreadAfterRead->assertOk()->assertJsonPath('unread', 0);
    }

    public function test_search_users_supports_full_name_at_username_shop_name_start_edit_and_requests_tab(): void
    {
        $me = User::factory()->create([
            'first_name' => 'Hashir',
            'last_name' => 'Admin',
            'username' => 'hashiradmin',
        ]);
        $rohit = User::factory()->create([
            'first_name' => 'Rohit',
            'last_name' => 'Deewan',
            'username' => 'smartx_rohit',
        ]);
        Vendor::create([
            'user_id' => $rohit->id,
            'shop_name' => 'SmartX Studio',
            'slug' => 'smartx-studio',
            'status' => 'approved',
        ]);
        $blocked = User::factory()->create([
            'first_name' => 'Rohit',
            'last_name' => 'Blocked',
            'username' => 'blocked_rohit',
            'is_blocked' => true,
        ]);

        // Full name multi-word search
        $fullNameRes = $this->actingAs($me)->getJson('/chat/api/search?q=' . urlencode('Rohit Deewan'));
        $fullNameRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $rohit->id);

        // Leading @ username search
        $handleRes = $this->actingAs($me)->getJson('/chat/api/search?q=' . urlencode('@smartx_rohit'));
        $handleRes->assertOk()->assertJsonPath('data.0.id', $rohit->id);

        // Shop name search excludes blocked user
        $shopRes = $this->actingAs($me)->getJson('/chat/api/search?q=' . urlencode('SmartX Studio'));
        $shopRes->assertOk()->assertJsonCount(1, 'data');

        // Start conversation, hide it, and reopen via start_or_find
        $startRes = $this->actingAs($me)->postJson('/chat/api/search', [
            'action' => 'start_or_find',
            'target_user_id' => $rohit->id,
        ]);
        $startRes->assertOk()->assertJsonPath('success', true);
        $convId = $startRes->json('conversation_id');

        $this->actingAs($me)->postJson('/chat/api/delete_conversation', ['conversation_id' => $convId])
            ->assertOk();
        $this->actingAs($me)->getJson('/chat/api/conversations?tab=primary')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $reopenRes = $this->actingAs($me)->postJson('/chat/api/search', [
            'action' => 'start_or_find',
            'target_user_id' => $rohit->id,
        ]);
        $reopenRes->assertOk()->assertJsonPath('conversation_id', $convId);
        $this->actingAs($me)->getJson('/chat/api/conversations?tab=primary')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        // Send and edit message -> reflected in /chat/api/messages
        $sendRes = $this->actingAs($me)->postJson('/chat/api/send', [
            'conversation_id' => $convId,
            'message' => 'Initial text',
        ]);
        $sendRes->assertOk();
        $msgId = $sendRes->json('message.id');

        $editRes = $this->actingAs($me)->postJson('/chat/api/edit_message', [
            'message_id' => $msgId,
            'body' => 'Updated text',
        ]);
        $editRes->assertOk()
            ->assertJsonPath('new_body', 'Updated text')
            ->assertJsonPath('is_edited', true);

        $this->actingAs($rohit)->getJson("/chat/api/messages?conversation_id={$convId}")
            ->assertOk()
            ->assertJsonPath('messages.0.body', 'Updated text')
            ->assertJsonPath('messages.0.is_edited', true);

        // Other user cannot edit someone else's message
        $this->actingAs($rohit)->postJson('/chat/api/edit_message', [
            'message_id' => $msgId,
            'body' => 'Tampered',
        ])->assertStatus(403);

        // Requests tab filtering vs Primary tab filtering
        MessageController::ensureChatSchema();
        $reqSender = User::factory()->create();
        DB::table('chat_conversations')->insert([
            'user_one_id' => min($me->id, $reqSender->id),
            'user_two_id' => max($me->id, $reqSender->id),
            'status' => 'pending',
            'requested_by' => $reqSender->id,
            'last_message_at' => now()->toDateTimeString(),
        ]);

        $primaryTab = $this->actingAs($me)->getJson('/chat/api/conversations?tab=primary');
        $primaryTab->assertOk()
            ->assertJsonPath('requests_count', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $convId);

        $requestsTab = $this->actingAs($me)->getJson('/chat/api/conversations?tab=requests');
        $requestsTab->assertOk()
            ->assertJsonPath('requests_count', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'pending');
    }
}
