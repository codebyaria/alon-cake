<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Only days that differ from config('alon.default_capacity') get a row.
     */
    public function up(): void
    {
        Schema::create('day_capacities', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedTinyInteger('capacity');
            $table->boolean('is_closed')->default(false);
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_capacities');
    }
};
