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
        $query = Schedule::with('schoolClass');

        // Handle both snake_case and camelCase (and Spatie filter format)
        $classId = $request->input('class_id') ?? $request->input('classId') ?? $request->input('filter.class_id');
        $dayOfWeek = $request->input('day_of_week') ?? $request->input('dayOfWeek') ?? $request->input('filter.day_of_week');

        if ($classId && $classId !== 'all') {
            $query->where('class_id', $classId);
        }

        if ($dayOfWeek && $dayOfWeek !== 'all') {
            $query->where('day_of_week', $dayOfWeek);
        }

        $schedules = $query->get()->map(function ($schedule) {
            // Log for debugging (temporary)
            if (!($schedule instanceof Schedule)) {
                \Illuminate\Support\Facades\Log::warning('Expected Schedule model, got ' . get_class($schedule));
            }
            return $this->transformSchedule($schedule);
        });
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
        $data = $this->mapFrontendFields($request->all());

        $validator = Validator::make($data, [
            'class_id' => 'required|uuid|exists:school_classes,id',
            'subject' => 'required|string|max:255',
            'day_of_week' => 'required|in:LUNDI,MARDI,MERCREDI,JEUDI,VENDREDI,SAMEDI,DIMANCHE',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
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

        $schedule = Schedule::create($data);
        $schedule->load('schoolClass');

        return response()->json([
            'status' => 'success',
            'data' => $this->transformSchedule($schedule)
        ], 211);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $schedule = Schedule::with('schoolClass')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $this->transformSchedule($schedule)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schedule = Schedule::findOrFail($id);
        $data = $this->mapFrontendFields($request->all());

        $validator = Validator::make($data, [
            'class_id' => 'uuid|exists:school_classes,id',
            'subject' => 'string|max:255',
            'day_of_week' => 'in:LUNDI,MARDI,MERCREDI,JEUDI,VENDREDI,SAMEDI,DIMANCHE',
            'start_time' => 'sometimes',
            'end_time' => 'sometimes|after:start_time',
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

        $schedule->update($data);
        $schedule->load('schoolClass');

        return response()->json([
            'status' => 'success',
            'data' => $this->transformSchedule($schedule)
        ]);
    }

    /**
     * Map camelCase frontend fields to snake_case backend fields.
     */
    private function mapFrontendFields(array $data): array
    {
        $mappings = [
            'classId' => 'class_id',
            'dayOfWeek' => 'day_of_week',
            'startTime' => 'start_time',
            'endTime' => 'end_time',
            'isActive' => 'is_active',
        ];

        foreach ($mappings as $frontend => $backend) {
            if (isset($data[$frontend]) && !isset($data[$backend])) {
                $data[$backend] = $data[$frontend];
            }
        }

        return $data;
    }

    /**
     * Transform the Schedule model into a frontend-friendly array (camelCase).
     */
    private function transformSchedule($schedule): array
    {
        // If for some reason it's an stdClass or array, handle it gracefully
        if (is_array($schedule)) {
            $schedule = (object)$schedule;
        }

        return [
            'id' => $schedule->id ?? null,
            'classId' => $schedule->class_id ?? $schedule->classId ?? null,
            'subject' => $schedule->subject ?? null,
            'dayOfWeek' => $schedule->day_of_week ?? $schedule->dayOfWeek ?? null,
            'startTime' => isset($schedule->start_time) ? substr($schedule->start_time, 0, 5) : (isset($schedule->startTime) ? substr($schedule->startTime, 0, 5) : null),
            'endTime' => isset($schedule->end_time) ? substr($schedule->end_time, 0, 5) : (isset($schedule->endTime) ? substr($schedule->endTime, 0, 5) : null),
            'room' => $schedule->room ?? null,
            'teacher' => $schedule->teacher ?? null,
            'type' => $schedule->type ?? null,
            'isActive' => (bool)($schedule->is_active ?? $schedule->isActive ?? true),
            'className' => isset($schedule->schoolClass) ? ($schedule->schoolClass->name ?? null) : ($schedule->className ?? null),
            'createdAt' => $schedule->created_at ?? $schedule->createdAt ?? null,
            'updatedAt' => $schedule->updated_at ?? $schedule->updatedAt ?? null,
        ];
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
