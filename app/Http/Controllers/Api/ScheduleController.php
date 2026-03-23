<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Schedule::class, 'schedule');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Schedule::with('schoolClass');

        if ($request->has('class_id') && $request->class_id !== 'all') {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('day_of_week') && $request->day_of_week !== 'all') {
            $query->where('day_of_week', $request->day_of_week);
        }

        $schedules = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $schedules
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id'    => 'required|uuid|exists:school_classes,id',
            'subject'     => 'required|string|max:255',
            'day_of_week' => 'required|in:LUNDI,MARDI,MERCREDI,JEUDI,VENDREDI,SAMEDI,DIMANCHE',
            'start_time'  => 'required',
            'end_time'    => 'required|after:start_time',
            'room'        => 'nullable|string|max:255',
            'teacher'     => 'nullable|string|max:255',
            'type'        => 'required|in:COURSE,TD,TP,EXAMEN',
            'is_active'   => 'boolean',
        ]);

        $schedule = Schedule::create($validated);
        $schedule->load('schoolClass');

        return response()->json([
            'status' => 'success',
            'data' => $schedule
        ], 211);
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        return response()->json([
            'status' => 'success',
            'data' => $schedule->load('schoolClass')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'class_id'    => 'uuid|exists:school_classes,id',
            'subject'     => 'string|max:255',
            'day_of_week' => 'in:LUNDI,MARDI,MERCREDI,JEUDI,VENDREDI,SAMEDI,DIMANCHE',
            'start_time'  => 'sometimes',
            'end_time'    => 'sometimes|after:start_time',
            'room'        => 'nullable|string|max:255',
            'teacher'     => 'nullable|string|max:255',
            'type'        => 'in:COURSE,TD,TP,EXAMEN',
            'is_active'   => 'boolean',
        ]);

        $schedule->update($validated);
        $schedule->load('schoolClass');

        return response()->json([
            'status' => 'success',
            'data' => $schedule
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Schedule deleted successfully'
        ]);
    }
}
