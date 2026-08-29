# NexHost - Automated Server Monitoring & Billing Platform

**Built with BuildWithNexClass**

## Overview

NexHost is a professional-grade SaaS platform for hosting infrastructure monitoring and automated billing. It provides real-time server monitoring, comprehensive billing management, and professional report generation for hosting companies and their clients.

## Key Features

### 1. Server Monitoring
- Real-time performance metrics (CPU, RAM, Disk, Network)
- Support for multiple monitoring sources:
  - Server monitoring agents
  - Prometheus integration
  - UptimeRobot integration
  - HTTP API monitoring
  - Manual data import
- Real-time monitoring dashboards
- Historical time-series data and graphs
- Professional status indicators
- Service status tracking
- Incident logging and analysis

### 2. IP Automation & Resolution
- Automatic IP geolocation lookup
- Hostname and reverse DNS resolution
- ASN and network provider identification
- Country, region, city, and timezone detection
- No fabricated or claimed physical datacenter location

### 3. Professional Reporting
- Multi-page professional PDF reports (9-page format)
- Real-time monitoring graphs and analytics
- Infrastructure health scoring (0-100)
- Service performance summaries
- Executive summaries with real metrics only
- Document integrity verification
- QR code verification system

### 4. Billing & Invoicing
- All pricing in Indian Rupees (₹ INR)
- Professional invoice generation
- Support for multiple tax types (GST, CGST, SGST, IGST)
- Flexible billing cycles (Monthly, Quarterly, Half-Yearly, Yearly)
- Recurring billing automation
- Payment tracking and recording
- Detailed service itemization
- Indian number formatting (₹5,000, ₹1,25,000)

### 5. Client Portal
- Responsive client-facing dashboard
- Server monitoring visibility
- Report and invoice downloads
- Payment history
- Report verification
- Support ticket system

### 6. Admin Dashboard
- Professional hosting control panel UI
- Client management
- Server management
- Monitoring configuration
- Report generation wizard (11-step process)
- Billing management
- Global search and filtering
- Tagging system
- Audit logging
- Notifications and alerts

### 7. Automation
- Scheduled report generation
- Automatic invoice creation
- Automated email delivery
- Health score calculation
- Performance analytics
- Data integrity verification

### 8. Security
- Role-Based Access Control (RBAC)
- Multi-level authentication
- CSRF and XSS protection
- SQL injection prevention
- Rate limiting
- Encrypted sensitive data
- Secure PDF storage
- Signed download URLs
- API token authentication for monitoring agents
- Comprehensive audit logging

## System Architecture

### Monitoring Architecture

```
MonitoringProviderInterface
├── ManualMonitoringProvider
├── HTTPMonitoringProvider
├── PrometheusMonitoringProvider (future)
├── UptimeRobotMonitoringProvider (future)
└── ServerAgentProvider (future)
```

### Health Score Calculation

Default weights:
- Uptime: 25%
- CPU: 10%
- RAM: 10%
- Disk: 10%
- Response Time: 15%
- Error Rate: 10%
- SSL: 5%
- Backup: 5%
- Database: 5%
- Network: 5%

Score ranges:
- 90-100: Excellent
- 75-89: Good
- 60-74: Warning
- 0-59: Critical

### Database Schema

**Core Tables:**
- `users` - System users with roles
- `roles` - RBAC roles
- `permissions` - RBAC permissions
- `clients` - Hosting clients
- `client_contacts` - Client contact information
- `servers` - Monitored servers
- `server_network_info` - IP geolocation and network data
- `server_metrics` - Time-series monitoring metrics
- `monitoring_sources` - Monitoring provider configuration
- `monitoring_periods` - Monitoring data collection periods
- `service_statuses` - Service health status
- `incidents` - Recorded incidents

**Billing Tables:**
- `products` - Service products
- `services` - Client services
- `pricing_items` - Service pricing
- `invoices` - Generated invoices
- `invoice_items` - Invoice line items
- `payments` - Payment records

**Report Tables:**
- `reports` - Generated reports
- `report_versions` - Report version history
- `report_metrics` - Report metric snapshots
- `report_charts` - Report chart data
- `pdf_verifications` - PDF integrity verification

**Operations Tables:**
- `scheduled_reports` - Automated report scheduling
- `email_logs` - Email delivery history
- `notifications` - System notifications
- `tags` - Custom tagging system
- `taggables` - Tag relationships
- `audit_logs` - Complete audit trail
- `settings` - System configuration

## Data Integrity Rules

**CRITICAL:** NexHost never fabricates monitoring data.

- No fake CPU, RAM, or disk metrics
- No invented network traffic data
- No fabricated uptime claims
- No false incident records
- No invented security events
- Data must be clearly marked as:
  - REAL MONITORING DATA
  - MANUAL DATA
  - IMPORTED DATA
  - UNAVAILABLE DATA

