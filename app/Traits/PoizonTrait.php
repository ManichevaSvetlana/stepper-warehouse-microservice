<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\NoReturn;

trait PoizonTrait
{
    /**
     * The list catalogs method for Bitrix24.
     *
     * @var string
     */
    private string $listCatalogsMethodBitrix = 'catalog.catalog.list';

    /**
     * Poizon: get prices for a product.
     *
     * @return array
     * @var string $productId
     */
    public function getPricesForProduct(string $productId): array
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
    public function getProductData(string $productId): array
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'apiKey' => env('POIZON_API_KEY'),
        ])->get('https://poison-api.com/Dewu/productDetail', [
            'spuId' => $productId,
        ]);

        return $response->json();
    }
}
