<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Appliance;
use App\Models\User;

use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ApplianceResource;
   
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

    // public function index1(Request $request)
    // {
    //     $fields = $request->query('fields', '*');
    //     $appliances = Appliance::select(explode(',', $fields))->get();

    //     return response()->json($appliances);
    //     return $this->sendResponse(ApplianceResource::collection($appliances), 'Selected Appliance retrieved successfully.');

    // }

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
            'a_category' => 'required',
            'a_watt' => 'required | min:1 | max: 3500',
            'a_consumption' => 'required',
            'device' => 'required'
            // 'user_id' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $appliance = Appliance::create($input);
   
        return $this->sendResponse(new ApplianceResource($appliance), 'Appliance created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function show($id)
    // {
    //     $appliance = Appliance::find($id);
  
    //     if (is_null($appliance)) {
    //         return $this->sendError('Appliance not found.');
    //     }
   
    //     return $this->sendResponse(new ApplianceResource($appliance), 'Appliance retrieved successfully.');
    // }
        public function show($id)
        {
            // Assuming you have a relationship between models, for example, a User model having many appliances
            $user = User::find($id);

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
        $input = $request->all();
   
        $validator = Validator::make($input, [
            'a_name' => 'required',
            'a_category' => 'required',
            'a_watt' => 'required',
            'a_consumption' => 'required',
            'device' => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $appliance->a_name = $input['a_name'];
        $appliance->a_category = $input['a_category'];
        $appliance->a_watt = $input['a_watt'];
        $appliance->a_consumption = $input['a_consumption'];
        $appliance->device = $input['device'];

        $appliance->save();
   
        return $this->sendResponse(new ApplianceResource($appliance), 'Appliance updated successfully.');
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
