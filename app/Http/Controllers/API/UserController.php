<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getUserCheckById($id) {
        $user = User::Where('id',$id)->first('check');
        return $this->sendResponse($user, "user data");
    }

    public function getUserIdByCheck($check) {
        $user = User::Where('check',$check)->first('id');
        return $this->sendResponse($user, "user data");
    }

    public function getUserIdEmail() {
        $user = User::Where('id', Auth::id())->first(['id','email']);
        return $this->sendResponse($user, "user data");
    }

    public function index()
    {
        $users = User::all();
        
        return $this->sendResponse(UserResource::collection($users), 'Users retrieved successfully.');
    }

    public function getTokenThroughCheck($check){
        $user = User::Where('check', $check)->first();
        if($user!=null){
            $data['token'] = $user->createToken('MyApp')->plainTextToken;
            $data['user_id'] = $user['id'];
            return $this->sendResponse($data, 'Data sent successfully');
        } else {
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
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
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'solar_capacity' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Generate a random 8-digit number for the 'check' field
        $input['check'] = mt_rand(10000000, 99999999);

        $user = User::create($input);

        return $this->sendResponse(new UserResource($user), 'User created successfully.');
    }

    // public function store(Request $request)
    // {
    //     $input = $request->all();
   
    //     $validator = Validator::make($input, [
    //         'name' => 'required',
    //         'email' => 'required',
    //         'password' => 'required',
    //         'latitude' => 'required',
    //         'longitude' => 'required',
    //         'solar_capacity' => 'required',
    //         'check' => 'required|digits:8|numeric|unique:users,check',
    //     ]);
   
    //     if($validator->fails()){
    //         return $this->sendError('Validation Error.', $validator->errors());       
    //     }

    //     $user = User::create($input);
   
    //     return $this->sendResponse(new UserResource($user), 'User created successfully.');
    // } 
   
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
        $input = $request->only(['name', 'email', 'password', 'latitude', 'longitude', 'solar_capacity', 'check']);

        $validator = Validator::make($input, [
            'name' => 'sometimes|required',
            'email' => 'sometimes|required|email',
            'password' => 'sometimes|required',
            'latitude' => 'sometimes|required',
            'longitude' => 'sometimes|required',
            'solar_capacity' => 'sometimes|required',
            'check' => 'sometimes|required|digits:8|numeric|unique:users,check'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        try {
            // Loop over the input data and update only the provided fields
            foreach ($input as $key => $value) {
                $user->{$key} = $value;
            }

            $user->save();

            return $this->sendResponse(new UserResource($user), 'User updated successfully.');
        } 
        catch (\Exception $e)
        {
            // Handle the exception, log, and return an error response
            return $this->sendError('Error updating user.', ['message' => $e->getMessage()]);
        }
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
