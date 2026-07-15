<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_results', function (Blueprint $table) {
            $table->id();
            $table->string('share_code', 10)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('motor_a_id')->constrained('motors');
            $table->foreignId('motor_b_id')->constrained('motors');
            $table->string('road_type', 20);
            $table->string('road_condition', 20);
            $table->unsignedSmallInteger('distance_m');
            $table->unsignedSmallInteger('rider_a_kg')->nullable();
            $table->unsignedSmallInteger('rider_b_kg')->nullable();
            $table->decimal('time_a_s', 7, 3);
            $table->decimal('time_b_s', 7, 3);
            $table->string('winner', 1);
            $table->json('samples')->nullable();
            $table->timestamps();

            $table->index('share_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_results');
    }
};
