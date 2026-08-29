<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->string('service_name');
            $table->string('status')->default('not_monitored'); // operational, warning, critical, not_monitored
            $table->string('status_icon')->nullable();
            $table->text('status_message')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->integer('error_count')->default(0);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            $table->unique(['server_id', 'service_name']);
            $table->index(['server_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_statuses');
    }
};
