<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Friends\ManageFriendRequest;
use App\Http\Requests\Api\Friends\SendFriendRequest;
use App\Http\Requests\Api\Friends\UnfriendRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\UserTrait;
use App\Models\Friend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    use ApiResponseTrait, UserTrait;

    public function searchUsers(Request $request)
    {
        $user = $request->user();
        $users = DB::table("users")
            ->leftJoin("profiles", "users.id", "=", "profiles.user_id")
            ->leftJoin("friends", "users.id", "=", "friends.sender_id")
            ->leftJoin(DB::raw("friends friends2"), "users.id", "=", "friends2.receiver_id")
            ->select(array_merge($this->allUserInfo(), $this->friendStatuses($user->id)))
            ->where("users.id", "!=", $user->id)
            ->where("users.name", "LIKE", "%$request->text%")
            ->distinct()
            ->get();

        return $this->apiResponse(200, "users", null, $users);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function getFriendRequests(Request $request) {
        $user = $request->user();
        $requests = DB::table("users")
            ->leftJoin("profiles", "users.id", "=", "profiles.user_id")
            ->leftJoin("friends", "users.id", "=", "friends.sender_id")
            ->select($this->allUserInfo())
            ->where("users.id", "!=", $user->id)
            ->where(function ($query) use ($user) {
                return $query->where("friends.receiver_id", "=", $user->id)->where("friends.is_accepted", "=", "0");
            })
            ->distinct()
            ->get();

        return $this->apiResponse(200, "users", null, $requests);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function getFriends(Request $request)
    {
        $user = $request->user();
        $friends = DB::table("users")
            ->leftJoin("profiles", "users.id", "=", "profiles.user_id")
            ->leftJoin("friends", "users.id", "=", "friends.sender_id")
            ->leftJoin(DB::raw("friends friends2"), "users.id", "=", "friends2.receiver_id")
            ->select($this->allUserInfo())
            ->where("users.id", "!=", $user->id)
            ->where(function ($query) use ($user) {
                return $query->where("friends.receiver_id", "=", $user->id)->orWhere("friends2.sender_id", "=", $user->id);
            })
            ->where(function ($query) {
                return $query->where("friends.is_accepted", "=", "1")->orWhere("friends2.is_accepted", "=", "1");
            })
            ->distinct()
            ->get();

        return $this->apiResponse(200, "users", null, $friends);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function sendFriendRequest(SendFriendRequest $request)
    {
        Friend::create([
            "sender_id" => $request->user()->id,
            "receiver_id" => $request->user_id,
            "is_accepted" => 0,
        ]);

        return $this->apiResponse(200, "friend request sent successfully");
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function manageFriendRequest(ManageFriendRequest $request)
    {
        $friend = Friend::where("sender_id", "=", $request->user_id)->first();

        if ($request->accept == 0) {
            $friend->delete();
            return $this->apiResponse(200, "friend request rejected successfully");
        }

        $friend->update([
            "is_accepted" => 1,
        ]);

        return $this->apiResponse(200, "friend request accepted successfully");
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function unfriend(UnfriendRequest $request)
    {
        $user = auth()->user();
        $friend = Friend::where([["sender_id", "=", $request->user_id], ["receiver_id", "=", $user->id], ["is_accepted", 1]])
            ->orWhere(function ($query) use ($request, $user) {
                return $query->where([["sender_id", "=", $user->id], ["receiver_id", "=", $request->user_id], ["is_accepted", 1]]);
            })->first();

        $friend->delete();

        return $this->apiResponse(200, "you are not friends now");
    }
}
