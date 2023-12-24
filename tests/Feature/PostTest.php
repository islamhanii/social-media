<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostTest extends TestCase
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

    public function test_user_can_display_friends_news_feed()
    {
        $response = $this->getJson('/api/newsFeed');

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'posts',
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_create_post()
    {
        $files = [
            UploadedFile::fake()->image("image.png"),
            UploadedFile::fake()->create('video1.mp4', 100),
        ];
        $response = $this->postJson('/api/createPost', [
            'text' => 'Test post',
            'files' => $files,
        ]);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'post created successfully',
        ]);

        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user->id,
            'text' => 'Test post',
        ]);

        foreach ($files as $file) {
            $this->assertDatabaseHas('files', [
                'link' => 'files/' . $file->hashName(),
            ]);

            Storage::disk('uploads')->assertExists('files/' . $file->hashName());
        }
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_like_post()
    {
        $post = Post::create([
            "user_id" => $this->user->id,
            "text" => 'Test post',
        ]);

        $response = $this->postJson('/api/likePost', [
            'post_id' => $post->id,
        ]);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'you liked post successfully',
        ]);

        $this->assertDatabaseHas('likes', [
            'user_id' => $this->user->id,
            'post_id' => $post->id,
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_unlike_post()
    {
        $sharedPost = Post::create([
            "user_id" => $this->user->id,
            "text" => 'Test post',
        ]);

        $post = Post::create([
            "user_id" => $this->user->id,
            "text" => 'Test post',
            "shared_post_id" => $sharedPost->id
        ]);

        Like::create([
            'user_id' => $this->user->id,
            'post_id' => $post->id,
        ]);

        $response = $this->postJson('/api/likePost', [
            'post_id' => $post->id,
        ]);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'you unliked post successfully',
        ]);

        $this->assertDatabaseMissing('likes', [
            'user_id' => $this->user->id,
            'post_id' => $post->id,
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_comment_post()
    {
        $post = Post::create([
            "user_id" => $this->user->id,
            "text" => 'Test post',
        ]);

        $response = $this->postJson('/api/commentPost', [
            'post_id' => $post->id,
            'text' => 'Test comment',
        ]);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'you commented post successfully',
        ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'post_id' => $post->id,
            'text' => 'Test comment',
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function test_user_can_share_post()
    {
        $post = Post::create([
            "user_id" => $this->user->id,
            "text" => 'Test post',
        ]);

        $response = $this->postJson('/api/sharePost', [
            'post_id' => $post->id,
            'text' => 'Test share',
        ]);

        $response->assertStatus(200)->assertJson([
            'status' => 200,
            'message' => 'you shared post successfully',
        ]);

        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user->id,
            'shared_post_id' => $post->id,
            'text' => 'Test share',
        ]);
    }
}
