<?php

namespace App\Http\Controllers\API\FitFlow;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TrainerClient;
use App\Models\Schedule;
use App\Models\Health\HealthAlert;
use App\Models\Health\HealthMetric;
use App\Models\Fitness\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FitFlowDashboardApiController extends Controller
{
    /**
     * Client dashboard data
     * GET /api/fitflow/dashboard/client
     */
    public function clientDashboard(Request $request)
    {
        $user = $request->user();

        $trainers = TrainerClient::with('trainer:id,name,profile,specializations')
            ->where('client_id', $user->id)->where('status', 'active')->get();

        $upcomingSessions = Schedule::with('trainer:id,name')
            ->where('client_id', $user->id)
            ->where('start_at', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_at')->limit(5)->get();

        $activePrograms = DB::table('client_programs')
            ->join('training_programs', 'training_programs.id', '=', 'client_programs.training_program_id')
            ->where('client_programs.client_id', $user->id)
            ->where('client_programs.status', 'active')
            ->select('training_programs.name', 'training_programs.program_type', 'client_programs.start_date')
            ->get();

        $latestMetrics = HealthMetric::where('user_id', $user->id)
            ->select('metric_type', DB::raw('MAX(id) as id'))
            ->groupBy('metric_type')->pluck('id');
        $metrics = HealthMetric::whereIn('id', $latestMetrics)->get()->keyBy('metric_type');

        return response()->json([
            'status' => true,
            'data' => [
                'trainers' => $trainers,
                'upcoming_sessions' => $upcomingSessions,
                'active_programs' => $activePrograms,
                'health_snapshot' => $metrics,
            ],
        ]);
    }

    /**
     * Trainer dashboard data
     * GET /api/fitflow/dashboard/trainer
     */
    public function trainerDashboard(Request $request)
    {
        $user = $request->user();

        $clientCount = TrainerClient::where('trainer_id', $user->id)->where('status', 'active')->count();
        $programCount = TrainingProgram::where('trainer_id', $user->id)->where('is_active', true)->count();

        $todaySessions = Schedule::with('client:id,name,profile')
            ->where('trainer_id', $user->id)
            ->whereDate('start_at', today())
            ->orderBy('start_at')->get();

        $alerts = HealthAlert::with('client:id,name,profile')
            ->where('trainer_id', $user->id)
            ->where('acknowledged', false)
            ->orderByRaw("FIELD(severity, 'red', 'yellow', 'green')")
            ->limit(10)->get();

        $recentClients = TrainerClient::with('client:id,name,profile,email')
            ->where('trainer_id', $user->id)
            ->where('status', 'active')
            ->orderBy('subscribed_at', 'desc')
            ->limit(5)->get();

        return response()->json([
            'status' => true,
            'data' => [
                'stats' => ['clients' => $clientCount, 'programs' => $programCount],
                'today_sessions' => $todaySessions,
                'health_alerts' => $alerts,
                'recent_clients' => $recentClients,
                'invite_code' => $user->invite_code,
            ],
        ]);
    }
}
