<?php

namespace App\Http\Controllers\API\FitFlow;

use App\Http\Controllers\Controller;
use App\Models\Health\HealthMetric;
use App\Models\Health\HealthBaseline;
use App\Models\Health\HealthAlert;
use App\Models\Health\BiometricConsent;
use App\Models\TrainerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthMetricsApiController extends Controller
{
    /**
     * Sync health metrics from wearable (client pushes batch data)
     * POST /api/fitflow/health/sync
     */
    public function sync(Request $request)
    {
        $request->validate([
            'metrics' => 'required|array|min:1|max:500',
            'metrics.*.metric_type' => 'required|string',
            'metrics.*.value' => 'required|numeric',
            'metrics.*.unit' => 'required|string|max:20',
            'metrics.*.source' => 'required|string',
            'metrics.*.recorded_at' => 'required|date',
            'metrics.*.metadata' => 'nullable|array',
        ]);

        $user = $request->user();
        $inserted = 0;

        DB::beginTransaction();
        try {
            foreach ($request->metrics as $metric) {
                HealthMetric::create([
                    'user_id' => $user->id,
                    'metric_type' => $metric['metric_type'],
                    'value' => $metric['value'],
                    'unit' => $metric['unit'],
                    'source' => $metric['source'],
                    'recorded_at' => $metric['recorded_at'],
                    'metadata' => $metric['metadata'] ?? null,
                ]);
                $inserted++;
            }

            // Recalculate baselines for affected metric types
            $types = collect($request->metrics)->pluck('metric_type')->unique();
            foreach ($types as $type) {
                $this->recalculateBaseline($user->id, $type);
            }

            // Check for alerts
            $this->checkForAlerts($user->id, $types->toArray());

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "{$inserted} metrics synced successfully",
                'count' => $inserted,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Sync failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get client's metrics (trainer or client can access)
     * GET /api/fitflow/health/metrics?client_id=X&type=heart_rate&period=14
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $clientId = $request->input('client_id', $user->id);

        // Authorization: client can see own data, trainer can see their clients
        if ($clientId != $user->id) {
            $hasAccess = TrainerClient::where('trainer_id', $user->id)
                ->where('client_id', $clientId)
                ->where('status', 'active')
                ->exists();

            if (!$hasAccess) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
            }

            // Check biometric consent
            $consent = BiometricConsent::where('client_id', $clientId)
                ->where('trainer_id', $user->id)
                ->where('consented', true)
                ->whereNull('revoked_at')
                ->exists();

            if (!$consent) {
                return response()->json(['status' => false, 'message' => 'Client has not consented to share health data'], 403);
            }
        }

        $period = $request->input('period', 14);
        $type = $request->input('type');

        $query = HealthMetric::where('user_id', $clientId)
            ->where('recorded_at', '>=', now()->subDays($period))
            ->orderBy('recorded_at', 'desc');

        if ($type) {
            $query->where('metric_type', $type);
        }

        $metrics = $query->get();

        // Get baselines
        $baselines = HealthBaseline::where('user_id', $clientId)->get()->keyBy('metric_type');

        return response()->json([
            'status' => true,
            'data' => [
                'metrics' => $metrics,
                'baselines' => $baselines,
                'period_days' => $period,
            ],
        ]);
    }

    /**
     * Get summary/latest for each metric type
     * GET /api/fitflow/health/summary?client_id=X
     */
    public function summary(Request $request)
    {
        $user = $request->user();
        $clientId = $request->input('client_id', $user->id);

        $latest = HealthMetric::where('user_id', $clientId)
            ->select('metric_type', DB::raw('MAX(id) as id'))
            ->groupBy('metric_type')
            ->pluck('id');

        $metrics = HealthMetric::whereIn('id', $latest)->get()->keyBy('metric_type');
        $baselines = HealthBaseline::where('user_id', $clientId)->get()->keyBy('metric_type');

        return response()->json([
            'status' => true,
            'data' => [
                'latest' => $metrics,
                'baselines' => $baselines,
            ],
        ]);
    }

    /**
     * Manage biometric consent
     * POST /api/fitflow/health/consent
     */
    public function updateConsent(Request $request)
    {
        $request->validate([
            'trainer_id' => 'required|exists:users,id',
            'metric_type' => 'required|string',
            'consented' => 'required|boolean',
        ]);

        $user = $request->user();

        $consent = BiometricConsent::updateOrCreate(
            ['client_id' => $user->id, 'trainer_id' => $request->trainer_id, 'metric_type' => $request->metric_type],
            [
                'consented' => $request->consented,
                'consented_at' => $request->consented ? now() : null,
                'revoked_at' => !$request->consented ? now() : null,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => $request->consented ? 'Consent granted' : 'Consent revoked',
            'data' => $consent,
        ]);
    }

    /**
     * Get alerts for trainer
     * GET /api/fitflow/health/alerts
     */
    public function alerts(Request $request)
    {
        $user = $request->user();

        $alerts = HealthAlert::with('client:id,name,profile')
            ->where('trainer_id', $user->id)
            ->orderByRaw("FIELD(severity, 'red', 'yellow', 'green')")
            ->orderBy('acknowledged')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json(['status' => true, 'data' => $alerts]);
    }

    /**
     * Acknowledge alert
     * POST /api/fitflow/health/alerts/{id}/acknowledge
     */
    public function acknowledgeAlert(Request $request, $id)
    {
        $alert = HealthAlert::where('trainer_id', $request->user()->id)->findOrFail($id);
        $alert->update(['acknowledged' => true, 'acknowledged_at' => now()]);

        return response()->json(['status' => true, 'message' => 'Alert acknowledged']);
    }

    // --- Private helpers ---

    private function recalculateBaseline(int $userId, string $metricType): void
    {
        $stats = HealthMetric::where('user_id', $userId)
            ->where('metric_type', $metricType)
            ->where('recorded_at', '>=', now()->subDays(14))
            ->selectRaw('AVG(value) as avg_val, STDDEV(value) as std_val, MIN(value) as min_val, MAX(value) as max_val, COUNT(*) as cnt')
            ->first();

        if ($stats->cnt >= 3) {
            HealthBaseline::updateOrCreate(
                ['user_id' => $userId, 'metric_type' => $metricType],
                [
                    'avg_value' => $stats->avg_val,
                    'std_deviation' => $stats->std_val,
                    'min_value' => $stats->min_val,
                    'max_value' => $stats->max_val,
                    'sample_count' => $stats->cnt,
                    'calculated_at' => now(),
                ]
            );
        }
    }

    private function checkForAlerts(int $userId, array $metricTypes): void
    {
        $baselines = HealthBaseline::where('user_id', $userId)
            ->whereIn('metric_type', $metricTypes)
            ->get()->keyBy('metric_type');

        $trainers = TrainerClient::where('client_id', $userId)->where('status', 'active')->pluck('trainer_id');

        if ($trainers->isEmpty()) return;

        foreach ($metricTypes as $type) {
            if (!isset($baselines[$type]) || $baselines[$type]->sample_count < 7) continue;

            $baseline = $baselines[$type];
            $latest = HealthMetric::where('user_id', $userId)
                ->where('metric_type', $type)
                ->orderBy('recorded_at', 'desc')
                ->first();

            if (!$latest) continue;

            $deviation = abs($latest->value - $baseline->avg_value);
            $stdDev = $baseline->std_deviation ?: 1;

            // Red alert: > 2.5 std deviations
            if ($deviation > $stdDev * 2.5) {
                $direction = $latest->value > $baseline->avg_value ? 'elevated' : 'low';
                foreach ($trainers as $trainerId) {
                    HealthAlert::create([
                        'client_id' => $userId,
                        'trainer_id' => $trainerId,
                        'severity' => 'red',
                        'alert_type' => "{$type}_{$direction}",
                        'title' => ucwords(str_replace('_', ' ', $type)) . ' ' . ucfirst($direction),
                        'message' => "Client's {$type} ({$latest->value} {$latest->unit}) is significantly {$direction} compared to baseline ({$baseline->avg_value} avg).",
                        'data_snapshot' => ['current' => $latest->value, 'baseline_avg' => $baseline->avg_value, 'std_dev' => $stdDev],
                        'recommendation' => $this->getRecommendation($type, $direction),
                    ]);
                }
            }
            // Yellow alert: > 1.5 std deviations
            elseif ($deviation > $stdDev * 1.5) {
                $direction = $latest->value > $baseline->avg_value ? 'elevated' : 'low';
                foreach ($trainers as $trainerId) {
                    HealthAlert::create([
                        'client_id' => $userId,
                        'trainer_id' => $trainerId,
                        'severity' => 'yellow',
                        'alert_type' => "{$type}_{$direction}",
                        'title' => ucwords(str_replace('_', ' ', $type)) . ' Trending ' . ucfirst($direction),
                        'message' => "Client's {$type} ({$latest->value} {$latest->unit}) is trending {$direction} from baseline ({$baseline->avg_value} avg).",
                        'data_snapshot' => ['current' => $latest->value, 'baseline_avg' => $baseline->avg_value],
                        'recommendation' => $this->getRecommendation($type, $direction),
                    ]);
                }
            }
        }
    }

    private function getRecommendation(string $type, string $direction): string
    {
        $recommendations = [
            'resting_heart_rate_elevated' => 'Consider reducing training intensity. Elevated resting HR may indicate overtraining, stress, or illness.',
            'resting_heart_rate_low' => 'Low resting HR is generally positive if the client feels well. Monitor for dizziness or fatigue.',
            'hrv_low' => 'Low HRV suggests poor recovery. Consider a rest day or light active recovery session.',
            'hrv_elevated' => 'Elevated HRV indicates strong recovery. Good time to push intensity.',
            'sleep_duration_low' => 'Sleep deficit detected. Consider reducing workout volume and emphasizing recovery.',
            'sleep_duration_elevated' => 'Oversleeping may indicate fatigue or illness. Check in with client.',
            'active_calories_low' => 'Activity levels declining. Client may need motivation or program adjustment.',
        ];

        return $recommendations["{$type}_{$direction}"] ?? "Monitor {$type} closely and adjust programming as needed.";
    }
}
