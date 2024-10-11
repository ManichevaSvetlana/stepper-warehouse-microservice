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
            $table->unsignedBigInteger('stock_order_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('order_site_id')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_article')->nullable();
            $table->text('product_link')->nullable();
            $table->string('product_size')->nullable();
            $table->date('date_of_order');
            $table->date('onex_date')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('first_payment', 15, 2)->nullable();
            $table->decimal('second_payment', 15, 2)->nullable();
            $table->boolean('is_fully_paid')->default(false);
            $table->string('contact_type')->nullable();
            $table->string('contact_value')->nullable();
            $table->string('site_email')->nullable();
            $table->string('site_name')->nullable();
            $table->string('site_phone')->nullable();
            $table->string('status_delivery')->nullable();
            $table->string('status_notification')->nullable();
            $table->decimal('sale_value', 15, 2)->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_address')->nullable();
            $table->string('delivery_type')->nullable();
            $table->string('source')->nullable();
            $table->text('comment')->nullable();
            $table->decimal('cny_price', 15, 2)->nullable();
            $table->decimal('price_for_sale', 15, 2)->nullable();
            $table->boolean('is_ordered')->default(false);
            $table->date('poizon_date')->nullable();
            $table->string('track_number')->nullable();
            $table->boolean('is_on_control')->default(false);
            $table->date('flight_date')->nullable();
            $table->boolean('is_online_order')->default(false);
            $table->boolean('is_return_possible')->default(false);
            $table->boolean('is_transformed_to_stock_order')->default(false);

            $table->string('tag_brand')->nullable();
            $table->string('tag_size_us')->nullable();
            $table->string('tag_size_uk')->nullable();
            $table->string('tag_size_eu')->nullable();
            $table->string('tag_size_sm')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_managers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('manager_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_managers');
    }
};
