<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Utilities\UserUtility;
use Illuminate\Http\Request;

class FilteredRidersController extends Controller
{
    public function getRidersForVehicle()
    {
        try {
            $ridersUserId = UserUtility::getRiderUsersId();

        } catch (\Exception $exception){

        }
    }
}
