<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->unique()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->text('bio')->nullable()->after('password');
            $table->string('locale', 5)->default('en')->after('bio');
            $table->string('theme', 10)->default('system')->after('locale');
            $table->boolean('is_verified')->default(false)->after('theme');
            $table->boolean('show_phone')->default(false)->after('is_verified');
            $table->string('referral_code', 16)->nullable()->unique()->after('show_phone');
            $table->foreignId('referred_by')->nullable()->after('referral_code');
            $table->unsignedBigInteger('city_id')->nullable()->after('referred_by');
            $table->decimal('latitude', 10, 7)->nullable()->after('city_id');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->timestamp('last_active_at')->nullable()->after('longitude');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'phone_verified_at',
                'bio',
                'locale',
                'theme',
                'is_verified',
                'show_phone',
                'referral_code',
                'referred_by',
                'city_id',
                'latitude',
                'longitude',
                'last_active_at',
                'deleted_at',
            ]);
        });
    }
};
