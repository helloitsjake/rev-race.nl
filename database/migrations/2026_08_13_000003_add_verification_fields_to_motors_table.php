<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motors', function (Blueprint $table) {
            $table->string('verification_status', 20)->default('unverified')->after('source');
            $table->timestamp('data_checked_at')->nullable()->after('verification_status');
            $table->string('reviewer', 120)->nullable()->after('data_checked_at');
            $table->string('confidence', 10)->nullable()->after('reviewer'); // low, medium, high
            $table->text('data_flags')->nullable()->after('confidence'); // comma-separated, zie MotorDataAuditor
        });

        // Bestaande data was nooit door een mens beoordeeld, ook niet de 'anthropic'-batch (die
        // wel per motor een bron had via web-onderzoek, maar dat is onderzoek, geen verificatie).
        // 'ai_researched' is dus nadrukkelijk niet hetzelfde als 'verified'.
        DB::table('motors')->where('source', 'anthropic')->update(['verification_status' => 'ai_researched']);
        DB::table('motors')->whereIn('source', ['seed', 'manual'])->update(['verification_status' => 'unverified']);
    }

    public function down(): void
    {
        Schema::table('motors', function (Blueprint $table) {
            $table->dropColumn(['verification_status', 'data_checked_at', 'reviewer', 'confidence', 'data_flags']);
        });
    }
};
