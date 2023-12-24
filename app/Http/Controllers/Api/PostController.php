<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Posts\CommentPostRequest;
use App\Http\Requests\Api\Posts\CreatePostRequest;
use App\Http\Requests\Api\Posts\LikePostRequest;
use App\Http\Requests\Api\Posts\SharePostRequest;
use App\Http\Resources\PostResource;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\FileStorage;
use App\Models\Comment;
use App\Models\File;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use ApiResponseTrait, FileStorage;

    public function newsFeed(Request $request)
    {
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;
        $offset = ($page - 1) * $perPage;
        $firstPostId = ($request->first_post_id) ? [["id", "<=", $request->first_post_id]] : [];

        $user = $request->user();
        $count = Post::where($firstPostId)
            ->whereHas("user", function ($query) use ($user) {
                $query->whereHas("receivers", function ($query) use ($user) {
                    return $query->where("is_accepted", 1)->where("sender_id", $user->id);
                })->orWhereHas("senders", function ($query) use ($user) {
                    return $query->where("is_accepted", 1)->where("receiver_id", $user->id);
                })->orWhere("id", $user->id);
            })
            ->count();

        $posts = Post::select("id", "user_id", "shared_post_id", "text", "created_at", "updated_at")
            ->withCount("likes", "comments")
            ->with(["post" => function ($postQuery) {
                $postQuery->select("id", "user_id", "text", "created_at");
                $postQuery->withCount("likes", "comments");
                $postQuery->with(["user" => function ($userQuery) {
                    $userQuery->select("id", "name");
                    $userQuery->with(["profile" => function ($profileQuery) {
                        $profileQuery->select("user_id", "picture");
                    }]);
                }]);
                $postQuery->with(["files" => function ($filesQuery) {
                    $filesQuery->select("id", "post_id", "type", "link");
                }]);
            }])->with(["files" => function ($filesQuery) {
                $filesQuery->select("id", "post_id", "type", "link");
            }])->with(["user" => function ($postQuery) {
                $postQuery->select("id", "name");
                $postQuery->with(["profile" => function ($profileQuery) {
                    $profileQuery->select("user_id", "picture");
                }]);
            }])
            ->where($firstPostId)
            ->whereHas("user", function ($query) use ($user) {
                $query->whereHas("receivers", function ($query) use ($user) {
                    return $query->where("is_accepted", 1)->where("sender_id", $user->id);
                })->orWhereHas("senders", function ($query) use ($user) {
                    return $query->where("is_accepted", 1)->where("receiver_id", $user->id);
                })->orWhere("id", $user->id);
            })
            ->latest()
            ->skip($offset)
            ->take($perPage)->get();

        $pagination = [
            "per_page" => (int)$perPage,
            "page" => (int)$page,
            "last_page" => ceil($count / $perPage),
            "first_post_id" => ($page == "1" && count($posts) > 0) ? $posts[0]->id : null
        ];

        return $this->apiResponse(200, "posts", null, [
            "posts" => PostResource::collection($posts),
            "pagination" => $pagination
        ]);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function createPost(CreatePostRequest $request)
    {
        $post = Post::create([
            "user_id" => $request->user()->id,
            "text" => $request->text,
        ]);

        if (count($request->files) > 0) {
            $paths = $this->uploadMultipleFiles($request, "files");
            $records = [];
            foreach ($paths as $path) {
                $explodedPath = explode(".", $path);
                $record = [
                    "post_id" => $post->id,
                    "type" => (end($explodedPath) == "mp4") ? 1 : 0,
                    "link" => $path,
                ];

                array_push($records, $record);
            }

            File::insert($records);
        }

        return $this->apiResponse(200, "post created successfully");
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function likePost(LikePostRequest $request)
    {
        $user = $request->user();
        $like = Like::where([["post_id", $request->post_id], ["user_id", $user->id]])->first();
        if ($like) {
            $like->delete();
            return $this->apiResponse(200, "you unliked post successfully");
        }

        Like::create([
            "post_id" => $request->post_id,
            "user_id" => $user->id,
        ]);

        return $this->apiResponse(200, "you liked post successfully");
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function commentPost(CommentPostRequest $request)
    {
        Comment::create([
            "post_id" => $request->post_id,
            "user_id" => $request->user()->id,
            "text" => $request->text
        ]);

        return $this->apiResponse(200, "you commented post successfully");
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function sharePost(SharePostRequest $request)
    {
        Post::create([
            "user_id" => $request->user()->id,
            "shared_post_id" => $request->post_id,
            "text" => $request->text
        ]);

        return $this->apiResponse(200, "you shared post successfully");
    }
}
