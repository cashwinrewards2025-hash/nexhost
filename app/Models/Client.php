<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'billing_address',
        'billing_email',
        'billing_phone',
        'gst_number',
        'pan_number',
        'status',
        'notes',
        'last_invoice_date',
        'total_paid',
        'total_due',
        'is_demo',
    ];

    protected $casts = [
        'last_invoice_date' => 'datetime',
        'total_paid' => 'decimal:2',
        'total_due' => 'decimal:2',
        'is_demo' => 'boolean',
    ];

    public function contacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scheduledReports()
    {
        return $this->hasMany(ScheduledReport::class);
    }
}
