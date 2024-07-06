<?php

namespace App\Traits;

use App\Models\Poizon\PoizonShopProduct;
use App\Models\System\TrackProduct;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

trait PoizonShopTrait
{
    /**
     * Poizon Shop: get popular products.
     *
     * @param int $page
     * @param string|null $category
     * @return array
     * @throws ConnectionException
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
     * Poizon Shop: get product data.
     *
     * @return array
     * @throws ConnectionException
     * @var string $productId
     */
    public function getPoizonShopProductData(string $productId): array
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
        ])->get('https://poizonshop.ru/api/catalog/product/' . $productId);


        return $response->json();
    }

    /**
     * Poizon Shop: get product data by article.
     *
     * @param bool $isSku
     * @return array
     * @throws ConnectionException
     * @param string $article
     */
    public function getPoizonShopProductByArticle(string $article, bool $isSku = true)
    {
        if($isSku) {
            $product = $this->getPoizonShopProductData($article);
        } else {
            $product = $this->getPoizonShopProductData($article);
            if($product) return $product;

            $response = Http::withHeaders([
                'accept' => 'application/json',
            ])->get('https://autocomplete.diginetica.net/autocomplete?apiKey=Y4789GTN7C&strategy=advanced_xname%2Czero_queries&productsSize=20&regionId=global&forIs=true&showUnavailable=false&withContent=false&withSku=false&st=' . $article);

            $products = $response->json()['products'];
            if(!count($products)) return [];
            $product = $products[0];
            $productId = explode('-', $product['link_url']);
            $productId = end($productId);
            if(!$productId) return [];

            $product = $this->getPoizonShopProductData($productId);
        }

        if(!$product || !($product['spuId'] ?? false)) return [];

        $popularityPoint = TrackProduct::where('system', 'poizon-shop')->max('type') + 1;
        $track = TrackProduct::create([
            'sku' => $product['spuId'],
            'system' => 'poizon-shop',
            'type' => $popularityPoint,
        ]);
        $product = PoizonShopProduct::updateOrCreate(
            ['sku' => $track->sku],
            [
                'data' => $product,
                'popularity' => $popularityPoint,
            ]
        );

        return $product;
    }
}
