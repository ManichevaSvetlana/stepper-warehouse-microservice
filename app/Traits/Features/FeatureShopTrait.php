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
     * Shop: get categories data.
     *
     * @return array
     */
    public function listShopCategories(): array
    {
        $response = Http::post("{$this->apiUrl}/categories/export", [
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
        $response = Http::post("{$this->apiUrl}/stickers/export", [
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
        $response = Http::post("{$this->apiUrl}/characteristics/export", [
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

        $response = Http::post("{$this->apiUrl}/brands/export", [
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

        $ids = [];
        foreach ($categories as $category) {
            $ids[] = $category['id'];
            self::updateOrCreate([
                'system_id' => $category['id'],
            ], [
                'data' => $category,
                'type' => 'category',
                'system' => 'shop',
            ]);
        }

        self::where('system', 'shop')->where('type', 'category')
            ->whereNotIn('system_id', $ids)
            ->delete();
    }

    /**
     * Shop: set stickers data.
     *
     * @return void
     */
    public function updateListOfLocalShopStickers(): void
    {
        $stickers = $this->listShopStickers();

        $ids = [];
        foreach ($stickers as $sticker) {
            $ids[] = $sticker['id'];
            self::updateOrCreate([
                'system_id' => $sticker['id'],
            ], [
                'data' => $sticker,
                'type' => 'sticker',
                'system' => 'shop',
            ]);
        }

        self::where('system', 'shop')->where('type', 'sticker')
            ->whereNotIn('system_id', $ids)
            ->delete();
    }

    /**
     * Shop: set characteristics data.
     *
     * @return void
     */
    public function updateListOfLocalShopCharacteristics(): void
    {
        $characteristics = $this->listShopCharacteristics();

        $ids = [];
        foreach ($characteristics as $characteristic) {
            $ids[] = $characteristic['id'];
            self::updateOrCreate([
                'system_id' => $characteristic['id'],
            ], [
                'data' => $characteristic,
                'type' => 'characteristic',
                'system' => 'shop',
            ]);
        }

        self::where('system', 'shop')->where('type', 'characteristic')
            ->whereNotIn('system_id', $ids)
            ->delete();
    }

    /**
     * Shop: set brands data.
     *
     * @return void
     */
    public function updateListOfLocalShopBrands(): void
    {
        $brands = $this->listShopBrands();

        $ids = [];
        foreach ($brands as $brand) {
            $ids[] = $brand['id'];
            self::updateOrCreate([
                'system_id' => $brand['id'],
            ], [
                'data' => $brand,
                'type' => 'brand',
                'system' => 'shop',
            ]);
        }

        self::where('system', 'shop')->where('type', 'brand')
            ->whereNotIn('system_id', $ids)
            ->delete();
    }
}
