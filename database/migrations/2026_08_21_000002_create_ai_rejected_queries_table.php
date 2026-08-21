<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Negatieve cache. Een zoekopdracht die geen motorfiets blijkt te zijn kostte tot nu
     * toe elke keer opnieuw een volledige API-call, ook als iemand exact hetzelfde tien
     * keer achter elkaar intypte. Die afwijzing wordt hier onthouden, zodat een herhaling
     * gratis is. De hits-teller is tegelijk het beste misbruiksignaal dat er is: een
     * zoekopdracht met honderden hits is geen bezoeker die zich vertypt.
     */
    public function up(): void
    {
        Schema::create('ai_rejected_queries', function (Blueprint $table) {
            $table->id();
            $table->string('normalized_query', 200)->unique();
            $table->string('original_query', 200);
            $table->unsignedInteger('hits')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('last_seen_at')->nullable();

            $table->index('hits');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_rejected_queries');
    }
};
