# NexHost Development Guide

## Project Structure

```
nexhost/
├── app/
│   ├── Http/Controllers/          # API Controllers
│   ├── Models/                    # Eloquent Models
│   ├── Services/                  # Business Logic
│   ├── Middleware/                # Middleware
│   ├── Exceptions/                # Custom Exceptions
│   ├── Jobs/                      # Queue Jobs
│   └── Events/                    # Events
├── config/                        # Configuration Files
├── database/
│   ├── migrations/                # Database Migrations
│   ├── seeders/                   # Database Seeders
│   └── factories/                 # Model Factories
├── routes/
│   ├── api.php                    # API Routes
│   ├── web.php                    # Web Routes
│   └── channels.php               # Broadcasting Channels
├── tests/                         # Test Files
├── bootstrap/                     # Bootstrap Files
├── public/                        # Public Files
├── resources/                     # Views & Assets
├── storage/                       # Storage
├── vendor/                        # Dependencies
└── .env                           # Environment Configuration
```

## Key Classes & Services

### Models

- **Client** - Client entity with relationships
- **Server** - Server definitions
- **ServerMetric** - Time-series metrics
- **Invoice** - Invoice records
- **InvoiceItem** - Line items
- **Report** - Generated reports
- **PdfVerification** - PDF integrity tracking
- **EmailLog** - Email tracking
- **ServerNetworkInfo** - Geolocation data
- **ReportChart** - Chart visualizations
- **ReportMetric** - Report metrics

### Services

#### Monitoring Service (`app/Services/Monitoring/MonitoringService.php`)

```php
// Get current metrics
$metrics = $monitoringService->getMetrics($server);

// Record metric
$metric = $monitoringService->recordMetric($serverId, $data);

// Calculate health score
$score = $monitoringService->calculateHealthScore($server, $start, $end);
```

#### Billing Service (`app/Services/Billing/BillingService.php`)

```php
// Generate invoice
$invoice = $billingService->generateInvoice($client, $services, $options);

// Record payment
$billingService->recordPayment($invoice, $paymentData);

// Get payment due
$due = $billingService->getPaymentDue($client);
```

#### Report Service (`app/Services/Reports/ReportGenerationService.php`)

```php
// Generate report
$report = $reportService->generateReport($server, $start, $end, $invoice);

// Calculate metrics
$metrics = $reportService->calculateReportMetrics($report);
```

#### PDF Service (`app/Services/PDF/PDFGenerationService.php`)

```php
// Generate PDF
$path = $pdfService->generateReportPDF($report);

// Verify integrity
$valid = $pdfService->verifyPDFIntegrity($report);

// Calculate hash
$hash = $pdfService->calculatePDFHash($path);
```

#### Email Service (`app/Services/Email/EmailService.php`)

```php
// Send report
$log = $emailService->sendReport($report, $recipients);

// Send invoice
$log = $emailService->sendInvoice($invoice, $recipients);
```

#### IP Resolution Service (`app/Services/IP/IPResolutionService.php`)

```php
// Resolve IP
$info = $ipService->resolveIP($ipAddress);

// Get geolocation
$location = $ipService->getGeolocation($ipAddress);
```

## API Controllers

### MonitoringController

Location: `app/Http/Controllers/API/Monitoring/MonitoringController.php`

- `getMetrics()` - Retrieve metrics for a server
- `recordMetric()` - Record new metric data
- `getHealthScore()` - Calculate health score
- `getCPUMetrics()` - Get CPU-specific metrics
- `getMemoryMetrics()` - Get memory metrics
- `getDiskMetrics()` - Get disk metrics

### BillingController

Location: `app/Http/Controllers/API/Billing/BillingController.php`

- `generateInvoice()` - Create new invoice
- `getInvoice()` - Get invoice details
- `getClientInvoices()` - List client invoices
- `recordPayment()` - Record payment
- `getPaymentDue()` - Calculate payment due

### ReportController

Location: `app/Http/Controllers/API/Reports/ReportController.php`

- `generateReport()` - Generate new report
- `getReport()` - Get report details
- `listReports()` - List server reports
- `downloadPDF()` - Download report PDF
- `verifyReport()` - Verify report authenticity

### ServerController

Location: `app/Http/Controllers/API/Core/ServerController.php`

- `listServers()` - List client servers
- `getServer()` - Get server details
- `createServer()` - Create new server
- `updateServer()` - Update server
- `deleteServer()` - Delete server

## Database Migrations

### Creating Migrations

```bash
php artisan make:migration create_table_name
```

### Running Migrations

```bash
# Run all pending migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Reset database
php artisan migrate:reset

# Refresh database
php artisan migrate:refresh
```

## Testing

### Writing Tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Server;

class MonitoringTest extends TestCase
{
    public function test_can_get_metrics()
    {
        $server = Server::factory()->create();
        
        $response = $this->actingAs($this->user)
            ->getJson("/api/monitoring/servers/{$server->id}/metrics");
        
        $response->assertStatus(200);
    }
}
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=MonitoringTest

# Run with coverage
php artisan test --coverage
```

## Code Standards

### PSR-12 Compliance

```bash
# Check code style
php artisan pint --check

# Fix code style
php artisan pint
```

### Static Analysis

```bash
# Run PHPStan
php artisan phpstan
```

## Environment Variables

### Critical Configuration

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=nexhost
DB_USERNAME=root
DB_PASSWORD=

# Mail
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=

# PDF
PDF_OWNER_PASSWORD=secure_password
PDF_USER_PASSWORD=

# IP Services
IPSTACK_API_KEY=
IPINFO_API_KEY=
```

## Debugging

### Laravel Tinker

```bash
php artisan tinker
```

### Query Logging

```php
DB::listen(function ($query) {
    Log::info($query->sql);
});
```

### Error Handling

Custom exceptions in `app/Exceptions/`:

```php
throw new InvalidMetricException('Invalid metric data');
```

## Performance Optimization

### Database Queries

```php
// Use eager loading
$reports = Report::with('client', 'server', 'metrics')->get();

// Use indexes
Schema::create('server_metrics', function (Blueprint $table) {
    $table->index(['server_id', 'collected_at']);
});
```

### Caching

```php
// Cache metrics
$metrics = Cache::remember('metrics_' . $server->id, 3600, function () use ($server) {
    return $this->getMetrics($server);
});
```

## Deployment

### Production Build

```bash
# Optimize
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build assets
npm run build

# Database migration
php artisan migrate --force
```

### CI/CD Pipeline

```yaml
stages:
  - test
  - deploy

test:
  script:
    - composer install
    - php artisan test
    - php artisan pint --check

deploy:
  script:
    - composer install
    - npm install
    - npm run build
    - php artisan migrate --force
```

---

**Last Updated**: August 29, 2026
