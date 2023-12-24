<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\DB;

trait UserTrait
{
    private function allUserInfo()
    {
        return [
            "users.id",
            "users.name",
            "users.email",
            DB::raw("COALESCE(CONCAT('" . env("APP_URL") . "/uploads/', profiles.picture), CONCAT('https://ui-avatars.com/api/?name=', users.name, '.png')) AS picture"),
            "profiles.bio",
            "profiles.contact_details"
        ];
    }

    /*-----------------------------------------------------------------------------------------------*/

    private function friendStatuses($user_id) {
        return [
            DB::raw("
                CASE
                WHEN ((friends.receiver_id = $user_id AND friends.is_accepted = 1) OR (friends2.sender_id = $user_id AND friends2.is_accepted = 1)) THEN 1 ELSE 0
                END AS is_friend,
                CASE
                WHEN (friends2.sender_id = $user_id AND friends2.is_accepted = 0) THEN 1 ELSE 0
                END AS friend_request_sender,
                CASE
                WHEN (friends.receiver_id = $user_id AND friends.is_accepted = 0) THEN 1 ELSE 0
                END AS friend_request_receiver
            ")
        ];
    }
}
