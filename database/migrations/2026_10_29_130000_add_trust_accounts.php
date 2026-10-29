<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('account_type', 20)->default('personal')->after('is_verified');
            $table->string('business_name')->nullable()->after('account_type');
            $table->string('gstin', 15)->nullable()->after('business_name');
            $table->string('kyc_status', 20)->default('none')->after('gstin');
        });

        Schema::create('kyc_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('legal_name');
            $table->string('document_type', 20);
            $table->string('document_last4', 4);
            $table->string('document_hash', 64)->unique();
            $table->string('document_path');
            $table->string('selfie_path');
            $table->string('status', 20)->default('pending');
            $table->string('note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('fraud_flags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 40);
            $table->timestamps();
            $table->index(['user_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_flags');
        Schema::dropIfExists('kyc_submissions');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['account_type', 'business_name', 'gstin', 'kyc_status']);
        });
    }
};
