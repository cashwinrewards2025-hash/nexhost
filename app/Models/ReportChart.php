<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportChart extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'report_id',
        'chart_type',
        'chart_name',
        'chart_data',
        'chart_options',
        'svg_path',
        'image_path',
    ];

    protected $casts = [
        'chart_data' => 'array',
        'chart_options' => 'array',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
