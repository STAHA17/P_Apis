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

//if (isset($data['forecasts']))    NhgolMHvPm3FfOawxnp771xMTjd0MIXx

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
                'appliance_id'  => $appliance->id, // Include 'appliance_id'
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
        $applianceIds = array_column($appliancesData, 'appliance_id'); // Include 'appliance_id'

        // Create a summary array containing all 'a_watt', 'a_consumption', and 'appliance_id' values
        $summaryData = [
            'appliance_ids'    => $applianceIds, // Include 'appliance_id'
            'all_watts'        => $allWatts,
            'all_consumptions' => $allConsumptions,
        ];

        $response = [
            'user_data'      => $userData,
            'appliances_data' => $summaryData,
        ];

        return $this->sendResponse($response, 'User details and appliance data retrieved successfully.');
    }

    public function showUserAppliances($id)
    {
        $user = User::find($id);
    
        if (is_null($user)) {
            return $this->sendError('User not found.');
        }
    
        $userData = [
            'user_id'    => $user->id,
        ];
    
        $appliancesData = [];
    
        foreach ($user->appliances as $appliance) {
            $appliancesData[] = [
                'appliance_id'  => $appliance->id,
                'a_name'        => $appliance->a_name,
                'a_watt'        => $appliance->a_watt,
                'a_consumption' => $appliance->a_consumption,
            ];
        }
    
        if (empty($appliancesData)) {
            return $this->sendError('No appliances found for the specified user ID.');
        }
    
        // Extracting 'a_watt' and 'a_consumption' from all appliances into separate arrays
        $allWatts = array_column($appliancesData, 'a_watt');
        $allConsumptions = array_column($appliancesData, 'a_consumption');
        $applianceIds = array_column($appliancesData, 'appliance_id');
    
        // Create a summary array containing all 'a_watt', 'a_consumption', 'appliance_id', and 'user_id' values
        $summaryData = [
            'user_data' => $userData,
            'appliances_data' => [
                'appliance_ids'    => $applianceIds,
                'all_watts'        => $allWatts,
                'all_consumptions' => $allConsumptions,
            ],
        ];
    
        // Create a separate variable for all_watts
        $appliancePower = $allWatts;
    
        // You can use $appliancePower in another space
    
        return $this->sendResponse($summaryData, 'Appliance data retrieved successfully.');

        // Assuming this is in your controller or service
        config(['app.appliance_power' => $appliancePower]);

    }
    


    // public function showUserAppliances($id)
    //     {
    //         $user = User::find($id);

    //         if (is_null($user)) {
    //             return $this->sendError('User not found.');
    //         }
            
    //         $appliancesData = [];
            
    //         foreach ($user->appliances as $appliance) {
    //             $appliancesData[] = [
    //                 'appliance_id'  => $appliance->id,
    //                 'a_name'        => $appliance->a_name,
    //                 'a_watt'        => $appliance->a_watt,
    //                 'a_consumption' => $appliance->a_consumption,
    //                 'user_id'       => $appliance->user_id,
    //             ];
    //         }
            
    //         if (empty($appliancesData)) {
    //             return $this->sendError('No appliances found for the specified user ID.');
    //         }
                        
    //         // Extracting 'a_watt' and 'a_consumption' from all appliances into separate arrays
    //         $allWatts = array_column($appliancesData, 'a_watt');
    //         $allConsumptions = array_column($appliancesData, 'a_consumption');
    //         $applianceIds = array_column($appliancesData, 'appliance_id'); // Include 'appliance_id'
            
    //         // Create a summary array containing all 'a_watt' and 'a_consumption' values
    //         $summaryData = [
    //             'appliance_ids'    => $applianceIds, // Include 'appliance_id'
    //             'all_watts'        => $allWatts,
    //             'all_consumptions' => $allConsumptions,
    //         ];
    //         //return $this->sendResponse($appliancesData, 'Appliances retrieved successfully.');
    //         return $this->sendResponse($summaryData, 'Appliance data retrieved successfully.');            
    // }

}
