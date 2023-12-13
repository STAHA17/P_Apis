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
