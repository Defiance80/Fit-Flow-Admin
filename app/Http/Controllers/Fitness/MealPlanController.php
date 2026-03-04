<?php

namespace App\Http\Controllers\Fitness;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Fitness\MealPlan;
use Illuminate\Http\Request;

class MealPlanController extends Controller
{
    /**
     * Display a listing of the meal plans.
     */
    public function index(Request $request)
    {
        $query = MealPlan::with(['client', 'trainer']);

        // Filter by trainer
        if ($request->has('trainer_id') && !empty($request->trainer_id)) {
            $query->where('trainer_id', $request->trainer_id);
        }

        $mealPlans = $query->orderBy('created_at', 'desc')->paginate(15);
        $trainers = User::where('user_role', 'trainer')->where('is_active', 1)->get();

        return view('fitness.meal-plans.index', compact('mealPlans', 'trainers'), [
            'type_menu' => 'meal-plans'
        ]);
    }

    /**
     * Show the form for creating a new meal plan.
     */
    public function create()
    {
        $trainers = User::where('user_role', 'trainer')->where('is_active', 1)->get();
        $clients = User::where('user_role', 'client')->where('is_active', 1)->get();
        
        return view('fitness.meal-plans.create', compact('trainers', 'clients'), [
            'type_menu' => 'meal-plans'
        ]);
    }

    /**
     * Store a newly created meal plan in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:users,id',
            'trainer_id' => 'required|exists:users,id',
            'goal' => 'required|string|max:100',
            'daily_calories' => 'required|integer|min:1000|max:5000',
            'protein_g' => 'nullable|integer|min:0|max:500',
            'carbs_g' => 'nullable|integer|min:0|max:800',
            'fats_g' => 'nullable|integer|min:0|max:300',
            'fiber_g' => 'nullable|integer|min:0|max:100',
            'dietary_restrictions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        try {
            $validatedData['is_active'] = true;
            $mealPlan = MealPlan::create($validatedData);

            return redirect()->route('meal-plans.index')
                ->with('success', 'Meal plan created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create meal plan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified meal plan.
     */
    public function show(MealPlan $mealPlan)
    {
        $mealPlan->load(['client', 'trainer', 'mealPlanDays.meals']);

        return view('fitness.meal-plans.show', compact('mealPlan'), [
            'type_menu' => 'meal-plans'
        ]);
    }

    /**
     * Show the form for editing the specified meal plan.
     */
    public function edit(MealPlan $mealPlan)
    {
        $trainers = User::where('user_role', 'trainer')->where('is_active', 1)->get();
        $clients = User::where('user_role', 'client')->where('is_active', 1)->get();
        $mealPlan->load(['client', 'trainer']);

        return view('fitness.meal-plans.edit', compact('mealPlan', 'trainers', 'clients'), [
            'type_menu' => 'meal-plans'
        ]);
    }

    /**
     * Update the specified meal plan in storage.
     */
    public function update(Request $request, MealPlan $mealPlan)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:users,id',
            'trainer_id' => 'required|exists:users,id',
            'goal' => 'required|string|max:100',
            'daily_calories' => 'required|integer|min:1000|max:5000',
            'protein_g' => 'nullable|integer|min:0|max:500',
            'carbs_g' => 'nullable|integer|min:0|max:800',
            'fats_g' => 'nullable|integer|min:0|max:300',
            'fiber_g' => 'nullable|integer|min:0|max:100',
            'dietary_restrictions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $mealPlan->update($validatedData);

            return redirect()->route('meal-plans.show', $mealPlan)
                ->with('success', 'Meal plan updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update meal plan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified meal plan from storage.
     */
    public function destroy(MealPlan $mealPlan)
    {
        try {
            $mealPlan->update(['is_active' => false]);
            
            return redirect()->route('meal-plans.index')
                ->with('success', 'Meal plan deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to deactivate meal plan: ' . $e->getMessage());
        }
    }
}
