# NexHost - Automated Server Monitoring & Billing Platform

**Built with BuildWithNexClass** | Enterprise-Grade Infrastructure Management Solution

## Overview

NexHost is a comprehensive Laravel-based platform for automated server monitoring, infrastructure analytics, and integrated billing systems. Designed for hosting providers, MSPs, and enterprises managing multiple servers and clients.

### Key Features

✅ **Real-time Server Monitoring**
- CPU, Memory, Disk, and Network metrics
- API response time tracking
- Error rate monitoring
- Health score calculation (0-100)
- Historical data analysis

✅ **Automated Billing System**
- Professional invoice generation
- Multiple service tracking
- Tax calculation (GST support)
- Payment recording and tracking
- Late payment notifications

✅ **Professional Reporting**
- Automated monthly reports
- PDF generation with QR codes
- Document verification and integrity checks
- Performance metrics visualization
- Health status analysis

✅ **Email Integration**
- Automated report delivery
- Invoice distribution
- Payment reminders
- Customizable email templates

✅ **Security & Compliance**
- PDF hash verification
- Document tamper detection
- Secure token-based verification
- IP logging for verification
- Audit trails

---

## Installation

### Prerequisites

- PHP 8.2+
- Laravel 11.x
- MySQL 8.0+
- Composer
- Node.js 18+ (for frontend assets)

### Setup Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/cashwinrewards2025-hash/nexhost.git
   cd nexhost
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build Assets**
   ```bash
   npm run build
   ```

6. **Start Development Server**
   ```bash
   php artisan serve
   ```

Access the application at `http://localhost:8000`

---

## Architecture

### Directory Structure

```
nexhost/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── API/
│   │           ├── Monitoring/
│   │           ├── Billing/
│   │           ├── Reports/
│   │           └── Core/
│   ├── Models/
│   │   ├── Client.php
│   │   ├── Server.php
│   │   ├── Invoice.php
│   │   ├── Report.php
│   │   └── ...
│   └── Services/
│       ├── Monitoring/
│       ├── Billing/
│       ├── Reports/
│       ├── PDF/
│       └── Email/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/
│   └── api.php
├── config/
│   ├── nexhost.php
│   ├── ip.php
│   └── mail.php
└── ...
```

### Core Services

#### 1. **Monitoring Service**
- Real-time metric collection
- Health score calculation
- Historical analysis
- Alert generation

#### 2. **Billing Service**
- Invoice generation
- Payment tracking
- Tax calculation
- Financial reporting

#### 3. **Report Service**
- Automated report generation
- Data aggregation
- Chart generation
- PDF export

#### 4. **PDF Service**
- Professional PDF generation
- QR code generation
- Document verification
- Hash-based integrity checking

#### 5. **Email Service**
- Template-based emails
- Batch sending
- Delivery tracking
- Error handling

#### 6. **IP Resolution Service**
- Geolocation lookup
- ASN detection
- DNS resolution
- Provider identification

---

## API Endpoints

### Authentication

```http
POST /api/auth/login
POST /api/auth/register
```

### Monitoring

```http
GET    /api/monitoring/servers/{server}/metrics
POST   /api/monitoring/servers/{server}/metrics
GET    /api/monitoring/servers/{server}/health-score
GET    /api/monitoring/servers/{server}/cpu
GET    /api/monitoring/servers/{server}/memory
GET    /api/monitoring/servers/{server}/disk
```

### Billing

```http
POST   /api/billing/clients/{client}/invoices
GET    /api/billing/invoices/{invoice}
GET    /api/billing/clients/{client}/invoices
POST   /api/billing/invoices/{invoice}/payments
GET    /api/billing/clients/{client}/payment-due
```

### Reports

```http
POST   /api/reports/servers/{server}/generate
GET    /api/reports/reports/{report}
GET    /api/reports/servers/{server}/reports
GET    /api/reports/reports/{report}/pdf
POST   /api/reports/verify
```

### Servers

```http
GET    /api/servers/clients/{client}/servers
GET    /api/servers/{server}
POST   /api/servers/clients/{client}/servers
PUT    /api/servers/{server}
DELETE /api/servers/{server}
```

---

## Database Schema

### Core Tables

- **clients** - Client information and totals
- **servers** - Server definitions and metadata
- **server_metrics** - Time-series metric data
- **invoices** - Invoice records
- **invoice_items** - Individual invoice line items
- **reports** - Generated reports
- **report_metrics** - Report-specific metrics
- **report_charts** - Report visualizations
- **pdf_verifications** - PDF integrity tracking
- **email_logs** - Email delivery tracking
- **server_network_infos** - Geolocation and network data

---

## Configuration

### Environment Variables

```env
# Application
APP_NAME=NexHost
APP_ENV=production
APP_KEY=
APP_DEBUG=false

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexhost
DB_USERNAME=root
DB_PASSWORD=

# Mail
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_FROM_ADDRESS=noreply@nexhost.com

# Monitoring
MONITORING_ENABLED=true
MONITORING_HTTP_ENABLED=false

# Features
BILLING_ENABLED=true
REPORTING_ENABLED=true
PDF_GENERATION_ENABLED=true
EMAIL_NOTIFICATIONS_ENABLED=true

# IP Services
IPSTACK_API_KEY=your_key_here
IPINFO_API_KEY=your_key_here
```

---

## Development

### Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage
```

### Database Seeding

```bash
# Seed the database
php artisan db:seed

# Seed specific seeder
php artisan db:seed --class=ClientSeeder
```

### Code Quality

```bash
# Run static analysis
php artisan pint

# Run linter
php artisan phpstan
```

---

## Deployment

### Production Checklist

- [ ] Set `APP_DEBUG=false`
- [ ] Configure database backups
- [ ] Set up email service (Mailgun, SendGrid, etc.)
- [ ] Configure IP service API keys
- [ ] Set PDF security passwords
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall rules
- [ ] Set up monitoring/alerting
- [ ] Configure log rotation
- [ ] Set up automated backups

### Docker Deployment

```bash
# Build Docker image
docker build -t nexhost .

# Run container
docker run -d -p 8000:8000 nexhost
```

---

## Monitoring

### Health Check Metrics

The platform calculates health scores based on:
- **Uptime** (25%) - Server availability
- **CPU** (10%) - Processor usage
- **Memory** (10%) - RAM utilization
- **Disk** (10%) - Storage usage
- **Response Time** (15%) - API latency
- **Error Rate** (10%) - Application errors
- **SSL** (5%) - Certificate validity
- **Backup** (5%) - Backup status
- **Database** (5%) - Database health
- **Network** (5%) - Network connectivity

---

## Support & Contribution

### Getting Help

- 📖 [Documentation](./docs)
- 🐛 [Report Issues](https://github.com/cashwinrewards2025-hash/nexhost/issues)
- 💬 [Discussions](https://github.com/cashwinrewards2025-hash/nexhost/discussions)

### Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

---

## License

This project is licensed under the MIT License - see [LICENSE](LICENSE) file for details.

---

## Acknowledgments

- **Built with BuildWithNexClass** - Advanced platform development framework
- **Laravel Framework** - Elegant PHP web framework
- **Community Contributors** - Feedback and improvements

---

## Contact

- **Email**: support@nexhost.com
- **Website**: https://nexhost.com
- **GitHub**: https://github.com/cashwinrewards2025-hash/nexhost

---

**Last Updated**: August 29, 2026
**Version**: 1.0.0
