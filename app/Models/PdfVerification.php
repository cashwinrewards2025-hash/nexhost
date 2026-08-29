<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PdfVerification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'report_id',
        'pdf_hash',
        'verification_token',
        'qr_code_path',
        'status',
        'verified_at',
        'verification_count',
        'last_verified_ip',
        'last_verified_at',
        'verification_notes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
