<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ServerMetric extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'server_id',
        'monitoring_period_id',
        'collected_at',
        'cpu_percentage',
        'memory_percentage',
        'memory_used_mb',
        'memory_total_mb',
        'disk_percentage',
        'disk_used_gb',
        'disk_total_gb',
        'network_in_bytes',
        'network_out_bytes',
        'api_response_time_ms',
        'load_average',
        'processes_running',
        'disk_io_read_mb',
        'disk_io_write_mb',
        'uptime_seconds',
        'error_rate_percentage',
        'data_source',
        'raw_data',
    ];

    protected $casts = [
        'cpu_percentage' => 'decimal:2',
        'memory_percentage' => 'decimal:2',
        'disk_percentage' => 'decimal:2',
        'error_rate_percentage' => 'decimal:2',
        'collected_at' => 'datetime',
        'raw_data' => 'array',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function monitoringPeriod()
    {
        return $this->belongsTo(MonitoringPeriod::class);
    }
}
