<?php

namespace App\Http\Controllers\API\Reports;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Server;
use App\Services\Reports\ReportGenerationService;
use App\Services\PDF\PDFGenerationService;
use App\Services\PDF\PDFVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected ReportGenerationService $reportService;
    protected PDFGenerationService $pdfService;
    protected PDFVerificationService $verificationService;

    public function __construct(
        ReportGenerationService $reportService,
        PDFGenerationService $pdfService,
        PDFVerificationService $verificationService
    ) {
        $this->reportService = $reportService;
        $this->pdfService = $pdfService;
        $this->verificationService = $verificationService;
    }

    /**
     * Generate report
     */
    public function generateReport(Request $request, Server $server): JsonResponse
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'invoice_id' => 'nullable|exists:invoices,id',
        ]);

        $invoice = $validated['invoice_id'] 
            ? \App\Models\Invoice::find($validated['invoice_id'])
            : null;

        $report = $this->reportService->generateReport(
            $server,
            Carbon::parse($validated['period_start']),
            Carbon::parse($validated['period_end']),
            $invoice
        );

        // Generate PDF
        $this->pdfService->generateReportPDF($report);

        return response()->json([
            'status' => 'success',
            'message' => 'Report generated successfully',
            'report' => $report->load(['client', 'server', 'metrics', 'charts']),
        ], 201);
    }

    /**
     * Get report details
     */
    public function getReport(Report $report): JsonResponse
    {
        return response()->json($report->load(['client', 'server', 'invoice', 'metrics', 'charts']));
    }

    /**
     * List server reports
     */
    public function listReports(Server $server): JsonResponse
    {
        $reports = $server->reports()->with('client', 'metrics')->paginate(15);

        return response()->json($reports);
    }

    /**
     * Download report PDF
     */
    public function downloadPDF(Report $report)
    {
        if (!$report->pdf_path) {
            return response()->json(['message' => 'PDF not generated'], 404);
        }

        return $this->pdfService->downloadPDF($report);
    }

    /**
     * Verify report
     */
    public function verifyReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $result = $this->verificationService->verifyByToken($validated['token']);

        return response()->json($result, $result['verified'] ? 200 : 400);
    }
}
