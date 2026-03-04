<?php

namespace App\Http\Controllers\API\FitFlow;

use App\Http\Controllers\Controller;
use App\Models\Fitness\MealPlan;
use App\Models\Fitness\MealPlanDay;
use App\Models\Fitness\Meal;
use App\Models\TrainerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MealPlanApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = MealPlan::with(['client:id,name,profile', 'trainer:id,name,profile']);

        if ($user->user_role === 'trainer') {
            $query->where('trainer_id', $user->id);
        } elseif ($user->user_role === 'client') {
            $query->where('client_id', $user->id);
        }

        if ($request->has('active')) $query->where('is_active', $request->boolean('active'));

        return response()->json(['status' => true, 'data' => $query->orderBy('created_at', 'desc')->paginate(20)]);
    }

    public function show($id)
    {
        $plan = MealPlan::with(['client:id,name', 'trainer:id,name', 'days.meals'])->findOrFail($id);
        return response()->json(['status' => true, 'data' => $plan]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'goal' => 'required|string',
            'daily_calories' => 'nullable|integer',
            'protein_g' => 'nullable|integer',
            'carbs_g' => 'nullable|integer',
            'fats_g' => 'nullable|integer',
            'days' => 'nullable|array',
            'days.*.day_of_week' => 'required|string',
            'days.*.day_type' => 'nullable|string',
            'days.*.meals' => 'nullable|array',
        ]);

        $user = $request->user();
        TrainerClient::where('trainer_id', $user->id)->where('client_id', $request->client_id)->where('status', 'active')->firstOrFail();

        DB::beginTransaction();
        try {
            $plan = MealPlan::create(array_merge(
                $request->only(['client_id', 'name', 'description', 'goal', 'daily_calories', 'protein_g', 'carbs_g', 'fats_g', 'fiber_g', 'notes', 'start_date', 'end_date']),
                ['trainer_id' => $user->id, 'is_active' => true, 'dietary_restrictions' => $request->dietary_restrictions, 'allergies' => $request->allergies]
            ));

            if ($request->has('days')) {
                foreach ($request->days as $dayData) {
                    $day = MealPlanDay::create([
                        'meal_plan_id' => $plan->id,
                        'day_of_week' => $dayData['day_of_week'],
                        'day_type' => $dayData['day_type'] ?? 'training',
                        'adjusted_calories' => $dayData['adjusted_calories'] ?? null,
                    ]);

                    if (!empty($dayData['meals'])) {
                        foreach ($dayData['meals'] as $mi => $mealData) {
                            Meal::create(array_merge($mealData, [
                                'meal_plan_day_id' => $day->id,
                                'sort_order' => $mi,
                            ]));
                        }
                    }
                }
            }

            DB::commit();
            $plan->load('days.meals');
            return response()->json(['status' => true, 'message' => 'Meal plan created', 'data' => $plan], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $plan = MealPlan::where('trainer_id', $request->user()->id)->findOrFail($id);
        $plan->update($request->only(['name', 'description', 'goal', 'daily_calories', 'protein_g', 'carbs_g', 'fats_g', 'is_active', 'notes']));
        return response()->json(['status' => true, 'message' => 'Meal plan updated', 'data' => $plan]);
    }

    public function destroy(Request $request, $id)
    {
        MealPlan::where('trainer_id', $request->user()->id)->findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Meal plan deleted']);
    }
}
