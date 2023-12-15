<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\SchedulingController as APISchedulingController;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\SchedulingController;
use App\Models\Scheduling;
use Illuminate\Http\Request;

class GenaticController extends Controller
{
    
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    public function showUserAppliancesX($id)
    {
        $user = User::Where('id',$id)->first();
        
        if (is_null($user)) {
            return $this->sendError('User not found.');
        }
        
        $userData = [
            'user_id'    => $user->id,
            'latitude'   => $user->latitude,
            'longitude'  => $user->longitude,
            'capacity'   => $user->solar_capacity,
        ];
        
        $appliancesData = [];
        
        foreach ($user->appliances as $appliance) {
            $appliancesData[] = [
                'appliance_id'  => $appliance->id,
                'a_name'        => $appliance->a_name,
                'a_watt'        => $appliance->a_watt,
                'a_consumption' => $appliance->a_consumption,
            ];
        }
        
        if (empty($appliancesData)) {
                return $this->sendError('No appliances found for the specified user ID.');
            }
        
            // Extracting 'a_watt' and 'a_consumption' from all appliances into separate arrays
            $allWatts = array_column($appliancesData, 'a_watt');
            $allConsumptions = array_column($appliancesData, 'a_consumption');
            $applianceIds = array_column($appliancesData, 'appliance_id');

            // Call the genetic algorithm with appliancePower from allWatts and applianceDuration from allConsumptions
            $bestSchedule = $this->runGeneticAlgorithm($allWatts, $allConsumptions,$applianceIds,$userData);
            print_r($bestSchedule);
            
            // Create a summary array containing all 'a_watt', 'a_consumption', 'appliance_id', and 'user_id' values
            $summaryData = [
                'user_data' => $userData,
                'appliances_data' => [
                    'appliance_ids'    => $applianceIds,
                    'all_watts'        => $allWatts,
                    'all_consumptions' => $allConsumptions,
                ],
            ];
        
        return $this->sendResponse($summaryData, 'Appliance data retrieved successfully.');
    }    

    private function runGeneticAlgorithm($power, $durations,$a_ids,$u_data)
    {
        // Genetic Algorithm Parameters
        $populationSize = 200;
        $generationCount = 200;
        $mutationRate = 0.02;
        $dynamicMutationRate = 0.01;
        global $user_Data,$appliancePower,$applianceDuration, 
        $totalAppliancePower, $appliancePowerBy15Min,$totalAppliancePowerBy15Min,
        $applianceIds,$firstEntryDate;
        //getting parameters in global variables
            $appliancePower=$power;
            $applianceDuration=$durations;
            $applianceIds=$a_ids;
            $user_Data=$u_data;
            print_r($applianceIds);
        $applianceCount = count($appliancePower);
        print_r($applianceCount);
        for ($i = 0; $i < count($appliancePower); $i++) {
            $totalAppliancePower += $appliancePower[$i];
            $appliancePower[$i] = ($appliancePower[$i] * 0.25);
            $appliancePowerBy15Min[$i] = $appliancePower[$i] * ($applianceDuration[$i] / 15);
            $totalAppliancePowerBy15Min+=$appliancePowerBy15Min[$i];
            echo " appliance" . ($applianceIds[$i]) . "power in 15 min:----" . $appliancePower[$i] . " ,";
            echo "total appliance power by 15 min :  " . $appliancePowerBy15Min[$i] . "<br>";
        }
            echo ($u_data['latitude']) . " ----";
            print_r($u_data['longitude']. " ----");
            print_r($u_data['capacity']. " ----");
            
    $latitude = -33.856784;
    $longitude = 151.215297;
    $hours = 48;
    $period = 'PT15M';
    $outputParameters = 'pv_power_rooftop';
    $capacity = 1;
    $apiKey = 'NhgolMHvPm3FfOawxnp771xMTjd0MIXx';

    // API endpoint
    $apiEndpoint = 'https://api.solcast.com.au/data/forecast/rooftop_pv_power';

    // Build the query string
    $queryParams = [
        'latitude' => $latitude,
        'longitude' => $longitude,
        'hours' => $hours,
        'period' => $period,
        'output_parameters' => $outputParameters,
        'capacity' => $capacity,
        'format' => 'json',
        'api_key' => $apiKey,
    ];

    // Combine the endpoint with the query string
    $apiUrl = $apiEndpoint . '?' . http_build_query($queryParams);

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Execute cURL session and get the API response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }

