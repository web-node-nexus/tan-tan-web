<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->date('interview_on')->nullable()->after('expected_salary');
            $table->string('interview_slot', 16)->nullable()->after('interview_on');
            $table->string('interview_place', 120)->nullable()->after('interview_slot');
            $table->string('interview_reply', 16)->nullable()->after('interview_place');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['interview_on', 'interview_slot', 'interview_place', 'interview_reply']);
        });
    }
};
