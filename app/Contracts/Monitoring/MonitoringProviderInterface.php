<?php

namespace App\Contracts\Monitoring;

interface MonitoringProviderInterface
{
    /**
     * Get the provider name
     */
    public function getName(): string;

    /**
     * Get the provider type
     */
    public function getType(): string;

    /**
     * Check if the provider is available
     */
    public function isAvailable(): bool;

    /**
     * Authenticate with the monitoring source
     */
    public function authenticate(): bool;

    /**
     * Retrieve metrics for a server
     */
    public function getMetrics(string $serverId, array $options = []): array;

    /**
     * Get uptime status
     */
    public function getUptime(string $serverId): ?float;

    /**
     * Get CPU metrics
     */
    public function getCpuMetrics(string $serverId): ?array;

    /**
     * Get memory metrics
     */
    public function getMemoryMetrics(string $serverId): ?array;

    /**
     * Get disk metrics
     */
    public function getDiskMetrics(string $serverId): ?array;

    /**
     * Get network metrics
     */
    public function getNetworkMetrics(string $serverId): ?array;

    /**
     * Get historical data
     */
    public function getHistoricalData(string $serverId, \DateTime $start, \DateTime $end): array;

    /**
     * Verify server connection
     */
    public function verifyConnection(string $serverId): bool;
}
