<?php

namespace App\Services\Reports;

use App\Models\Report;
use App\Models\Invoice;
use App\Models\Server;
use App\Models\Client;
use App\Models\ServerMetric;
use App\Models\ReportChart;
use App\Models\ReportMetric;
use App\Services\Monitoring\MonitoringService;
use App\Services\Billing\BillingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportGenerationService
{
    protected MonitoringService $monitoringService;
    protected BillingService $billingService;

    public function __construct(
        MonitoringService $monitoringService,
        BillingService $billingService
    ) {
        $this->monitoringService = $monitoringService;
        $this->billingService = $billingService;
    }

    /**
     * Generate comprehensive report
     */
    public function generateReport(Server $server, Carbon $periodStart, Carbon $periodEnd, ?Invoice $invoice = null): Report
    {
        return DB::transaction(function () use ($server, $periodStart, $periodEnd, $invoice) {
            // Create report record
            $reportNumber = $this->generateReportNumber();
            $verificationToken = Str::random(64);

            $report = Report::create([
                'client_id' => $server->client_id,
                'server_id' => $server->id,
                'invoice_id' => $invoice?->id,
                'report_number' => $reportNumber,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'status' => 'draft',
                'version' => 1,
                'verification_token' => $verificationToken,
                'is_demo' => $server->is_demo,
            ]);

            // Collect metrics
            $metrics = ServerMetric::where('server_id', $server->id)
                ->whereBetween('collected_at', [$periodStart, $periodEnd])
                ->orderBy('collected_at')
                ->get();

            if ($metrics->isNotEmpty()) {
                // Store metric snapshots
                $this->storeMetricSnapshots($report, $metrics);

                // Calculate metrics
                $cpuMetrics = $this->calculateMetric($metrics, 'cpu_percentage');
                $memoryMetrics = $this->calculateMetric($metrics, 'memory_percentage');
                $diskMetrics = $metrics->whereNotNull('disk_percentage')->first();
                $uptimePercentage = $this->calculateUptime($metrics);
                $apiResponse = $this->calculateMetric($metrics, 'api_response_time_ms');

                // Update report with calculated metrics
                $report->update([
                    'cpu_average' => $cpuMetrics['average'] ?? null,
                    'cpu_peak' => $cpuMetrics['peak'] ?? null,
                    'memory_average' => $memoryMetrics['average'] ?? null,
                    'memory_peak' => $memoryMetrics['peak'] ?? null,
                    'disk_usage' => $diskMetrics?->disk_percentage,
                    'uptime_percentage' => $uptimePercentage,
                    'api_response_time_ms' => $apiResponse['average'] ?? null,
                    'load_average' => $metrics->avg('load_average'),
                ]);

                // Generate charts
                $this->generateCharts($report, $metrics);
            }

            // Calculate health score
            $healthScore = $this->monitoringService->calculateHealthScore($server, $periodStart, $periodEnd);
            $healthStatus = $this->getHealthStatus($healthScore);

            $report->update([
                'health_score' => $healthScore,
                'health_status' => $healthStatus,
            ]);

            return $report;
        });
    }

    /**
     * Store metric snapshots in report
     */
    protected function storeMetricSnapshots(Report $report, $metrics): void
    {
        $metricNames = ['cpu_percentage', 'memory_percentage', 'disk_percentage', 'api_response_time_ms'];

        foreach ($metricNames as $metricName) {
            $values = $metrics->whereNotNull($metricName)->pluck($metricName);

            if ($values->isNotEmpty()) {
                ReportMetric::create([
                    'report_id' => $report->id,
                    'metric_name' => $metricName,
                    'average_value' => round($values->avg(), 2),
                    'peak_value' => $values->max(),
                    'minimum_value' => $values->min(),
                    'sample_count' => $values->count(),
                    'time_series' => $metrics->pluck($metricName, 'collected_at')->toArray(),
                ]);
            }
        }
    }

    /**
     * Calculate metric statistics
     */
    protected function calculateMetric($metrics, string $field): array
    {
        $values = $metrics->whereNotNull($field)->pluck($field);

        if ($values->isEmpty()) {
            return ['average' => null, 'peak' => null, 'minimum' => null];
        }

        return [
            'average' => round($values->avg(), 2),
            'peak' => $values->max(),
            'minimum' => $values->min(),
        ];
    }

    /**
     * Calculate uptime percentage
     */
    protected function calculateUptime($metrics): float
    {
        $total = $metrics->count();
        $online = $metrics->where('cpu_percentage', '>', 0)->count();

        return $total > 0 ? round(($online / $total) * 100, 2) : 0;
    }

    /**
     * Generate report charts
     */
    protected function generateCharts(Report $report, $metrics): void
    {
        // CPU Usage Chart
        $this->createChart($report, 'line', 'CPU Usage', $metrics, 'cpu_percentage');

        // Memory Usage Chart
        $this->createChart($report, 'line', 'Memory Usage', $metrics, 'memory_percentage');

        // Network Chart
        $networkData = $metrics->whereNotNull('network_in_bytes')
            ->pluck('network_out_bytes', 'collected_at')
            ->toArray();
        
        if (!empty($networkData)) {
            ReportChart::create([
                'report_id' => $report->id,
                'chart_type' => 'line',
                'chart_name' => 'Network Traffic',
                'chart_data' => $networkData,
            ]);
        }
    }

    /**
     * Create individual chart
     */
    protected function createChart(Report $report, string $type, string $name, $metrics, string $field): void
    {
        $chartData = $metrics->whereNotNull($field)
            ->pluck($field, 'collected_at')
            ->toArray();

        if (!empty($chartData)) {
            ReportChart::create([
                'report_id' => $report->id,
                'chart_type' => $type,
                'chart_name' => $name,
                'chart_data' => $chartData,
            ]);
        }
    }

    /**
     * Get health status from score
     */
    protected function getHealthStatus(int $score): string
    {
        if ($score >= 90) {
            return 'excellent';
        } elseif ($score >= 75) {
            return 'good';
        } elseif ($score >= 60) {
            return 'warning';
        } else {
            return 'critical';
        }
    }

    /**
     * Generate report number
     */
    protected function generateReportNumber(): string
    {
        $prefix = config('nexhost.billing.report_prefix', 'NXH-REP');
        $year = now()->year;
        $lastReport = Report::where('report_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('report_number', 'desc')
            ->first();

        if ($lastReport) {
            $lastNumber = (int) substr($lastReport->report_number, -6);
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "{$prefix}-{$year}-{$newNumber}";
    }
}
