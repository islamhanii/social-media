<?php

namespace App\Http\Requests\Api\Users;

use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\CustomFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            "picture" => "required|mimes:png,jpg,jpeg,webp|max:10240",
            "bio" => "required|string|max:250",
            "contact_details" => "required|string|max:250",
        ];
    }
}
