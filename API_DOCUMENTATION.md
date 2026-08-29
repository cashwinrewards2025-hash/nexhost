# NexHost API Documentation

## Base URL

```
https://api.nexhost.com/api
```

All requests must include authentication token in headers:

```
Authorization: Bearer {token}
Content-Type: application/json
```

---

## Authentication

### Login

```http
POST /auth/login
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "status": "success",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com"
  }
}
```

### Register

```http
POST /auth/register
```

**Request Body:**
```json
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

---

## Monitoring API

### Get Server Metrics

```http
GET /monitoring/servers/{server_id}/metrics
```

**Query Parameters:**
- `period[start]` - Start date (YYYY-MM-DD)
- `period[end]` - End date (YYYY-MM-DD)

**Response:**
```json
{
  "status": "success",
  "metrics": {
    "cpu": 45.5,
    "memory": 62.3,
    "disk": 78.9,
    "network_in": 1024000000,
    "network_out": 512000000,
    "api_response_time_ms": 125,
    "error_rate": 0.5
  }
}
```

### Record Metric

```http
POST /monitoring/servers/{server_id}/metrics
```

**Request Body:**
```json
{
  "cpu_percentage": 45.5,
  "memory_percentage": 62.3,
  "disk_percentage": 78.9,
  "network_in_bytes": 1024000000,
  "network_out_bytes": 512000000,
  "api_response_time_ms": 125,
  "error_rate_percentage": 0.5
}
```

### Get Health Score

```http
GET /monitoring/servers/{server_id}/health-score
```

**Query Parameters:**
- `period_start` - Start date (YYYY-MM-DD)
- `period_end` - End date (YYYY-MM-DD)

**Response:**
```json
{
  "server_id": 1,
  "health_score": 87,
  "health_status": "good",
  "period_start": "2026-08-01",
  "period_end": "2026-08-29"
}
```

---

## Billing API

### Generate Invoice

```http
POST /billing/clients/{client_id}/invoices
```

**Request Body:**
```json
{
  "services": [
    {
      "service_id": 1,
      "quantity": 1,
      "rate": 5000
    }
  ],
  "discount_amount": 500,
  "discount_percentage": 10,
  "notes": "Monthly billing for August"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Invoice generated successfully",
  "invoice": {
    "id": 1,
    "invoice_number": "NXH-INV-001001",
    "client_id": 1,
    "subtotal": 5000,
    "tax_amount": 900,
    "grand_total": 5900,
    "status": "generated",
    "created_at": "2026-08-29T07:35:00Z"
  }
}
```

### Record Payment

```http
POST /billing/invoices/{invoice_id}/payments
```

**Request Body:**
```json
{
  "amount": 5900,
  "payment_method": "bank_transfer",
  "reference_id": "TRF123456",
  "notes": "Payment for Invoice NXH-INV-001001"
}
```

### Get Client Payment Due

```http
GET /billing/clients/{client_id}/payment-due
```

**Response:**
```json
{
  "client_id": 1,
  "total_due": 15000,
  "formatted_due": "₹15,000.00"
}
```

---

## Reports API

### Generate Report

```http
POST /reports/servers/{server_id}/generate
```

**Request Body:**
```json
{
  "period_start": "2026-08-01",
  "period_end": "2026-08-31",
  "invoice_id": 1
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Report generated successfully",
  "report": {
    "id": 1,
    "report_number": "NXH-REP-001",
    "server_id": 1,
    "period_start": "2026-08-01",
    "period_end": "2026-08-31",
    "health_score": 87,
    "status": "generated",
    "pdf_path": "/storage/reports/NXH-REP-001.pdf"
  }
}
```

### Verify Report

```http
POST /reports/verify
```

**Request Body:**
```json
{
  "token": "verification_token_here"
}
```

**Response:**
```json
{
  "verified": true,
  "status": "valid",
  "message": "Document integrity verified successfully",
  "report": {
    "report_number": "NXH-REP-001",
    "invoice_number": "NXH-INV-001001",
    "client_name": "Acme Corporation",
    "server_name": "Production Server 1",
    "period_start": "01 Aug 2026",
    "period_end": "31 Aug 2026",
    "generated_date": "29 Aug 2026, 07:35 AM",
    "verification_count": 3
  }
}
```

---

## Servers API

### List Servers

```http
GET /servers/clients/{client_id}/servers
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Production Server 1",
      "ip_address": "192.168.1.100",
      "is_active": true,
      "tags": ["production", "web-server"]
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 25,
    "last_page": 2
  }
}
```

### Create Server

```http
POST /servers/clients/{client_id}/servers
```

**Request Body:**
```json
{
  "name": "New Server",
  "ip_address": "192.168.1.101",
  "description": "Production web server",
  "tags": ["production", "web"]
}
```

---

## Error Responses

### Invalid Request

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required"]
  }
}
```

### Unauthorized

```json
{
  "status": "error",
  "message": "Unauthorized",
  "code": 401
}
```

### Not Found

```json
{
  "status": "error",
  "message": "Resource not found",
  "code": 404
}
```

### Server Error

```json
{
  "status": "error",
  "message": "An error occurred while processing your request",
  "code": 500
}
```

---

## Rate Limiting

API requests are rate limited to:
- **60 requests per minute** for authenticated users
- **10 requests per minute** for unauthenticated endpoints

Rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1693310400
```

---

## Pagination

All list endpoints support pagination:

```http
GET /endpoint?page=1&per_page=15
```

**Response:**
```json
{
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

---

## Changelog

### Version 1.0.0 (August 29, 2026)
- Initial release
- Monitoring API
- Billing API
- Reports API
- Server management
- PDF verification

---
