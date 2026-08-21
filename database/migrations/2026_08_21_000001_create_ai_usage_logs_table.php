<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Legt elke poging tot een AI-call vast, ook de geblokkeerde. Zonder deze tabel is
     * achteraf niet te zien wie de tokens heeft opgestookt: het model en de kosten
     * werden nergens bewaard.
     */
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('purpose', 30);
            $table->string('outcome', 20);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('model', 60)->nullable();
            $table->string('query', 200)->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0);
            $table->timestamp('created_at')->useCurrent();

            // Voor de dagelijkse budgetoptelling, die bij elke call gedaan wordt.
            $table->index(['created_at', 'cost_usd']);
            // Voor het terugzoeken van misbruik per bezoeker.
            $table->index(['ip_address', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['outcome', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
