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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tag_brand')->nullable();
            $table->string('tag_size_us')->nullable();
            $table->string('tag_size_uk')->nullable();
            $table->string('tag_size_eu')->nullable();
            $table->string('tag_size_sm')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('tag_brand');
            $table->dropColumn('tag_size_us');
            $table->dropColumn('tag_size_uk');
            $table->dropColumn('tag_size_eu');
            $table->dropColumn('tag_size_sm');
        });
    }
};
