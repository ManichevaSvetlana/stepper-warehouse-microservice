<?php

use App\Models\Poizon\PoizonProduct;
use App\Models\System\TrackProduct;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    $psku = 2361233;
    $poizonProduct = PoizonProduct::where('sku', $psku)->first();
    $sizesPropertiesList = collect($poizonProduct->data['saleProperties']['list']);
    $sku = 608677824;
    $skus = collect($poizonProduct->data['skus']);
    $neededSku = $skus->firstWhere('skuId', $sku);
    $propertyValueId = collect($neededSku['properties'])->sortByDesc('level')->first()['propertyValueId'];
    $property = $sizesPropertiesList->firstWhere('propertyValueId', $propertyValueId);
    $value = $property['value'];
    dd($value);
});

Route::get('/test1', function () {
    $links = [
        'https://dw4.co/t/A/23rLxLHa',
        'https://dw4.co/t/A/23rHtuwS',
        'https://dw4.co/t/A/23rMBvQv',
        'https://dw4.co/t/A/23rMKayX',
        'https://dw4.co/t/A/23rIFjYD',
        'https://dw4.co/t/A/23rILxBn',
        'https://dw4.co/t/A/23rMhH2s',
        'https://dw4.co/t/A/23rIcvy4',
        'https://dw4.co/t/A/23rIipwC',
        'https://dw4.co/t/A/23rIokvx',
        'https://dw4.co/t/A/23rN7mJU',
        'https://dw4.co/t/A/23rJ4JJH',
        'https://dw4.co/t/A/23rjFmhC',
        'https://dw4.co/t/A/23rjN7vo',
        'https://dw4.co/t/A/23rjSmLU',
        'https://dw4.co/t/A/23rjaJtB',
        'https://dw4.co/t/A/23rO0K7e',
        'https://dw4.co/t/A/23rjtkDW',
        'https://dw4.co/t/A/23rOBfxC',
        'https://dw4.co/t/A/23rOH6YP',
        'https://dw4.co/t/A/23rk9VUA',
        'https://dw4.co/t/A/23rOSdJa',
        'https://dw4.co/t/A/23rkLQEH',
        'https://dw4.co/t/A/23rkX1RG',
        'https://dw4.co/t/A/23rkeaoh',
        'https://dw4.co/t/A/23rkkhAB',
        'https://dw4.co/t/A/23rl1E3u',
        'https://dw4.co/t/A/23rPJELb',
        'https://dw4.co/t/A/23rPPZ2s',
        'https://dw4.co/t/A/23rPV7pj',
        'https://dw4.co/t/A/23rlOlx1',
        'https://dw4.co/t/A/23rlVhET',
        'https://dw4.co/t/A/23rlc6MG',
        'https://dw4.co/t/A/23rPvsTk',
        'https://dw4.co/t/A/23rlq0wS',
        'https://dw4.co/t/A/23rm3atU',
        'https://dw4.co/t/A/23rmJFz2',
        'https://dw4.co/t/A/23rmYBVs',
        'https://dw4.co/t/A/23rQqt5Z',
        'https://dw4.co/t/A/23rQwvO9',
        'https://dw4.co/t/A/23rR1NeE',
        'https://dw4.co/t/A/23rmueul',
        'https://dw4.co/t/A/23rRCPdC',
        'https://dw4.co/t/A/23rn9TYR',
        'https://dw4.co/t/A/23rRweZF',
        'https://dw4.co/t/A/23rS5J2Q',
        'https://dw4.co/t/A/23rSEIVS',
        'https://dw4.co/t/A/23ro8UTc',
        'https://dw4.co/t/A/23rSYnFb',
        'https://dw4.co/t/A/23roScYP',
        'https://dw4.co/t/A/23rocv9J',
        'https://dw4.co/t/A/23rTeyYF',
        'https://dw4.co/t/A/23rqO793',
        'https://dw4.co/t/A/23rqm12J',
        'https://dw4.co/t/A/23rr0ZkR',
        'https://dw4.co/t/A/23rrNR8T',
        'https://dw4.co/t/A/23rW2BiX',
        'https://dw4.co/t/A/23rWBQty',
        'https://dw4.co/t/A/23rsovtQ',
        'https://dw4.co/t/A/23rsvufT',
        'https://dw4.co/t/A/23rXuVt1',
        'https://dw4.co/t/A/23ru3ExZ',
        'https://dw4.co/t/A/23rYULrM'];
    $skus = [];
    $poizon = new PoizonProduct();

    foreach ($links as $link) {
        $sku = $poizon->convertPoizonLinkToSKU($link);
        TrackProduct::updateOrCreate(['sku' => $sku], ['type' => 'shoes']);
        $skus[] = $sku;
    }

    dd($skus);
});

Route::get('/test4', function () {

    function calculatePrice($initialPrice): array
    {
        $lari = 0.37;
        $shipment = 32;
        $terminalCommission = 1.02;
        $vat = 1.19;

        $originalPriceInCNY = $initialPrice / 100; // original price
        $originalPriceInLari = $originalPriceInCNY * $lari; // price in lari - 1900
        $price = $originalPriceInLari + $shipment; // price with shipment - 1900 + 32 = 1932
        $price = 1.3 * $price; // price with coefficient = 1932 * 1.1 = 2125.2


        $price = ($price * $terminalCommission) * $vat - ($originalPriceInLari * abs(1 - $vat - 0.01)) ; // price with commissions 2575
        $income = $price - ($price * abs(1 - $vat)) - ($price * abs(1 - $terminalCommission)) + ($originalPriceInLari * abs(1 - $vat - 0.01)) - $originalPriceInLari;

        return [
            "price" => $price,
            "originalPriceInCNY" => $originalPriceInCNY,
            "originalPriceInLari" => $originalPriceInLari,
            "originalPriceWithExpenses" => ($originalPriceInLari) * ($vat - 0.01) + $shipment,
            "income" => $income
        ];
    }

    dd(calculatePrice(60000));
});

Route::get('/test2', function () {
    $products = [];
    foreach ($products as $product) {
        \App\Models\System\TrackProduct::updateOrCreate(['sku' => $product], ['type' => 'shoes']);
    }
});

Route::get('/test3', function () {
    $trackSkus = TrackProduct::all();

    $nonExistentSkus = $trackSkus->filter(function($track) {
        return !PoizonProduct::where('sku', $track->sku)->exists();
    });

    dd(count($nonExistentSkus));
});

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__ . '/auth.php';
