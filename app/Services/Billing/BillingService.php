<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use App\Models\Client;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Generate invoice for a client
     */
    public function generateInvoice(Client $client, array $services, array $options = []): Invoice
    {
        return DB::transaction(function () use ($client, $services, $options) {
            $invoiceDate = $options['invoice_date'] ?? now();
            $periodStart = $options['period_start'] ?? Carbon::now()->startOfMonth();
            $periodEnd = $options['period_end'] ?? Carbon::now()->endOfMonth();
            $dueDate = $options['due_date'] ?? Carbon::parse($invoiceDate)->addDays(30);

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Calculate amounts
            $subtotal = 0;
            $taxAmount = 0;
            $items = [];

            foreach ($services as $serviceData) {
                $service = Service::findOrFail($serviceData['service_id']);
                $quantity = $serviceData['quantity'] ?? 1;
                $rate = $serviceData['rate'] ?? $service->price;
                $lineTotal = $quantity * $rate;
                $subtotal += $lineTotal;

                $items[] = [
                    'service_id' => $service->id,
                    'service_name' => $service->service_name,
                    'description' => $service->description,
                    'quantity' => $quantity,
                    'unit_rate' => $rate,
                    'line_total' => $lineTotal,
                    'billing_cycle' => $service->billing_cycle,
                ];
            }

            // Apply discount if provided
            $discountAmount = $options['discount_amount'] ?? 0;
            $discountPercentage = $options['discount_percentage'] ?? 0;

            if ($discountPercentage > 0) {
                $discountAmount = ($subtotal * $discountPercentage) / 100;
            }

            $subtotalAfterDiscount = $subtotal - $discountAmount;

            // Calculate tax
            $taxType = $options['tax_type'] ?? config('nexhost.billing.default_tax_type', 'GST');
            $taxRate = $options['tax_rate'] ?? config('nexhost.billing.gst_rate', 18);
            $taxAmount = ($subtotalAfterDiscount * $taxRate) / 100;

            $grandTotal = $subtotalAfterDiscount + $taxAmount;

            // Create invoice
            $invoice = Invoice::create([
                'client_id' => $client->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'status' => 'generated',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $discountPercentage,
                'discount_reason' => $options['discount_reason'] ?? null,
                'tax_amount' => $taxAmount,
                'tax_type' => $taxType,
                'tax_rate' => $taxRate,
                'grand_total' => $grandTotal,
                'balance_due' => $grandTotal,
                'currency' => 'INR',
                'notes' => $options['notes'] ?? null,
                'is_demo' => $client->is_demo,
            ]);

            // Add line items
            foreach ($items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $item['service_id'],
                    'service_name' => $item['service_name'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_rate' => $item['unit_rate'],
                    'line_total' => $item['line_total'],
                    'billing_cycle' => $item['billing_cycle'],
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Generate next invoice number
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = config('nexhost.billing.invoice_prefix', 'NXH-INV');
        $year = now()->year;
        $lastInvoice = Invoice::where('invoice_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -6);
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "{$prefix}-{$year}-{$newNumber}";
    }

    /**
     * Format amount in INR
     */
    public function formatINR(float $amount): string
    {
        $amount = abs($amount);
        $formatted = number_format($amount, 2);

        // Convert to Indian number format
        return '₹' . $this->indianFormat($formatted);
    }

    /**
     * Convert to Indian numbering format
     */
    protected function indianFormat(string $number): string
    {
        $parts = explode('.', $number);
        $intPart = $parts[0];
        $decPart = $parts[1] ?? '00';

        // Reverse to add commas from right
        $reversed = strrev($intPart);
        $formatted = '';

        for ($i = 0; $i < strlen($reversed); $i++) {
            if ($i == 3 || ($i > 3 && ($i - 3) % 2 == 0)) {
                $formatted .= ',';
            }
            $formatted .= $reversed[$i];
        }

        return strrev($formatted) . '.' . $decPart;
    }

    /**
     * Calculate service charges
     */
    public function calculateServiceCharges(Service $service, Carbon $billingDate): array
    {
        return [
            'service_name' => $service->service_name,
            'quantity' => 1,
            'rate' => $service->price,
            'amount' => $service->price,
            'tax_amount' => ($service->price * config('nexhost.billing.gst_rate', 18)) / 100,
            'total' => $service->price + (($service->price * config('nexhost.billing.gst_rate', 18)) / 100),
        ];
    }

    /**
     * Get payment due amount for client
     */
    public function getPaymentDue(Client $client): float
    {
        return $client->invoices()
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->sum('balance_due');
    }

    /**
     * Record payment
     */
    public function recordPayment(Invoice $invoice, array $paymentData): void
    {
        $paidAmount = $paymentData['amount'];
        $newBalance = max(0, $invoice->balance_due - $paidAmount);

        $invoice->update([
            'paid_amount' => $invoice->paid_amount + $paidAmount,
            'balance_due' => $newBalance,
            'status' => $newBalance == 0 ? 'paid' : 'partially_paid',
        ]);

        $invoice->client->update([
            'total_paid' => $invoice->client->total_paid + $paidAmount,
            'total_due' => max(0, $invoice->client->total_due - $paidAmount),
        ]);
    }
}
