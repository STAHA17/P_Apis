<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Models\User;

use App\Http\Controllers\API\BaseController as BaseController;

class CheckController extends BaseController
{
    public function checkCode(Request $request)
    {
        $request->validate([
            'check' => 'required|string',
        ]);

        $check = $request->input('check');

        // Find user by check code
        $user = User::findByCheckCode($check);

        if (!$user) {
            return response()->json(['error' => 'Invalid code'], 401);
        }

        // Generate token for the user
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user_id' => $user->id,
            'access_token' => $token,
        ]);
    }
}


// class CheckController extends Controller
// {
//     public function checkCode(Request $request)
//     {
//         $request->validate([
//             'code' => 'required|string', // Assuming the code is sent in the request
//         ]);

//         $code = $request->input('code');

//         // Find user(s) with the provided code
//         $users = User::where('check_code', $code)->get();

//         if ($users->isEmpty()) {
//             return response()->json(['error' => 'No user found with the provided code'], 404);
//         }

//         // If a user is found, you can return user data or generate an authentication token
//         // For example, returning user data:
//         return response()->json(['users' => $users]);
//     }
// }
