<?php

namespace App\Services\Monitoring;

use App\Models\Server;
use App\Models\ServerMetric;
use App\Services\Monitoring\Providers\ManualMonitoringProvider;
use App\Services\Monitoring\Providers\HTTPMonitoringProvider;
use App\Contracts\Monitoring\MonitoringProviderInterface;

class MonitoringService
{
    protected MonitoringProviderInterface $provider;

    /**
     * Initialize monitoring provider based on server configuration
     */
    public function initializeProvider(Server $server): MonitoringProviderInterface
    {
        if ($server->monitoringSource?->type === 'http') {
            $config = json_decode($server->monitoringSource->configuration, true);
            $this->provider = new HTTPMonitoringProvider(
                $config['endpoint'] ?? '',
                $config['token'] ?? ''
            );
        } else {
            $this->provider = new ManualMonitoringProvider();
        }

        return $this->provider;
    }

    /**
     * Get current metrics for a server
     */
    public function getMetrics(Server $server, ?array $options = []): array
    {
        $this->initializeProvider($server);

        return $this->provider->getMetrics($server->id, $options);
    }

    /**
     * Record metric data from monitoring agent
     */
    public function recordMetric(string $serverId, array $metricData): ServerMetric
    {
        $server = Server::findOrFail($serverId);

        $metric = ServerMetric::create([
            'server_id' => $serverId,
            'collected_at' => now(),
            'cpu_percentage' => $metricData['cpu_percentage'] ?? null,
            'memory_percentage' => $metricData['memory_percentage'] ?? null,
            'memory_used_mb' => $metricData['memory_used_mb'] ?? null,
            'memory_total_mb' => $metricData['memory_total_mb'] ?? null,
            'disk_percentage' => $metricData['disk_percentage'] ?? null,
            'disk_used_gb' => $metricData['disk_used_gb'] ?? null,
            'disk_total_gb' => $metricData['disk_total_gb'] ?? null,
            'network_in_bytes' => $metricData['network_in_bytes'] ?? null,
            'network_out_bytes' => $metricData['network_out_bytes'] ?? null,
            'api_response_time_ms' => $metricData['api_response_time_ms'] ?? null,
            'load_average' => $metricData['load_average'] ?? null,
            'processes_running' => $metricData['processes_running'] ?? null,
            'disk_io_read_mb' => $metricData['disk_io_read_mb'] ?? null,
            'disk_io_write_mb' => $metricData['disk_io_write_mb'] ?? null,
            'uptime_seconds' => $metricData['uptime_seconds'] ?? null,
            'error_rate_percentage' => $metricData['error_rate_percentage'] ?? 0,
            'data_source' => $metricData['data_source'] ?? 'agent',
            'raw_data' => $metricData['raw_data'] ?? [],
        ]);

        $server->update([
            'last_check_at' => now(),
            'monitoring_status' => 'active',
        ]);

        return $metric;
    }

    /**
     * Get health score for a server
     */
    public function calculateHealthScore(Server $server, \DateTime $periodStart, \DateTime $periodEnd): int
    {
        $metrics = ServerMetric::where('server_id', $server->id)
            ->whereBetween('collected_at', [$periodStart, $periodEnd])
            ->orderBy('collected_at')
            ->get();

        if ($metrics->isEmpty()) {
            return 0;
        }

        // Get configuration weights
        $weights = [
            'uptime' => (float) config('nexhost.health_score.uptime_weight', 25),
            'cpu' => (float) config('nexhost.health_score.cpu_weight', 10),
            'ram' => (float) config('nexhost.health_score.ram_weight', 10),
            'disk' => (float) config('nexhost.health_score.disk_weight', 10),
            'response_time' => (float) config('nexhost.health_score.response_time_weight', 15),
            'error_rate' => (float) config('nexhost.health_score.error_rate_weight', 10),
            'ssl' => (float) config('nexhost.health_score.ssl_weight', 5),
            'backup' => (float) config('nexhost.health_score.backup_weight', 5),
            'database' => (float) config('nexhost.health_score.database_weight', 5),
            'network' => (float) config('nexhost.health_score.network_weight', 5),
        ];

        $score = 0;

        // Uptime calculation (max 25)
        $uptime = $this->calculateUptime($metrics);
        $uptimeScore = ($uptime / 100) * $weights['uptime'];
        $score += $uptimeScore;

        // CPU calculation (max 10) - lower is better
        $avgCpu = $metrics->avg('cpu_percentage') ?? 0;
        $cpuScore = max(0, 10 - ($avgCpu / 10)) * ($weights['cpu'] / 10);
        $score += $cpuScore;

        // RAM calculation (max 10)
        $avgRam = $metrics->avg('memory_percentage') ?? 0;
        $ramScore = max(0, 10 - ($avgRam / 10)) * ($weights['ram'] / 10);
        $score += $ramScore;

        // Disk calculation (max 10)
        $avgDisk = $metrics->avg('disk_percentage') ?? 0;
        $diskScore = max(0, 10 - ($avgDisk / 10)) * ($weights['disk'] / 10);
        $score += $diskScore;

        // Response time calculation (max 15)
        $avgResponse = $metrics->avg('api_response_time_ms') ?? 0;
        $responseScore = max(0, 15 - ($avgResponse / 100)) * ($weights['response_time'] / 15);
        $score += $responseScore;

        // Error rate calculation (max 10)
        $avgErrors = $metrics->avg('error_rate_percentage') ?? 0;
        $errorScore = max(0, 10 - $avgErrors) * ($weights['error_rate'] / 10);
        $score += $errorScore;

        return min(100, (int) round($score));
    }

    /**
     * Calculate uptime percentage
     */
    protected function calculateUptime($metrics): float
    {
        $total = $metrics->count();
        $online = $metrics->where('cpu_percentage', '>', 0)->count();

        return $total > 0 ? ($online / $total) * 100 : 0;
    }
}
