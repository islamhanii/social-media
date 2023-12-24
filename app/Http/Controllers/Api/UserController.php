<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Users\ResetPasswordRequest;
use App\Http\Requests\Api\Users\UpdateProfileRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\FileStorage;
use App\Http\Traits\UserTrait;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponseTrait, FileStorage, UserTrait;

    public function getUserProfile(Request $request)
    {
        $user = DB::table("users")
            ->leftJoin("profiles", "users.id", "=", "profiles.user_id")
            ->select($this->allUserInfo())
            ->where("users.id", "=", $request->user_id)
            ->first();

        return $this->apiResponse(200, "profile", null, $user);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function getMyProfile(Request $request)
    {
        $request->merge(["user_id" => $request->user()->id]);
        return $this->getUserProfile($request);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $profile = Profile::where("user_id", "=", $user->id)->first();
        $path = $this->uploadFile($request, "users", $profile, "picture", "picture");

        if ($profile) {
            $profile->update([
                "picture" => $path,
                "bio" => $request->bio,
                "contact_details" => $request->contact_details,
            ]);
        } else {
            Profile::create([
                "user_id" => $user->id,
                "picture" => $path,
                "bio" => $request->bio,
                "contact_details" => $request->contact_details,
            ]);
        }

        return $this->apiResponse(200, "profile updated successfully");
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = User::findOrFail($request->user()->id);
        $user->update([
            "password" => Hash::make($request->password),
        ]);

        return $this->apiResponse(200, "password changed successfully");
    }
}
