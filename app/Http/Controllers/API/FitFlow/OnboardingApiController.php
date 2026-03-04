<?php

namespace App\Http\Controllers\API\FitFlow;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SaasSubscription;
use App\Models\TrainerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OnboardingApiController extends Controller
{
    /**
     * Register as trainer
     * POST /api/fitflow/register/trainer
     */
    public function registerTrainer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'mobile' => 'nullable|string',
            'bio' => 'nullable|string',
            'specializations' => 'nullable|array',
            'certifications' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile' => $request->mobile,
            'is_active' => true,
            'user_role' => 'trainer',
            'is_independent' => true,
            'invite_code' => User::generateInviteCode(),
            'bio' => $request->bio,
            'specializations' => $request->specializations,
            'certifications' => $request->certifications,
        ]);

        // Create free tier subscription
        SaasSubscription::create([
            'user_id' => $user->id,
            'tier' => 'free',
            'billing_cycle' => 'monthly',
            'price' => 0,
            'status' => 'active',
            'max_clients' => 5,
            'max_trainers' => 1,
            'storage_mb' => 500,
            'current_period_start' => now(),
            'current_period_end' => now()->addYear(),
        ]);

        $token = $user->createToken('fitflow-mobile')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Trainer account created',
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'user_role', 'invite_code', 'profile']),
                'token' => $token,
                'subscription' => ['tier' => 'free', 'max_clients' => 5],
            ],
        ], 201);
    }

    /**
     * Register as client
     * POST /api/fitflow/register/client
     */
    public function registerClient(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'mobile' => 'nullable|string',
            'invite_code' => 'nullable|string|size:8',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'date_of_birth' => 'nullable|date',
            'fitness_goals' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile' => $request->mobile,
            'is_active' => true,
            'user_role' => 'client',
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'fitness_goals' => $request->fitness_goals,
        ]);

        // Auto-connect if invite code provided
        $trainerConnected = null;
        if ($request->invite_code) {
            $trainer = User::where('invite_code', strtoupper($request->invite_code))->where('user_role', 'trainer')->first();
            if ($trainer) {
                TrainerClient::create([
                    'trainer_id' => $trainer->id,
                    'client_id' => $user->id,
                    'facility_id' => $trainer->facility_id,
                    'status' => 'active',
                    'subscribed_at' => now(),
                ]);
                $trainerConnected = $trainer->only(['id', 'name', 'profile']);
            }
        }

        $token = $user->createToken('fitflow-mobile')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Client account created',
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'user_role', 'profile']),
                'token' => $token,
                'trainer_connected' => $trainerConnected,
            ],
        ], 201);
    }

    /**
     * Get user profile with role-specific data
     * GET /api/fitflow/profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $data = $user->toArray();

        if ($user->user_role === 'trainer') {
            $data['client_count'] = TrainerClient::where('trainer_id', $user->id)->where('status', 'active')->count();
            $data['subscription'] = $user->saasSubscription;
            $data['invite_code'] = $user->invite_code;
        } elseif ($user->user_role === 'client') {
            $data['trainer_count'] = TrainerClient::where('client_id', $user->id)->where('status', 'active')->count();
        }

        return response()->json(['status' => true, 'data' => $data]);
    }

    /**
     * Update profile
     * PUT /api/fitflow/profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $fillable = ['name', 'mobile', 'bio', 'gender', 'date_of_birth', 'height_cm', 'weight_kg', 'fitness_goals', 'medical_notes', 'emergency_contact_name', 'emergency_contact_phone'];

        if ($user->user_role === 'trainer') {
            $fillable = array_merge($fillable, ['specializations', 'certifications']);
        }

        $user->update($request->only($fillable));

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles/' . $user->id, 'public');
            $user->update(['profile' => $path]);
        }

        return response()->json(['status' => true, 'message' => 'Profile updated', 'data' => $user]);
    }
}
