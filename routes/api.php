<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
  
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ApplianceController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\SchedulingController;

use App\Http\Controllers\API\CheckController;
use App\Http\Controllers\API\GAController;
use App\Http\Controllers\API\GenaticController;

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


Route::get('getTokenThroughCheck/{check}', [UserController::class, 'getTokenThroughCheck'])->name('getTokenThroughCheck')->middleware(['cors']);

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

    //This for testing
    Route::post('some',function(){
        return "some thing ho geya";
    });

    //These Two Rutes for GA
    Route::get('getUserDetailsThroughId/{id}', [GAController::class,'getUserDetailsThroughId'])->name("getUserDetailsThroughId");
    Route::get('showUserAppliances/{id}', [GAController::class,'showUserAppliances'])->name("showUserAppliances");
    Route::get('getUserDetailsAndAppliances/{id}', [GAController::class,'getUserDetailsAndAppliances'])->name("getUserDetailsAndAppliances");
    
    Route::get('showUserAppliancesX/{id}', [GenaticController::class,'showUserAppliancesX'])->name("showUserAppliancesX");
    
    //These Routes for Retreve Data Function
    //Route::get('getuserbyid', "UserController@GetUserLatLong")->name("GetUserLatLong");
    Route::get('getUserCheckById/{id}', [UserController::class,'getUserCheckById'])->name("getUserCheckById");
    Route::get('getUserIdByCheck/{check}', [UserController::class,'getUserIdByCheck'])->name("getUserIdByCheck");
});

//These Routes For testing data of APIs, Open Routes 
Route::resource('appliances', ApplianceController::class);
Route::resource('users', UserController::class);
Route::resource('schedulings', SchedulingController::class);



