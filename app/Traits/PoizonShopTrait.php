<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait PoizonShopTrait
{
    /**
     * Poizon Shpop: get product data.
     *
     * @return array
     * @var string $productId
     */
    public function getPoizonShopProductData(string $productId): array
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'apiKey' => env('POIZON_API_KEY'),
        ])->get('https://poizonshop.ru/api/catalog/product/' . $productId);


        return $response->json();
    }
}
