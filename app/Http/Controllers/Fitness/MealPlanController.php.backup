<?php

namespace App\Http\Controllers\Fitness;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Fitness\MealPlan;
use App\Models\Fitness\MealPlanDay;
use App\Models\Fitness\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MealPlanController extends Controller
{
    /**
     * Display a listing of the meal plans.
     */
    public function index(Request $request)
    {
        $query = MealPlan::with(['client', 'trainer']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('goal', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function($subQ) use ($search) {
                      $subQ->where('first_name', 'LIKE', "%{$search}%")
                           ->orWhere('last_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter by trainer
        if ($request->has('trainer_id') && !empty($request->trainer_id)) {
            $query->where('trainer_id', $request->trainer_id);
        }

        // Filter by goal
        if ($request->has('goal') && !empty($request->goal)) {
            $query->where('goal', $request->goal);
        }

        $mealPlans = $query->orderBy('created_at', 'desc')->paginate(15);
        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();

        return view('fitness.meal-plans.index', compact('mealPlans', 'trainers'), [
            'type_menu' => 'meal-plans'
        ]);
    }

    /**
     * Show the form for creating a new meal plan.
     */
    public function create()
    {
        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();
        $clients = User::where('user_role', 'client')->where('status', 'active')->get();
        
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
            'daily_calories_target' => 'required|integer|min:1000|max:5000',
            'daily_protein_grams' => 'nullable|integer|min:0|max:500',
            'daily_carbs_grams' => 'nullable|integer|min:0|max:800',
            'daily_fats_grams' => 'nullable|integer|min:0|max:300',
            'duration_days' => 'required|integer|min:1|max:365',
            'dietary_restrictions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $mealPlan = MealPlan::create($validatedData);

            return redirect()->route('meal-plans.show', $mealPlan)
                ->with('success', 'Meal plan created successfully. You can now add meals for each day.');
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
        $mealPlan->load([
            'client', 
            'trainer',
            'mealPlanDays' => function($query) {
                $query->orderBy('day_number');
            },
            'mealPlanDays.meals' => function($query) {
                $query->orderBy('meal_order');
            }
        ]);

        return view('fitness.meal-plans.show', compact('mealPlan'), [
            'type_menu' => 'meal-plans'
        ]);
    }

    /**
     * Show the form for editing the specified meal plan.
     */
    public function edit(MealPlan $mealPlan)
    {
        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();
        $clients = User::where('user_role', 'client')->where('status', 'active')->get();
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
            'daily_calories_target' => 'required|integer|min:1000|max:5000',
            'daily_protein_grams' => 'nullable|integer|min:0|max:500',
            'daily_carbs_grams' => 'nullable|integer|min:0|max:800',
            'daily_fats_grams' => 'nullable|integer|min:0|max:300',
            'duration_days' => 'required|integer|min:1|max:365',
            'dietary_restrictions' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,completed',
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
            $mealPlan->update(['status' => 'inactive']);
            
            return redirect()->route('meal-plans.index')
                ->with('success', 'Meal plan deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to deactivate meal plan: ' . $e->getMessage());
        }
    }
}
