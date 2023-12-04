<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
  
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ApplianceController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\SchedulingController;

use App\Http\Controllers\API\CheckController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['cors'])->group(function(){
    Route::controller(RegisterController::class)->group(function(){
        Route::post('register', 'register');
        Route::post('login', 'login');
    });
            
    Route::middleware('auth:sanctum')->group( function () {
        Route::resource('appliance', ApplianceController::class);
    });
    
    Route::middleware('auth:sanctum')->group( function () {
        Route::resource('user', UserController::class);
        Route::get('getUserIdEmail', [UserController::class,'getUserIdEmail'])->name("getUserIdEmail");
    });
      
    Route::middleware('auth:sanctum')->group( function () {
        Route::resource('scheduling', SchedulingController::class);
    });
    Route::post('some',function(){
        return "some thing ho geya";
    });
    // Route::get('getuserbyid', "UserController@GetUserLatLong")->name("GetUserLatLong");
    Route::get('getUserCheckById/{id}', [UserController::class,'getUserCheckById'])->name("getUserCheckById");


    Route::get('getUserIdByCheck/{check}', [UserController::class,'getUserIdByCheck'])->name("getUserIdByCheck");
});

Route::resource('appliances', ApplianceController::class);
Route::resource('users', UserController::class);
Route::resource('schedulings', SchedulingController::class);

//Route::resource('checks', CheckController::class);

// Route::get('/api/users', 'UserController@index1');
// Route::get('/api/appliances', 'ApplianceController@index1');


