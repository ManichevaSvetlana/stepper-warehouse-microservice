<?php

namespace App\Traits;

use App\Console\Commands\SyncSystemsProducts;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

trait PoizonTrait
{
    /**
     * Poizon API URL.
     */
    private string $url = 'https://poizon-api.com/api/dewu';

    /**
     * Poizon: get prices for a product.
     *
     * @return array
     * @throws ConnectionException
     * @var string $productId
     */
    public function getPoizonPricesForProduct(string $productId): array
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'apiKey' => env('POIZON_API_KEY'),
        ])->get($this->url . '/priceInfo', [
            'spuId' => $productId,
            'tradeTypes' => [0, 2],
        ]);

        return $response->json();
    }

    /**
     * Poizon: get product data.
     *
     * @return array
     * @throws ConnectionException
     * @var string $productId
     */
    public function getPoizonProductData(string $productId): array
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'apiKey' => env('POIZON_API_KEY'),
        ])->get($this->url . '/productDetail', [
            'spuId' => $productId,
        ]);

        return $response->json();
    }

    /**
     * Poizon: convert link to sku.
     *
     * @return array
     * @throws ConnectionException
     * @var string $link
     */
    public function convertPoizonLinkToSKU(string $link): mixed
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'apiKey' => env('DEMO_POIZON_API_KEY'),
        ])->get($this->url . '/convertLinkToSpuId', [
            'link' => $link,
        ]);

        return $response->json()['skuId'];
    }

    /**
     * Poizon: get product data in shop format.
     *
     * @param array $product
     * @param int $categoryId
     * @param array $categoryIds
     * @param array $filtersIds
     * @return array
     */
    public function getShopFormatFromData(array $product, int $categoryId, array $categoryIds, array $filtersIds): array
    {
        $mainJob = new SyncSystemsProducts();
        $colorPoizonId = '主色';
        $responseProducts = [];

        $sku = $product['detail']['spuId'];
        $name = $mainJob->processName($product['detail']['title']);
        $images = array_map(function($image) {
            return $image['url'];
        }, $product['image']['spuImage']['images']);
        $brand = $mainJob->processBrand($product['brandRootInfo']['brandItemList'][0]['brandName']);
        $article = $product['detail']['articleNumber'];
        $colorName = collect($product['basicParam']['basicList'])->firstWhere('key', $colorPoizonId)['value'];
        $sizesTable = $this->getSizesTable($product['sizeDto']['sizeInfo']['sizeTemplate']['list']);

        foreach ($product['saleProperties']['list'] as $sizeData) {
            $size = $sizeData['value'];
            $productSku = $sizeData['propertyValueId'];
            $responseProducts[] = [
                "sku" => $sku,
                "productSku" => $productSku,
                "name" => $name,
                "size" => $size,
                "price" => 0,
                "originalPriceInLari" => 0,
                "originalPriceInCNY" => 0,
                "originalPriceWithExpenses" => 0,
                "income" => 0,
                "images" => $images,
                "no_images" => false,
                "brand" => $brand,
                "category" => $categoryId,
                "articleNumber" => $article,
                "colorName" => $colorName,
                "colorId" => 0,
                "categoryIds" => $categoryIds,
                "categoryFiltersIds" => $filtersIds,
                "sizesTable" => $sizesTable
            ];
        }

        return $responseProducts;
    }

    /**
     * Poizon: get sizes table.
     *
     * @param array $sizeData
     * @return string
     */
    public function getSizesTable(array $sizeData): string
    {
        return view('components.sizes', compact('sizeData'))->render();
    }
}
