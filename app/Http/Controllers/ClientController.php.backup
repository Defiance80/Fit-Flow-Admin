<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TrainerClient;
use App\Models\Health\HealthMetric;
use App\Models\Fitness\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients.
     */
    public function index(Request $request)
    {
        $query = User::where('user_role', 'client')
            ->with(['trainerClients.trainer', 'clientPrograms']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filter by trainer
        if ($request->has('trainer_id') && !empty($request->trainer_id)) {
            $query->whereHas('trainerClients', function($q) use ($request) {
                $q->where('trainer_id', $request->trainer_id);
            });
        }

        $clients = $query->paginate(15);
        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();

        return view('clients.index', compact('clients', 'trainers'), [
            'type_menu' => 'clients'
        ]);
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();
        return view('clients.create', compact('trainers'), [
            'type_menu' => 'clients'
        ]);
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'invite_code' => 'nullable|string|exists:users,invite_code',
            'trainer_id' => 'nullable|exists:users,id',
            'fitness_goals' => 'nullable|string',
            'medical_notes' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $userData = [
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'user_role' => 'client',
                'status' => 'active',
                'fitness_goals' => $validatedData['fitness_goals'] ?? null,
                'medical_notes' => $validatedData['medical_notes'] ?? null,
                'emergency_contact_name' => $validatedData['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validatedData['emergency_contact_phone'] ?? null,
                'date_of_birth' => $validatedData['date_of_birth'] ?? null,
                'email_verified_at' => now(),
            ];

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('client_photos', $filename, 'public');
                $userData['profile_photo'] = $path;
            }

            $client = User::create($userData);

            // Link to trainer
            $trainerId = null;
            if (!empty($validatedData['invite_code'])) {
                $trainer = User::where('invite_code', $validatedData['invite_code'])
                    ->where('user_role', 'trainer')
                    ->first();
                if ($trainer) {
                    $trainerId = $trainer->id;
                }
            } elseif (!empty($validatedData['trainer_id'])) {
                $trainerId = $validatedData['trainer_id'];
            }

            if ($trainerId) {
                TrainerClient::create([
                    'trainer_id' => $trainerId,
                    'client_id' => $client->id,
                    'status' => 'active',
                    'start_date' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('clients.index')
                ->with('success', 'Client created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create client: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified client.
     */
    public function show(User $client)
    {
        if ($client->user_role !== 'client') {
            abort(404);
        }

        $client->load([
            'trainerClients.trainer',
            'clientPrograms.program',
            'healthMetrics' => function($query) {
                $query->orderBy('recorded_at', 'desc')->take(10);
            }
        ]);

        // Get recent health metrics summary
        $recentMetrics = $client->healthMetrics()
            ->where('recorded_at', '>=', now()->subDays(30))
            ->orderBy('recorded_at', 'desc')
            ->take(5)
            ->get();

        $activePrograms = $client->clientPrograms()
            ->where('status', 'active')
            ->with('program')
            ->get();

        return view('clients.show', compact('client', 'recentMetrics', 'activePrograms'), [
            'type_menu' => 'clients'
        ]);
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(User $client)
    {
        if ($client->user_role !== 'client') {
            abort(404);
        }

        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();
        $client->load('trainerClients.trainer');
        
        return view('clients.edit', compact('client', 'trainers'), [
            'type_menu' => 'clients'
        ]);
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, User $client)
    {
        if ($client->user_role !== 'client') {
            abort(404);
        }

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $client->id,
            'password' => 'nullable|string|min:8|confirmed',
            'fitness_goals' => 'nullable|string',
            'medical_notes' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'status' => 'required|in:active,inactive',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $updateData = [
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'fitness_goals' => $validatedData['fitness_goals'] ?? null,
                'medical_notes' => $validatedData['medical_notes'] ?? null,
                'emergency_contact_name' => $validatedData['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validatedData['emergency_contact_phone'] ?? null,
                'date_of_birth' => $validatedData['date_of_birth'] ?? null,
                'status' => $validatedData['status'],
            ];

            // Update password if provided
            if (!empty($validatedData['password'])) {
                $updateData['password'] = Hash::make($validatedData['password']);
            }

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('client_photos', $filename, 'public');
                $updateData['profile_photo'] = $path;
            }

            $client->update($updateData);

            return redirect()->route('clients.show', $client)
                ->with('success', 'Client updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update client: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified client from storage (soft delete).
     */
    public function destroy(User $client)
    {
        if ($client->user_role !== 'client') {
            abort(404);
        }

        try {
            $client->update(['status' => 'inactive']);
            $client->delete(); // This will soft delete if using SoftDeletes trait

            return redirect()->route('clients.index')
                ->with('success', 'Client deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to deactivate client: ' . $e->getMessage());
        }
    }
}
