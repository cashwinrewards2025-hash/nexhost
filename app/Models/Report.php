<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Report extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'client_id',
        'server_id',
        'invoice_id',
        'report_number',
        'period_start',
        'period_end',
        'status',
        'version',
        'health_score',
        'health_status',
        'cpu_average',
        'cpu_peak',
        'memory_average',
        'memory_peak',
        'disk_usage',
        'uptime_percentage',
        'api_response_time_ms',
        'load_average',
        'incident_count',
        'downtime_minutes',
        'pdf_path',
        'pdf_hash',
        'verification_token',
        'pdf_verified',
        'metadata',
        'is_demo',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'health_score' => 'integer',
        'cpu_average' => 'decimal:2',
        'cpu_peak' => 'decimal:2',
        'memory_average' => 'decimal:2',
        'memory_peak' => 'decimal:2',
        'disk_usage' => 'decimal:2',
        'uptime_percentage' => 'decimal:2',
        'api_response_time_ms' => 'integer',
        'load_average' => 'decimal:2',
        'pdf_verified' => 'boolean',
        'is_demo' => 'boolean',
        'metadata' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function metrics()
    {
        return $this->hasMany(ReportMetric::class);
    }

    public function charts()
    {
        return $this->hasMany(ReportChart::class);
    }

    public function versions()
    {
        return $this->hasMany(ReportVersion::class);
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }

    public function verification()
    {
        return $this->hasOne(PdfVerification::class);
    }
}
