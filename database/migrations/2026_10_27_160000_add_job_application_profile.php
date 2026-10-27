<?php

use App\Models\Listing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('experience', 16)->nullable()->after('note');
            $table->string('education', 16)->nullable()->after('experience');
            $table->string('can_join', 16)->nullable()->after('education');
            $table->unsignedInteger('expected_salary')->nullable()->after('can_join');
        });

        Listing::query()->where('slug', 'sales-executive-lucknow-demo')->update([
            'attributes' => json_encode([
                'sales_type' => 'field',
                'incentive' => true,
                'experience' => 'fresher',
                'target' => 'monthly',
            ]),
        ]);

        Listing::query()->where('slug', 'data-entry-part-time-delhi-demo')->update([
            'attributes' => json_encode([
                'work_mode' => 'home',
                'english' => 'basic',
                'computer' => true,
                'openings' => 2,
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['experience', 'education', 'can_join', 'expected_salary']);
        });
    }
};
