<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Server Agent", "Prometheus", "UptimeRobot", "Manual"
            $table->string('type')->unique(); // manual, http, prometheus, uptimerobot, agent
            $table->string('description')->nullable();
            $table->text('configuration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('server_monitoring_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->string('token')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, revoked, expired
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index('token');
            $table->index('status');
        });

        Schema::create('monitoring_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status')->default('active'); // active, closed, archived
            $table->dateTime('metrics_collected_at')->nullable();
            $table->integer('metrics_count')->default(0);
            $table->timestamps();
            $table->unique(['server_id', 'period_start', 'period_end']);
            $table->index(['server_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_periods');
        Schema::dropIfExists('server_monitoring_tokens');
        Schema::dropIfExists('monitoring_sources');
    }
};
