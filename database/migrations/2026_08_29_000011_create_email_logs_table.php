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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_id')->nullable()->constrained('reports');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices');
            $table->string('recipient_email');
            $table->text('cc')->nullable();
            $table->text('subject');
            $table->longText('body');
            $table->string('email_type'); // report, invoice, notification
            $table->string('status')->default('queued'); // queued, sent, failed, bounced
            $table->integer('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
