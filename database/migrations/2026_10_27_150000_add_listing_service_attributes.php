<?php

use App\Models\Listing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->json('attributes')->nullable()->after('condition');
        });

        $samples = [
            'electrician-kanpur-demo' => [
                'work_types' => ['wiring', 'fan', 'inverter'],
                'same_day' => true,
                'experience_years' => 8,
            ],
            'packers-pune-demo' => [
                'move_type' => 'local',
                'vehicle' => 'tempo',
                'helpers' => 2,
                'packing_included' => true,
            ],
        ];

        foreach ($samples as $slug => $attributes) {
            Listing::query()->where('slug', $slug)->update(['attributes' => json_encode($attributes)]);
        }
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('attributes');
        });
    }
};
