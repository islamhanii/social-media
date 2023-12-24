<?php

namespace App\Http\Requests\Api\Auth;

use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\CustomFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email' => 'required|email:filter',
            'password' => 'required|string',
        ];
    }
}
