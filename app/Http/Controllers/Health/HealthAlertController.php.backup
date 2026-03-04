<?php

namespace App\Http\Controllers\Health;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Health\HealthAlert;
use Illuminate\Http\Request;

class HealthAlertController extends Controller
{
    /**
     * Display a listing of health alerts.
     */
    public function index(Request $request)
    {
        $query = HealthAlert::with(['user']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('alert_type', 'LIKE', "%{$search}%")
                  ->orWhere('message', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($subQ) use ($search) {
                      $subQ->where('first_name', 'LIKE', "%{$search}%")
                           ->orWhere('last_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter by trainer (show alerts for trainer's clients)
        if ($request->has('trainer_id') && !empty($request->trainer_id)) {
            $query->whereHas('user.trainerClients', function($q) use ($request) {
                $q->where('trainer_id', $request->trainer_id);
            });
        }

        // Filter by severity
        if ($request->has('severity') && !empty($request->severity)) {
            $query->where('severity', $request->severity);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        } else {
            // Default: show unacknowledged first
            $query->orderByRaw("CASE WHEN status = 'unacknowledged' THEN 0 ELSE 1 END");
        }

        $alerts = $query->orderByRaw("CASE 
            WHEN severity = 'critical' THEN 0 
            WHEN severity = 'high' THEN 1 
            WHEN severity = 'medium' THEN 2 
            ELSE 3 END")
            ->orderBy('triggered_at', 'desc')
            ->paginate(20);

        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();

        // Summary stats
        $unacknowledgedCount = HealthAlert::where('status', 'unacknowledged')->count();
        $criticalCount = HealthAlert::where('severity', 'critical')->where('status', 'unacknowledged')->count();
        $todaysAlertsCount = HealthAlert::whereDate('triggered_at', today())->count();

        return view('health.alerts', compact(
            'alerts', 'trainers', 'unacknowledgedCount', 'criticalCount', 'todaysAlertsCount'
        ), [
            'type_menu' => 'health-alerts'
        ]);
    }

    /**
     * Mark an alert as acknowledged.
     */
    public function acknowledge(HealthAlert $alert, Request $request)
    {
        try {
            $alert->update([
                'status' => 'acknowledged',
                'acknowledged_at' => now(),
                'acknowledged_by' => auth()->id(),
                'notes' => $request->notes
            ]);

            return redirect()->back()
                ->with('success', 'Alert acknowledged successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to acknowledge alert: ' . $e->getMessage());
        }
    }
}
