<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Facility;
use App\Models\TrainerClient;
use App\Models\Fitness\TrainingProgram;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TrainerController extends Controller
{
    /**
     * Display a listing of the trainers.
     */
    public function index(Request $request)
    {
        $query = User::where('user_role', 'trainer')
            ->with(['facility', 'trainerClients']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filter by facility
        if ($request->has('facility_id') && !empty($request->facility_id)) {
            $query->where('facility_id', $request->facility_id);
        }

        $trainers = $query->paginate(15);
        $facilities = Facility::all();

        return view('trainers.index', compact('trainers', 'facilities'), [
            'type_menu' => 'trainers'
        ]);
    }

    /**
     * Show the form for creating a new trainer.
     */
    public function create()
    {
        $facilities = Facility::all();
        return view('trainers.create', compact('facilities'), [
            'type_menu' => 'trainers'
        ]);
    }

    /**
     * Store a newly created trainer in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'bio' => 'nullable|string',
            'specializations' => 'nullable|string',
            'certifications' => 'nullable|string',
            'facility_id' => 'nullable|exists:facilities,id',
            'is_independent' => 'nullable|boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $userData = [
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'user_role' => 'trainer',
                'status' => 'active',
                'invite_code' => strtoupper(Str::random(8)),
                'facility_id' => $validatedData['facility_id'] ?? null,
                'is_independent' => $validatedData['is_independent'] ?? false,
                'bio' => $validatedData['bio'] ?? null,
                'specializations' => $validatedData['specializations'] ?? null,
                'certifications' => $validatedData['certifications'] ?? null,
                'email_verified_at' => now(),
            ];

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('trainer_photos', $filename, 'public');
                $userData['profile_photo'] = $path;
            }

            $trainer = User::create($userData);

            DB::commit();

            return redirect()->route('trainers.index')
                ->with('success', 'Trainer created successfully. Invite code: ' . $trainer->invite_code);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create trainer: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified trainer.
     */
    public function show(User $trainer)
    {
        if ($trainer->user_role !== 'trainer') {
            abort(404);
        }

        $trainer->load([
            'facility', 
            'trainerClients.client',
            'trainingPrograms',
            'schedules' => function($query) {
                $query->where('start_time', '>=', now())->orderBy('start_time');
            }
        ]);

        $clientsCount = $trainer->trainerClients()->count();
        $programsCount = $trainer->trainingPrograms()->count();
        $upcomingSessionsCount = $trainer->schedules()
            ->where('start_time', '>=', now())
            ->count();

        return view('trainers.show', compact('trainer', 'clientsCount', 'programsCount', 'upcomingSessionsCount'), [
            'type_menu' => 'trainers'
        ]);
    }

    /**
     * Show the form for editing the specified trainer.
     */
    public function edit(User $trainer)
    {
        if ($trainer->user_role !== 'trainer') {
            abort(404);
        }

        $facilities = Facility::all();
        return view('trainers.edit', compact('trainer', 'facilities'), [
            'type_menu' => 'trainers'
        ]);
    }

    /**
     * Update the specified trainer in storage.
     */
    public function update(Request $request, User $trainer)
    {
        if ($trainer->user_role !== 'trainer') {
            abort(404);
        }

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $trainer->id,
            'password' => 'nullable|string|min:8|confirmed',
            'bio' => 'nullable|string',
            'specializations' => 'nullable|string',
            'certifications' => 'nullable|string',
            'facility_id' => 'nullable|exists:facilities,id',
            'is_independent' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $updateData = [
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'facility_id' => $validatedData['facility_id'] ?? null,
                'is_independent' => $validatedData['is_independent'] ?? false,
                'bio' => $validatedData['bio'] ?? null,
                'specializations' => $validatedData['specializations'] ?? null,
                'certifications' => $validatedData['certifications'] ?? null,
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
                $path = $file->storeAs('trainer_photos', $filename, 'public');
                $updateData['profile_photo'] = $path;
            }

            $trainer->update($updateData);

            return redirect()->route('trainers.show', $trainer)
                ->with('success', 'Trainer updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update trainer: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified trainer from storage (soft delete).
     */
    public function destroy(User $trainer)
    {
        if ($trainer->user_role !== 'trainer') {
            abort(404);
        }

        try {
            $trainer->update(['status' => 'inactive']);
            $trainer->delete(); // This will soft delete if using SoftDeletes trait

            return redirect()->route('trainers.index')
                ->with('success', 'Trainer deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to deactivate trainer: ' . $e->getMessage());
        }
    }
}