    // Close cURL session
    curl_close($ch);

    // Check if the API request was successful
    if (!$response) {
        die('Error: No response from API');
    }

    // Convert the JSON response to an associative array
    $responseArray = json_decode($response, true);
    // Check if there is at least one entry in the forecast
    if (!empty($responseArray)) {
        // Adjust timestamps to timezone +11
        foreach ($responseArray['forecasts'] as &$entry) {
            $entry['period_end'] = date('Y-m-d H:i:s', strtotime($entry['period_end'] . ' +11 hours'));
            // $entry['period'] = date('Y-m-d H:i:s', strtotime($entry['period'] . ' +11 hours'));
        }

        date_default_timezone_set('Australia/Sydney');
        // Get the current date
        $currentDate = date('Y-m-d');

        // Get the date of the next day
        $nextDay = date('Y-m-d', strtotime($currentDate . ' +1 day'));

        // Extract the forecast data for the next day
        $nextDayForecast = [];

        foreach ($responseArray['forecasts'] as $entry) {
            // Assuming 'period_end' is the key containing the date and time in the response
            $entryDate = date('Y-m-d', strtotime($entry['period_end']));

            // Check if the entry date matches the next day
            if ($entryDate === $nextDay) {
                // Add the entry to the next day forecast
                $nextDayForecast[] = $entry;
            }
        }

        // Now $nextDayForecast contains the forecast data for the next day
        // print_r($nextDayForecast);
        // Create Solar Power Forecast array
        $solarPowerForecast = [];

        foreach ($nextDayForecast as $entry) {
            $solarPowerForecast[] = $entry['pv_power_rooftop']*1000;
        }

        // Extract the date from the first entry in the forecast
        $firstEntryDate = date('Y-m-d', strtotime($nextDayForecast[0]['period_end']));

        // Now $firstEntryDate contains the date
        echo "Date: " . $firstEntryDate . "\n";
        // Now $solarPowerForecast contains the solar power forecast for each period
        //print_r($solarPowerForecast);
    } else {
        // Handle the case where there is no forecast data
        echo "No forecast data available.";
    }
        // $solarPowerForecast = [
        //     0, 0, 0, 0,  // Midnight
        //     0, 0, 0, 0,  // 1:00 AM
        //     0, 0, 0, 0,  // 2:00 AM
        //     0, 0, 0, 0,  // 3:00 AM
        //     0, 0, 0, 0,  // 4:00 AM
        //     0, 0, 0, 0,  // 5:00 AM (Morning)21
        //     200, 250, 260, 200,  // 6:00 AM
        //     120, 260, 260, 230,  // 7:00 AM
        //     260, 270, 260, 270,  // 8:00 AM
        //     400, 360, 350, 400,  // 9:00 AM
        //     400, 400, 400, 350,  // 10:00 AM
        //     400, 400, 400, 350,  // 11:00 AM (Midday)
        //     400, 400, 400, 350,  // 12:00 PM
        //     400, 360, 350, 400,  // 1:00 PM
        //     400, 360, 350, 400,  // 2:00 PM
        //     400, 360, 350, 400,  // 3:00 PM
        //     400, 360, 350, 400,  // 4:00 PM (Afternoon)
        //     400, 360, 350, 400,  // 5:00 PM
        //     400, 360, 350, 400,      // 6:00 PM (Evening)//74
        //     0, 0, 0, 0,          // 7:00 PM
        //     0, 0, 0, 0,          // 8:00 PM
        //     0, 0, 0, 0,          // 9:00 PM
        //     0, 0, 0, 0,          // 10:00 PM
        //     0, 0, 0, 0           // 11:00 PM (Night)
        // ];

            print_r($solarPowerForecast);
        // Initialize population
        $population = $this->initializePopulation($populationSize, $applianceCount, $solarPowerForecast);


        // Genetic Algorithm Main Loop
        for ($generation = 1; $generation <= $generationCount; $generation++) {
            $fitnessScores = $this->calculateFitnessScores($population, $solarPowerForecast, $appliancePower);
            $newPopulation = [];


            // Elitism: Keep the best individual without modification
            $bestIndex = array_search(max($fitnessScores), $fitnessScores);
            $newPopulation[] = $population[$bestIndex];

            // Dynamic Mutation Rate: Adjust mutation rate based on generation
            $mutationRate = $dynamicMutationRate / sqrt($generation);

            for ($i = 1; $i < $populationSize; $i++) {
                $parent1 = $this->selectParent($population, $fitnessScores);
                $parent2 = $this->selectParent($population, $fitnessScores);
                $offspring = $this->crossover($parent1, $parent2, $solarPowerForecast);
                $offspring = $this->mutate($offspring, $applianceCount, $mutationRate);
                $newPopulation[] = $offspring;
            }
            $population = $newPopulation;
        }

