<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_nodes', function (Blueprint $table): void {
            $table->id();
            $table->string('root', 32);
            $table->foreignId('parent_id')->nullable()->constrained('catalog_nodes')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['root', 'parent_id']);
        });

        Schema::create('user_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('saved_search_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('body', 500);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['saved_search_id', 'listing_id']);
            $table->index(['user_id', 'read_at']);
        });

        Schema::create('push_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->string('platform', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_tokens');
        Schema::dropIfExists('user_alerts');
        Schema::dropIfExists('catalog_nodes');
    }
};
