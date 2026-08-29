<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->string('report_number')->unique(); // NXH-REP-2026-000001
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status')->default('draft'); // draft, generated, sent, verified, archived
            $table->integer('version')->default(1);
            
            // Health Score
            $table->integer('health_score')->nullable();
            $table->string('health_status')->nullable(); // excellent, good, warning, critical
            
            // Metrics Snapshot
            $table->decimal('cpu_average', 5, 2)->nullable();
            $table->decimal('cpu_peak', 5, 2)->nullable();
            $table->decimal('memory_average', 5, 2)->nullable();
            $table->decimal('memory_peak', 5, 2)->nullable();
            $table->decimal('disk_usage', 5, 2)->nullable();
            $table->decimal('uptime_percentage', 5, 2)->nullable();
            $table->integer('api_response_time_ms')->nullable();
            $table->decimal('load_average', 5, 2)->nullable();
            $table->integer('incident_count')->default(0);
            $table->integer('downtime_minutes')->default(0);
            
            // PDF Storage
            $table->string('pdf_path')->nullable();
            $table->string('pdf_hash')->nullable(); // SHA-256
            $table->string('verification_token')->unique()->nullable();
            $table->boolean('pdf_verified')->default(false);
            
            // Metadata
            $table->json('metadata')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('report_number');
            $table->index(['client_id', 'status']);
            $table->index(['server_id', 'period_start']);
            $table->index('verification_token');
        });

        Schema::create('report_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->onDelete('cascade');
            $table->integer('version_number');
            $table->string('pdf_path');
            $table->string('pdf_hash');
            $table->string('reason')->nullable();
            $table->json('changes')->nullable();
            $table->timestamps();
            $table->unique(['report_id', 'version_number']);
        });

        Schema::create('report_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->onDelete('cascade');
            $table->string('metric_name');
            $table->decimal('average_value', 10, 2)->nullable();
            $table->decimal('peak_value', 10, 2)->nullable();
            $table->decimal('minimum_value', 10, 2)->nullable();
            $table->integer('sample_count')->default(0);
            $table->json('time_series')->nullable();
            $table->timestamps();
            $table->index(['report_id', 'metric_name']);
        });

        Schema::create('report_charts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->onDelete('cascade');
            $table->string('chart_type'); // line, bar, area, pie
            $table->string('chart_name'); // CPU Usage, Memory Usage, etc.
            $table->json('chart_data');
            $table->json('chart_options')->nullable();
            $table->string('svg_path')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->index(['report_id', 'chart_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_charts');
        Schema::dropIfExists('report_metrics');
        Schema::dropIfExists('report_versions');
        Schema::dropIfExists('reports');
    }
};
