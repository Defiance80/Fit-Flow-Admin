<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the schedules.
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['trainer', 'client']);

        // Date filter (default to current month)
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $query->whereBetween('start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Filter by trainer
        if ($request->has('trainer_id') && !empty($request->trainer_id)) {
            $query->where('trainer_id', $request->trainer_id);
        }

        // Filter by client
        if ($request->has('client_id') && !empty($request->client_id)) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by session type
        if ($request->has('session_type') && !empty($request->session_type)) {
            $query->where('session_type', $request->session_type);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $schedules = $query->orderBy('start_time')->paginate(20);
        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();
        $clients = User::where('user_role', 'client')->where('status', 'active')->get();

        // Upcoming sessions for today
        $todaysSchedules = Schedule::with(['trainer', 'client'])
            ->whereDate('start_time', today())
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->take(10)
            ->get();

        return view('schedules.index', compact(
            'schedules', 'trainers', 'clients', 'startDate', 'endDate', 'todaysSchedules'
        ), [
            'type_menu' => 'calendar'
        ]);
    }

    /**
     * Show the form for creating a new schedule.
     */
    public function create()
    {
        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();
        $clients = User::where('user_role', 'client')->where('status', 'active')->get();
        
        return view('schedules.create', compact('trainers', 'clients'), [
            'type_menu' => 'calendar'
        ]);
    }

    /**
     * Store a newly created schedule in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'trainer_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:users,id',
            'session_type' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'is_virtual' => 'boolean',
            'meeting_link' => 'nullable|url|max:500',
            'recurrence_type' => 'nullable|in:none,daily,weekly,monthly',
            'recurrence_end_date' => 'nullable|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $scheduleData = array_merge($validatedData, [
                'status' => 'scheduled',
                'is_virtual' => $validatedData['is_virtual'] ?? false,
            ]);

            $schedule = Schedule::create($scheduleData);

            // Handle recurring schedules
            if (!empty($validatedData['recurrence_type']) && $validatedData['recurrence_type'] !== 'none') {
                $this->createRecurringSchedules($schedule, $validatedData);
            }

            DB::commit();

            return redirect()->route('schedules.index')
                ->with('success', 'Schedule created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create schedule: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified schedule.
     */
    public function show(Schedule $schedule)
    {
        $schedule->load(['trainer', 'client']);
        
        return view('schedules.show', compact('schedule'), [
            'type_menu' => 'calendar'
        ]);
    }

    /**
     * Show the form for editing the specified schedule.
     */
    public function edit(Schedule $schedule)
    {
        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();
        $clients = User::where('user_role', 'client')->where('status', 'active')->get();
        $schedule->load(['trainer', 'client']);
        
        return view('schedules.edit', compact('schedule', 'trainers', 'clients'), [
            'type_menu' => 'calendar'
        ]);
    }

    /**
     * Update the specified schedule in storage.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validatedData = $request->validate([
            'trainer_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:users,id',
            'session_type' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'is_virtual' => 'boolean',
            'meeting_link' => 'nullable|url|max:500',
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
            'notes' => 'nullable|string',
        ]);

        try {
            $updateData = array_merge($validatedData, [
                'is_virtual' => $validatedData['is_virtual'] ?? false,
            ]);

            $schedule->update($updateData);

            return redirect()->route('schedules.show', $schedule)
                ->with('success', 'Schedule updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update schedule: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified schedule from storage.
     */
    public function destroy(Schedule $schedule)
    {
        try {
            $schedule->update(['status' => 'cancelled']);
            
            return redirect()->route('schedules.index')
                ->with('success', 'Schedule cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to cancel schedule: ' . $e->getMessage());
        }
    }

    /**
     * Create recurring schedules based on recurrence rules.
     */
    private function createRecurringSchedules(Schedule $originalSchedule, array $data)
    {
        if (empty($data['recurrence_end_date']) || $data['recurrence_type'] === 'none') {
            return;
        }

        $currentDate = new \DateTime($data['start_time']);
        $endDate = new \DateTime($data['recurrence_end_date']);
        $duration = (new \DateTime($data['start_time']))->diff(new \DateTime($data['end_time']));

        while ($currentDate <= $endDate) {
            // Move to next occurrence
            switch ($data['recurrence_type']) {
                case 'daily':
                    $currentDate->modify('+1 day');
                    break;
                case 'weekly':
                    $currentDate->modify('+1 week');
                    break;
                case 'monthly':
                    $currentDate->modify('+1 month');
                    break;
            }

            if ($currentDate > $endDate) {
                break;
            }

            $newEndTime = clone $currentDate;
            $newEndTime->add($duration);

            Schedule::create([
                'trainer_id' => $originalSchedule->trainer_id,
                'client_id' => $originalSchedule->client_id,
                'session_type' => $originalSchedule->session_type,
                'title' => $originalSchedule->title,
                'description' => $originalSchedule->description,
                'start_time' => $currentDate->format('Y-m-d H:i:s'),
                'end_time' => $newEndTime->format('Y-m-d H:i:s'),
                'location' => $originalSchedule->location,
                'is_virtual' => $originalSchedule->is_virtual,
                'meeting_link' => $originalSchedule->meeting_link,
                'status' => 'scheduled',
                'notes' => $originalSchedule->notes,
                'parent_schedule_id' => $originalSchedule->id,
            ]);
        }
    }
}
