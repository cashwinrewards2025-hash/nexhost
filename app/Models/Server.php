<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Server extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'client_id',
        'name',
        'ip_address',
        'hostname',
        'operating_system',
        'server_type',
        'cpu_cores',
        'memory_gb',
        'storage_gb',
        'storage_type',
        'bandwidth_gb',
        'environment',
        'status',
        'monitoring_enabled',
        'monitoring_source_id',
        'monitoring_status',
        'last_check_at',
        'last_check_ip',
        'notes',
        'metadata',
        'is_demo',
    ];

    protected $casts = [
        'cpu_cores' => 'integer',
        'memory_gb' => 'integer',
        'storage_gb' => 'integer',
        'bandwidth_gb' => 'integer',
        'monitoring_enabled' => 'boolean',
        'is_demo' => 'boolean',
        'last_check_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function networkInfo()
    {
        return $this->hasOne(ServerNetworkInfo::class);
    }

    public function metrics()
    {
        return $this->hasMany(ServerMetric::class);
    }

    public function monitoringSource()
    {
        return $this->belongsTo(MonitoringSource::class);
    }

    public function monitoringPeriods()
    {
        return $this->hasMany(MonitoringPeriod::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function serviceStatuses()
    {
        return $this->hasMany(ServiceStatus::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
