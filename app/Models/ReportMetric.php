<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportMetric extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'report_id',
        'metric_name',
        'average_value',
        'peak_value',
        'minimum_value',
        'sample_count',
        'time_series',
    ];

    protected $casts = [
        'average_value' => 'decimal:2',
        'peak_value' => 'decimal:2',
        'minimum_value' => 'decimal:2',
        'time_series' => 'array',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
