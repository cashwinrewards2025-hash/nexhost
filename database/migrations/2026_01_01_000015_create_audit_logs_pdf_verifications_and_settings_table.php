<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action');
            $table->string('entity_type');
            $table->uuid('entity_id')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
            $table->index('created_at');
        });

        Schema::create('pdf_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->onDelete('cascade');
            $table->string('pdf_hash');
            $table->string('verification_token')->unique();
            $table->string('qr_code_path')->nullable();
            $table->string('status')->default('valid'); // valid, invalid, modified, expired
            $table->timestamp('verified_at')->nullable();
            $table->integer('verification_count')->default(0);
            $table->string('last_verified_ip')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();
            $table->index('verification_token');
            $table->index('status');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, decimal, boolean, json, array
            $table->string('group')->default('general'); // general, billing, monitoring, email, pdf, security
            $table->text('description')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
            $table->index(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('pdf_verifications');
        Schema::dropIfExists('audit_logs');
    }
};
