<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
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

    public function test_user_can_get_user_profile()
    {
        $response = $this->getJson("/api/getUserProfile?user_id={$this->user->id}");

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'profile',
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_get_his_profile()
    {
        $response = $this->getJson("/api/getMyProfile");

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'profile',
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_update_his_profile()
    {
        $fakeImage = UploadedFile::fake()->image('test_image.jpg');

        $response = $this->postJson("/api/updateProfile", [
            'bio' => 'New bio',
            'contact_details' => 'New contact details',
            'picture' => $fakeImage,
        ]);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'profile updated successfully',
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->user->id,
            'bio' => 'New bio',
            'contact_details' => 'New contact details',
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_resets_his_password()
    {
        $response = $this->postJson("/api/resetPassword", [
            'current_password' => '12345678',
            'password' => '123456789',
            'password_confirmation' => '123456789',
        ]);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'password changed successfully',
        ]);

        $this->assertTrue(Hash::check('123456789', $this->user->fresh()->password));
    }
}
