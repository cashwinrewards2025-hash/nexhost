<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->onDelete('cascade');
            $table->string('category'); // performance, security, storage, capacity
            $table->string('priority')->default('medium'); // critical, high, medium, low
            $table->text('recommendation');
            $table->text('rationale')->nullable();
            $table->text('action_items')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['report_id', 'priority']);
        });

        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignUuid('server_id')->nullable()->constrained('servers')->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('frequency')->default('monthly'); // daily, weekly, monthly, quarterly, yearly
            $table->integer('day_of_month')->nullable();
            $table->string('day_of_week')->nullable();
            $table->time('execution_time')->default('00:00:00');
            $table->string('status')->default('active'); // active, paused, inactive
            $table->string('email_template')->default('default');
            $table->json('email_recipients')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->boolean('include_pdf')->default(true);
            $table->boolean('include_invoice')->default(true);
            $table->boolean('send_email')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['client_id', 'status']);
            $table->index('next_run_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('recommendations');
    }
};
