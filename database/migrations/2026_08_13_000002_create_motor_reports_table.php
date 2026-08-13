<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motor_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motor_id')->constrained()->cascadeOnDelete();
            $table->string('reporter_email', 191)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('new'); // new, reviewed, resolved, dismissed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motor_reports');
    }
};
