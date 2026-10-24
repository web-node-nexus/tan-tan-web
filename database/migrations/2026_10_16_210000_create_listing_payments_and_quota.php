<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->string('purpose', 40)->default('extra_ad'); // extra_ad | boost
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('status', 20)->default('pending'); // pending|paid|failed|refunded|consumed
            $table->string('provider', 40)->default('demo'); // demo|razorpay
            $table->string('provider_ref')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'purpose']);
        });

        Schema::table('listings', function (Blueprint $table): void {
            if (! Schema::hasColumn('listings', 'posting_fee')) {
                $table->decimal('posting_fee', 10, 2)->default(0)->after('price');
            }
            if (! Schema::hasColumn('listings', 'posting_payment_id')) {
                $table->foreignId('posting_payment_id')->nullable()->after('posting_fee')
                    ->constrained('listing_payments')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            if (Schema::hasColumn('listings', 'posting_payment_id')) {
                $table->dropConstrainedForeignId('posting_payment_id');
            }
            if (Schema::hasColumn('listings', 'posting_fee')) {
                $table->dropColumn('posting_fee');
            }
        });

        Schema::dropIfExists('listing_payments');
    }
};
