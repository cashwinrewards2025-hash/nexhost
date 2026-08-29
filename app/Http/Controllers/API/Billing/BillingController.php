<?php

namespace App\Http\Controllers\API\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Service;
use App\Services\Billing\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Generate invoice
     */
    public function generateInvoice(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'nullable|integer|min:1',
            'services.*.rate' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $invoice = $this->billingService->generateInvoice($client, $validated['services'], $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice generated successfully',
            'invoice' => $invoice->load('items'),
        ], 201);
    }

    /**
     * Get invoice details
     */
    public function getInvoice(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load(['client', 'items', 'payments']));
    }

    /**
     * Get client invoices
     */
    public function getClientInvoices(Client $client): JsonResponse
    {
        $invoices = $client->invoices()->with('items')->paginate(15);

        return response()->json($invoices);
    }

    /**
     * Record payment
     */
    public function recordPayment(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0|max:' . $invoice->balance_due,
            'payment_method' => 'required|in:credit_card,bank_transfer,upi,check',
            'reference_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $this->billingService->recordPayment($invoice, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment recorded successfully',
            'invoice' => $invoice->fresh(),
        ]);
    }

    /**
     * Get payment due
     */
    public function getPaymentDue(Client $client): JsonResponse
    {
        $due = $this->billingService->getPaymentDue($client);

        return response()->json([
            'client_id' => $client->id,
            'total_due' => $due,
            'formatted_due' => $this->billingService->formatINR($due),
        ]);
    }
}
