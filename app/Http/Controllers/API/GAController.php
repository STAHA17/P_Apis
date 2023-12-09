<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Appliance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GAController extends Controller
{
    public function UsersForGA($user_id) {
        Auth::user()->latitude;
        $user_id = User::Where('id',$user_id)->first('id','latitude', 'longitude', 'check');

        $user_id = User::all()->pluck('id','latitude','longitude', 'check');
    }

    public function AppliancesForGA($appliance_id) {
        Auth::appliance()->a_watt;
        $appliance_id = Appliance::Where('id',$appliance_id)->first('name', 'a_watt', 'a_consumption','user_id');

        $appliance_id = Appliance::all()->pluck('name', 'a_watt', 'a_consumption','user_id');
    }

}
