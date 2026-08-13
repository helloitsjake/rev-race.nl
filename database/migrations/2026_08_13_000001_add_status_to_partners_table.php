<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('status', 30)->default('draft')->after('is_active');
            $table->timestamp('verified_at')->nullable()->after('status');
            $table->text('internal_notes')->nullable()->after('verified_at');
        });

        // Alle bestaande partners zijn MVP-demodata (zie ROADMAP), nooit een echte
        // geverifieerde partner geweest. is_active=true betekende alleen "zichtbaar",
        // niet "geverifieerd" — dus niemand krijgt hier automatisch 'verified', dat zou
        // de fictie onder een nieuwe naam gewoon doorzetten.
        DB::table('partners')->update(['status' => 'draft']);

        DB::table('partners')
            ->whereIn('name', ['MotoPlus NL', 'Vroom Verzekert', 'TT Circuit Events'])
            ->update([
                'internal_notes' => 'Fictieve demo-partner uit de MVP-fase, geen echt bedrijf (verzonnen adres/contactgegevens). Niet op verified zetten zonder een echte, geverifieerde partner met kloppende gegevens.',
            ]);

        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('logo_url');
        });

        DB::table('partners')->update(['is_active' => true]);

        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['status', 'verified_at', 'internal_notes']);
        });
    }
};
