<?php

namespace App\Http\Controllers\API\FitFlow;

use App\Http\Controllers\Controller;
use App\Models\Fitness\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExercisesApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Exercise::query();
        $user = $request->user();

        $query->where(function ($q) use ($user) {
            $q->where('is_global', true)->orWhere('created_by', $user->id);
        });

        if ($request->has('category')) $query->where('category', $request->category);
        if ($request->has('difficulty')) $query->where('difficulty', $request->difficulty);
        if ($request->has('search')) $query->where('name', 'like', "%{$request->search}%");
        if ($request->has('muscle_group')) $query->whereJsonContains('muscle_groups', $request->muscle_group);

        return response()->json(['status' => true, 'data' => $query->orderBy('name')->paginate(50)]);
    }

    public function show($id)
    {
        return response()->json(['status' => true, 'data' => Exercise::findOrFail($id)]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'difficulty' => 'required|string',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'muscle_groups' => 'nullable|array',
            'equipment' => 'nullable|array',
            'video_url' => 'nullable|url',
        ]);

        $exercise = Exercise::create(array_merge($request->all(), [
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'created_by' => $request->user()->id,
            'is_global' => $request->boolean('is_global', false),
        ]));

        return response()->json(['status' => true, 'message' => 'Exercise created', 'data' => $exercise], 201);
    }

    public function update(Request $request, $id)
    {
        $exercise = Exercise::where('created_by', $request->user()->id)->findOrFail($id);
        $exercise->update($request->only(['name', 'description', 'instructions', 'category', 'difficulty', 'muscle_groups', 'equipment', 'video_url']));
        return response()->json(['status' => true, 'data' => $exercise]);
    }

    public function destroy(Request $request, $id)
    {
        Exercise::where('created_by', $request->user()->id)->findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Exercise deleted']);
    }

    public function categories()
    {
        return response()->json(['status' => true, 'data' => [
            'categories' => ['strength','cardio','flexibility','balance','plyometric','olympic','bodyweight','machine','other'],
            'difficulties' => ['beginner','intermediate','advanced'],
            'muscle_groups' => ['chest','back','shoulders','biceps','triceps','forearms','core','glutes','quadriceps','hamstrings','calves','hip_flexors','traps','lats','obliques'],
        ]]);
    }
}
