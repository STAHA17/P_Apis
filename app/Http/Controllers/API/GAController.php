<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Appliance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GAController extends Controller
{
    public function user($user_id) {
        Auth::user()->latitude;
        $user = User::Where('id',$user_id)->first('id','latitude', 'longitude', 'check');

        $users = User::all()->pluck('id','latitude','longitude', 'check');
    }

    public function appliance($appliance_id) {
        Auth::appliance()->a_watt;
        $appliance = Appliance::Where('id',$appliance_id)->first('name', 'a_watt', 'a_consumption','user_id');

        $appliance = Appliance::all()->pluck('name', 'a_watt', 'a_consumption','user_id');
    }

}
