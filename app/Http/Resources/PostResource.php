<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "user_id" => $this->user_id,
            "user_name" => $this->user->name,
            "user_picture" => $this->user->profile->picture,
            "post_text" => $this->text ?? "",
            $this->mergeWhen((count($this->files) > 0), [
                "files" => FileResource::collection($this->files),
            ]),
            $this->mergeWhen($this->post, [
                "shared_post" => new PostResource($this->post),
            ]),
            "likes_count" => $this->likes_count,
            "comments_count" => $this->comments_count,
            "created_at" => $this->created_at,
        ];
    }
}
