<?php

return [
    'monitoring' => [
        'providers' => [
            'manual' => [
                'class' => 'App\Services\Monitoring\Providers\ManualMonitoringProvider',
                'enabled' => true,
            ],
            'http' => [
                'class' => 'App\Services\Monitoring\Providers\HTTPMonitoringProvider',
                'enabled' => env('MONITORING_HTTP_ENABLED', false),
            ],
        ],
        'default_provider' => 'manual',
    ],
    'health_score' => [
        'uptime_weight' => 25,
        'cpu_weight' => 10,
        'ram_weight' => 10,
        'disk_weight' => 10,
        'response_time_weight' => 15,
        'error_rate_weight' => 10,
        'ssl_weight' => 5,
        'backup_weight' => 5,
        'database_weight' => 5,
        'network_weight' => 5,
    ],
    'billing' => [
        'default_tax_type' => 'GST',
        'gst_rate' => 18,
        'invoice_prefix' => 'NXH-INV',
        'report_prefix' => 'NXH-REP',
        'payment_terms' => 'Payment is due within 30 days of invoice date.',
        'currency' => 'INR',
    ],
    'pdf' => [
        'owner_password' => env('PDF_OWNER_PASSWORD', 'nexhost_secure'),
        'user_password' => env('PDF_USER_PASSWORD', ''),
        'encryption_level' => 256,
    ],
    'company' => [
        'name' => env('COMPANY_NAME', 'NexHost'),
        'logo_path' => env('COMPANY_LOGO_PATH', '/images/logo.png'),
        'email' => env('COMPANY_EMAIL', 'support@nexhost.com'),
        'phone' => env('COMPANY_PHONE', '+91-XXX-XXX-XXXX'),
        'address' => env('COMPANY_ADDRESS', 'NexHost Office, City, Country'),
    ],
    'features' => [
        'monitoring_enabled' => env('MONITORING_ENABLED', true),
        'billing_enabled' => env('BILLING_ENABLED', true),
        'reporting_enabled' => env('REPORTING_ENABLED', true),
        'pdf_generation_enabled' => env('PDF_GENERATION_ENABLED', true),
        'email_notifications_enabled' => env('EMAIL_NOTIFICATIONS_ENABLED', true),
    ],
];
