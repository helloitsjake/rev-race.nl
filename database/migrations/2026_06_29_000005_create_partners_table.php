<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->string('category', 60);
            $table->text('description')->nullable();
            $table->string('website_url', 300)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 40)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
