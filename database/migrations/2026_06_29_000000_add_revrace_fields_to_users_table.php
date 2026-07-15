<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('weight_kg')->nullable()->after('password');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('weight_kg');
            $table->unsignedTinyInteger('age')->nullable()->after('height_cm');
            $table->string('riding_style', 30)->default('recreatief')->after('age');
            $table->unsignedTinyInteger('riding_experience_years')->nullable()->after('riding_style');
            $table->string('license_category', 10)->default('A')->after('riding_experience_years');
            $table->boolean('is_premium')->default(false)->after('license_category');
            $table->timestamp('premium_until')->nullable()->after('is_premium');
            $table->string('mollie_customer_id')->nullable()->after('premium_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'weight_kg',
                'height_cm',
                'age',
                'riding_style',
                'riding_experience_years',
                'license_category',
                'is_premium',
                'premium_until',
                'mollie_customer_id',
            ]);
        });
    }
};
