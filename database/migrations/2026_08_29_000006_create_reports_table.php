<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices');
            $table->string('report_number')->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status')->default('draft'); // draft, generated, sent, viewed
            $table->integer('version')->default(1);
            $table->string('verification_token')->unique();
            $table->string('pdf_path')->nullable();
            $table->string('pdf_hash')->nullable();
            $table->decimal('cpu_average', 5, 2)->nullable();
            $table->decimal('cpu_peak', 5, 2)->nullable();
            $table->decimal('memory_average', 5, 2)->nullable();
            $table->decimal('memory_peak', 5, 2)->nullable();
            $table->decimal('disk_usage', 5, 2)->nullable();
            $table->decimal('uptime_percentage', 5, 2)->nullable();
            $table->integer('api_response_time_ms')->nullable();
            $table->decimal('load_average', 5, 2)->nullable();
            $table->integer('health_score')->nullable();
            $table->string('health_status')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
