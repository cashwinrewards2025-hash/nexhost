<?php

namespace App\Services\Monitoring\Providers;

use App\Contracts\Monitoring\MonitoringProviderInterface;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class HTTPMonitoringProvider implements MonitoringProviderInterface
{
    protected string $endpoint;
    protected string $token;
    protected int $timeout = 30;

    public function __construct(string $endpoint, string $token)
    {
        $this->endpoint = $endpoint;
        $this->token = $token;
    }

    public function getName(): string
    {
        return 'HTTP Monitoring API';
    }

    public function getType(): string
    {
        return 'http';
    }

    public function isAvailable(): bool
    {
        return !empty($this->endpoint) && !empty($this->token);
    }

    public function authenticate(): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeader('Authorization', "Bearer {$this->token}")
                ->get("{$this->endpoint}/health");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getMetrics(string $serverId, array $options = []): array
    {
        try {
            $period = $options['period'] ?? null;
            $params = [
                'server_id' => $serverId,
            ];

            if ($period) {
                $params['start'] = Carbon::parse($period['start'])->toIso8601String();
                $params['end'] = Carbon::parse($period['end'])->toIso8601String();
            }

            $response = Http::timeout($this->timeout)
                ->withHeader('Authorization', "Bearer {$this->token}")
                ->get("{$this->endpoint}/metrics", $params);

            if (!$response->successful()) {
                return [
                    'status' => 'error',
                    'message' => 'Failed to retrieve metrics from HTTP API',
                    'metrics' => [],
                ];
            }

            return [
                'status' => 'success',
                'source' => 'http_api',
                'metrics' => $response->json('data', []),
                'metric_count' => count($response->json('data', [])),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'metrics' => [],
            ];
        }
    }

    public function getUptime(string $serverId): ?float
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeader('Authorization', "Bearer {$this->token}")
                ->get("{$this->endpoint}/servers/{$serverId}/uptime");

            if ($response->successful()) {
                return $response->json('uptime_percentage');
            }
        } catch (\Exception $e) {
            //
        }

        return null;
    }

    public function getCpuMetrics(string $serverId): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeader('Authorization', "Bearer {$this->token}")
                ->get("{$this->endpoint}/servers/{$serverId}/cpu");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            //
        }

        return null;
    }

    public function getMemoryMetrics(string $serverId): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeader('Authorization', "Bearer {$this->token}")
                ->get("{$this->endpoint}/servers/{$serverId}/memory");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            //
        }

        return null;
    }

    public function getDiskMetrics(string $serverId): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeader('Authorization', "Bearer {$this->token}")
                ->get("{$this->endpoint}/servers/{$serverId}/disk");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            //
        }

        return null;
    }

    public function getNetworkMetrics(string $serverId): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeader('Authorization', "Bearer {$this->token}")
                ->get("{$this->endpoint}/servers/{$serverId}/network");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            //
        }

        return null;
    }

    public function getHistoricalData(string $serverId, \DateTime $start, \DateTime $end): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeader('Authorization', "Bearer {$this->token}")
                ->get("{$this->endpoint}/servers/{$serverId}/history", [
                    'start' => $start->toIso8601String(),
                    'end' => $end->toIso8601String(),
                ]);

            if ($response->successful()) {
                return $response->json('data', []);
            }
        } catch (\Exception $e) {
            //
        }

        return [];
    }

    public function verifyConnection(string $serverId): bool
    {
        return $this->authenticate();
    }
}
