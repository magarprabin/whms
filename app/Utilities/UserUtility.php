<?php

namespace App\Utilities;

use App\Models\Rider;

class UserUtility
{
    public static function getRiderUsersId()
    {
        $usersId = Rider::whereNull('deleted_at')->where('status',1)->pluck('user_id')->toArray();

        return $usersId;
    }

    public static function getVehicleId()
    {
        $usersId = Rider::whereNull('deleted_at')->where('status',1)->pluck('user_id')->toArray();

        return $usersId;
    }
}