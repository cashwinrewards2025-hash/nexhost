<?php

namespace App\Services\PDF;

use App\Models\Report;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PDFGenerationService
{
    /**
     * Generate professional PDF report
     */
    public function generateReportPDF(Report $report): string
    {
        $report->load(['client', 'server', 'invoice', 'metrics', 'charts']);

        // Prepare data for PDF
        $data = $this->prepareReportData($report);

        // Generate PDF
        $pdf = Pdf::loadView('reports.pdf.professional-report', $data);

        // Configure PDF security
        $this->configurePDFSecurity($pdf, $report);

        // Store PDF
        $filename = "reports/{$report->report_number}.pdf";
        $pdfPath = $this->storePDF($pdf, $filename);

        // Calculate hash
        $pdfHash = $this->calculatePDFHash($pdfPath);

        // Update report
        $report->update([
            'pdf_path' => $pdfPath,
            'pdf_hash' => $pdfHash,
            'status' => 'generated',
        ]);

        return $pdfPath;
    }

    /**
     * Prepare report data for PDF template
     */
    protected function prepareReportData(Report $report): array
    {
        $client = $report->client;
        $server = $report->server;
        $invoice = $report->invoice;

        return [
            'report' => $report,
            'client' => $client,
            'server' => $server,
            'invoice' => $invoice,
            'generatedDate' => now()->format('d M Y, h:i A'),
            'verificationUrl' => route('verify.report', ['token' => $report->verification_token]),
            'qrCode' => $this->generateQRCode($report),
            'brandName' => config('app.name', 'NexHost'),
            'tagline' => 'Automated Server Monitoring & Billing',
            'secondaryBranding' => 'Built with BuildWithNexClass',
            'companyName' => config('nexhost.company.name', 'NexHost'),
            'companyLogo' => config('nexhost.company.logo_path'),
            'companyEmail' => config('nexhost.company.email'),
            'companyPhone' => config('nexhost.company.phone'),
            'companyAddress' => config('nexhost.company.address'),
        ];
    }

    /**
     * Configure PDF security settings
     */
    protected function configurePDFSecurity($pdf, Report $report): void
    {
        $ownerPassword = config('nexhost.pdf.owner_password', 'nexhost_secure');
        $userPassword = config('nexhost.pdf.user_password', '');
        $encryptionLevel = config('nexhost.pdf.encryption_level', 256);

        // Note: DomPDF has limited PDF security features
        // For production, consider using FPDI or other libraries
        // This is a placeholder for security configuration
    }

    /**
     * Store PDF file
     */
    protected function storePDF($pdf, string $filename): string
    {
        $pdfPath = "/storage/app/private/reports/{$filename}";
        Storage::disk('local')->put("private/reports/{$filename}", $pdf->output());
        return $pdfPath;
    }

    /**
     * Calculate PDF SHA-256 hash
     */
    public function calculatePDFHash(string $pdfPath): string
    {
        $fullPath = storage_path('app' . $pdfPath);
        if (file_exists($fullPath)) {
            return hash_file('sha256', $fullPath);
        }
        return '';
    }

    /**
     * Generate QR code for verification
     */
    protected function generateQRCode(Report $report): string
    {
        $verificationUrl = route('verify.report', ['token' => $report->verification_token]);
        $qrPath = "reports/qr/{$report->report_number}.png";
        
        $qrCode = QrCode::format('png')
            ->size(200)
            ->errorCorrection('H')
            ->generate($verificationUrl);

        Storage::disk('local')->put("public/{$qrPath}", $qrCode);

        return asset("storage/{$qrPath}");
    }

    /**
     * Verify PDF integrity
     */
    public function verifyPDFIntegrity(Report $report): bool
    {
        if (!$report->pdf_path || !$report->pdf_hash) {
            return false;
        }

        $fullPath = storage_path('app' . $report->pdf_path);
        if (!file_exists($fullPath)) {
            return false;
        }

        $currentHash = hash_file('sha256', $fullPath);
        return $currentHash === $report->pdf_hash;
    }

    /**
     * Download secure PDF
     */
    public function downloadPDF(Report $report): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!$this->verifyPDFIntegrity($report)) {
            throw new \Exception('PDF integrity verification failed.');
        }

        $fullPath = storage_path('app' . $report->pdf_path);
        return response()->download($fullPath, "{$report->report_number}.pdf");
    }
}
