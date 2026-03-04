<?php

namespace App\Http\Controllers\Fitness;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Fitness\TrainingProgram;
use App\Models\Fitness\ProgramPhase;
use App\Models\Fitness\WorkoutSession;
use App\Models\Fitness\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainingProgramController extends Controller
{
    /**
     * Display a listing of the training programs.
     */
    public function index(Request $request)
    {
        $query = TrainingProgram::with(['trainer', 'phases']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filter by trainer
        if ($request->has('trainer_id') && !empty($request->trainer_id)) {
            $query->where('trainer_id', $request->trainer_id);
        }

        // Filter by program type
        if ($request->has('program_type') && !empty($request->program_type)) {
            $query->where('program_type', $request->program_type);
        }

        // Filter by difficulty
        if ($request->has('difficulty') && !empty($request->difficulty)) {
            $query->where('difficulty', $request->difficulty);
        }

        $programs = $query->orderBy('created_at', 'desc')->paginate(15);
        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();

        return view('fitness.programs.index', compact('programs', 'trainers'), [
            'type_menu' => 'training-programs'
        ]);
    }

    /**
     * Show the form for creating a new training program.
     */
    public function create()
    {
        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();
        $exercises = Exercise::where('status', 'active')->get();
        
        return view('fitness.programs.create', compact('trainers', 'exercises'), [
            'type_menu' => 'training-programs'
        ]);
    }

    /**
     * Store a newly created training program in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trainer_id' => 'required|exists:users,id',
            'program_type' => 'required|string|max:100',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_weeks' => 'required|integer|min:1|max:52',
            'sessions_per_week' => 'required|integer|min:1|max:7',
            'estimated_duration_minutes' => 'nullable|integer|min:15|max:300',
            'equipment_required' => 'nullable|string',
            'target_goals' => 'nullable|string',
            'phases' => 'required|array|min:1',
            'phases.*.name' => 'required|string|max:255',
            'phases.*.description' => 'nullable|string',
            'phases.*.duration_weeks' => 'required|integer|min:1',
            'phases.*.sessions' => 'required|array|min:1',
            'phases.*.sessions.*.name' => 'required|string|max:255',
            'phases.*.sessions.*.description' => 'nullable|string',
            'phases.*.sessions.*.duration_minutes' => 'required|integer|min:15|max:300',
        ]);

        DB::beginTransaction();
        try {
            // Create the training program
            $program = TrainingProgram::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'trainer_id' => $validatedData['trainer_id'],
                'program_type' => $validatedData['program_type'],
                'difficulty' => $validatedData['difficulty'],
                'duration_weeks' => $validatedData['duration_weeks'],
                'sessions_per_week' => $validatedData['sessions_per_week'],
                'estimated_duration_minutes' => $validatedData['estimated_duration_minutes'],
                'equipment_required' => $validatedData['equipment_required'],
                'target_goals' => $validatedData['target_goals'],
                'status' => 'active',
            ]);

            // Create phases and sessions
            foreach ($validatedData['phases'] as $phaseIndex => $phaseData) {
                $phase = ProgramPhase::create([
                    'program_id' => $program->id,
                    'name' => $phaseData['name'],
                    'description' => $phaseData['description'],
                    'duration_weeks' => $phaseData['duration_weeks'],
                    'phase_order' => $phaseIndex + 1,
                ]);

                foreach ($phaseData['sessions'] as $sessionIndex => $sessionData) {
                    WorkoutSession::create([
                        'phase_id' => $phase->id,
                        'name' => $sessionData['name'],
                        'description' => $sessionData['description'],
                        'duration_minutes' => $sessionData['duration_minutes'],
                        'session_order' => $sessionIndex + 1,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('programs.index')
                ->with('success', 'Training program created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create training program: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified training program.
     */
    public function show(TrainingProgram $program)
    {
        $program->load([
            'trainer',
            'phases' => function($query) {
                $query->orderBy('phase_order');
            },
            'phases.sessions' => function($query) {
                $query->orderBy('session_order');
            },
            'phases.sessions.exercises',
            'clientPrograms' => function($query) {
                $query->where('status', 'active');
            }
        ]);

        $activeClientsCount = $program->clientPrograms()->where('status', 'active')->count();

        return view('fitness.programs.show', compact('program', 'activeClientsCount'), [
            'type_menu' => 'training-programs'
        ]);
    }

    /**
     * Show the form for editing the specified training program.
     */
    public function edit(TrainingProgram $program)
    {
        $program->load(['phases.sessions']);
        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();
        $exercises = Exercise::where('status', 'active')->get();

        return view('fitness.programs.edit', compact('program', 'trainers', 'exercises'), [
            'type_menu' => 'training-programs'
        ]);
    }

    /**
     * Update the specified training program in storage.
     */
    public function update(Request $request, TrainingProgram $program)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trainer_id' => 'required|exists:users,id',
            'program_type' => 'required|string|max:100',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_weeks' => 'required|integer|min:1|max:52',
            'sessions_per_week' => 'required|integer|min:1|max:7',
            'estimated_duration_minutes' => 'nullable|integer|min:15|max:300',
            'equipment_required' => 'nullable|string',
            'target_goals' => 'nullable|string',
            'status' => 'required|in:active,inactive,draft',
        ]);

        try {
            $program->update($validatedData);

            return redirect()->route('programs.show', $program)
                ->with('success', 'Training program updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update training program: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified training program from storage.
     */
    public function destroy(TrainingProgram $program)
    {
        try {
            $program->update(['status' => 'inactive']);
            
            return redirect()->route('programs.index')
                ->with('success', 'Training program deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to deactivate training program: ' . $e->getMessage());
        }
    }
}
