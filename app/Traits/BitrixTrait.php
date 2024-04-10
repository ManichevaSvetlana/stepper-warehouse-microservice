<?php

namespace App\Traits;

use JetBrains\PhpStorm\NoReturn;

trait BitrixTrait
{
    /**
     * The list catalogs method for Bitrix24.
     *
     * @var string
     */
    private string $listCatalogsMethodBitrix = 'catalog.catalog.list';

    /**
     * The list products method for Bitrix24.
     *
     * @var string
     */
    private string $listMethodBitrix = 'catalog.product.list';

    /**
     * The add products method for Bitrix24.
     *
     * @var string
     */
    private string $createProductMethodBitrix = 'catalog.product.service.add';

    /**
     * The add product variations method for Bitrix24.
     *
     * @var string
     */
    private string $createProductVariationMethodBitrix = 'catalog.product.offer.add';

    /**
     * The list prices method for Bitrix24.
     *
     * @var string
     */
    private string $listPriceMethodBitrix = 'catalog.price.list';

    /**
     * The update price method for Bitrix24.
     *
     * @var string
     */
    private string $updatePriceMethodBitrix = 'catalog.price.update';

    /**
     * The add price method for Bitrix24.
     *
     * @var string
     */
    private string $createPriceMethodBitrix = 'catalog.price.add';

    /**
     * The variations block id for Bitrix24.
     *
     * @var int
     */
    private int $variationsBlockId = 16;

    /**
     * The variations block id for Bitrix24.
     *
     * @var int
     */
    private int $parentsBlockId = 14;

    /**
     * The properties ids for Bitrix24.
     *
     * @var array
     */
    private array $propertiesIds = [
        'externalId' => [
            'id' => 120,
        ],
        'size' => [
            'id' => 114,
        ],
        'parentId' => [
            'id' => 112,
        ],
        'price' => [
            'id' => 118,
        ],
        'isOnline' => [
            'id' => 108,
            'value' => 102,
        ],
    ];

    /**
     * The variations block id for Bitrix24.
     *
     * @var string
     */
    private string $bitrixUrl;

    /**
     * Test: Bitrix24.
     *
     * @return mixed
     */
    public function testBitrix(): mixed
    {
        $response = \Illuminate\Support\Facades\Http::get($this->bitrixUrl . 'catalog.price.list');

        $catalogData = $response->json();

        return $catalogData;
    }

    /**
     * Get the catalogs from Bitrix24.
     *
     * @return array
     */
    public function listBitrixCatalogs(): array
    {
        $response = \Illuminate\Support\Facades\Http::get($this->bitrixUrl . $this->listCatalogsMethodBitrix);

        $catalogData = $response->json();

        return $catalogData['result']['catalogs'];
    }


    /**
     * Get the variations products from Bitrix24.
     *
     * @return array
     */
    public function listBitrixVariationsProducts(): array
    {
        $response = \Illuminate\Support\Facades\Http::get($this->bitrixUrl . $this->listMethodBitrix, [
            "select" => [
                "iblockId",
                "id",
                'name',
                'purchasingPrice',
                'id',
                'quantity',
                'xmlId',
                'property' . $this->propertiesIds['size']['id'],
                'property' . $this->propertiesIds['parentId']['id'],
                'property' . $this->propertiesIds['isOnline']['id'],
                'parentId',
            ],
            "filter" => [
                "iblockId" => $this->variationsBlockId
            ],
        ]);

        $productData = $response->json();

        return $productData['result']['products'];
    }

    /**
     * Get the variations products from Bitrix24.
     *
     * @return array
     */
    public function listBitrixParentProducts(): array
    {
        $response = \Illuminate\Support\Facades\Http::get($this->bitrixUrl . $this->listMethodBitrix, [
            "select" => [
                "iblockId",
                "id",
                'name',
                'purchasingPrice',
                'id',
                'quantity',
                'xmlId',
                'type'
            ],
            "filter" => [
                "iblockId" => $this->parentsBlockId
            ],
        ]);

        $productData = $response->json();

        return $productData['result']['products'];
    }

    /**
     * List the product prices in Bitrix24.
     *
     * @param int|null $productId
     * @return array
     */
    public function listPricesBitrix(int $productId = null): array
    {
        $filter = $productId ? [
            'filter' => [
                'productID' => $productId,
            ],
        ] : [];

        $response = \Illuminate\Support\Facades\Http::post($this->bitrixUrl . $this->listPriceMethodBitrix, $filter);

        return $response->json()['result']['prices'];
    }

    /**
     * Create the product price in Bitrix24.
     *
     * @param int $productId
     * @param float $price
     * @return array
     */
    public function createProductPriceBitrix(int $productId, float $price): array
    {
        $fields = [
            'fields' => [
                'catalogGroupId' => 2,
                'currency' => "RUB",
                'price' => $price,
                'productId' => $productId
            ],
        ];

        $response = \Illuminate\Support\Facades\Http::post($this->bitrixUrl . $this->createPriceMethodBitrix, $fields);

        return $response->json();
    }

    /**
     * Update product price in Bitrix24.
     *
     * @param int $productId
     * @param float $price
     * @return array
     */
    public function updateOrCreateProductPriceBitrix(int $productId, float $price): array
    {
        $catalogPrice = null;
        // Get the first price from the list if exists
        try {
            $priceList = $this->listPricesBitrix($productId);
            if ($priceList) $catalogPrice = $priceList[0] ?? null;
        } catch (\Exception $e) {}

        // Create the price if not exists
        if (!$catalogPrice) {
            return $this->createProductPriceBitrix($productId, $price);
        } else {
            $fields = [
                'id' => 1,
                'fields' => [
                    'price' => $price,
                    'quantity' => 3,
                    'purchasingPrice' => $price,
                ],
            ];

            $response = \Illuminate\Support\Facades\Http::post($this->bitrixUrl . $this->updatePriceMethodBitrix, $fields);

            return $response->json();
        }
    }

    /**
     * Add the product to Bitrix24.
     *
     * @param array $product
     * @param bool $isOnlineType
     * @return array
     */
    public function addProductToBitrix(array $product, bool $isOnlineType = true): array
    {
        $fields = [
            'fields' => [
                "canBuyZero" => "Y",
                "active" => "Y",
                "type" => 7,
                "iblockId" => $this->parentsBlockId,
                "name" => $product['name'],
                ("property" . $this->propertiesIds['isOnline']['id']) => ['value' => $isOnlineType ? $this->propertiesIds['isOnline']['value'] : 0],
                ("property" . $this->propertiesIds['externalId']['id']) => $product['id'],
            ],
        ];
        if ($product['price'] ?? false) {
            $fields['fields']['property' . $this->propertiesIds['price']['id']] = $product['price'];
            $fields['fields']['price'] = $product['price'];
            $fields['fields']['purchasingPrice'] = $product['price'];
            $fields['fields']['purchasingCurrency'] = 'RUB';
        }
        if ($product['quantity'] ?? false) {
            $fields['fields']['quantity'] = $product['quantity'];
            $fields['fields']['measure'] = '9';
        }
        if ($product['size'] ?? false) {
            $fields['fields']['property' . $this->propertiesIds['size']['id']] = $product['size'];
        }

        $method = $this->createProductMethodBitrix;

        $parent = \Illuminate\Support\Facades\Http::post($this->bitrixUrl . $method, $fields);

        if($product['price']) $price = $this->createProductPriceBitrix($parent['result']['service']['id'], $product['price']);

        return $parent->json();
    }
}
