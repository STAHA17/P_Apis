<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserResource;
   
class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    //  public function __construct()
    //  {
    //      // Apply the 'auth:api' middleware to all methods except 'index' and 'show'
    //      $this->middleware('auth:api')->except(['index', 'show']);
    //  }

    public function index()
    {
        $users = User::all();
    
        return $this->sendResponse(UserResource::collection($users), 'Users retrieved successfully.');
    }

    // public function index1(Request $request)
    // {
    //     $fields = $request->query('fields', '*');
    //     $users = User::select(explode(',', $fields))->get();

    //     return response()->json($users);
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
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'solar_capacity' => 'required',
            'status' => 'required'

        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $user = User::create($input);
   
        return $this->sendResponse(new UserResource($user), 'User created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
  
        if (is_null($user)) {
            return $this->sendError('User not found.');
        }
   
        return $this->sendResponse(new UserResource($user), 'User retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $input = $request->all();
   
        $validator = Validator::make($input, [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'solar_capacity' => 'required',
            'status' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->password = $input['password'];
        $user->latitude = $input['latitude'];
        $user->longitude = $input['longitude'];
        $user->solar_capacity = $input['solar_capacity'];
        $user->status = $input['status'];

        $user->save();
   
        return $this->sendResponse(new UserResource($user), 'User updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
   
        return $this->sendResponse([], 'User deleted successfully.');
    }
}
