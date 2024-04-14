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
        Schema::create('poizon_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->json('data');
            $table->string('type')->nullable();
            $table->json('prices')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poizon_products');
    }
};
