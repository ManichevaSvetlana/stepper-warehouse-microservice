<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait PoizonTrait
{
    /**
     * Poizon: get prices for a product.
     *
     * @return array
     * @var string $productId
     */
    public function getPoizonPricesForProduct(string $productId): array
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'apiKey' => env('POIZON_API_KEY'),
        ])->get('https://poison-api.com/Dewu/priceInfo', [
            'spuId' => $productId,
            'tradeTypes' => [0, 2],
        ]);

        return $response->json();
    }

    /**
     * Poizon: get product data.
     *
     * @return array
     * @var string $productId
     */
    public function getPoizonProductData(string $productId): array
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'apiKey' => env('POIZON_API_KEY'),
        ])->get('https://poison-api.com/Dewu/productDetail', [
            'spuId' => $productId,
        ]);

        return $response->json()['data'];
    }

    /**
     * Poizon: convert link to sku.
     *
     * @return array
     * @var string $link
     */
    public function convertPoizonLinkToSKU(string $link) : mixed
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'apiKey' => env('DEMO_POIZON_API_KEY'),
        ])->get('https://poison-api.com/Dewu/convertLinkToSpuId', [
            'link' => $link,
        ]);

        dd($response->json());

        return $response->json()['skuId'];
    }
}
