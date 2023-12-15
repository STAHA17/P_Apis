<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Appliance;
use App\Models\User;

use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ApplianceResource;
use Illuminate\Support\Facades\Auth;

class ApplianceController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $appliances = Appliance::all();
    
        return $this->sendResponse(ApplianceResource::collection($appliances), 'Appliance retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
   
        $validator = Validator::make($input, [
            'a_name' => 'required',
            'a_watt' => 'required | min:1 | max: 3500',
            'a_consumption' => 'required',
            'a_status' => 'sometimes|required',
            'a_IP' => 'sometimes|required',
            'a_MAC' => 'sometimes|required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $user = Auth::user();
        $appliance = Appliance::create($input);
        $appliance->user_id = $user->id;
        $appliance->save();
        return $this->sendResponse(new ApplianceResource($appliance), 'Appliance created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
        public function show($id)
        {
            // Assuming you have a relationship between models, for example, a User model having many appliances
            $user = Auth::user();

            if (is_null($user)) {
                return $this->sendError('User not found.');
            }

            $appliances = $user->appliances;

            if ($appliances->isEmpty()) {
                return $this->sendError('No appliances found for the specified user ID.');
            }

            return $this->sendResponse(ApplianceResource::collection($appliances), 'Appliances retrieved successfully.');
        }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Appliance $appliance)
    {
        $input = $request->only(['a_name', 'a_watt', 'a_consumption', 'a_status','a_IP','a_MAC']);

        $validator = Validator::make($input, [
            'a_name' => 'sometimes|required',
            'a_watt' => 'sometimes|required',
            'a_consumption' => 'sometimes|required',
            'a_status' => 'sometimes|required',
            'a_IP' => 'sometimes|required',
            'a_MAC' => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        try {
            // Loop over the input data and update only the provided fields
            foreach ($input as $key => $value) {
                $appliance->{$key} = $value;
            }

            $appliance->save();

            return $this->sendResponse(new ApplianceResource($appliance), 'Appliance updated successfully.');
        } 
        catch (\Exception $e)
        {
            // Handle the exception, log, and return an error response
            return $this->sendError('Error updating appliance.', ['message' => $e->getMessage()]);
        }
    }
  
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Appliance $appliance)
    {
        $appliance->delete();
   
        return $this->sendResponse([], 'Appliance deleted successfully.');
    }
}
