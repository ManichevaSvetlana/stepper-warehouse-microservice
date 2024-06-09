<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait PoizonShopTrait
{
    /**
     * Poizon Shop: get popular products.
     *
     * @param int $page
     * @param string|null $category
     * @return array
     */
    public function getPoizonShopPopularProducts(int $page, ?string $category = 'sneakers'): array
    {
        if($category === null) $url = 'https://poizonshop.ru/api/catalog/product?sort=by-relevance&perPage=40&page=' . $page;
        else $url = "https://poizonshop.ru/api/catalog/product?sort=by-relevance&categorySlug=$category&perPage=40&page=" . $page;

        $response = Http::withHeaders([
            'accept' => 'application/json',
        ])->get($url);


        return $response->json()['items'];
    }

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
        ])->get('https://poizonshop.ru/api/catalog/product/' . $productId);


        return $response->json();
    }
}
