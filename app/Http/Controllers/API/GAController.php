<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Appliance;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Resources\ApplianceResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class GAController extends Controller
{
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    public function getUserDetailsThroughId($id){
        $user = User::Where('id', $id)->first();
        if($user!=null){
            $data['user_id'] = $user['id'];
            $data['latitude'] = $user['latitude'];
            $data['longitude'] = $user['longitude'];
            $data['check'] = $user['check'];
            return $this->sendResponse($data, 'Data sent successfully');
        } else {
            return $this->sendError('Unauthorised. This User does not Exist', ['error'=>'Unauthorised']);
        }
    }

    public function getUserDetailsAndAppliances($id)
    {
        $user = User::with('appliances')->find($id);

        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        $userData = [
            'user_id'    => $user->id,
            'latitude'   => $user->latitude,
            'longitude'  => $user->longitude,
            'check'      => $user->check,
        ];

        $appliancesData = [];

        foreach ($user->appliances as $appliance) {
            $appliancesData[] = [
                'appliance_id'  => $appliance->id,
                'a_name'        => $appliance->a_name,
                'a_watt'        => $appliance->a_watt,
                'a_consumption' => $appliance->a_consumption,
                'user_id'       => $appliance->user_id,
            ];
        }

        if (empty($appliancesData)) {
            return $this->sendError('No appliances found for the specified user ID.');
        }

        // Extracting 'a_watt' and 'a_consumption' from all appliances into separate arrays
        $allWatts = array_column($appliancesData, 'a_watt');
        $allConsumptions = array_column($appliancesData, 'a_consumption');

        // Create a summary array containing all 'a_watt' and 'a_consumption' values
        $summaryData = [
            'all_watts'        => $allWatts,
            'all_consumptions' => $allConsumptions,
        ];

        $response = [
            'user_data'      => $userData,
            'appliances_data' => $summaryData,
        ];


        //return('User details and appliance data retrieved for user ID ' . $id . ': ' . json_encode($response));

        return $this->sendResponse($response, 'User details and appliance data retrieved successfully.');
    }


    public function showUserAppliances($id)
        {
            $user = User::find($id);

            if (is_null($user)) {
                return $this->sendError('User not found.');
            }
            
            $appliancesData = [];
            
            foreach ($user->appliances as $appliance) {
                $appliancesData[] = [
                    'appliance_id'  => $appliance->id,
                    'a_name'        => $appliance->a_name,
                    'a_watt'        => $appliance->a_watt,
                    'a_consumption' => $appliance->a_consumption,
                    'user_id'       => $appliance->user_id,
                ];
            }
            
            if (empty($appliancesData)) {
                return $this->sendError('No appliances found for the specified user ID.');
            }
                        
            // Extracting 'a_watt' and 'a_consumption' from all appliances into separate arrays
            $allWatts = array_column($appliancesData, 'a_watt');
            $allConsumptions = array_column($appliancesData, 'a_consumption');
            
            // Create a summary array containing all 'a_watt' and 'a_consumption' values
            $summaryData = [
                'all_watts'        => $allWatts,
                'all_consumptions' => $allConsumptions,
            ];
            //return $this->sendResponse($appliancesData, 'Appliances retrieved successfully.');
            return $this->sendResponse($summaryData, 'Appliance data retrieved successfully.');            
    }


    // public function getUserDetailsAndAppliances($id)
    // {
    //     $user = User::with('appliances')->find($id);

    //     if (is_null($user)) {
    //         return('User not found for ID ' . $id);
    //         return $this->sendError('User not found.');
    //     }

    //     $userData = [
    //         'user_id'    => $user->id,
    //         'latitude'   => $user->latitude,
    //         'longitude'  => $user->longitude,
    //         'check'      => $user->check,
    //     ];

    //     $appliancesData = [];

    //     foreach ($user->appliances as $appliance) {
    //         $appliancesData[] = [
    //             'appliance_id'  => $appliance->id,
    //             'a_name'        => $appliance->a_name,
    //             'a_watt'        => $appliance->a_watt,
    //             'a_consumption' => $appliance->a_consumption,
    //             'user_id'       => $appliance->user_id,
    //         ];
    //     }

    //     if (empty($appliancesData)) {
    //         return('No appliances found for the specified user ID ' . $id);
    //         return $this->sendError('No appliances found for the specified user ID.');
    //     }

    //     // Extracting 'a_watt' and 'a_consumption' from all appliances into separate arrays
    //     $allWatts = array_column($appliancesData, 'a_watt');
    //     $allConsumptions = array_column($appliancesData, 'a_consumption');

    //     // Create a summary array containing all 'a_watt' and 'a_consumption' values
    //     $summaryData = [
    //         'all_watts'        => $allWatts,
    //         'all_consumptions' => $allConsumptions,
    //     ];

    //     $response = [
    //         'user_data'      => $userData,
    //         'appliances_data' => $summaryData,
    //     ];

    //     return('User details and appliance data retrieved for user ID ' . $id . ': ' . json_encode($response));

    //     return $this->sendResponse($response, 'User details and appliance data retrieved successfully.');
    // }


    // public function showUserAppliances($id)
    //     {
    //     // Having a relationship between models, a User model having many appliances
    //     $user = User::find($id);

    //     if (is_null($user)) {
    //         return $this->sendError('User not found.');
    //     }

    //     $appliancesData = [];

    //     foreach ($user->appliances as $appliance) {
    //         $appliancesData[] = [
    //             'appliance_id'  => $appliance->id,
    //             'a_name'        => $appliance->a_name,
    //             'a_watt'        => $appliance->a_watt,
    //             'a_consumption' => $appliance->a_consumption,
    //             'user_id'       => $appliance->user_id,
    //         ];
    //     }

    //     if (empty($appliancesData)) {
    //         return $this->sendError('No appliances found for the specified user ID.');
    //     }

    //     return $this->sendResponse($appliancesData, 'Appliances retrieved successfully.');

    // }

    // public function UsersForGA($user_id) {
    //     Auth::user()->latitude;
    //     $user_id = User::Where('id',$user_id)->first('id','latitude', 'longitude', 'check');

    //     $user_id = User::all()->pluck('id','latitude','longitude', 'check');
    // }

    // public function AppliancesForGA($appliance_id) {
    //     Auth::appliance()->a_watt;
    //     $appliance_id = Appliance::Where('id',$appliance_id)->first('name', 'a_watt', 'a_consumption','user_id');

    //     $appliance_id = Appliance::all()->pluck('name', 'a_watt', 'a_consumption','user_id');
    // }


    // public function showUserAppliances($id)
    // {
    //     $appliances = Appliance::where('user_id', $id)->get();

    //     if($appliances->isNotEmpty()){
    //         $applianceData = [];
        
    //         foreach ($appliances as $appliance) {
    //             $applianceData[] = [
    //                 'appliance_id'  => $appliance->id,
    //                 'name'          => $appliance->a_name,
    //                 'wattage'       => $appliance->a_watt,
    //                 'consumption'   => $appliance->a_consumption,
    //                 'user_id'       => $appliance->user_id,
    //             ];
    //         }
        
    //         return $this->sendResponse($applianceData, 'Data sent successfully');
    //     } else {
    //         return $this->sendError('No appliances found for the specified user ID.', ['error' => 'No appliances found']);
    //     }
    // }

}
