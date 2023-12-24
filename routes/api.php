<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post("login", [AuthController::class, "login"]);
Route::post("register", [AuthController::class, "register"]);

Route::middleware("auth:sanctum")->group(function() {
    Route::post("logout", [AuthController::class, "logout"]);

    Route::get("getUserProfile", [UserController::class, "getUserProfile"]);
    Route::get("getMyProfile", [UserController::class, "getMyProfile"]);
    Route::post("updateProfile", [UserController::class, "updateProfile"]);
    Route::post("resetPassword", [UserController::class, "resetPassword"]);

    Route::get("searchUsers", [FriendController::class, "searchUsers"]);
    Route::get("getFriendRequests", [FriendController::class, "getFriendRequests"]);
    Route::get("getFriends", [FriendController::class, "getFriends"]);
    Route::post("sendFriendRequest", [FriendController::class, "sendFriendRequest"]);
    Route::post("manageFriendRequest", [FriendController::class, "manageFriendRequest"]);
    Route::post("unfriend", [FriendController::class, "unfriend"]);
    
    Route::get("newsFeed", [PostController::class, "newsFeed"]);
    Route::post("createPost", [PostController::class, "createPost"]);
    Route::post("likePost", [PostController::class, "likePost"]);
    Route::post("commentPost", [PostController::class, "commentPost"]);
    Route::post("sharePost", [PostController::class, "sharePost"]);
});
