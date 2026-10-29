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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->date('pickup_date')->index();
            $table->string('time_slot');
            $table->string('stage')->default('recorded')->index();

            $table->foreignId('cake_id')->constrained()->restrictOnDelete();
            $table->foreignId('size_id')->constrained()->restrictOnDelete();
            $table->foreignId('flavor_id')->constrained()->restrictOnDelete();
            $table->string('cake_text', 24)->nullable();
            $table->string('card_message', 160)->nullable();
            $table->string('card_from', 40)->nullable();

            $table->string('fulfillment');
            $table->string('delivery_address')->nullable();

            $table->string('customer_name');
            $table->string('customer_whatsapp', 20);
            $table->string('locale', 2)->default('id');

            // Prices are copied at order time so later menu changes never rewrite history.
            $table->unsignedInteger('cake_price');
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('total');
            $table->string('payment_method');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
