<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('invoice_number')->unique(); // NXH-INV-2026-000001
            $table->date('invoice_date');
            $table->date('due_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status')->default('draft'); // draft, generated, sent, paid, partially_paid, overdue, cancelled
            
            // Amounts
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            
            // Tax Configuration
            $table->string('tax_type')->default('GST'); // GST, CGST, SGST, IGST
            $table->decimal('tax_rate', 5, 2)->default(0);
            
            // Additional Info
            $table->string('currency')->default('INR');
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->text('payment_terms')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            $table->boolean('is_demo')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('invoice_number');
            $table->index(['client_id', 'status']);
            $table->index(['invoice_date', 'due_date']);
            $table->index('status');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignUuid('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->string('service_name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_rate', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);
            $table->string('billing_cycle')->default('monthly');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
