<?php

use Illuminate\Support\Facades\Route;
use \Illuminate\Support\Facades\Schema;
use \Illuminate\Database\Schema\Blueprint;


Route::get('/run-command', function () {
    if (!Schema::hasColumn('orders', 'is_return_possible')) {
         Schema::table('orders', function (Blueprint $table) {
              $table->boolean('is_return_possible')->default(false);
         });
    }
});

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::prefix('nova-custom-api')->middleware('auth')->controller(\App\Http\Controllers\ApplicationController::class)->group(function () {
    Route::post('/store-product-to-shop', 'createProductInShop');
});

require __DIR__ . '/auth.php';
