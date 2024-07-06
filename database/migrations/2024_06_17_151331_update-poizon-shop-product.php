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
        Schema::table('poizon_shop_products', function (Blueprint $table) {
            $table->boolean('easy_return')->nullable();
            $table->bigInteger('easy_return_max_cny_price')->nullable();
            $table->string('easy_return_sizes')->nullable();
            $table->boolean('has_discount')->nullable();
            $table->integer('visible_discount')->nullable();
            $table->integer('real_discount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poizon_shop_products', function (Blueprint $table) {
            $table->dropColumn('easy_return');
            $table->dropColumn('easy_return_max_cny_price');
            $table->dropColumn('easy_return_sizes');
            $table->dropColumn('has_discount');
            $table->dropColumn('visible_discount');
            $table->dropColumn('real_discount');
        });
    }
};
