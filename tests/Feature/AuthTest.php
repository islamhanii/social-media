<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login()
    {
        $user = User::factory()->create();
        $data = [
            'email' => $user->email,
            'password' => '12345678',
        ];

        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'access token',
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_register()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '12345678',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'access token',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->actingAs($user)->postJson('/api/logout');

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'you logged out successfully',
        ]);

        $this->assertNull($user->fresh()->currentAccessToken());
    }
}
