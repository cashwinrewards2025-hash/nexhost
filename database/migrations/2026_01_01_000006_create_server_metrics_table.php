<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->foreignUuid('monitoring_period_id')->nullable()->constrained('monitoring_periods')->onDelete('set null');
            $table->timestamp('collected_at');
            
            // CPU Metrics
            $table->decimal('cpu_percentage', 5, 2)->nullable();
            
            // Memory Metrics
            $table->decimal('memory_percentage', 5, 2)->nullable();
            $table->bigInteger('memory_used_mb')->nullable();
            $table->bigInteger('memory_total_mb')->nullable();
            
            // Disk Metrics
            $table->decimal('disk_percentage', 5, 2)->nullable();
            $table->bigInteger('disk_used_gb')->nullable();
            $table->bigInteger('disk_total_gb')->nullable();
            
            // Network Metrics
            $table->bigInteger('network_in_bytes')->nullable();
            $table->bigInteger('network_out_bytes')->nullable();
            
            // Performance Metrics
            $table->integer('api_response_time_ms')->nullable();
            $table->integer('load_average')->nullable();
            $table->integer('processes_running')->nullable();
            
            // Disk I/O
            $table->bigInteger('disk_io_read_mb')->nullable();
            $table->bigInteger('disk_io_write_mb')->nullable();
            
            // Uptime
            $table->bigInteger('uptime_seconds')->nullable();
            
            // Error Rate
            $table->decimal('error_rate_percentage', 5, 2)->nullable()->default(0);
            
            // Data source
            $table->string('data_source')->default('manual'); // manual, agent, prometheus, uptimerobot, http
            $table->json('raw_data')->nullable();
            
            $table->timestamps();
            $table->index(['server_id', 'collected_at']);
            $table->index(['monitoring_period_id', 'collected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_metrics');
    }
};
