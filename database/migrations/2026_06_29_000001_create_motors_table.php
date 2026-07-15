<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motors', function (Blueprint $table) {
            $table->id();
            $table->string('brand', 80);
            $table->string('model', 120);
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('power_hp');
            $table->unsignedSmallInteger('torque_nm');
            $table->unsignedSmallInteger('weight_kg');
            $table->string('engine_type', 40);
            $table->unsignedSmallInteger('displacement_cc');
            $table->unsignedSmallInteger('top_speed_kmh')->nullable();
            $table->decimal('zero_to_hundred_s', 4, 2)->nullable();
            $table->decimal('drag_coefficient', 5, 3);
            $table->decimal('frontal_area_m2', 5, 3);
            $table->string('photo_url', 500)->nullable();
            $table->string('source', 30)->default('seed');
            $table->timestamp('api_fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['brand', 'model', 'year']);
            $table->index(['brand', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motors');
    }
};
