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
        Schema::create('pdf_verifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('pdf_hash');
            $table->string('verification_token')->unique();
            $table->string('qr_code_path')->nullable();
            $table->string('status')->default('valid'); // valid, invalid, modified, expired
            $table->timestamp('verified_at')->nullable();
            $table->integer('verification_count')->default(0);
            $table->ipAddress('last_verified_ip')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_verifications');
    }
};
