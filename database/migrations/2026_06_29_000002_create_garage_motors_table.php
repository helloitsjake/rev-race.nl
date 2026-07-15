<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garage_motors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('motor_id')->constrained()->cascadeOnDelete();
            $table->string('nickname', 80)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'motor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garage_motors');
    }
};
