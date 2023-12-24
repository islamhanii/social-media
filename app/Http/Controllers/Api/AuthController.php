<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function login(LoginRequest $request)
    {
        $user = User::where([["email", "=", $request->email]])->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken("auth-token")->plainTextToken;

            return $this->apiResponse(200, "access token", null, [
                "access_token" => $token,
            ]);
        }

        return $this->apiResponse(400, "errors", "wrong email or password");
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function register(RegisterRequest $request)
    {
        User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ]);

        $loginRequest = new LoginRequest();
        $loginRequest->merge($request->all());

        return $this->login($loginRequest);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->apiResponse(200, "you logged out successfully");
    }
}
