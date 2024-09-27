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
        Schema::create('stock_orders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('article');
            $table->string('sku');
            $table->string('size');
            $table->decimal('cny_price', 15, 2);
            $table->decimal('price_for_sale', 15, 2)->nullable();
            $table->date('poizon_date')->nullable();
            $table->string('track_number')->nullable();
            $table->boolean('is_on_control')->default(false);
            $table->boolean('is_on_website')->default(false);
            $table->string('onex_status')->nullable();
            $table->date('onex_date')->nullable();
            $table->date('flight_date')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_orders');
    }
};
