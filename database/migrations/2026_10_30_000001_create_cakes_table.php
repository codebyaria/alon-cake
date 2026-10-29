<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cakes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            // Translatable copy: {"id": "...", "en": "..."}
            $table->json('description');
            $table->json('flavor_note');
            // Price for the smallest size, in rupiah. Sizes add their surcharge.
            $table->unsignedInteger('base_price');
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cakes');
    }
};
