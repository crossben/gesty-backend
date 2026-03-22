<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Schedule::query();

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|uuid|exists:school_classes,id',
            'subject' => 'required|string|max:255',
            'day_of_week' => 'required|in:LUNDI,MARDI,MERCREDI,JEUDI,VENDREDI,SAMEDI,DIMANCHE',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:255',
            'teacher' => 'nullable|string|max:255',
            'type' => 'required|in:COURSE,TD,TP,EXAMEN',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $schedule = Schedule::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $schedule
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $schedule = Schedule::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $schedule
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schedule = Schedule::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'class_id' => 'uuid|exists:school_classes,id',
            'subject' => 'string|max:255',
            'day_of_week' => 'in:LUNDI,MARDI,MERCREDI,JEUDI,VENDREDI,SAMEDI,DIMANCHE',
            'start_time' => 'date_format:H:i',
            'end_time' => 'date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:255',
            'teacher' => 'nullable|string|max:255',
            'type' => 'in:COURSE,TD,TP,EXAMEN',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $schedule->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $schedule
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Schedule deleted successfully'
        ]);
    }
}
