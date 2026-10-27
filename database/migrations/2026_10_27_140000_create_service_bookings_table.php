<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('visit_on');
            $table->string('slot', 16);
            $table->text('note');
            $table->string('status', 32)->default('requested');
            $table->timestamps();

            $table->unique(['listing_id', 'user_id', 'visit_on', 'slot']);
            $table->index(['listing_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
    }
};
