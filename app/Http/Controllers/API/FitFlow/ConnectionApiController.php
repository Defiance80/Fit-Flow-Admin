<?php

namespace App\Http\Controllers\API\FitFlow;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TrainerClient;
use Illuminate\Http\Request;

class ConnectionApiController extends Controller
{
    /**
     * Connect client to trainer via invite code
     * POST /api/fitflow/connect
     */
    public function connect(Request $request)
    {
        $request->validate([
            'invite_code' => 'required|string|size:8',
        ]);

        $client = $request->user();

        if ($client->user_role !== 'client') {
            return response()->json(['status' => false, 'message' => 'Only clients can connect to trainers'], 403);
        }

        $trainer = User::where('invite_code', strtoupper($request->invite_code))
            ->where('user_role', 'trainer')
            ->first();

        if (!$trainer) {
            return response()->json(['status' => false, 'message' => 'Invalid invite code'], 404);
        }

        // Check if already connected
        $existing = TrainerClient::where('trainer_id', $trainer->id)
            ->where('client_id', $client->id)
            ->first();

        if ($existing) {
            if ($existing->status === 'active') {
                return response()->json(['status' => false, 'message' => 'Already connected to this trainer'], 409);
            }
            // Reactivate
            $existing->update(['status' => 'active', 'subscribed_at' => now(), 'cancelled_at' => null]);
            return response()->json([
                'status' => true,
                'message' => "Reconnected with {$trainer->name}",
                'data' => ['trainer' => $trainer->only(['id', 'name', 'email', 'profile', 'bio', 'specializations'])],
            ]);
        }

        // Check trainer's subscription capacity
        $sub = $trainer->saasSubscription;
        if ($sub) {
            $currentClients = TrainerClient::where('trainer_id', $trainer->id)->where('status', 'active')->count();
            if ($currentClients >= $sub->max_clients) {
                return response()->json(['status' => false, 'message' => 'Trainer has reached their client limit'], 403);
            }
        }

        TrainerClient::create([
            'trainer_id' => $trainer->id,
            'client_id' => $client->id,
            'facility_id' => $trainer->facility_id,
            'status' => 'active',
            'subscribed_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => "Connected with {$trainer->name}!",
            'data' => ['trainer' => $trainer->only(['id', 'name', 'email', 'profile', 'bio', 'specializations'])],
        ]);
    }

    /**
     * Disconnect from trainer
     * POST /api/fitflow/disconnect
     */
    public function disconnect(Request $request)
    {
        $request->validate(['trainer_id' => 'required|exists:users,id']);

        $client = $request->user();
        $connection = TrainerClient::where('trainer_id', $request->trainer_id)
            ->where('client_id', $client->id)
            ->where('status', 'active')
            ->firstOrFail();

        $connection->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return response()->json(['status' => true, 'message' => 'Disconnected from trainer']);
    }

    /**
     * Get my trainers (client) or my clients (trainer)
     * GET /api/fitflow/connections
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->user_role === 'client') {
            $connections = TrainerClient::with('trainer:id,name,email,profile,bio,specializations')
                ->where('client_id', $user->id)
                ->where('status', 'active')
                ->get();

            return response()->json(['status' => true, 'data' => ['trainers' => $connections]]);
        }

        if ($user->user_role === 'trainer') {
            $connections = TrainerClient::with('client:id,name,email,profile,fitness_goals,date_of_birth,gender')
                ->where('trainer_id', $user->id)
                ->where('status', 'active')
                ->get();

            return response()->json(['status' => true, 'data' => ['clients' => $connections]]);
        }

        return response()->json(['status' => false, 'message' => 'Invalid role'], 403);
    }

    /**
     * Get trainer's invite code (or generate one)
     * GET /api/fitflow/invite-code
     */
    public function getInviteCode(Request $request)
    {
        $user = $request->user();

        if ($user->user_role !== 'trainer') {
            return response()->json(['status' => false, 'message' => 'Only trainers have invite codes'], 403);
        }

        if (!$user->invite_code) {
            $user->invite_code = User::generateInviteCode();
            $user->save();
        }

        return response()->json([
            'status' => true,
            'data' => ['invite_code' => $user->invite_code],
        ]);
    }
}
