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
            $table->string('return_status')->nullable();
            $table->unsignedBigInteger('return_order_id')->nullable();
            $table->text('return_reason')->nullable();
            $table->boolean('is_paid_back')->default(false);
            $table->decimal('return_sum', 10, 2)->nullable();
            $table->string('return_number')->nullable();
            $table->string('return_name')->nullable();
            $table->date('return_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('return_status');
            $table->dropColumn('return_order_id');
            $table->dropColumn('return_reason');
            $table->dropColumn('is_paid_back');
        });
    }
};
