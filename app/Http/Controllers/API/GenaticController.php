<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\API\UserController;
use App\Models\User;
use App\Http\Controllers\Controller;
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
        $user = User::find($id);
    
        if (is_null($user)) {
            return $this->sendError('User not found.');
        }
    
        $userData = [
            'user_id'    => $user->id,
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
        $bestSchedule = $this->runGeneticAlgorithm($allWatts, $allConsumptions);
        echo $bestSchedule;
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
    private function runGeneticAlgorithm($appliancePower, $applianceDuration)
    {
        // Genetic Algorithm Parameters
        $populationSize = 200;
        $generationCount = 200;
        $mutationRate = 0.02;
        $dynamicMutationRate = 0.01;
        global  $totalAppliancePower, $appliancePowerBy15Min,$totalAppliancePowerBy15Min;
        
$applianceCount = count($appliancePower);
for ($i = 0; $i < count($appliancePower); $i++) {
    $totalAppliancePower += $appliancePower[$i];
    $appliancePower[$i] = ($appliancePower[$i] * 0.25);
    $appliancePowerBy15Min[$i] = $appliancePower[$i] * ($applianceDuration[$i] / 15);
    $totalAppliancePowerBy15Min+=$appliancePowerBy15Min[$i];
    echo " appliance" . ($i+1) . "power in 15 min:----" . $appliancePower[$i] . " ,";
    echo "total appliance power by 15 min :  " . $appliancePowerBy15Min[$i] . "<br>";
}

// Solar Power Forecast (example values)
$solarPowerForecast = [
    0, 0, 0, 0,  // Midnight
    0, 0, 0, 0,  // 1:00 AM
    0, 0, 0, 0,  // 2:00 AM
    0, 0, 0, 0,  // 3:00 AM
    0, 0, 0, 0,  // 4:00 AM
    0, 0, 0, 0,  // 5:00 AM (Morning)21
    200, 250, 260, 200,  // 6:00 AM
    120, 260, 260, 230,  // 7:00 AM
    260, 270, 260, 270,  // 8:00 AM
    400, 360, 350, 400,  // 9:00 AM
    400, 400, 400, 350,  // 10:00 AM
    400, 400, 400, 350,  // 11:00 AM (Midday)
    400, 400, 400, 350,  // 12:00 PM
    400, 360, 350, 400,  // 1:00 PM
    400, 360, 350, 400,  // 2:00 PM
    400, 360, 350, 400,  // 3:00 PM
    400, 360, 350, 400,  // 4:00 PM (Afternoon)
    400, 360, 350, 400,  // 5:00 PM
    400, 360, 350, 400,      // 6:00 PM (Evening)//74
    0, 0, 0, 0,          // 7:00 PM
    0, 0, 0, 0,          // 8:00 PM
    0, 0, 0, 0,          // 9:00 PM
    0, 0, 0, 0,          // 10:00 PM
    0, 0, 0, 0           // 11:00 PM (Night)
];

// Initialize population
$population = initializePopulation($populationSize, $applianceCount, $solarPowerForecast);

// Genetic Algorithm Main Loop
for ($generation = 1; $generation <= $generationCount; $generation++) {
    $fitnessScores = calculateFitnessScores($population, $solarPowerForecast, $appliancePower);
    $newPopulation = [];

    // Elitism: Keep the best individual without modification
    $bestIndex = array_search(max($fitnessScores), $fitnessScores);
    $newPopulation[] = $population[$bestIndex];

    // Dynamic Mutation Rate: Adjust mutation rate based on generation
    $mutationRate = $dynamicMutationRate / sqrt($generation);

    for ($i = 1; $i < $populationSize; $i++) {
        $parent1 = selectParent($population, $fitnessScores);
        $parent2 = selectParent($population, $fitnessScores);
        $offspring = crossover($parent1, $parent2, $solarPowerForecast);
        $offspring = mutate($offspring, $applianceCount, $mutationRate);
        $newPopulation[] = $offspring;
    }

    $population = $newPopulation;
}

$bestSchedule = findBestSchedule($population, $solarPowerForecast, $appliancePower);
printSchedule($bestSchedule, $appliancePower);


// Genetic Algorithm Functions

function initializePopulation($populationSize, $applianceCount, $solarPowerForecast)
{
    $population = [];

    for ($i = 0; $i < $populationSize; $i++) {
        $schedule = generateRandomSchedule($applianceCount, $solarPowerForecast);
        $population[] = $schedule;
    }

    return $population;
}

function generateRandomSchedule($applianceCount, $solarPowerForecast)
{
    global $appliancePower, $applianceDuration;
    $schedule = array_fill(0, count($solarPowerForecast), 0); // Initialize all appliances as off
    $timeSlots = count($solarPowerForecast);
    for ($i = 0; $i < count($applianceDuration); $i++) {
        $applianceDurationDuplicate[$i] = $applianceDuration[$i];
        //echo "appliance duration duplicate no. ".$i."now" . $applianceDurationDuplicate[$i];
    }
    for ($slot = 0; $slot < $timeSlots; $slot++) {
        if (rand(0, 1) == 1) { //&& $solarPowerForecast[$slot]!=0
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

function calculateFitnessScores($population, $solarPowerForecast, $appliancePower)
{
    $fitnessScores = [];

    foreach ($population as $schedule) {
        $fitnessScores[] = calculateFitness($schedule, $solarPowerForecast, $appliancePower);
    }

    return $fitnessScores;
}

function calculateFitness($schedule, $solarPowerForecast, $appliancePower)
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

function selectParent($population, $fitnessScores)
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

function crossover($parent1, $parent2, $solarPowerForecast)
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

function mutate(&$schedule, $applianceCount, $mutationRate)
{
    for ($i = 0; $i < count($schedule); $i++) {
        if (rand(0, 100) / 100 < $mutationRate) {
            $schedule[$i] = ($schedule[$i] == 0) ? rand(1, $applianceCount) : 0;
        }
    }

    return $schedule;
}

function findBestSchedule($population, $solarPowerForecast, $appliancePower)
{
    $bestSchedule = $population[0];
    $bestFitness = calculateFitness($bestSchedule, $solarPowerForecast, $appliancePower);

    foreach ($population as $schedule) {
        $fitness = calculateFitness($schedule, $solarPowerForecast, $appliancePower);
        if ($fitness > $bestFitness) {
            $bestFitness = $fitness;
            $bestSchedule = $schedule;

        }
    }
    echo " best fitness: " . $bestFitness . " ----------";
    return $bestSchedule;
}

function printSchedule($schedule, $appliancePower)
{
    echo "Optimized Schedule: <br>";

    $timeSlots = 96;

    for ($slot = 0; $slot < $timeSlots; $slot++) {
        if ($schedule[$slot] != 0) {
            echo "Time Slot $slot: ";
            echo "Appliance " . $schedule[$slot];
        }
        echo PHP_EOL;
    }
        
    }
        
    }
}

// // Solar Power Forecast (example values)
// //https://api.solcast.com.au/data/forecast/rooftop_pv_power?latitude=-33.856784&longitude=151.215297&output_parameters=pv_power_rooftop&capacity=1&format=json&api_key=NhgolMHvPm3FfOawxnp771xMTjd0MIXx

// //https://api.solcast.com.au/data/forecast/rooftop_pv_power?latitude=-33.856784&longitude=151.215297&period=PT15M&output_parameters=pv_power_rooftop&capacity=1&format=json&api_key=NhgolMHvPm3FfOawxnp771xMTjd0MIXx

// // Genetic Algorithm Parameters
// $populationSize = 200;
// $generationCount = 200;
// $mutationRate = 0.02;
// $dynamicMutationRate = 0.01;
// global $appliancePower, $applianceDuration, $totalAppliancePower, $appliancePowerBy15Min, $applianceDuration,$totalAppliancePowerBy15Min;

// // Appliance Parameters
// $appliancePower = [1000, 1500, 500, 1000,700];
// // Now $appliancePower contains the data

// $applianceCount = count($appliancePower);
// $applianceDuration = [30, 45, 15, 30,30];
// for ($i = 0; $i < count($appliancePower); $i++) {
//     $totalAppliancePower += $appliancePower[$i];
//     $appliancePower[$i] = ($appliancePower[$i] * 0.25);
//     $appliancePowerBy15Min[$i] = $appliancePower[$i] * ($applianceDuration[$i] / 15);
//     $totalAppliancePowerBy15Min+=$appliancePowerBy15Min[$i];
//     echo " appliance" . ($i+1) . "power in 15 min:----" . $appliancePower[$i] . " ,";
//     echo "total appliance power by 15 min :  " . $appliancePowerBy15Min[$i] . "<br>";
// }

// // Solar Power Forecast (example values)
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

// // Initialize population
// $population = initializePopulation($populationSize, $applianceCount, $solarPowerForecast);

// // Genetic Algorithm Main Loop
// for ($generation = 1; $generation <= $generationCount; $generation++) {
//     $fitnessScores = calculateFitnessScores($population, $solarPowerForecast, $appliancePower);
//     $newPopulation = [];

//     // Elitism: Keep the best individual without modification
//     $bestIndex = array_search(max($fitnessScores), $fitnessScores);
//     $newPopulation[] = $population[$bestIndex];

//     // Dynamic Mutation Rate: Adjust mutation rate based on generation
//     $mutationRate = $dynamicMutationRate / sqrt($generation);

//     for ($i = 1; $i < $populationSize; $i++) {
//         $parent1 = selectParent($population, $fitnessScores);
//         $parent2 = selectParent($population, $fitnessScores);
//         $offspring = crossover($parent1, $parent2, $solarPowerForecast);
//         $offspring = mutate($offspring, $applianceCount, $mutationRate);
//         $newPopulation[] = $offspring;
//     }

//     $population = $newPopulation;
// }

// $bestSchedule = findBestSchedule($population, $solarPowerForecast, $appliancePower);
// printSchedule($bestSchedule, $appliancePower);


// // Genetic Algorithm Functions

// function initializePopulation($populationSize, $applianceCount, $solarPowerForecast)
// {
//     $population = [];

//     for ($i = 0; $i < $populationSize; $i++) {
//         $schedule = generateRandomSchedule($applianceCount, $solarPowerForecast);
//         $population[] = $schedule;
//     }

//     return $population;
// }

// function generateRandomSchedule($applianceCount, $solarPowerForecast)
// {
//     global $appliancePower, $applianceDuration;
//     $schedule = array_fill(0, count($solarPowerForecast), 0); // Initialize all appliances as off
//     $timeSlots = count($solarPowerForecast);
//     for ($i = 0; $i < count($applianceDuration); $i++) {
//         $applianceDurationDuplicate[$i] = $applianceDuration[$i];
//         //echo "appliance duration duplicate no. ".$i."now" . $applianceDurationDuplicate[$i];
//     }
//     for ($slot = 0; $slot < $timeSlots; $slot++) {
//         if (rand(0, 1) == 1) { //&& $solarPowerForecast[$slot]!=0
//             // Randomly choose an appliance to turn on
//             $applianceIndex = rand(1, $applianceCount);
//             if ($applianceDurationDuplicate[$applianceIndex - 1] != 0 && $appliancePower[$applianceIndex - 1] <= $solarPowerForecast[$slot]) {
//                 $schedule[$slot] = $applianceIndex; // Turn on the selected appliance
//                 $applianceDurationDuplicate[$applianceIndex - 1] = $applianceDurationDuplicate[$applianceIndex - 1] - 15;
//             }

//         }
//     }
//     return $schedule;
// }

// function calculateFitnessScores($population, $solarPowerForecast, $appliancePower)
// {
//     $fitnessScores = [];

//     foreach ($population as $schedule) {
//         $fitnessScores[] = calculateFitness($schedule, $solarPowerForecast, $appliancePower);
//     }

//     return $fitnessScores;
// }

// function calculateFitness($schedule, $solarPowerForecast, $appliancePower)
// {
//     global $totalAppliancePower, $appliancePowerBy15Min, $applianceDuration,$totalAppliancePowerBy15Min;

//     $totalEnergyConsumption = 0;
//     $totalSolarPower = array_sum($solarPowerForecast);
//     $fitness = 100;
//     // Penalty weights
//     $durationPenaltyWeight = 1;  // Adjust as needed
//     $overutilizationPenaltyWeight = 1;  // Adjust as needed
//     $underutilizationPenaltyWeight = 1;  // Adjust as needed
//     $solarPowerPenaltyWeight = 1;  // Adjust as needed

//     // Dynamic Penalty for Appliance Duration
//     $durationPenalty = 0;
//     for ($i = 0; $i < count($applianceDuration); $i++) {
//         $applianceDurationDuplicate[$i] = $applianceDuration[$i];
//         //echo "appliance duration duplicate no. ".$i."now" . $applianceDurationDuplicate[$i];
//     }
//     foreach ($applianceDuration as $index => $expectedDuration) {
//         $remainingDuration = $expectedDuration - $applianceDurationDuplicate[$index];
//         $durationPenalty += ($remainingDuration < 0) ? abs($remainingDuration) * $durationPenaltyWeight : 0;
//     }
    
//     // Separate Penalties for Overutilization and Underutilization
//     $overutilizationPenalty = 0;
//     $underutilizationPenalty = 0;
//     foreach ($applianceDurationDuplicate as $remainingDuration) {
//         if ($remainingDuration < 0) {
//             $overutilizationPenalty += abs($remainingDuration) * $overutilizationPenaltyWeight;
//         } elseif ($remainingDuration > 0) {
//             $underutilizationPenalty += $remainingDuration * $underutilizationPenaltyWeight;
//         }
//     }
    
//     // Fine-Tune Solar Power Constraints Penalty
//     $solarPowerPenalty = 0;
//     for ($slot = 0; $slot < count($schedule); $slot++) {
//         if ($schedule[$slot] != 0 && $appliancePower[$schedule[$slot] - 1] > $solarPowerForecast[$slot]) {
//             $solarPowerPenalty += ($appliancePower[$schedule[$slot] - 1] - $solarPowerForecast[$slot]) * $solarPowerPenaltyWeight;
//         }
//         if($schedule[$slot] != 0)
//         {
//         $totalEnergyConsumption += $appliancePower[$schedule[$slot] - 1];
//         }
//     }
//     // Normalize Fitness Score
//     $normalizedFitness = max(0, 100 - ($durationPenalty + $overutilizationPenalty + $underutilizationPenalty + $solarPowerPenalty));
    
//     // Bonus for meeting total energy consumption target
//     $energyConsumptionBonus = ($totalEnergyConsumption == $totalAppliancePowerBy15Min) ? 30 : 0;
    
//     // Adjusted Fitness Score
//     $fitness = $normalizedFitness + $energyConsumptionBonus;
    
//     return $fitness;
// }

// function selectParent($population, $fitnessScores)
// {
//     $totalFitness = array_sum($fitnessScores);
//     $randomValue = rand(0, $totalFitness);
//     $cumulativeFitness = 0;

//     foreach ($population as $index => $schedule) {
//         $cumulativeFitness += $fitnessScores[$index];

//         if ($cumulativeFitness >= $randomValue) {
//             return $schedule;
//         }
//     }

//     return $population[count($population) - 1];
// }

// function crossover($parent1, $parent2, $solarPowerForecast)
// {
//     global $appliancePower;
//     $crossoverPoint = rand(1, count($parent1) - 1);
//     $offspring = array_merge(array_slice($parent1, 0, $crossoverPoint), array_slice($parent2, $crossoverPoint));

//     // Ensure slots with 0 solar power and where appliance power exceeds solar power are set to 0
//     $timeSlots = count($solarPowerForecast);

//     for ($i = 0; $i < $timeSlots; $i++) {
//         if ($offspring[$i] != 0) {
//             if ($solarPowerForecast[$i] == 0 || $appliancePower[$offspring[$i] - 1] > $solarPowerForecast[$i]) {
//                 $offspring[$i] = 0;
//             }
//         }
//     }

//     return $offspring;
// }

// function mutate(&$schedule, $applianceCount, $mutationRate)
// {
//     for ($i = 0; $i < count($schedule); $i++) {
//         if (rand(0, 100) / 100 < $mutationRate) {
//             $schedule[$i] = ($schedule[$i] == 0) ? rand(1, $applianceCount) : 0;
//         }
//     }

//     return $schedule;
// }

// function findBestSchedule($population, $solarPowerForecast, $appliancePower)
// {
//     $bestSchedule = $population[0];
//     $bestFitness = calculateFitness($bestSchedule, $solarPowerForecast, $appliancePower);

//     foreach ($population as $schedule) {
//         $fitness = calculateFitness($schedule, $solarPowerForecast, $appliancePower);
//         if ($fitness > $bestFitness) {
//             $bestFitness = $fitness;
//             $bestSchedule = $schedule;

//         }
//     }
//     echo " best fitness: " . $bestFitness . " ----------";
//     return $bestSchedule;
// }

// function printSchedule($schedule, $appliancePower)
// {
//     echo "Optimized Schedule: <br>";

//     $timeSlots = 96;

//     for ($slot = 0; $slot < $timeSlots; $slot++) {
//         if ($schedule[$slot] != 0) {
//             echo "Time Slot $slot: ";
//             echo "Appliance " . $schedule[$slot];
//         }
//         echo PHP_EOL;
//     }
// }

