<?php

namespace App\Http\Requests\Api\Users;

use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\CustomFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            "password" => "required|string|min:8|max:32|confirmed",
        ];
    }
}
