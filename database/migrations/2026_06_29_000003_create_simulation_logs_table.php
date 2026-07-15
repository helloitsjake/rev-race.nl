<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45);
            $table->string('identifier', 80);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['identifier', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_logs');
    }
};
