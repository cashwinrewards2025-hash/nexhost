<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $invoiceCounter = 1001;

        foreach ($clients as $client) {
            for ($month = 0; $month < 3; $month++) {
                $invoiceDate = now()->subMonths($month)->startOfMonth();
                $dueDate = $invoiceDate->copy()->addDays(30);
                $periodStart = $invoiceDate->copy();
                $periodEnd = $invoiceDate->copy()->endOfMonth();

                // Generate random services
                $subtotal = 0;
                $items = [];

                // Base server hosting
                $serverCost = rand(5000, 15000);
                $subtotal += $serverCost;
                $items[] = [
                    'service_name' => 'Server Hosting',
                    'description' => 'Cloud server hosting with monitoring',
                    'quantity' => 1,
                    'unit_rate' => $serverCost,
                    'line_total' => $serverCost,
                    'billing_cycle' => 'monthly',
                ];

                // Add-on services
                if (rand(0, 1)) {
                    $backupCost = 2000;
                    $subtotal += $backupCost;
                    $items[] = [
                        'service_name' => 'Backup Service',
                        'description' => 'Daily automated backups',
                        'quantity' => 1,
                        'unit_rate' => $backupCost,
                        'line_total' => $backupCost,
                        'billing_cycle' => 'monthly',
                    ];
                }

                // Calculate totals
                $discountPercentage = rand(0, 1) ? rand(5, 15) : 0;
                $discountAmount = ($subtotal * $discountPercentage) / 100;
                $taxRate = 18;
                $taxAmount = (($subtotal - $discountAmount) * $taxRate) / 100;
                $grandTotal = $subtotal - $discountAmount + $taxAmount;

                // Determine payment status
                $status = 'generated';
                $paidAmount = 0;

                if ($month === 0) {
                    $status = rand(0, 2) === 0 ? 'paid' : 'partially_paid';
                    $paidAmount = $status === 'paid' ? $grandTotal : round($grandTotal * rand(30, 70) / 100, 2);
                }

                $invoice = Invoice::create([
                    'uuid' => Str::uuid(),
                    'client_id' => $client->id,
                    'invoice_number' => 'NXH-INV-' . str_pad($invoiceCounter++, 6, '0', STR_PAD_LEFT),
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'status' => $status,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'discount_percentage' => $discountPercentage,
                    'tax_amount' => $taxAmount,
                    'tax_type' => 'GST',
                    'tax_rate' => $taxRate,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $paidAmount,
                    'balance_due' => $grandTotal - $paidAmount,
                    'currency' => 'INR',
                    'payment_terms' => 'Payment is due within 30 days of invoice date.',
                ]);

                // Create invoice items
                foreach ($items as $item) {
                    InvoiceItem::create(array_merge($item, [
                        'uuid' => Str::uuid(),
                        'invoice_id' => $invoice->id,
                    ]));
                }
            }
        }
    }
}
