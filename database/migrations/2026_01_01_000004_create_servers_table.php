<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('name'); // e.g., CTOC-PROD-01
            $table->string('ip_address');
            $table->string('hostname')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('server_type')->nullable(); // VPS, Dedicated, Shared, etc.
            $table->integer('cpu_cores')->nullable();
            $table->integer('memory_gb')->nullable();
            $table->integer('storage_gb')->nullable();
            $table->string('storage_type')->nullable(); // SSD, HDD, NVMe
            $table->integer('bandwidth_gb')->nullable();
            $table->string('environment')->default('production'); // production, staging, development
            $table->string('status')->default('online'); // online, offline, maintenance, suspended
            $table->boolean('monitoring_enabled')->default(true);
            $table->foreignId('monitoring_source_id')->nullable()->constrained('monitoring_sources')->onDelete('set null');
            $table->string('monitoring_status')->default('not_configured'); // not_configured, active, inactive, error
            $table->timestamp('last_check_at')->nullable();
            $table->string('last_check_ip')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['client_id', 'ip_address']);
            $table->index('status');
            $table->index('monitoring_enabled');
            $table->index('is_demo');
        });

        Schema::create('server_network_info', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->string('ip_address');
            $table->string('hostname')->nullable();
            $table->string('reverse_dns')->nullable();
            $table->string('ptr_record')->nullable();
            $table->string('ipv6_address')->nullable();
            $table->string('network_provider')->nullable();
            $table->string('asn')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('timezone')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('isp')->nullable();
            $table->json('raw_data')->nullable();
            $table->string('data_source')->default('manual'); // manual, ipstack, geolite, other
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_network_info');
        Schema::dropIfExists('servers');
    }
};
