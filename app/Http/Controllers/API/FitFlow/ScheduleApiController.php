<?php

namespace App\Http\Controllers\API\FitFlow;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\TrainerClient;
use Illuminate\Http\Request;

class ScheduleApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Schedule::with(['trainer:id,name,profile', 'client:id,name,profile']);

        if ($user->user_role === 'trainer') {
            $query->where('trainer_id', $user->id);
        } elseif ($user->user_role === 'client') {
            $query->where('client_id', $user->id);
        }

        if ($request->has('from')) $query->where('start_at', '>=', $request->from);
        if ($request->has('to')) $query->where('start_at', '<=', $request->to);
        if ($request->has('status')) $query->where('status', $request->status);

        $query->where('status', '!=', 'cancelled')->orderBy('start_at');

        return response()->json(['status' => true, 'data' => $query->paginate(50)]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:session,consultation,assessment,group_class,blocked',
            'start_at' => 'required|date|after:now',
            'end_at' => 'required|date|after:start_at',
            'location' => 'nullable|string',
            'is_virtual' => 'nullable|boolean',
            'meeting_url' => 'nullable|url',
            'recurrence' => 'nullable|in:none,daily,weekly,biweekly,monthly',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();
        if ($request->client_id) {
            TrainerClient::where('trainer_id', $user->id)->where('client_id', $request->client_id)->where('status', 'active')->firstOrFail();
        }

        $schedule = Schedule::create(array_merge($request->all(), [
            'trainer_id' => $user->id,
            'facility_id' => $user->facility_id,
            'status' => 'scheduled',
        ]));

        // Handle recurrence
        if ($request->recurrence && $request->recurrence !== 'none') {
            $intervals = ['daily' => 1, 'weekly' => 7, 'biweekly' => 14, 'monthly' => 30];
            $days = $intervals[$request->recurrence];
            for ($i = 1; $i <= 8; $i++) {
                Schedule::create(array_merge($schedule->toArray(), [
                    'id' => null,
                    'start_at' => $schedule->start_at->addDays($days * $i),
                    'end_at' => $schedule->end_at->addDays($days * $i),
                    'parent_schedule_id' => $schedule->id,
                    'recurrence' => 'none',
                ]));
            }
        }

        return response()->json(['status' => true, 'message' => 'Session booked', 'data' => $schedule], 201);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::where('trainer_id', $request->user()->id)->findOrFail($id);
        $schedule->update($request->only(['title', 'start_at', 'end_at', 'location', 'is_virtual', 'meeting_url', 'status', 'notes', 'cancellation_reason']));
        return response()->json(['status' => true, 'message' => 'Schedule updated', 'data' => $schedule]);
    }

    public function destroy(Request $request, $id)
    {
        $schedule = Schedule::where('trainer_id', $request->user()->id)->findOrFail($id);
        $schedule->update(['status' => 'cancelled', 'cancellation_reason' => $request->input('reason', 'Cancelled')]);
        return response()->json(['status' => true, 'message' => 'Session cancelled']);
    }

    public function upcoming(Request $request)
    {
        $user = $request->user();
        $col = $user->user_role === 'trainer' ? 'trainer_id' : 'client_id';

        $sessions = Schedule::with(['trainer:id,name,profile', 'client:id,name,profile'])
            ->where($col, $user->id)
            ->where('start_at', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_at')
            ->limit($request->input('limit', 10))
            ->get();

        return response()->json(['status' => true, 'data' => $sessions]);
    }
}
