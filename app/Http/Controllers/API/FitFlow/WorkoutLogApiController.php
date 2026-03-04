<?php

namespace App\Http\Controllers\API\FitFlow;

use App\Http\Controllers\Controller;
use App\Models\TrainerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkoutLogApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $clientId = $request->input('client_id', $user->id);

        if ($clientId != $user->id && $user->user_role === 'trainer') {
            TrainerClient::where('trainer_id', $user->id)->where('client_id', $clientId)->where('status', 'active')->firstOrFail();
        }

        $logs = DB::table('workout_logs')
            ->leftJoin('workout_sessions', 'workout_logs.workout_session_id', '=', 'workout_sessions.id')
            ->where('workout_logs.client_id', $clientId)
            ->select('workout_logs.*', 'workout_sessions.name as session_name')
            ->orderBy('logged_date', 'desc')
            ->paginate(20);

        return response()->json(['status' => true, 'data' => $logs]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'workout_session_id' => 'nullable|exists:workout_sessions,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'logged_date' => 'required|date',
            'duration_minutes' => 'nullable|integer',
            'calories_burned' => 'nullable|integer',
            'avg_heart_rate' => 'nullable|integer',
            'max_heart_rate' => 'nullable|integer',
            'overall_rpe' => 'nullable|integer|between:1,10',
            'mood' => 'nullable|in:great,good,okay,tired,exhausted',
            'notes' => 'nullable|string',
            'exercises' => 'nullable|array',
            'exercises.*.exercise_id' => 'required|exists:exercises,id',
            'exercises.*.set_number' => 'required|integer',
            'exercises.*.reps_completed' => 'nullable|integer',
            'exercises.*.weight_used' => 'nullable|numeric',
            'exercises.*.weight_unit' => 'nullable|string|in:lbs,kg',
            'exercises.*.duration_seconds' => 'nullable|integer',
            'exercises.*.rpe' => 'nullable|integer|between:1,10',
        ]);

        DB::beginTransaction();
        try {
            $logId = DB::table('workout_logs')->insertGetId([
                'client_id' => $request->user()->id,
                'workout_session_id' => $request->workout_session_id,
                'schedule_id' => $request->schedule_id,
                'logged_date' => $request->logged_date,
                'duration_minutes' => $request->duration_minutes,
                'calories_burned' => $request->calories_burned,
                'avg_heart_rate' => $request->avg_heart_rate,
                'max_heart_rate' => $request->max_heart_rate,
                'overall_rpe' => $request->overall_rpe,
                'mood' => $request->mood,
                'notes' => $request->notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($request->has('exercises')) {
                foreach ($request->exercises as $ex) {
                    DB::table('exercise_logs')->insert([
                        'workout_log_id' => $logId,
                        'exercise_id' => $ex['exercise_id'],
                        'set_number' => $ex['set_number'],
                        'reps_completed' => $ex['reps_completed'] ?? null,
                        'weight_used' => $ex['weight_used'] ?? null,
                        'weight_unit' => $ex['weight_unit'] ?? 'lbs',
                        'duration_seconds' => $ex['duration_seconds'] ?? null,
                        'rpe' => $ex['rpe'] ?? null,
                        'completed' => true,
                        'notes' => $ex['notes'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Workout logged', 'data' => ['id' => $logId]], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $log = DB::table('workout_logs')->where('id', $id)->first();
        if (!$log) return response()->json(['status' => false, 'message' => 'Not found'], 404);

        $exercises = DB::table('exercise_logs')
            ->join('exercises', 'exercise_logs.exercise_id', '=', 'exercises.id')
            ->where('exercise_logs.workout_log_id', $id)
            ->select('exercise_logs.*', 'exercises.name as exercise_name', 'exercises.category')
            ->get();

        return response()->json(['status' => true, 'data' => ['log' => $log, 'exercises' => $exercises]]);
    }
}
