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
    public function index(Request )
    {
        // Get summary statistics
         = User::where('user_role', 'client')->where('is_active', 1)->count();
         = HealthAlert::where('created_at', '>=', now()->subWeek())->count();
        
        // Average heart rate across all clients with recent data
         = HealthMetric::where('metric_type', 'heart_rate')
            ->where('recorded_at', '>=', now()->subDays(7))
            ->avg('value');
         =  ? round(, 1) : null;

        // Get trainers for filter dropdown
         = User::where('user_role', 'trainer')->where('is_active', 1)->get();

        // Base query for clients with their latest health metrics
         = User::where('user_role', 'client')
            ->where('is_active', 1)
            ->select('users.*')
            ->leftJoin('trainer_clients', 'users.id', '=', 'trainer_clients.client_id')
            ->leftJoin('users as trainers', 'trainer_clients.trainer_id', '=', 'trainers.id');

        // Filter by trainer if specified
        if (->has('trainer_id') && !empty(->trainer_id)) {
            ->where('trainer_clients.trainer_id', ->trainer_id);
        }

         = ->with(['trainers' => function() {
            ->select('users.id', 'users.name');
        }])->get();

        // Get latest metrics for each client
         = [];
        foreach ( as ) {
             = HealthMetric::where('user_id', ->id)
                ->select('metric_type', 'value', 'unit', 'recorded_at', 'source')
                ->whereIn('id', function() use () {
                    ->select(DB::raw('MAX(id)'))
                        ->from('health_metrics')
                        ->where('user_id', ->id)
                        ->groupBy('metric_type');
                })
                ->get()
                ->keyBy('metric_type');

             = ->trainers->first() ? ->trainers->first()->name : 'Unassigned';
            
            [] = [
                'client' => ,
                'trainer_name' => ,
                'heart_rate' => ->get('heart_rate'),
                'spo2' => ->get('spo2'),
                'sleep_duration' => ->get('sleep_duration'),
                'steps' => ->get('steps'),
                'latest_update' => ->max('recorded_at')
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
    public function show(User , Request )
    {
        if (->user_role !== 'client') {
            abort(404);
        }

        // Get latest metrics for overview cards
         = HealthMetric::where('user_id', ->id)
            ->select('metric_type', 'value', 'unit', 'recorded_at', 'source')
            ->whereIn('id', function() use () {
                ->select(DB::raw('MAX(id)'))
                    ->from('health_metrics')
                    ->where('user_id', ->id)
                    ->groupBy('metric_type');
            })
            ->get()
            ->keyBy('metric_type');

        // Get previous metrics for trend calculation
         = [];
        foreach ( as  => ) {
             = HealthMetric::where('user_id', ->id)
                ->where('metric_type', )
                ->where('id', '<', ->id)
                ->orderBy('recorded_at', 'desc')
                ->first();
            
            if () {
                [] = ;
            }
        }

        // Get history table data (last 30 records)
         = HealthMetric::where('user_id', ->id)
            ->orderBy('recorded_at', 'desc')
            ->take(30)
            ->get()
            ->groupBy(function() {
                return ->recorded_at->format('Y-m-d H:i');
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
    public static function isInNormalRange(, )
    {
        if (!) return null;
        
        switch () {
            case 'heart_rate':
                return  >= 60 &&  <= 100;
            case 'spo2':
                return  >= 95 &&  <= 100;
            case 'respiratory_rate':
                return  >= 12 &&  <= 20;
            case 'temperature':
                return  >= 97.0 &&  <= 99.0;
            case 'hrv':
                return  >= 20; // Simplified check
            default:
                return null;
        }
    }

    /**
     * Get color class for metric value
     */
    public static function getMetricColorClass(, )
    {
         = self::isInNormalRange(, );
        
        if ( === null) {
            return 'text-muted'; // Unknown range
        }
        
        return  ? 'text-success' : 'text-danger';
    }
}
