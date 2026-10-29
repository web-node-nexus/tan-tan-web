<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('google_place_id')->nullable()->unique();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamps();

            $table->index(['city_id', 'name']);
        });

        Schema::table('listings', function (Blueprint $table): void {
            $table->foreignId('area_id')->nullable()->after('city_id')->constrained()->nullOnDelete();
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropConstrainedForeignId('area_id');
        });

        Schema::dropIfExists('areas');
    }
};
