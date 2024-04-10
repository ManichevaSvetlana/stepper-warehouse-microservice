<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $product = new App\Models\Product();
    //dd($product->listPricesBitrix());
    //dd($product->updatePriceToBitrix(73, 1000));
    //dd($product->updateOrCreateProductPriceBitrix(107, 288));
    $quantity1 = rand(1, 10);
    $quantity2 = rand(1, 10);
    dd($product->addProductToBitrix([
        'id' => 'ref434',
        'name' => 'Parent Product Test ' . rand(3, 100),
        'quantity' => $quantity1 + $quantity2,
        'price' => rand(100, 2000),
        'size' => rand(35, 45),
    ]));
});