If data cannot be collected, display:
- "Not available"
- "Not monitored"
- "Data not provided"

## Report Structure (9-Page Professional PDF)

**Page 1:** Cover/Header
- NexHost branding
- Client name
- Billing period
- Report and Invoice IDs
- Server health summary

**Page 2:** Client & Server Information
- Client details and contacts
- Server configuration
- Network information
- Environment details

**Page 3:** Monitoring Summary
- Health score details
- Key metrics (CPU, RAM, Disk, Network, Uptime, API)
- Compact professional tables

**Page 4:** Performance Analytics
- Real time-series graphs
- CPU, RAM, Network usage
- API response time
- Average, peak, minimum values

**Page 5:** Availability & Reliability
- Uptime percentage and downtime
- Incident count and duration
- Availability target status
- Incident table

**Page 6:** Services & System Status
- Service operational status
- SSL certificate status
- Backup status
- Database health
- Professional status indicators

**Page 7:** Service Billing
- Itemized services
- Quantity, rate, and amount
- Subtotal, discounts, tax
- Grand total in INR

**Page 8:** Executive Summary
- Objective summary of real metrics only
- Key observations
- Performance assessment
- Data-driven recommendations only

**Page 9:** Report Verification
- Report and Invoice IDs
- Generated timestamp
- SHA-256 hash
- QR verification code
- Verification URL

## Installation

### Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Redis (for queue processing)
- Node.js 18+ (for frontend build)

### Setup

```bash
# Clone repository
git clone https://github.com/cashwinrewards2025-hash/nexhost.git
cd nexhost

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Build frontend assets
npm run build

# Start services
php artisan serve
php artisan queue:work

# Scheduled jobs
php artisan schedule:work
```

## Configuration

### Admin Settings

Configure via `/admin/settings`:

- Company branding (NexHost)
- Tagline and secondary branding
- Logo and website
- Tax rates (GST, CGST, SGST, IGST)
- Invoice and Report prefixes
- Monitoring thresholds
- Health score weights
- Email configuration
- PDF security settings

### Monitoring Configuration

1. **Server Agent Setup**
   - Token generation
   - HTTPS endpoint
   - Metric submission format
   - Authentication

2. **Prometheus Integration**
   - Endpoint URL
   - Metric query configuration
   - Authentication

3. **UptimeRobot Integration**
   - API key configuration
   - Monitor mapping

## API Documentation

### Monitoring Agent API

**Endpoint:** `POST /api/monitoring/metrics`

```json
{
  "server_id": "uuid",
  "token": "monitoring_token",
  "timestamp": "2026-08-29T11:04:00Z",
  "metrics": {
    "cpu_percentage": 34.5,
    "memory_percentage": 61.2,
    "disk_percentage": 48.7,
    "uptime_seconds": 8640000,
    "network_in_bytes": 1024000,
    "network_out_bytes": 512000,
    "api_response_time_ms": 182,
    "load_average": 0.82,
    "processes_running": 245,
    "disk_io_read_mb": 1024,
    "disk_io_write_mb": 512
  }
}
```

**Response:** 200 OK
```json
{
  "success": true,
  "message": "Metrics recorded",
  "metric_id": "uuid"
}
```

## Workflow: From Client Entry to Report Delivery

1. **Admin Entry**
   - Client IP address input
   - Server name and type
   - Billing period selection
   - Service plan configuration

2. **System Processing**
   - IP geolocation and network resolution
   - Monitoring source connection
   - Metric collection
   - Performance analysis
   - Health score calculation
   - Graph generation

3. **Report Generation**
   - Monitoring snapshot
   - Service summary
   - Billing calculation
   - Professional PDF generation
   - Document integrity hashing
   - QR verification creation

4. **Client Delivery**
   - Professional email with attachment
   - Report storage
   - Invoice storage
   - Billing history recording
   - Verification link generation

## Development

### Code Style

Using Laravel Pint for code formatting:

```bash
./vendor/bin/pint
```

### Testing

```bash
./vendor/bin/pest
```

### Database Migrations

```bash
php artisan make:migration create_servers_table
php artisan migrate
php artisan migrate:rollback
```

## Deployment

See `DEPLOYMENT.md` for:
- Production environment setup
- SSL/TLS configuration
- Database backup strategy
- Queue configuration
- Scheduled jobs
- Monitoring and alerting
- Scaling considerations

## Security

See `SECURITY.md` for:
- Authentication and authorization
- Data encryption
- API security
- PDF security
- Audit logging
- Compliance considerations

## License

MIT License - See LICENSE file for details.

## Support

For questions and support:
- GitHub Issues: https://github.com/cashwinrewards2025-hash/nexhost/issues
- Documentation: https://docs.nexhost.local

---

**NexHost - Professional Infrastructure Monitoring & Billing**

*Built with BuildWithNexClass*
