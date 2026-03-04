<?php

namespace App\Http\Controllers\Health;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Health\HealthMetric;
use App\Models\Health\HealthAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthMetricController extends Controller
{
    /**
     * Display the health metrics dashboard.
     */
    public function index(Request $request)
    {
        // Get summary statistics
        $totalClients = User::where('user_role', 'client')->where('is_active', 1)->count();
        $alertsThisWeek = HealthAlert::where('created_at', '>=', now()->subWeek())->count();
        
        // Average heart rate across all clients with recent data
        $avgHeartRateValue = HealthMetric::where('metric_type', 'heart_rate')
            ->where('recorded_at', '>=', now()->subDays(7))
            ->avg('value');
        $avgHeartRate = $avgHeartRateValue ? round($avgHeartRateValue, 1) : null;

        // Get trainers for filter dropdown
        $trainers = User::where('user_role', 'trainer')->where('is_active', 1)->get();

        // Base query for clients with their latest health metrics
        $clientsQuery = User::where('user_role', 'client')
            ->where('is_active', 1)
            ->select('users.*')
            ->leftJoin('trainer_clients', 'users.id', '=', 'trainer_clients.client_id')
            ->leftJoin('users as trainers', 'trainer_clients.trainer_id', '=', 'trainers.id');

        // Filter by trainer if specified
        if ($request->has('trainer_id') && !empty($request->trainer_id)) {
            $clientsQuery->where('trainer_clients.trainer_id', $request->trainer_id);
        }

        $clients = $clientsQuery->with(['trainers' => function($query) {
            $query->select('users.id', 'users.name');
        }])->get();

        // Get latest metrics for each client
        $clientsWithMetrics = [];
        foreach ($clients as $client) {
            $latestMetrics = HealthMetric::where('user_id', $client->id)
                ->select('metric_type', 'value', 'unit', 'recorded_at', 'source')
                ->whereIn('id', function($query) use ($client) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('health_metrics')
                        ->where('user_id', $client->id)
                        ->groupBy('metric_type');
                })
                ->get()
                ->keyBy('metric_type');

            $trainerName = $client->trainers->first() ? $client->trainers->first()->name : 'Unassigned';
            
            $clientsWithMetrics[] = [
                'client' => $client,
                'trainer_name' => $trainerName,
                'heart_rate' => $latestMetrics->get('heart_rate'),
                'spo2' => $latestMetrics->get('spo2'),
                'sleep_duration' => $latestMetrics->get('sleep_duration'),
                'steps' => $latestMetrics->get('steps'),
                'latest_update' => $latestMetrics->max('recorded_at')
            ];
        }

        return view('health.metrics-dashboard', compact(
            'totalClients', 'alertsThisWeek', 'avgHeartRate', 'trainers', 'clientsWithMetrics'
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

        // Get latest metrics for overview cards
        $latestMetrics = HealthMetric::where('user_id', $client->id)
            ->select('metric_type', 'value', 'unit', 'recorded_at', 'source')
            ->whereIn('id', function($query) use ($client) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('health_metrics')
                    ->where('user_id', $client->id)
                    ->groupBy('metric_type');
            })
            ->get()
            ->keyBy('metric_type');

        // Get previous metrics for trend calculation
        $previousMetrics = [];
        foreach ($latestMetrics as $metricType => $latestMetric) {
            $previousMetric = HealthMetric::where('user_id', $client->id)
                ->where('metric_type', $metricType)
                ->where('id', '<', $latestMetric->id)
                ->orderBy('recorded_at', 'desc')
                ->first();
            
            if ($previousMetric) {
                $previousMetrics[$metricType] = $previousMetric;
            }
        }

        // Get history table data (last 30 records)
        $metricsHistory = HealthMetric::where('user_id', $client->id)
            ->orderBy('recorded_at', 'desc')
            ->take(30)
            ->get()
            ->groupBy(function($metric) {
                return $metric->recorded_at->format('Y-m-d H:i');
            });

        return view('health.metrics-detail', compact(
            'client', 'latestMetrics', 'previousMetrics', 'metricsHistory'
        ), [
            'type_menu' => 'health-metrics'
        ]);
    }

    /**
     * Helper function to check if a metric value is in normal range
     */
    public static function isInNormalRange($metricType, $value)
    {
        if (!$value) return null;
        
        switch ($metricType) {
            case 'heart_rate':
                return $value >= 60 && $value <= 100;
            case 'spo2':
                return $value >= 95 && $value <= 100;
            case 'respiratory_rate':
                return $value >= 12 && $value <= 20;
            case 'temperature':
                return $value >= 97.0 && $value <= 99.0;
            case 'hrv':
                return $value >= 20; // Simplified check
            default:
                return null;
        }
    }

    /**
     * Get color class for metric value
     */
    public static function getMetricColorClass($metricType, $value)
    {
        $isNormal = self::isInNormalRange($metricType, $value);
        
        if ($isNormal === null) {
            return 'text-muted'; // Unknown range
        }
        
        return $isNormal ? 'text-success' : 'text-danger';
    }
}
