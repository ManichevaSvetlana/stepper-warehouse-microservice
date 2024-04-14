<?php

namespace App\Traits\Features;

use Illuminate\Support\Facades\Http;

trait FeatureShopTrait
{
    /**
     * The Shop auth token.
     *
     * @var string
     */
    private string $shopAuthToken;

    /**
     * Shop: set auth token.
     *
     * @return void
     */
    public function setShopAuth(): void
    {
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

        $response = Http::post('https://steppershop.ge/api/auth', [
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
     * Shop: get categories data.
     *
     * @return array
     */
    public function listShopCategories(): array
    {
        $response = Http::post('https://steppershop.ge/api/categories/export', [
            'token' => $this->shopAuthToken,
        ]);

        return $response->json()['response']['categories'];
    }

    /**
     * Shop: get stickers data.
     *
     * @return array
     */
    public function listShopStickers(): array
    {
        $response = Http::post('https://steppershop.ge/api/stickers/export', [
            'token' => $this->shopAuthToken,
        ]);

        return $response->json()['response']['stickers'];
    }

    /**
     * Shop: get characteristics data.
     *
     * @return array
     */
    public function listShopCharacteristics(): array
    {
        $response = Http::post('https://steppershop.ge/api/characteristics/export', [
            'token' => $this->shopAuthToken,
        ]);

        return $response->json()['response']['modifications'];
    }

    /**
     * Shop: get brands data.
     *
     * @return array
     */
    public function listShopBrands(): array
    {
        $this->shopAuthToken = $this->getShopAuthToken();

        $response = Http::post('https://steppershop.ge/api/brands/export', [
            'token' => $this->shopAuthToken,
        ]);

        return $response->json()['response']['brands'];
    }

    /**
     * Shop: set categories data.
     *
     * @return void
     */
    public function updateListOfLocalShopCategories(): void
    {
        $categories = $this->listShopCategories();

        foreach ($categories as $category) {
            self::updateOrCreate([
                'system_id' => $category['id'],
            ], [
                'data' => $category,
                'type' => 'category',
                'system' => 'shop',
            ]);
        }
    }

    /**
     * Shop: set stickers data.
     *
     * @return void
     */
    public function updateListOfLocalShopStickers(): void
    {
        $stickers = $this->listShopStickers();

        foreach ($stickers as $sticker) {
            self::updateOrCreate([
                'system_id' => $sticker['id'],
            ], [
                'data' => $sticker,
                'type' => 'sticker',
                'system' => 'shop',
            ]);
        }
    }

    /**
     * Shop: set characteristics data.
     *
     * @return void
     */
    public function updateListOfLocalShopCharacteristics(): void
    {
        $characteristics = $this->listShopCharacteristics();

        foreach ($characteristics as $characteristic) {
            self::updateOrCreate([
                'system_id' => $characteristic['id'],
            ], [
                'data' => $characteristic,
                'type' => 'characteristic',
                'system' => 'shop',
            ]);
        }
    }

    /**
     * Shop: set brands data.
     *
     * @return void
     */
    public function updateListOfLocalShopBrands(): void
    {
        $brands = $this->listShopBrands();

        foreach ($brands as $brand) {
            self::updateOrCreate([
                'system_id' => $brand['id'],
            ], [
                'data' => $brand,
                'type' => 'brand',
                'system' => 'shop',
            ]);
        }
    }
}
