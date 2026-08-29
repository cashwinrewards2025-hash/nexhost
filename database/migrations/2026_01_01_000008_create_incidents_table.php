<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->string('incident_type'); // api_latency, cpu_spike, memory_spike, disk_full, downtime, ssl_expiry, backup_failure, etc.
            $table->string('severity')->default('medium'); // critical, high, medium, low
            $table->text('description');
            $table->timestamp('started_at');
            $table->timestamp('resolved_at')->nullable();
            $table->string('status')->default('open'); // open, resolved, acknowledged
            $table->integer('duration_minutes')->nullable();
            $table->json('metrics')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            $table->index(['server_id', 'status']);
            $table->index(['server_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
