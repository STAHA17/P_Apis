<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Scheduling;

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
        // // Update all schedules
        // Scheduling::query()->update([
        //     // Update fields as needed
        //     'end_time' => now()->addDay(),
        // ]);

        // // You can add additional logic or output as needed
        // $this->info('Schedule update completed.');
        return 0;
    }
}
