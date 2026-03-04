<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * Display a listing of the facilities.
     */
    public function index(Request $request)
    {
        $query = Facility::with(['owner']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
            });
        }

        // Filter by tier
        if ($request->has('tier') && !empty($request->tier)) {
            $query->where('tier', $request->tier);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $facilities = $query->withCount(['trainers', 'clients'])
            ->orderBy('name')
            ->paginate(15);

        return view('facilities.index', compact('facilities'), [
            'type_menu' => 'facilities'
        ]);
    }

    /**
     * Show the form for creating a new facility.
     */
    public function create()
    {
        $owners = User::where('user_role', 'admin')->orWhere('user_role', 'facility_owner')->get();
        
        return view('facilities.create', compact('owners'), [
            'type_menu' => 'facilities'
        ]);
    }

    /**
     * Store a newly created facility in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'tier' => 'required|in:basic,premium,enterprise',
            'max_trainers' => 'nullable|integer|min:1|max:1000',
            'max_clients' => 'nullable|integer|min:1|max:10000',
            'operating_hours' => 'nullable|json',
            'amenities' => 'nullable|string',
            'status' => 'required|in:active,inactive,pending'
        ]);

        try {
            Facility::create($validatedData);

            return redirect()->route('facilities.index')
                ->with('success', 'Facility created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create facility: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified facility.
     */
    public function show(Facility $facility)
    {
        $facility->load(['owner']);
        
        // Get trainers associated with this facility
        $trainers = User::where('user_role', 'trainer')
            ->where('facility_id', $facility->id)
            ->with(['trainerClients'])
            ->get();

        // Get clients through trainer relationships
        $clientIds = $trainers->flatMap(function($trainer) {
            return $trainer->trainerClients->pluck('client_id');
        })->unique();

        $clients = User::whereIn('id', $clientIds)->get();

        $stats = [
            'total_trainers' => $trainers->count(),
            'total_clients' => $clients->count(),
            'active_trainers' => $trainers->where('status', 'active')->count(),
            'utilization' => $facility->max_trainers > 0 ? 
                round(($trainers->count() / $facility->max_trainers) * 100, 1) : 0,
        ];

        return view('facilities.show', compact('facility', 'trainers', 'clients', 'stats'), [
            'type_menu' => 'facilities'
        ]);
    }

    /**
     * Show the form for editing the specified facility.
     */
    public function edit(Facility $facility)
    {
        $owners = User::where('user_role', 'admin')->orWhere('user_role', 'facility_owner')->get();
        
        return view('facilities.edit', compact('facility', 'owners'), [
            'type_menu' => 'facilities'
        ]);
    }

    /**
     * Update the specified facility in storage.
     */
    public function update(Request $request, Facility $facility)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'tier' => 'required|in:basic,premium,enterprise',
            'max_trainers' => 'nullable|integer|min:1|max:1000',
            'max_clients' => 'nullable|integer|min:1|max:10000',
            'operating_hours' => 'nullable|json',
            'amenities' => 'nullable|string',
            'status' => 'required|in:active,inactive,pending'
        ]);

        try {
            $facility->update($validatedData);

            return redirect()->route('facilities.show', $facility)
                ->with('success', 'Facility updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update facility: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified facility from storage.
     */
    public function destroy(Facility $facility)
    {
        try {
            // Check if facility has active trainers
            $activeTrainers = User::where('facility_id', $facility->id)
                ->where('status', 'active')
                ->count();

            if ($activeTrainers > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete facility with active trainers. Please reassign or deactivate trainers first.');
            }

            $facility->update(['status' => 'inactive']);
            
            return redirect()->route('facilities.index')
                ->with('success', 'Facility deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to deactivate facility: ' . $e->getMessage());
        }
    }
}