        $bestSchedule = $this->findBestSchedule($population, $solarPowerForecast, $appliancePower);
        $this->printSchedule($bestSchedule, $appliancePower);
        return $bestSchedule;
    }

    // Genetic Algorithm Functions
    private function initializePopulation($populationSize, $applianceCount, $solarPowerForecast)
    {
        $population = [];

        for ($i = 0; $i < $populationSize; $i++) {
            $schedule = $this->generateRandomSchedule($applianceCount, $solarPowerForecast);
            $population[] = $schedule;
        }

        return $population;
    }

    private function generateRandomSchedule($applianceCount, $solarPowerForecast)
    {
        global $appliancePower, $applianceDuration;
        $schedule = array_fill(0, count($solarPowerForecast), 0); // Initialize all appliances as off
        $timeSlots = count($solarPowerForecast);
        for ($i = 0; $i < count($applianceDuration); $i++) {
            $applianceDurationDuplicate[$i] = $applianceDuration[$i];
            //echo "appliance duration duplicate no. ".$i."now" . $applianceDurationDuplicate[$i];
        }
        for ($slot = 0; $slot < $timeSlots; $slot++) {
            if (rand(0, 1) == 1) { 
                // Randomly choose an appliance to turn on
                $applianceIndex = rand(1, $applianceCount);
                if ($applianceDurationDuplicate[$applianceIndex - 1] != 0 && $appliancePower[$applianceIndex - 1] <= $solarPowerForecast[$slot]) {
                    $schedule[$slot] = $applianceIndex; // Turn on the selected appliance
                    $applianceDurationDuplicate[$applianceIndex - 1] = $applianceDurationDuplicate[$applianceIndex - 1] - 15;
                }

            }
        }
        return $schedule;
    }

    private function calculateFitnessScores($population, $solarPowerForecast, $appliancePower)
    {
        $fitnessScores = [];

        foreach ($population as $schedule) {
            $fitnessScores[] = $this->calculateFitness($schedule, $solarPowerForecast, $appliancePower);
        }

        return $fitnessScores;
    }

    private function calculateFitness($schedule, $solarPowerForecast, $appliancePower)
    {
        global $totalAppliancePower, $appliancePowerBy15Min, $applianceDuration,$totalAppliancePowerBy15Min;

        $totalEnergyConsumption = 0;
        $totalSolarPower = array_sum($solarPowerForecast);
        $fitness = 100;
        // Penalty weights
        $durationPenaltyWeight = 1;  // Adjust as needed
        $overutilizationPenaltyWeight = 1;  // Adjust as needed
        $underutilizationPenaltyWeight = 1;  // Adjust as needed
        $solarPowerPenaltyWeight = 1;  // Adjust as needed

        // Dynamic Penalty for Appliance Duration
        $durationPenalty = 0;
        for ($i = 0; $i < count($applianceDuration); $i++) {
            $applianceDurationDuplicate[$i] = $applianceDuration[$i];
            //echo "appliance duration duplicate no. ".$i."now" . $applianceDurationDuplicate[$i];
        }
        foreach ($applianceDuration as $index => $expectedDuration) {
            $remainingDuration = $expectedDuration - $applianceDurationDuplicate[$index];
            $durationPenalty += ($remainingDuration < 0) ? abs($remainingDuration) * $durationPenaltyWeight : 0;
        }
        
        // Separate Penalties for Overutilization and Underutilization
        $overutilizationPenalty = 0;
        $underutilizationPenalty = 0;
        foreach ($applianceDurationDuplicate as $remainingDuration) {
            if ($remainingDuration < 0) {
                $overutilizationPenalty += abs($remainingDuration) * $overutilizationPenaltyWeight;
            } elseif ($remainingDuration > 0) {
                $underutilizationPenalty += $remainingDuration * $underutilizationPenaltyWeight;
            }
        }
        
        // Fine-Tune Solar Power Constraints Penalty
        $solarPowerPenalty = 0;
        for ($slot = 0; $slot < count($schedule); $slot++) {
            if ($schedule[$slot] != 0 && $appliancePower[$schedule[$slot] - 1] > $solarPowerForecast[$slot]) {
                $solarPowerPenalty += ($appliancePower[$schedule[$slot] - 1] - $solarPowerForecast[$slot]) * $solarPowerPenaltyWeight;
            }
            if($schedule[$slot] != 0)
            {
            $totalEnergyConsumption += $appliancePower[$schedule[$slot] - 1];
            }
        }
        // Normalize Fitness Score
        $normalizedFitness = max(0, 100 - ($durationPenalty + $overutilizationPenalty + $underutilizationPenalty + $solarPowerPenalty));
        
        // Bonus for meeting total energy consumption target
        $energyConsumptionBonus = ($totalEnergyConsumption == $totalAppliancePowerBy15Min) ? 30 : 0;
        
        // Adjusted Fitness Score
        $fitness = $normalizedFitness + $energyConsumptionBonus;
        
        return $fitness;
    }

    private function selectParent($population, $fitnessScores)
    {
        $totalFitness = array_sum($fitnessScores);
        $randomValue = rand(0, $totalFitness);
        $cumulativeFitness = 0;

        foreach ($population as $index => $schedule) {
            $cumulativeFitness += $fitnessScores[$index];

            if ($cumulativeFitness >= $randomValue) {
                return $schedule;
            }
        }

        return $population[count($population) - 1];
    }

    private function crossover($parent1, $parent2, $solarPowerForecast)
    {
        global $appliancePower;
        $crossoverPoint = rand(1, count($parent1) - 1);
        $offspring = array_merge(array_slice($parent1, 0, $crossoverPoint), array_slice($parent2, $crossoverPoint));

        // Ensure slots with 0 solar power and where appliance power exceeds solar power are set to 0
        $timeSlots = count($solarPowerForecast);

        for ($i = 0; $i < $timeSlots; $i++) {
            if ($offspring[$i] != 0) {
                if ($solarPowerForecast[$i] == 0 || $appliancePower[$offspring[$i] - 1] > $solarPowerForecast[$i]) {
                    $offspring[$i] = 0;
                }
            }
        }

        return $offspring;
    }

    private function mutate(&$schedule, $applianceCount, $mutationRate)
    {
        for ($i = 0; $i < count($schedule); $i++) {
            if (rand(0, 100) / 100 < $mutationRate) {
                $schedule[$i] = ($schedule[$i] == 0) ? rand(1, $applianceCount) : 0;
            }
        }

        return $schedule;
    }

    private function findBestSchedule($population, $solarPowerForecast, $appliancePower)
    {
        $bestSchedule = $population[0];
        $bestFitness = $this->calculateFitness($bestSchedule, $solarPowerForecast, $appliancePower);

        foreach ($population as $schedule) {
            $fitness = $this->calculateFitness($schedule, $solarPowerForecast, $appliancePower);
            if ($fitness > $bestFitness) {
                $bestFitness = $fitness;
                $bestSchedule = $schedule;

            }
        }
        //echo " best fitness: " . $bestFitness . " ----------";
        return $bestSchedule;
    }

    private function printSchedule($schedule, $appliancePower)
    {
        
        global $applianceIds,$firstEntryDate,$user_Data,$applianceIds;
        echo "Optimized Schedule: <br>";
        
        // $sc=new SchedulingController();
        
        
        
        $timeSlots = 96;
        $slotDuration = 15;
        for ($slot = 0; $slot < $timeSlots; $slot++) {
                if ($schedule[$slot] != 0) {
                    $sc=new Scheduling();
                    $slotStartTime = sprintf('%02d:%02d', (int)($slot * $slotDuration / 60), $slot * $slotDuration % 60);
                    $slotEndTime = sprintf('%02d:%02d', (int)(($slot + 1) * $slotDuration / 60), ($slot + 1) * $slotDuration % 60);

                        $sc['start_time'] = $slotStartTime; 
                        $sc['end_time'] =  $slotEndTime;  
                        $sc['date'] =  $firstEntryDate;
                        $sc['user_id'] =  $user_Data['user_id'];
                        $sc['appliance_id'] =  $applianceIds[$schedule[$slot] - 1];   
                        $sc->save();

                    // echo "Time Slot $slot: ";
                    // echo "Appliance " . $applianceIds[$schedule[$slot]-1];
                }
            echo PHP_EOL;
        }

    }
    
    private function sendError($message, $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        return response()->json($response, $code);
    }

    
}
?>