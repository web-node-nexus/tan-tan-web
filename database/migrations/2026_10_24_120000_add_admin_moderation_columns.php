<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('banned_at')->nullable()->after('last_active_at');
            $table->string('ban_reason', 500)->nullable()->after('banned_at');
            $table->index('banned_at');
        });

        Schema::table('listings', function (Blueprint $table): void {
            $table->string('moderation_note', 500)->nullable()->after('status');
            $table->timestamp('moderated_at')->nullable()->after('moderation_note');
        });

        Schema::table('reports', function (Blueprint $table): void {
            $table->string('admin_note', 1000)->nullable()->after('status');
            $table->foreignId('resolved_by')->nullable()->after('admin_note')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropConstrainedForeignId('resolved_by');
            $table->dropColumn(['admin_note', 'resolved_at']);
        });

        Schema::table('listings', function (Blueprint $table): void {
            $table->dropColumn(['moderation_note', 'moderated_at']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['banned_at']);
            $table->dropColumn(['banned_at', 'ban_reason']);
        });
    }
};
