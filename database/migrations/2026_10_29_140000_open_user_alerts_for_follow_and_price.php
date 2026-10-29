<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_alerts', function (Blueprint $table): void {
            $table->dropUnique(['saved_search_id', 'listing_id']);
            $table->string('kind', 24)->default('saved_search')->after('user_id');
            $table->string('dedupe', 120)->nullable()->unique()->after('kind');
        });

        Schema::table('user_alerts', function (Blueprint $table): void {
            $table->unsignedBigInteger('saved_search_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_alerts', function (Blueprint $table): void {
            $table->unsignedBigInteger('saved_search_id')->nullable(false)->change();
            $table->dropUnique(['dedupe']);
            $table->dropColumn(['kind', 'dedupe']);
            $table->unique(['saved_search_id', 'listing_id']);
        });
    }
};
