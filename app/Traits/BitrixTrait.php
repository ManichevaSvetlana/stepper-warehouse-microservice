<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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
            'id' => 124,
            'value' => 104,
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
     * Get the properties from Bitrix24.
     *
     * @return array
     */
    public function listBitrixProperties(): array
    {
        $response = \Illuminate\Support\Facades\Http::get($this->bitrixUrl . 'catalog.productProperty.list');

        $catalogData = $response->json();

        return $catalogData['result']['productProperties'];
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
    public function listBitrixParentProducts($page = 1): array
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
                'type',
                'property' . $this->propertiesIds['size']['id'],
                'property' . $this->propertiesIds['isOnline']['id'],
                'property' . $this->propertiesIds['price']['id'], //
                'property44', // Картинки галереи
                'property48', // Картинки галереи
                'property120', // External ID
                'property122', // Brand
                'property126', // Артикул
                'property128', // Product SKU in Poizon
                'property136', // Shop ID
                'property138', // Quantity
                'property140', // Original price in CNY
                'property130', // Original price in GEL
                'property132', // Original price in GEL with expenses
                'property134', // Income
            ],
            "filter" => [
                "iblockId" => $this->parentsBlockId
            ],
            'start' => $page * 50 - 50,
            'order' => ['ID' => 'ASC'],
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
     * Add the product photo to Bitrix24.
     *
     * @param string $productId
     * @param string $fileBase64
     * @param string $fileName
     * @return array
     */
    public function addProductPhotoBitrix(string $productId, string $fileBase64, string $fileName): array
    {
        $fields = [
            'fields' => [
                'productId' => $productId,
                'type' => 'MORE_PHOTO',
            ],
            'fileContent' => [
                $fileName,
                $fileBase64
            ]
        ];

        $response = \Illuminate\Support\Facades\Http::post($this->bitrixUrl . 'catalog.productImage.add', $fields);
        return $response->json();
    }

    /**
     * Get the filename from the URL.
     *
     * @param string $url
     * @return string
     */
    private function getFilenameFromUrl(string $url): string
    {
        $url = strtok($url, '?'); // Удаляем параметры запроса, если они есть
        $parsedUrl = parse_url($url); // Разбиваем URL на компоненты
        $path = $parsedUrl['path']; // Получаем путь к файлу
        $filename = basename($path); // Извлекаем имя файла

        // Проверяем расширение файла и заменяем 'jpg' на 'jpeg'
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (strtolower($extension) === 'jpg') {
            $filename = str_replace('.jpg', '.jpeg', $filename);
        }

        return $filename;
    }

    /**
     * Convert image to base64.
     *
     * @param string $url
     * @return string
     * @throws GuzzleException
     */
    private function convertImageToBase64(string $url) : string {
        $client = new Client();
        $response = $client->get($url);
        $imageData = $response->getBody()->getContents();
        return base64_encode($imageData);
    }


    /**
     * Add the product to Bitrix24.
     *
     * @param array $product
     * @param bool $isUpdate
     * @param bool $isOnlineType
     * @return array
     * @throws GuzzleException
     */
    public function addProductToBitrix(array $product, bool $isUpdate = false, bool $isOnlineType = true): array
    {
        $fields = [
            'fields' => [
                "canBuyZero" => "Y",
                "active" => "Y",
                "available" => "Y",
                "type" => 7,
                "iblockId" => $this->parentsBlockId,
                "name" => $product['name'],
                'measure' => '9',
                ("property" . $this->propertiesIds['isOnline']['id']) => ['value' => $isOnlineType ? $this->propertiesIds['isOnline']['value'] : 0],

                'property120' => [
                    'value' => $product['sku']
                ], // External ID
                'property122' => [
                    'value' => $product['brand']
                ], // Brand
                'property126' => [
                    'value' => $product['articleNumber']
                ], // Brand
                'property128' => [
                    'value' => $product['productSku']
                ], // Brand
                'property130' => [
                    'value' => $product['originalPriceInLari'] ?? 0
                ], // Original price in GEL
                'property132' => [
                    'value' => $product['originalPriceWithExpenses'] ?? 0
                ], // Original price in GEL with expenses
                'property134' => [
                    'value' => $product['income'] ?? 0
                ], // Original price in GEL with expenses
                'property140' => [
                    'value' => $product['originalPriceInCNY'] ?? 0
                ], // Original price in CNY with expenses

            ],
        ];
        if ($product['price'] ?? false) {
            $fields['fields']['property' . $this->propertiesIds['price']['id']] = [
                'value' => $product['price']
            ];
            $fields['fields']['price'] = $product['price'];
            $fields['fields']['purchasingPrice'] = $product['price'];
            $fields['fields']['purchasingCurrency'] = 'RUB';
        }
        if ($product['size'] ?? false) {
            $fields['fields']['property' . $this->propertiesIds['size']['id']] = [
                'value' => $product['size']
            ];
        }

        if(!$isOnlineType) {
            $property = 'element';
            $method = $isUpdate ? 'catalog.product.update' : 'catalog.product.add';
            $fields['fields']['property136'] = $product['shopId'] ?? '';
            if ($product['quantity'] ?? false) {
                $fields['fields']['quantity'] = $product['quantity'];
                $fields['fields']['property138'] = $product['quantity'];
                $fields['fields']['measure'] = '9';
            }
        }
        else {
            $property = 'service';
            $method = $isUpdate ? 'catalog.product.service.update' : $this->createProductMethodBitrix;
            $fields['fields']['quantity'] = 999999;
            $fields['fields']['measure'] = '9';
        }

        if($isUpdate) {
            $fields['id'] = $product['id'];
        }

        $parent = \Illuminate\Support\Facades\Http::post($this->bitrixUrl . $method, $fields);

        if($product['price']) {
            $productId = $isUpdate ? $product['id'] : $parent['result'][$property]['id'];
            $productPrice = $product['price'];
            $price = $isUpdate ? $this->updateOrCreateProductPriceBitrix($productId, $productPrice) : $this->createProductPriceBitrix($productId, $productPrice);
        }

        if($product['images'] && count($product['images']) && !$isUpdate) {
            $file = $product['images'][0];
            echo "Add photo to product: $file \n";
            $fileBase64 = $this->convertImageToBase64($file);
            $fileName = $this->getFilenameFromUrl($file);
            $productId = $isUpdate ? $product['id'] : $parent['result'][$property]['id'];
            $productPhoto = $this->addProductPhotoBitrix($productId, $fileBase64, $fileName);
        }

        return $parent->json();
    }
}
