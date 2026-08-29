<?php

namespace App\Services\Email;

use App\Models\Report;
use App\Models\Invoice;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Send report to client
     */
    public function sendReport(Report $report, array $recipients = []): EmailLog
    {
        if (empty($recipients)) {
            $recipients = [
                $report->client->billing_email ?? $report->client->email,
            ];
        }

        $subject = "NexHost Infrastructure & Billing Statement - {$report->client->name} - {$report->period_start->format('M Y')}";
        $body = $this->buildReportEmailBody($report);

        $emailLog = EmailLog::create([
            'client_id' => $report->client_id,
            'report_id' => $report->id,
            'recipient_email' => $recipients[0],
            'cc' => implode(',', array_slice($recipients, 1)),
            'subject' => $subject,
            'body' => $body,
            'email_type' => 'report',
            'status' => 'queued',
        ]);

        // Queue email job
        // SendReportEmailJob::dispatch($emailLog);

        return $emailLog;
    }

    /**
     * Build report email body
     */
    protected function buildReportEmailBody(Report $report): string
    {
        return <<<EOT
<html>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto;">
        <h2>NexHost Infrastructure & Billing Statement</h2>
        
        <p>Hello {$report->client->name},</p>
        
        <p>Please find attached your NexHost Infrastructure & Billing Statement for the period <strong>{$report->period_start->format('d M Y')} - {$report->period_end->format('d M Y')}</strong>.</p>
        
        <p>The statement includes:</p>
        <ul>
            <li>Server performance metrics</li>
            <li>Infrastructure monitoring data</li>
            <li>System availability and status</li>
            <li>Service summary</li>
            <li>Detailed billing information</li>
            <li>Professional recommendations</li>
        </ul>
        
        <p><strong>Report Details:</strong></p>
        <ul>
            <li>Report ID: {$report->report_number}</li>
            <li>Invoice ID: {$report->invoice?->invoice_number ?? 'N/A'}</li>
            <li>Server: {$report->server->name}</li>
            <li>Health Score: {$report->health_score}/100</li>
        </ul>
        
        <p>You can verify the authenticity of this report using the verification link in the PDF document.</p>
        
        <p>If you have any questions or concerns regarding this statement, please don't hesitate to contact us.</p>
        
        <p>Best regards,<br>
        <strong>NexHost Automated Server Monitoring & Billing</strong><br>
        <em>Built with BuildWithNexClass</em></p>
    </div>
</body>
</html>
EOT;
    }

    /**
     * Send invoice to client
     */
    public function sendInvoice(Invoice $invoice, array $recipients = []): EmailLog
    {
        if (empty($recipients)) {
            $recipients = [
                $invoice->client->billing_email ?? $invoice->client->email,
            ];
        }

        $subject = "NexHost Invoice {$invoice->invoice_number} - {$invoice->period_start->format('M Y')}";
        $body = $this->buildInvoiceEmailBody($invoice);

        $emailLog = EmailLog::create([
            'client_id' => $invoice->client_id,
            'invoice_id' => $invoice->id,
            'recipient_email' => $recipients[0],
            'cc' => implode(',', array_slice($recipients, 1)),
            'subject' => $subject,
            'body' => $body,
            'email_type' => 'invoice',
            'status' => 'queued',
        ]);

        return $emailLog;
    }

    /**
     * Build invoice email body
     */
    protected function buildInvoiceEmailBody(Invoice $invoice): string
    {
        return <<<EOT
<html>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto;">
        <h2>Invoice {$invoice->invoice_number}</h2>
        
        <p>Hello {$invoice->client->name},</p>
        
        <p>Your invoice is now ready. Please find the details below:</p>
        
        <p><strong>Invoice Information:</strong></p>
        <ul>
            <li>Invoice Number: {$invoice->invoice_number}</li>
            <li>Invoice Date: {$invoice->invoice_date->format('d M Y')}</li>
            <li>Due Date: {$invoice->due_date->format('d M Y')}</li>
            <li>Amount Due: ₹{$this->formatINR($invoice->grand_total)}</li>
        </ul>
        
        <p><strong>Payment Terms:</strong></p>
        <p>{$invoice->payment_terms ?? 'Please arrange payment by the due date mentioned above.'}</p>
        
        <p>If you have any questions, please reply to this email or contact us directly.</p>
        
        <p>Best regards,<br>
        <strong>NexHost</strong><br>
        <em>Built with BuildWithNexClass</em></p>
    </div>
</body>
</html>
EOT;
    }

    /**
     * Format amount in INR
     */
    protected function formatINR(float $amount): string
    {
        return number_format($amount, 2);
    }
}
