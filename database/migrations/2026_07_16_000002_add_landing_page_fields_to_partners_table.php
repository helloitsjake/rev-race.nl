<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('address_street', 150)->nullable()->after('logo_url');
            $table->string('address_postcode', 10)->nullable()->after('address_street');
            $table->string('address_city', 100)->nullable()->after('address_postcode');
            $table->unsignedSmallInteger('founded_year')->nullable()->after('address_city');
            $table->text('about_text')->nullable()->after('founded_year');
            $table->text('why_choose_text')->nullable()->after('about_text');
            $table->json('usps')->nullable()->after('why_choose_text');
            $table->string('opening_hours', 255)->nullable()->after('usps');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn([
                'address_street',
                'address_postcode',
                'address_city',
                'founded_year',
                'about_text',
                'why_choose_text',
                'usps',
                'opening_hours',
            ]);
        });
    }
};
