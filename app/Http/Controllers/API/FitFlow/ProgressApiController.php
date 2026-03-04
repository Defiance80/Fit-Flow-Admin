<?php

namespace App\Http\Controllers\API\FitFlow;

use App\Http\Controllers\Controller;
use App\Models\TrainerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProgressApiController extends Controller
{
    public function getMeasurements(Request $request)
    {
        $clientId = $request->input('client_id', $request->user()->id);
        if ($clientId != $request->user()->id) {
            TrainerClient::where('trainer_id', $request->user()->id)->where('client_id', $clientId)->where('status', 'active')->firstOrFail();
        }

        $measurements = DB::table('body_measurements')
            ->where('client_id', $clientId)
            ->orderBy('measured_date', 'desc')
            ->paginate(30);

        return response()->json(['status' => true, 'data' => $measurements]);
    }

    public function storeMeasurement(Request $request)
    {
        $request->validate([
            'measured_date' => 'required|date',
            'weight' => 'nullable|numeric',
            'weight_unit' => 'nullable|in:lbs,kg',
            'body_fat_pct' => 'nullable|numeric|between:1,60',
            'chest_cm' => 'nullable|numeric',
            'waist_cm' => 'nullable|numeric',
            'hips_cm' => 'nullable|numeric',
            'left_arm_cm' => 'nullable|numeric',
            'right_arm_cm' => 'nullable|numeric',
            'left_thigh_cm' => 'nullable|numeric',
            'right_thigh_cm' => 'nullable|numeric',
            'left_calf_cm' => 'nullable|numeric',
            'right_calf_cm' => 'nullable|numeric',
        ]);

        $id = DB::table('body_measurements')->insertGetId(array_merge(
            $request->only(['measured_date','weight','weight_unit','body_fat_pct','chest_cm','waist_cm','hips_cm','left_arm_cm','right_arm_cm','left_thigh_cm','right_thigh_cm','left_calf_cm','right_calf_cm','notes']),
            ['client_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]
        ));

        return response()->json(['status' => true, 'message' => 'Measurement recorded', 'data' => ['id' => $id]], 201);
    }

    public function getPhotos(Request $request)
    {
        $clientId = $request->input('client_id', $request->user()->id);
        $photos = DB::table('progress_photos')
            ->where('client_id', $clientId)
            ->orderBy('taken_date', 'desc')
            ->paginate(20);

        return response()->json(['status' => true, 'data' => $photos]);
    }

    public function storePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:10240',
            'pose' => 'nullable|in:front,side,back,other',
            'taken_date' => 'required|date',
            'shared_with_trainer' => 'nullable|boolean',
        ]);

        $path = $request->file('photo')->store('progress_photos/' . $request->user()->id, 'public');

        $id = DB::table('progress_photos')->insertGetId([
            'client_id' => $request->user()->id,
            'photo_path' => $path,
            'pose' => $request->input('pose', 'front'),
            'taken_date' => $request->taken_date,
            'notes' => $request->notes,
            'shared_with_trainer' => $request->boolean('shared_with_trainer', false),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => true, 'message' => 'Photo uploaded', 'data' => ['id' => $id, 'path' => Storage::url($path)]], 201);
    }

    public function getProgressSummary(Request $request)
    {
        $clientId = $request->input('client_id', $request->user()->id);

        $latestMeasurement = DB::table('body_measurements')->where('client_id', $clientId)->orderBy('measured_date', 'desc')->first();
        $firstMeasurement = DB::table('body_measurements')->where('client_id', $clientId)->orderBy('measured_date', 'asc')->first();

        $totalWorkouts = DB::table('workout_logs')->where('client_id', $clientId)->count();
        $thisMonthWorkouts = DB::table('workout_logs')->where('client_id', $clientId)->where('logged_date', '>=', now()->startOfMonth())->count();
        $avgRpe = DB::table('workout_logs')->where('client_id', $clientId)->whereNotNull('overall_rpe')->avg('overall_rpe');

        $weightChange = null;
        if ($latestMeasurement && $firstMeasurement && $latestMeasurement->weight && $firstMeasurement->weight) {
            $weightChange = round($latestMeasurement->weight - $firstMeasurement->weight, 1);
        }

        return response()->json(['status' => true, 'data' => [
            'latest_measurement' => $latestMeasurement,
            'weight_change' => $weightChange,
            'total_workouts' => $totalWorkouts,
            'this_month_workouts' => $thisMonthWorkouts,
            'avg_rpe' => $avgRpe ? round($avgRpe, 1) : null,
            'photo_count' => DB::table('progress_photos')->where('client_id', $clientId)->count(),
        ]]);
    }
}
