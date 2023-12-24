<?php

namespace App\Http\Requests\Api\Friends;

use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\CustomFailedValidation;
use App\Models\Friend;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnfriendRequest extends FormRequest
{
    use ApiResponseTrait, CustomFailedValidation;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $values = [];
        $user = auth()->user();
        $friend = Friend::where([["sender_id", "=", $this->user_id], ["receiver_id", "=", $user->id], ["is_accepted", 1]])->orWhere(function ($query) use ($user) {
            return $query->where([["sender_id", "=", $user->id], ["receiver_id", "=", $this->user_id], ["is_accepted", 1]]);
        })->first();

        if ($friend) {
            $values = [$friend->sender_id, $friend->receiver_id];
        }

        return [
            "user_id" => [
                "required",
                Rule::in($values),
            ],
        ];
    }
}
