<?php

namespace App\Services\PDF;

use App\Models\PdfVerification;
use App\Models\Report;
use Illuminate\Support\Str;

class PDFVerificationService
{
    /**
     * Create verification record for PDF
     */
    public function createVerification(Report $report): PdfVerification
    {
        $verification = PdfVerification::create([
            'report_id' => $report->id,
            'pdf_hash' => $report->pdf_hash,
            'verification_token' => $report->verification_token,
            'status' => 'valid',
            'verified_at' => null,
        ]);

        return $verification;
    }

    /**
     * Verify report by token
     */
    public function verifyByToken(string $token): array
    {
        $report = Report::where('verification_token', $token)->first();

        if (!$report) {
            return [
                'verified' => false,
                'status' => 'not_found',
                'message' => 'Report not found.',
            ];
        }

        $verification = PdfVerification::where('verification_token', $token)->first();

        if (!$verification) {
            return [
                'verified' => false,
                'status' => 'verification_missing',
                'message' => 'Verification record not found.',
            ];
        }

        // Check if PDF still exists and matches hash
        $pdfPath = storage_path('app' . $report->pdf_path);
        if (!file_exists($pdfPath)) {
            $verification->update(['status' => 'invalid']);
            return [
                'verified' => false,
                'status' => 'pdf_missing',
                'message' => 'Original PDF file not found.',
            ];
        }

        $currentHash = hash_file('sha256', $pdfPath);
        if ($currentHash !== $report->pdf_hash) {
            $verification->update(['status' => 'modified']);
            return [
                'verified' => false,
                'status' => 'modified',
                'message' => 'Document integrity could not be verified. PDF has been modified.',
            ];
        }

        // Update verification
        $verification->update([
            'verified_at' => now(),
            'verification_count' => $verification->verification_count + 1,
            'last_verified_ip' => request()->ip(),
            'last_verified_at' => now(),
        ]);

        return [
            'verified' => true,
            'status' => 'valid',
            'message' => 'Document integrity verified successfully.',
            'report' => [
                'report_number' => $report->report_number,
                'invoice_number' => $report->invoice?->invoice_number,
                'client_name' => $report->client->name,
                'server_name' => $report->server->name,
                'period_start' => $report->period_start->format('d M Y'),
                'period_end' => $report->period_end->format('d M Y'),
                'generated_date' => $report->created_at->format('d M Y, h:i A'),
                'pdf_hash' => $report->pdf_hash,
                'verification_count' => $verification->verification_count,
            ],
        ];
    }

    /**
     * Generate verification hash
     */
    public function generateVerificationHash(Report $report): string
    {
        $data = json_encode([
            'report_id' => $report->id,
            'report_number' => $report->report_number,
            'period_start' => $report->period_start->toDateString(),
            'period_end' => $report->period_end->toDateString(),
            'pdf_hash' => $report->pdf_hash,
            'created_at' => $report->created_at->toIso8601String(),
        ]);

        return hash('sha256', $data);
    }

    /**
     * Invalidate verification
     */
    public function invalidateVerification(Report $report): void
    {
        PdfVerification::where('report_id', $report->id)
            ->update(['status' => 'invalid']);
    }
}
