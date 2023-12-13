<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Scheduling;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\SchedulingResource;

class SchedulingController extends BaseController
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schedulings = Scheduling::all();
    
        return $this->sendResponse(SchedulingResource::collection($schedulings), 'Scheduling retrieved successfully.');
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
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Convert input strings to Carbon instances
        $input['start_time'] = Carbon::createFromFormat('H:i:s', $input['start_time']);
        $input['end_time'] = Carbon::createFromFormat('H:i:s', $input['end_time']);

        $scheduling = Scheduling::create($input);

        return $this->sendResponse(new SchedulingResource($scheduling), 'Scheduling Schedule successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @param  string  $date
     * @return \Illuminate\Http\Response
     */

    public function show($id)
    {
        $schedules = Scheduling::where('user_id', $id)->get();

        if ($schedules->isEmpty()) {
            return $this->sendError('No schedules found for the specified user.');
        }

        // Check if all schedules belong to the same user ID
        foreach ($schedules as $schedule) {
            if ($schedule->user_id != $id) {
                return $this->sendError('Invalid request. Schedules do not belong to the specified User ID.');
            }
        }

        return $this->sendResponse(SchedulingResource::collection($schedules), 'Scheduling Schedule retrieved successfully.');
    }

    //for Hardware
    public function show1($date)
    {
        $schedules = Scheduling::whereDate('date', $date)->get();

        if ($schedules->isEmpty()) {
            return $this->sendError('No schedules found for the specified date.');
        }

        return $this->sendResponse(SchedulingResource::collection($schedules), 'Schedules retrieved successfully.');
    }

    //For users
    public function show2($id, $date)
    {
        $schedules = Scheduling::where('user_id', $id)
                            ->whereDate('date', $date)
                            ->get();

        if ($schedules->isEmpty()) {
            return $this->sendError('No schedules found for the specified appliance and date.');
        }
        //Check if all schedules belong to the same user ID
                foreach ($schedules as $schedule) {
                    if ($schedule->user_id != $id) {
                        return $this->sendError('Invalid request. Schedules do not belong to the specified User ID.');
                    }
                }
        return $this->sendResponse(SchedulingResource::collection($schedules), 'Schedules retrieved successfully.');
    }

   
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Scheduling $scheduling)
    {
        $input = $request->only(['start_time', 'end_time','date']);

        $validator = Validator::make($input, [
            'start_time' => 'sometimes|required',
            'end_time' => 'sometimes|required',
            'date' => 'sometimes|required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        try {
            // Loop through the input data and update only the provided fields
            foreach ($input as $key => $value) {
                $scheduling->{$key} = $value;
            }

            $scheduling->save();

            return $this->sendResponse(new SchedulingResource($scheduling), 'Scheduling schedule updated successfully.');
        } catch (\Exception $e) {
            // Handle the exception, log, and return an error response
            return $this->sendError('Error updating scheduling schedule.', ['message' => $e->getMessage()]);
        }
    }   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Scheduling $scheduling)
    {
        $scheduling->delete();
   
        return $this->sendResponse([], 'Scheduling schedule deleted successfully.');
    }
}