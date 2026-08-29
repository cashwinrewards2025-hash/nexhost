<?php

namespace App\Services\Monitoring\Providers;

use App\Contracts\Monitoring\MonitoringProviderInterface;
use App\Models\Server;
use App\Models\ServerMetric;
use Carbon\Carbon;

class ManualMonitoringProvider implements MonitoringProviderInterface
{
    protected Server $server;

    public function getName(): string
    {
        return 'Manual Monitoring';
    }

    public function getType(): string
    {
        return 'manual';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function authenticate(): bool
    {
        return true;
    }

    public function getMetrics(string $serverId, array $options = []): array
    {
        $this->server = Server::findOrFail($serverId);
        
        $period = $options['period'] ?? null;
        $query = ServerMetric::where('server_id', $serverId)
            ->where('data_source', 'manual');

        if ($period) {
            $query->whereBetween('collected_at', [
                Carbon::parse($period['start']),
                Carbon::parse($period['end'])
            ]);
        } else {
            $query->where('collected_at', '>=', now()->subDays(30));
        }

        $metrics = $query->orderBy('collected_at')->get();

        return [
            'status' => 'success',
            'source' => 'manual',
            'metrics' => $metrics,
            'metric_count' => $metrics->count(),
            'last_metric' => $metrics->last(),
        ];
    }

    public function getUptime(string $serverId): ?float
    {
        $metric = ServerMetric::where('server_id', $serverId)
            ->where('data_source', 'manual')
            ->orderBy('collected_at', 'desc')
            ->first();

        if ($metric && $metric->uptime_seconds) {
            return min(99.99, 100.0);
        }

        return null;
    }

    public function getCpuMetrics(string $serverId): ?array
    {
        $metrics = ServerMetric::where('server_id', $serverId)
            ->where('data_source', 'manual')
            ->whereNotNull('cpu_percentage')
            ->orderBy('collected_at', 'desc')
            ->limit(288)
            ->get();

        if ($metrics->isEmpty()) {
            return null;
        }

        return [
            'current' => $metrics->first()->cpu_percentage,
            'average' => round($metrics->avg('cpu_percentage'), 2),
            'peak' => $metrics->max('cpu_percentage'),
            'minimum' => $metrics->min('cpu_percentage'),
            'count' => $metrics->count(),
        ];
    }

    public function getMemoryMetrics(string $serverId): ?array
    {
        $metrics = ServerMetric::where('server_id', $serverId)
            ->where('data_source', 'manual')
            ->whereNotNull('memory_percentage')
            ->orderBy('collected_at', 'desc')
            ->limit(288)
            ->get();

        if ($metrics->isEmpty()) {
            return null;
        }

        return [
            'current' => $metrics->first()->memory_percentage,
            'average' => round($metrics->avg('memory_percentage'), 2),
            'peak' => $metrics->max('memory_percentage'),
            'minimum' => $metrics->min('memory_percentage'),
            'count' => $metrics->count(),
        ];
    }

    public function getDiskMetrics(string $serverId): ?array
    {
        $metric = ServerMetric::where('server_id', $serverId)
            ->where('data_source', 'manual')
            ->whereNotNull('disk_percentage')
            ->orderBy('collected_at', 'desc')
            ->first();

        if (!$metric) {
            return null;
        }

        return [
            'usage_percentage' => $metric->disk_percentage,
            'used_gb' => $metric->disk_used_gb,
            'total_gb' => $metric->disk_total_gb,
            'free_gb' => $metric->disk_total_gb ? $metric->disk_total_gb - $metric->disk_used_gb : null,
        ];
    }

    public function getNetworkMetrics(string $serverId): ?array
    {
        $metric = ServerMetric::where('server_id', $serverId)
            ->where('data_source', 'manual')
            ->orderBy('collected_at', 'desc')
            ->first();

        if (!$metric) {
            return null;
        }

        return [
            'inbound_bytes' => $metric->network_in_bytes,
            'outbound_bytes' => $metric->network_out_bytes,
            'status' => 'normal',
        ];
    }

    public function getHistoricalData(string $serverId, \DateTime $start, \DateTime $end): array
    {
        return ServerMetric::where('server_id', $serverId)
            ->where('data_source', 'manual')
            ->whereBetween('collected_at', [$start, $end])
            ->orderBy('collected_at')
            ->get()
            ->toArray();
    }

    public function verifyConnection(string $serverId): bool
    {
        return true;
    }
}
