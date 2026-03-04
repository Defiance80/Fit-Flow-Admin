<?php

namespace App\Http\Controllers\Fitness;

use App\Http\Controllers\Controller;
use App\Models\Fitness\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    /**
     * Display a listing of the exercises.
     */
    public function index(Request $request)
    {
        $query = Exercise::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('instructions', 'LIKE', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Filter by muscle groups
        if ($request->has('muscle_groups') && !empty($request->muscle_groups)) {
            $query->where('muscle_groups', 'LIKE', '%' . $request->muscle_groups . '%');
        }

        // Filter by difficulty
        if ($request->has('difficulty') && !empty($request->difficulty)) {
            $query->where('difficulty', $request->difficulty);
        }

        $exercises = $query->orderBy('name')->paginate(20);

        // Get unique values for filters
        $categories = Exercise::distinct()->pluck('category')->filter();
        $difficulties = ['beginner', 'intermediate', 'advanced'];

        return view('fitness.exercises.index', compact('exercises', 'categories', 'difficulties'), [
            'type_menu' => 'exercises'
        ]);
    }

    /**
     * Show the form for creating a new exercise.
     */
    public function create()
    {
        return view('fitness.exercises.create', [
            'type_menu' => 'exercises'
        ]);
    }

    /**
     * Store a newly created exercise in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'required|string',
            'category' => 'required|string|max:100',
            'muscle_groups' => 'required|string|max:255',
            'equipment_needed' => 'nullable|string|max:255',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_minutes' => 'nullable|integer|min:1|max:120',
            'calories_per_minute' => 'nullable|numeric|min:0|max:20',
            'video_url' => 'nullable|url|max:500',
            'image_url' => 'nullable|url|max:500',
            'safety_tips' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            Exercise::create($validatedData);

            return redirect()->route('exercises.index')
                ->with('success', 'Exercise created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create exercise: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified exercise.
     */
    public function show(Exercise $exercise)
    {
        return view('fitness.exercises.show', compact('exercise'), [
            'type_menu' => 'exercises'
        ]);
    }

    /**
     * Show the form for editing the specified exercise.
     */
    public function edit(Exercise $exercise)
    {
        return view('fitness.exercises.edit', compact('exercise'), [
            'type_menu' => 'exercises'
        ]);
    }

    /**
     * Update the specified exercise in storage.
     */
    public function update(Request $request, Exercise $exercise)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'required|string',
            'category' => 'required|string|max:100',
            'muscle_groups' => 'required|string|max:255',
            'equipment_needed' => 'nullable|string|max:255',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_minutes' => 'nullable|integer|min:1|max:120',
            'calories_per_minute' => 'nullable|numeric|min:0|max:20',
            'video_url' => 'nullable|url|max:500',
            'image_url' => 'nullable|url|max:500',
            'safety_tips' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            $exercise->update($validatedData);

            return redirect()->route('exercises.show', $exercise)
                ->with('success', 'Exercise updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update exercise: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified exercise from storage.
     */
    public function destroy(Exercise $exercise)
    {
        try {
            $exercise->update(['status' => 'inactive']);
            
            return redirect()->route('exercises.index')
                ->with('success', 'Exercise deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to deactivate exercise: ' . $e->getMessage());
        }
    }
}
