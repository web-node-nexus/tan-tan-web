<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            if (! Schema::hasColumn('listings', 'is_negotiable')) {
                $table->boolean('is_negotiable')->default(true)->after('price');
            }
            if (! Schema::hasColumn('listings', 'previous_price')) {
                $table->decimal('previous_price', 12, 2)->nullable()->after('is_negotiable');
            }
            if (! Schema::hasColumn('listings', 'favourites_count')) {
                $table->unsignedInteger('favourites_count')->default(0)->after('views_count');
            }
            if (! Schema::hasColumn('listings', 'chats_count')) {
                $table->unsignedInteger('chats_count')->default(0)->after('favourites_count');
            }
            if (! Schema::hasColumn('listings', 'reserved_at')) {
                $table->timestamp('reserved_at')->nullable()->after('sold_at');
            }
            if (! Schema::hasColumn('listings', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('published_at');
            }
        });

        Schema::create('recently_viewed', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->timestamp('viewed_at');
            $table->timestamps();
            $table->unique(['user_id', 'listing_id']);
            $table->index(['user_id', 'viewed_at']);
        });

        Schema::create('seller_follows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['follower_id', 'seller_id']);
        });

        Schema::table('search_histories', function (Blueprint $table): void {
            if (! Schema::hasColumn('search_histories', 'filters')) {
                $table->json('filters')->nullable()->after('query');
            }
            if (! Schema::hasColumn('search_histories', 'results_count')) {
                $table->unsignedInteger('results_count')->nullable()->after('filters');
            }
        });
    }

    public function down(): void
    {
        Schema::table('search_histories', function (Blueprint $table): void {
            if (Schema::hasColumn('search_histories', 'results_count')) {
                $table->dropColumn('results_count');
            }
            if (Schema::hasColumn('search_histories', 'filters')) {
                $table->dropColumn('filters');
            }
        });

        Schema::dropIfExists('seller_follows');
        Schema::dropIfExists('recently_viewed');

        Schema::table('listings', function (Blueprint $table): void {
            foreach (['is_negotiable', 'previous_price', 'favourites_count', 'chats_count', 'reserved_at', 'expires_at'] as $col) {
                if (Schema::hasColumn('listings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
