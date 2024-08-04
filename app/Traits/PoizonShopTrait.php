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
        $url = 'https://poizonshop.ru/api/catalog/product/' . $productId;

        $headers = [
            'accept: application/json',
            'Content-Type: application/json; charset=utf-8',
            'Baggage: sentry-environment=production,sentry-release=e94rvlLllb-RqxbeEwsDN,sentry-public_key=238d9da4918fb742c57924b6c815910c',
            'Sec-Ch-Ua: "Not/A)Brand";v="8", "Chromium";v="126", "Google Chrome";v="126"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "macOS"',
            'Sentry-Trace: ff75eb8baa8b41109d54c9085b8d4c07-b43f61051faf1f2f-1',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true);
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
