<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // "Server Hosting", "Monitoring", etc.
            $table->text('description')->nullable();
            $table->string('billing_cycle')->default('monthly'); // monthly, quarterly, half_yearly, yearly, one_time
            $table->decimal('price', 15, 2);
            $table->string('currency')->default('INR');
            $table->string('status')->default('active');
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_recurring')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('billing_cycle');
        });

        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignUuid('server_id')->nullable()->constrained('servers')->onDelete('set null');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('restrict');
            $table->string('service_name');
            $table->text('description')->nullable();
            $table->string('billing_cycle')->default('monthly');
            $table->decimal('price', 15, 2);
            $table->string('currency')->default('INR');
            $table->date('start_date');
            $table->date('next_due_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); // active, suspended, cancelled, expired
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('amount_due', 15, 2)->default(0);
            $table->boolean('auto_renew')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['client_id', 'status']);
            $table->index('next_due_date');
        });

        Schema::create('pricing_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('currency')->default('INR');
            $table->boolean('is_taxable')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_items');
        Schema::dropIfExists('services');
        Schema::dropIfExists('products');
    }
};
