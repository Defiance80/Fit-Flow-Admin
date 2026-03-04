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
    public function index(Request )
    {
         = HealthAlert::with(['client', 'trainer']);

        // Filter by trainer
        if (->has('trainer_id') && !empty(->trainer_id)) {
            ->where('trainer_id', ->trainer_id);
        }

        // Filter by severity
        if (->has('severity') && !empty(->severity)) {
            ->where('severity', ->severity);
        }

        // Filter by acknowledged status
        if (->has('acknowledged') && ->acknowledged !== '') {
             = ->acknowledged === '1';
            ->where('acknowledged', );
        }

        // Order by: unacknowledged first, then by severity, then by date
         = ->orderByRaw("CASE WHEN acknowledged = 0 THEN 0 ELSE 1 END")
            ->orderByRaw("CASE 
                WHEN severity = 'critical' THEN 0 
                WHEN severity = 'warning' THEN 1 
                ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get trainers for filter
         = User::where('user_role', 'trainer')->where('is_active', 1)->get();

        return view('health.alerts', compact('alerts', 'trainers'), [
            'type_menu' => 'health-alerts'
        ]);
    }

    /**
     * Acknowledge an alert.
     */
    public function acknowledge(HealthAlert , Request )
    {
        try {
            ->update([
                'acknowledged' => true,
                'acknowledged_at' => now()
            ]);

            return redirect()->back()
                ->with('success', 'Alert acknowledged successfully.');
        } catch (\Exception ) {
            return redirect()->back()
                ->with('error', 'Failed to acknowledge alert: ' . ->getMessage());
        }
    }
}
