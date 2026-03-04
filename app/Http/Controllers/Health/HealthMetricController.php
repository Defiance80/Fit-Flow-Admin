<?php

namespace App\Http\Controllers\Health;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Health\HealthMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthMetricController extends Controller
{
    /**
     * Display the health metrics dashboard.
     */
    public function index(Request $request)
    {
        $query = User::where('user_role', 'client')
            ->where('status', 'active')
            ->with(['healthMetrics' => function($q) {
                $q->orderBy('recorded_at', 'desc')->take(1);
            }]);

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

        $clients = $query->paginate(20);
        $trainers = User::where('user_role', 'trainer')->where('status', 'active')->get();

        // Get summary statistics
        $totalClients = User::where('user_role', 'client')->where('status', 'active')->count();
        $totalMetricsToday = HealthMetric::whereDate('recorded_at', today())->count();
        $avgHeartRate = HealthMetric::where('metric_type', 'heart_rate')
            ->whereDate('recorded_at', '>=', now()->subDays(7))
            ->avg('value');
        $avgSteps = HealthMetric::where('metric_type', 'steps')
            ->whereDate('recorded_at', '>=', now()->subDays(7))
            ->avg('value');

        return view('health.metrics-dashboard', compact(
            'clients', 'trainers', 'totalClients', 'totalMetricsToday', 'avgHeartRate', 'avgSteps'
        ), [
            'type_menu' => 'health-metrics'
        ]);
    }

    /**
     * Display detailed metrics for a specific client.
     */
    public function show(User $client, Request $request)
    {
        if ($client->user_role !== 'client') {
            abort(404);
        }

        // Date range filter (default to last 30 days)
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $metrics = $client->healthMetrics()
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->groupBy('metric_type');

        // Get latest metrics for quick overview
        $latestMetrics = $client->healthMetrics()
            ->select('metric_type', 'value', 'unit', 'recorded_at')
            ->whereIn('id', function($query) use ($client) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('health_metrics')
                    ->where('user_id', $client->id)
                    ->groupBy('metric_type');
            })
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->keyBy('metric_type');

        return view('health.metrics-detail', compact('client', 'metrics', 'latestMetrics', 'startDate', 'endDate'), [
            'type_menu' => 'health-metrics'
        ]);
    }
}
