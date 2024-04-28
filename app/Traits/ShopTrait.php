<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\NoReturn;

trait ShopTrait
{
    /**
     * The Shop auth token.
     *
     * @var string
     */
    private string $shopAuthToken;

    /**
     * The Shop API URL.
     *
     * @var string
     */
    private string $apiUrl = '';

    /**
     * Shop: set auth token.
     *
     * @return void
     */
    public function setShopAuth(): void
    {
        $this->apiUrl = env('SHOP_API_URL');
        $this->shopAuthToken = $this->getShopAuthToken();
    }

    /**
     * Shop: get auth token.
     *
     * @return string|null
     */
    public function getShopAuthToken(): ?string
    {
        $loginDetails = [
            'login' => env('SHOP_LOGIN'),
            'password' => env('SHOP_PASSWORD'),
        ];

        $response = Http::post("{$this->apiUrl}/auth", [
            'login' => $loginDetails['login'],
            'password' => $loginDetails['password'],
        ]);

        if ($response->successful()) {
            return $response->json()['response']['token'];
        } else {
            return '';
        }
    }

    /**
     * Shop: get products data.
     *
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function listShopProducts(int $limit = 200, int $page = 1): array
    {
        $response = Http::post("{$this->apiUrl}/catalog/export", [
            'token' => $this->shopAuthToken,
            'limit' => $limit,
            'offset' => $page * $limit - $limit,
        ]);

        return $response->json()['response']['products'];
    }

    /**
     * Shop: store products data.
     *
     * @return array
     * @var array $products
     */
    public function createShopProducts(array $products): array
    {
        $response = Http::post("{$this->apiUrl}/catalog/import", [
            'token' => $this->shopAuthToken,
            'products' => $products
        ]);

        return $response->json();
    }
}
