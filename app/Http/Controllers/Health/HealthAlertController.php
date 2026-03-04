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
        $query = HealthAlert::with(['client', 'trainer']);

        // Filter by trainer
        if ($request->has('trainer_id') && !empty($request->trainer_id)) {
            $query->where('trainer_id', $request->trainer_id);
        }

        // Filter by severity
        if ($request->has('severity') && !empty($request->severity)) {
            $query->where('severity', $request->severity);
        }

        // Filter by acknowledged status
        if ($request->has('acknowledged') && $request->acknowledged !== '') {
            $acknowledged = $request->acknowledged === '1';
            $query->where('acknowledged', $acknowledged);
        }

        // Order by: unacknowledged first, then by severity, then by date
        $alerts = $query->orderByRaw("CASE WHEN acknowledged = 0 THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN severity = 'critical' THEN 0 WHEN severity = 'warning' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get trainers for filter
        $trainers = User::where('user_role', 'trainer')->where('is_active', 1)->get();

        return view('health.alerts', compact('alerts', 'trainers'), [
            'type_menu' => 'health-alerts'
        ]);
    }

    /**
     * Acknowledge an alert.
     */
    public function acknowledge(HealthAlert $alert, Request $request)
    {
        try {
            $alert->update([
                'acknowledged' => true,
                'acknowledged_at' => now()
            ]);

            return redirect()->back()
                ->with('success', 'Alert acknowledged successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to acknowledge alert: ' . $e->getMessage());
        }
    }
}
