<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('easy_return_products', function (Blueprint $table) {
            $table->id();
            $table->string('article')->unique()->nullable();
            $table->string('sku')->unique()->nullable();
            $table->string('size');
            $table->decimal('price_in_cny', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('easy_return_products');
    }
};
