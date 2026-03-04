<?php

namespace App\Http\Controllers\API\FitFlow;

use App\Http\Controllers\Controller;
use App\Models\Fitness\TrainingProgram;
use App\Models\Fitness\ProgramPhase;
use App\Models\Fitness\WorkoutSession;
use App\Models\Fitness\WorkoutExercise;
use App\Models\TrainerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProgramsApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = TrainingProgram::with('trainer:id,name,profile');

        if ($user->user_role === 'trainer') {
            $query->where('trainer_id', $user->id);
        } elseif ($user->user_role === 'client') {
            $programIds = DB::table('client_programs')->where('client_id', $user->id)->pluck('training_program_id');
            $query->whereIn('id', $programIds);
        }

        if ($request->has('type')) $query->where('program_type', $request->type);
        if ($request->has('active')) $query->where('is_active', $request->boolean('active'));

        return response()->json(['status' => true, 'data' => $query->orderBy('created_at', 'desc')->paginate(20)]);
    }

    public function show(Request $request, $id)
    {
        $program = TrainingProgram::with([
            'trainer:id,name,profile',
            'phases.workoutSessions.exercises.exercise:id,name,category,muscle_groups,video_url',
            'clients:id,name,profile'
        ])->findOrFail($id);

        return response()->json(['status' => true, 'data' => $program]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'program_type' => 'required|string',
            'difficulty' => 'required|string',
            'description' => 'nullable|string',
            'duration_weeks' => 'nullable|integer',
            'sessions_per_week' => 'nullable|integer',
            'price' => 'nullable|numeric',
            'is_template' => 'nullable|boolean',
            'phases' => 'nullable|array',
            'phases.*.name' => 'required|string',
            'phases.*.sessions' => 'nullable|array',
            'phases.*.sessions.*.name' => 'required|string',
            'phases.*.sessions.*.exercises' => 'nullable|array',
        ]);

        $user = $request->user();

        DB::beginTransaction();
        try {
            $program = TrainingProgram::create([
                'trainer_id' => $user->id,
                'facility_id' => $user->facility_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name) . '-' . Str::random(5),
                'description' => $request->description,
                'program_type' => $request->program_type,
                'difficulty' => $request->difficulty,
                'duration_weeks' => $request->duration_weeks,
                'sessions_per_week' => $request->sessions_per_week,
                'price' => $request->price,
                'is_template' => $request->boolean('is_template'),
                'is_active' => true,
            ]);

            if ($request->has('phases')) {
                foreach ($request->phases as $pi => $phaseData) {
                    $phase = ProgramPhase::create([
                        'training_program_id' => $program->id,
                        'name' => $phaseData['name'],
                        'sort_order' => $pi,
                        'duration_days' => $phaseData['duration_days'] ?? null,
                        'notes' => $phaseData['notes'] ?? null,
                    ]);

                    if (!empty($phaseData['sessions'])) {
                        foreach ($phaseData['sessions'] as $si => $sessionData) {
                            $session = WorkoutSession::create([
                                'program_phase_id' => $phase->id,
                                'name' => $sessionData['name'],
                                'sort_order' => $si,
                                'estimated_duration_min' => $sessionData['estimated_duration_min'] ?? null,
                                'warmup_notes' => $sessionData['warmup_notes'] ?? null,
                                'cooldown_notes' => $sessionData['cooldown_notes'] ?? null,
                                'notes' => $sessionData['notes'] ?? null,
                            ]);

                            if (!empty($sessionData['exercises'])) {
                                foreach ($sessionData['exercises'] as $ei => $exData) {
                                    WorkoutExercise::create([
                                        'workout_session_id' => $session->id,
                                        'exercise_id' => $exData['exercise_id'],
                                        'sort_order' => $ei,
                                        'sets' => $exData['sets'] ?? null,
                                        'reps' => $exData['reps'] ?? null,
                                        'weight' => $exData['weight'] ?? null,
                                        'rest_seconds' => $exData['rest_seconds'] ?? null,
                                        'tempo' => $exData['tempo'] ?? null,
                                        'rpe_target' => $exData['rpe_target'] ?? null,
                                        'notes' => $exData['notes'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            $program->load('phases.workoutSessions.exercises');
            return response()->json(['status' => true, 'message' => 'Program created', 'data' => $program], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $program = TrainingProgram::where('trainer_id', $request->user()->id)->findOrFail($id);
        $program->update($request->only(['name', 'description', 'program_type', 'difficulty', 'duration_weeks', 'sessions_per_week', 'price', 'is_active', 'is_template']));
        if ($request->has('name')) $program->slug = Str::slug($request->name) . '-' . Str::random(5);
        $program->save();

        return response()->json(['status' => true, 'message' => 'Program updated', 'data' => $program]);
    }

    public function destroy(Request $request, $id)
    {
        $program = TrainingProgram::where('trainer_id', $request->user()->id)->findOrFail($id);
        $program->delete();
        return response()->json(['status' => true, 'message' => 'Program deleted']);
    }

    public function assignToClient(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:training_programs,id',
            'client_id' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
        ]);

        $user = $request->user();
        TrainerClient::where('trainer_id', $user->id)->where('client_id', $request->client_id)->where('status', 'active')->firstOrFail();

        DB::table('client_programs')->updateOrInsert(
            ['training_program_id' => $request->program_id, 'client_id' => $request->client_id],
            ['trainer_id' => $user->id, 'start_date' => $request->start_date ?? now(), 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]
        );

        return response()->json(['status' => true, 'message' => 'Program assigned to client']);
    }
}
