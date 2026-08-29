<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignUuid('report_id')->nullable()->constrained('reports')->onDelete('set null');
            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->string('recipient_email');
            $table->string('cc')->nullable();
            $table->string('subject');
            $table->text('body')->nullable();
            $table->string('email_type'); // report, invoice, statement, notification, alert
            $table->string('status')->default('queued'); // queued, sent, failed, bounced, opened, clicked
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('tracking_id')->nullable();
            $table->integer('retry_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['client_id', 'status']);
            $table->index(['email_type', 'sent_at']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignUuid('client_id')->nullable()->constrained('clients')->onDelete('cascade');
            $table->foreignUuid('server_id')->nullable()->constrained('servers')->onDelete('cascade');
            $table->string('type'); // alert, warning, info, success
            $table->string('title');
            $table->text('message');
            $table->string('alert_type')->nullable(); // high_cpu, high_ram, disk_full, downtime, ssl_expiry, etc.
            $table->string('severity')->default('medium'); // critical, high, medium, low, info
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read']);
            $table->index(['client_id', 'severity']);
            $table->index('created_at');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            $table->index('slug');
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tag_id')->constrained('tags')->onDelete('cascade');
            $table->string('taggable_type');
            $table->uuid('taggable_id');
            $table->timestamps();
            $table->unique(['tag_id', 'taggable_type', 'taggable_id']);
            $table->index(['taggable_type', 'taggable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('email_logs');
    }
};
