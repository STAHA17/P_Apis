<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\GenaticController;
use Illuminate\Console\Command;
use App\Models\Scheduling;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\SchedulingController;

class GetSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the Appliance Schedules';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::all();

        if ($users->isNotEmpty()) {
            $responseData = [];

            foreach ($users as $user) {
                $data['user_id'] = $user->id;
                $data['latitude'] = $user->latitude;
                $data['longitude'] = $user->longitude;
                $data['solar_capacity'] = $user->solar_capacity; 
                $data['check'] = $user->check;
                if ($user->latitude !== null && $user->longitude !== null && $user->check !== null) {
                    // $gc = new GenaticController($data['user_id']);
                    $gc = new GenaticController();
                    $gc->showUserAppliancesX($data['user_id']);

                }
                else
                {
                    return;
                }
            }
            return $this->sendResponse($responseData, 'Users Retrieve successfully');
        } else {
            return $this->sendError('No users found', ['error' => 'No users found']);
        }
    
    }
}
