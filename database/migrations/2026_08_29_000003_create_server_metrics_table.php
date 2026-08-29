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
        Schema::create('server_metrics', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->decimal('cpu_percentage', 5, 2)->nullable();
            $table->decimal('memory_percentage', 5, 2)->nullable();
            $table->decimal('disk_percentage', 5, 2)->nullable();
            $table->decimal('disk_used_gb', 10, 2)->nullable();
            $table->decimal('disk_total_gb', 10, 2)->nullable();
            $table->bigInteger('network_in_bytes')->nullable();
            $table->bigInteger('network_out_bytes')->nullable();
            $table->integer('api_response_time_ms')->nullable();
            $table->decimal('error_rate_percentage', 5, 2)->nullable();
            $table->decimal('load_average', 5, 2)->nullable();
            $table->string('status')->default('online');
            $table->timestamp('collected_at');
            $table->timestamps();
            $table->index(['server_id', 'collected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_metrics');
    }
};
