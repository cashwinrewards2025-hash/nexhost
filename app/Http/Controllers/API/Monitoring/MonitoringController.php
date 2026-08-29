<?php

namespace App\Http\Controllers\API\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\ServerMetric;
use App\Services\Monitoring\MonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    protected MonitoringService $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Get current metrics for a server
     */
    public function getMetrics(Request $request, Server $server): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'nullable|array',
            'period.start' => 'required_with:period|date',
            'period.end' => 'required_with:period|date|after:period.start',
        ]);

        $metrics = $this->monitoringService->getMetrics($server, $validated);

        return response()->json($metrics);
    }

    /**
     * Record metric from monitoring agent
     */
    public function recordMetric(Request $request, Server $server): JsonResponse
    {
        $validated = $request->validate([
            'cpu_percentage' => 'nullable|numeric|min:0|max:100',
            'memory_percentage' => 'nullable|numeric|min:0|max:100',
            'disk_percentage' => 'nullable|numeric|min:0|max:100',
            'network_in_bytes' => 'nullable|integer',
            'network_out_bytes' => 'nullable|integer',
            'api_response_time_ms' => 'nullable|integer',
            'error_rate_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $metric = $this->monitoringService->recordMetric($server->id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Metric recorded successfully',
            'metric' => $metric,
        ], 201);
    }

    /**
     * Get server health score
     */
    public function getHealthScore(Request $request, Server $server): JsonResponse
    {
        $validated = $request->validate([
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after:period_start',
        ]);

        $periodStart = $validated['period_start'] 
            ? Carbon::parse($validated['period_start'])
            : now()->subDays(30);
        
        $periodEnd = $validated['period_end']
            ? Carbon::parse($validated['period_end'])
            : now();

        $healthScore = $this->monitoringService->calculateHealthScore($server, $periodStart, $periodEnd);

        return response()->json([
            'server_id' => $server->id,
            'health_score' => $healthScore,
            'health_status' => $healthScore >= 90 ? 'excellent' : ($healthScore >= 75 ? 'good' : ($healthScore >= 60 ? 'warning' : 'critical')),
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
        ]);
    }

    /**
     * Get CPU metrics
     */
    public function getCPUMetrics(Server $server): JsonResponse
    {
        $metrics = ServerMetric::where('server_id', $server->id)
            ->whereNotNull('cpu_percentage')
            ->orderBy('collected_at', 'desc')
            ->limit(288)
            ->get();

        if ($metrics->isEmpty()) {
            return response()->json(['message' => 'No CPU metrics found'], 404);
        }

        return response()->json([
            'server_id' => $server->id,
            'current' => $metrics->first()->cpu_percentage,
            'average' => round($metrics->avg('cpu_percentage'), 2),
            'peak' => $metrics->max('cpu_percentage'),
            'minimum' => $metrics->min('cpu_percentage'),
            'samples' => $metrics->count(),
            'data_points' => $metrics->map(fn($m) => [
                'timestamp' => $m->collected_at->toIso8601String(),
                'value' => $m->cpu_percentage,
            ])->all(),
        ]);
    }

    /**
     * Get memory metrics
     */
    public function getMemoryMetrics(Server $server): JsonResponse
    {
        $metrics = ServerMetric::where('server_id', $server->id)
            ->whereNotNull('memory_percentage')
            ->orderBy('collected_at', 'desc')
            ->limit(288)
            ->get();

        if ($metrics->isEmpty()) {
            return response()->json(['message' => 'No memory metrics found'], 404);
        }

        return response()->json([
            'server_id' => $server->id,
            'current' => $metrics->first()->memory_percentage,
            'average' => round($metrics->avg('memory_percentage'), 2),
            'peak' => $metrics->max('memory_percentage'),
            'minimum' => $metrics->min('memory_percentage'),
            'samples' => $metrics->count(),
            'data_points' => $metrics->map(fn($m) => [
                'timestamp' => $m->collected_at->toIso8601String(),
                'value' => $m->memory_percentage,
            ])->all(),
        ]);
    }

    /**
     * Get disk metrics
     */
    public function getDiskMetrics(Server $server): JsonResponse
    {
        $metric = ServerMetric::where('server_id', $server->id)
            ->whereNotNull('disk_percentage')
            ->orderBy('collected_at', 'desc')
            ->first();

        if (!$metric) {
            return response()->json(['message' => 'No disk metrics found'], 404);
        }

        return response()->json([
            'server_id' => $server->id,
            'usage_percentage' => $metric->disk_percentage,
            'used_gb' => $metric->disk_used_gb,
            'total_gb' => $metric->disk_total_gb,
            'free_gb' => $metric->disk_total_gb ? $metric->disk_total_gb - $metric->disk_used_gb : null,
            'last_updated' => $metric->collected_at->toIso8601String(),
        ]);
    }
}
