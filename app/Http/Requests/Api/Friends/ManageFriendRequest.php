<?php

namespace App\Http\Requests\Api\Friends;

use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\CustomFailedValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManageFriendRequest extends FormRequest
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
        $user = auth()->user();

        return [
            "user_id" => [
                "required",
                Rule::exists("friends", "sender_id")->where(function ($query) use ($user) {
                    return $query->where("receiver_id", "=", $user->id)->where("is_accepted", "=", "0");
                }),
            ],
            "accept" => "required|boolean"
        ];
    }
}
