<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Friend;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FriendTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_searches_for_users()
    {
        $response = $this->getJson('/api/searchUsers?text=John');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'users',
            ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_get_friend_requests()
    {
        $response = $this->getJson('/api/getFriendRequests');

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'users',
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_get_friends()
    {
        $response = $this->getJson('/api/getFriends');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'users',
            ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_send_friend_request()
    {
        $friend = User::factory()->create();

        $response = $this->postJson('/api/sendFriendRequest', [
            'user_id' => $friend->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'friend request sent successfully',
            ]);

        $this->assertDatabaseHas('friends', [
            'sender_id' => $this->user->id,
            'receiver_id' => $friend->id,
            'is_accepted' => 0,
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_accept_friend_request()
    {
        $friendRequest = Friend::create([
            'sender_id' => User::factory()->create()->id,
            'receiver_id' => $this->user->id,
            'is_accepted' => 0,
        ]);

        $response = $this->postJson('/api/manageFriendRequest', [
            'user_id' => $friendRequest->sender_id,
            'accept' => 1,
        ]);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'friend request accepted successfully',
        ]);

        $this->assertDatabaseHas('friends', [
            'sender_id' => $friendRequest->sender_id,
            'receiver_id' => $this->user->id,
            'is_accepted' => 1,
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_reject_friend_request()
    {
        $friendRequest = Friend::create([
            'sender_id' => User::factory()->create()->id,
            'receiver_id' => $this->user->id,
            'is_accepted' => 0,
        ]);

        $response = $this->postJson('/api/manageFriendRequest', [
            'user_id' => $friendRequest->sender_id,
            'accept' => 0,
        ]);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'friend request rejected successfully',
        ]);

        $this->assertDatabaseMissing('friends', [
            'sender_id' => $friendRequest->sender_id,
            'receiver_id' => $this->user->id,
            'is_accepted' => 0,
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_unfriend_his_friends()
    {
        $friend = Friend::create([
            'sender_id' => $this->user->id,
            'receiver_id' => User::factory()->create()->id,
            'is_accepted' => 1,
        ]);

        $response = $this->postJson('/api/unfriend', [
            'user_id' => $friend->receiver_id,
        ]);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'you are not friends now',
        ]);

        $this->assertDatabaseMissing('friends', [
            'sender_id' => $friend->sender_id,
            'receiver_id' => $friend->receiver_id,
            'is_accepted' => 1,
        ]);
    }
}
