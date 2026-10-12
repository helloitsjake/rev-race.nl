<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elke klik van RevRace naar een aanbodsite (Marktplaats, Google). Dit is het bewijs dat
     * de site koopintentie doorstuurt, en daarmee de basis voor elk gesprek met een partner
     * of affiliateprogramma. Bewust zonder IP of gebruiker: alleen wat, waarheen en vanaf
     * welke pagina, zodat er geen toestemming voor nodig is.
     */
    public function up(): void
    {
        Schema::create('outbound_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motor_id')->constrained()->cascadeOnDelete();
            $table->string('destination', 32);
            $table->string('source', 32)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at', 'destination']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_clicks');
    }
};
