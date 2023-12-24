<?php

namespace App\Http\Requests\Api\Posts;

use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\CustomFailedValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SharePostRequest extends FormRequest
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
        return [
            "post_id" => [
                "required",
                Rule::exists("posts", "id")->where(function ($query) {
                    return $query->whereNull("shared_post_id");
                })
            ],
            "text" => "required|string|max:5000",
        ];
    }
}
