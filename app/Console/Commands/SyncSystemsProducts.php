<?php

namespace App\Console\Commands;

use App\Models\Bitrix\BitrixProduct;
use App\Models\Feature;
use App\Models\Poizon\PoizonProduct;
use App\Models\Poizon\PoizonShopProduct;
use App\Models\Shop\ShopProduct;
use App\Models\System\FailedProduct;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class SyncSystemsProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string // sync:systems-products --system=poizon-shop --section=shop --count=10000
     */
    protected $signature = 'sync:systems-products {--system=poizon} {--section=all} {--sku=} {--images=1} {--count=200} {--mode=create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync systems products.';

    /**
     * Categories mapping.
     *
     * @var array
     */
    protected array $categoriesMapping = [
        'casual' => 636307,
        'casual/sneakers' => 636308,
        'casual/vintage_basketball' => 636309,
        'casual/sport' => 636310,
        'casual/daddy' => 636311,
        'casual/canvas' => 636312,

        'sport' => 636313,
        'sport/running' => 636314,
        'sport/basketball' => 636315,
        'sport/football' => 636316,
        'sport/training' => 636317,
        'sport/badminton' => 636318,
        'sport/tennis' => 636319,
        'sport/golf' => 636320,
        'sport/cycling' => 636321,

        'slippers' => 636322,
        'slippers/bautou' => 636323,
        'slippers/flip_flops' => 636324,
        'slippers/sport_sandals' => 636325,
        'slippers/sport' => 636326,
    ];

    /**
     * Categories mapping.
     *
     * @var array
     */
    protected array $categoriesFilterMapping = [
        'women' => 4646925,
        'men' => 4646924,
        'unisex' => 4806893,
        'easy-return' => 4804123
    ];

    /**
     * Categories mapping.
     *
     * @var array
     */
    public array $deliveryDays = [
        "ID_759629" => [
            [
                "id" => 4644055,
                "value" => [
                    "en" => "Delivery 10 - 20 days"
                ],
            ]
        ],
        "ID_759681" => [
            [
                "id" => 4644232,
                "value" => [
                    "en" => "Split payment: 50% now & 50% when you get your sneakers",
                ]
            ]
        ]
    ];

    /**
     * Catalog ID.
     *
     * @var int
     */
    //public int $catalogId = 487155;
    public int $catalogId = 0;

    /**
     * Category filter ID.
     *
     * @var int
     */
    public int $categoryFilterId = 760721;

    /**
     * Easy return filter ID.
     *
     * @var int
     */
    public int $easyReturnFilterId = 814830;

    /**
     * Easy return category ID.
     *
     * @var int
     */
    public int $easyReturnCategoryId = 636327;

    /**
     * Easy return sticker ID.
     *
     * @var int
     */
    public int $easyReturnStickerId = 98442;

    /**
     * Delivery ID.
     *
     * @var int
     */
    //public int $deliveryId = 607325;
    public int $deliveryId = 615677;

    /**
     * Sale category ID.
     *
     * @var int
     */
    public int $saleCategoryId = 615755;

    /**
     * Catalog modification ID.
     *
     * @var int
     */
    //public int $catalogModificationId = 58481;
    public int $catalogModificationId = 87201;

    /**
     * Categories and run products count.
     *
     * @var array
     */
    public array $categoriesAndRunProductsCount = [];

    /**
     * Execute the console command.
     * @throws GuzzleException
     */
    public function handle()
    {
        $section = $this->option('section');
        $startSku = $this->option('sku');
        $systemName = $this->option('system');
        $withoutImages = !$this->option('images');
        $pzCount = $this->option('count');
        $pzMode = $this->option('mode');

        $modelProducts = $systemName === 'poizon-shop' ? PoizonShopProduct::orderBy('popularity', 'asc') : PoizonProduct::orderBy('id', 'asc');
        $failedProductsShop = [];
        $startProcessing = false;

        $modelProducts->chunk(100, function ($poizonProducts) use (&$failedProductsShop, &$startProcessing, $startSku, $systemName, $section, $withoutImages, $pzCount, $pzMode) {
            foreach ($poizonProducts as $poizonProduct) {

                $visibilityPz = true;
                $categoriesPZ = $poizonProduct->data['category'];
                $lastCategoryPZ = end($categoriesPZ);

                if (!isset($this->categoriesAndRunProductsCount[$lastCategoryPZ])) {
                    $this->categoriesAndRunProductsCount[$lastCategoryPZ] = 0;
                }
                $this->categoriesAndRunProductsCount[$lastCategoryPZ]++;

                echo 'Product popularity: ' . $poizonProduct->popularity . '. In category: ' . $lastCategoryPZ . '. Current product index: ' . $this->categoriesAndRunProductsCount[$lastCategoryPZ] . "\n";

                if ($startSku) {
                    if (!$startProcessing && $poizonProduct->sku !== $startSku) {
                        echo 'Skip product: ' . $poizonProduct->sku . "\n";
                        continue;
                    }

                    $startProcessing = true;
                }

                $productCountInCategoryPZ = PoizonShopProduct::whereJsonContains('data->category->category3', $lastCategoryPZ)->count();
                if ($this->categoriesAndRunProductsCount[$lastCategoryPZ] < ($productCountInCategoryPZ - $pzCount) && $poizonProduct->popularity < 5000) {
                    $visibilityPz = false;
                }
                echo 'Total products in category: ' . $productCountInCategoryPZ . "\n";


                $syncedProductsForShop = $this->syncProductsForBitrix($systemName, $poizonProduct, $section, $withoutImages);

                if ($section === 'all' || $section === 'shop') {
                    $shop = new ShopProduct();
                    $shop->setShopAuth();

                    try {
                        if($pzMode === 'create' && !$visibilityPz) echo '!!!!! You are in create mode an pz visibility is false so far for this product!!!! \n';
                        else {
                            $easyReturn = [];
                            $saleInfo = [];
                            if($poizonProduct->easy_return) {
                                $easyReturn = [
                                    'status' => true,
                                    'max_cny_price' => $poizonProduct->easy_return_max_cny_price,
                                    'sizes' => $poizonProduct->easy_return_sizes
                                ];
                            }
                            if($poizonProduct->has_discount) {
                                $saleInfo = [
                                    'status' => true,
                                    'real_discount' => $poizonProduct->real_discount,
                                    'visible_discount' => $poizonProduct->visible_discount
                                ];
                            }

                            $this->createOrUpdateInShop($shop, $syncedProductsForShop, $withoutImages, $visibilityPz, $easyReturn, $saleInfo, $poizonProduct->popularity);
                        }
                    } catch (\Exception $e) {
                        echo "Error while storing shop: {$e->getMessage()}\n";
                        $failedProductsShop[$poizonProduct->sku] = $e->getMessage();
                        FailedProduct::create([
                            'sku' => $poizonProduct->sku,
                            'type' => 'shop',
                            'message' => $e->getMessage(),
                            'data' => $e->getTrace()
                        ]);
                    }
                }
            }
        });

        $count = count($failedProductsShop);
        echo "Failed products for shop: $count \n";
    }

    /**
     * Sync products for Bitrix.
     *
     * @param string $systemName
     * @param PoizonProduct|PoizonShopProduct $poizonProduct
     * @param string $section
     * @param bool $withoutImages
     * @param bool $ignoreZeroPrice
     * @return array
     * @throws GuzzleException
     */
    public function syncProductsForBitrix(string $systemName, mixed $poizonProduct, string $section, bool $withoutImages = false, bool $ignoreZeroPrice = true): array
    {
        if ($systemName !== 'poizon-shop') {
            echo "Product Poizon: {$poizonProduct->sku}\n";
            echo "Product Poizon: {$poizonProduct->data['detail']['title']}\n";

            $sizesPropertiesList = collect($poizonProduct->data['saleProperties']['list']);
            $skus = collect($poizonProduct->data['skus']);

            $syncedProductsForShop = [];
            $k = 0;
            foreach ($poizonProduct->prices as $sku => $priceModel) {
                try {
                    $syncedProduct = $this->prepareProduct($skus, $sku, $sizesPropertiesList, $priceModel, $poizonProduct);
                    $syncedProductsForShop[] = $syncedProduct;
                    if ($section === 'all' || $section === 'bitrix') {
                        $k++;
                        $this->createOrUpdateProductInBitrix($syncedProduct, $k, $withoutImages);
                    }
                } catch (\Exception $e) {
                    echo "Error while preparing and storing bitrix: {$e->getMessage()}\n";
                    if ($section === 'all' || $section === 'bitrix') FailedProduct::create([
                        'sku' => $sku,
                        'type' => 'bitrix',
                        'message' => $e->getMessage(),
                        'data' => $e->getTrace()
                    ]);
                    if ($section === 'all' || $section === 'shop') FailedProduct::create([
                        'sku' => $sku,
                        'type' => 'shop',
                        'message' => $e->getMessage(),
                        'data' => $e->getTrace()
                    ]);
                }
            }
        } else {
            echo "Product Poizon Shop: {$poizonProduct->sku}\n";
            echo "Product Poizon Shop: {$poizonProduct->data['name']}\n";
            $syncedProductsForShop = [];
            $prices = $poizonProduct->data['skus'];
            $k = 0;
            foreach ($prices as $priceModel) {
                if (($priceModel['cnyPrice'] < 5 && $ignoreZeroPrice) || (!key_exists('primary', $priceModel['size'] ?? []) && $ignoreZeroPrice))  continue;
                if(!key_exists('primary', $priceModel['size'] ?? [])) $priceModel['size']['primary'] = 0;
                try {
                    $syncedProduct = $this->prepareProductFromPoizonShop($poizonProduct, $priceModel);
                    $syncedProductsForShop[] = $syncedProduct;

                    if ($section === 'all' || $section === 'bitrix') {
                        $k++;
                        $this->createOrUpdateProductInBitrix($syncedProduct, $k, $withoutImages);
                    }
                } catch (\Exception $e) {
                    echo "Error while preparing and storing bitrix: {$e->getMessage()}\n";
                    if ($section === 'all' || $section === 'bitrix') FailedProduct::create([
                        'sku' => $priceModel['skuId'],
                        'type' => 'bitrix',
                        'message' => $e->getMessage(),
                        'data' => $e->getTrace()
                    ]);
                    if ($section === 'all' || $section === 'shop') FailedProduct::create([
                        'sku' => $priceModel['skuId'],
                        'type' => 'shop',
                        'message' => $e->getMessage(),
                        'data' => $e->getTrace()
                    ]);
                }
            }
        }

        return $syncedProductsForShop;
    }

    public function removeColumn($html, $columnName) {
        // Парсинг HTML
        $dom = new \DOMDocument();
        $dom->loadHTML($html);

        // Найти индекс столбца, который нужно удалить
        $headers = $dom->getElementsByTagName('th');
        $columnIndex = -1;

        foreach ($headers as $index => $header) {
            if ($header->nodeValue == $columnName) {
                $columnIndex = $index;
                break;
            }
        }

        if ($columnIndex == -1) {
            return $html; // Если столбец не найден, вернуть оригинальный HTML
        }

        // Удалить заголовок столбца
        $headerRow = $headers->item(0)->parentNode;
        $headerRow->removeChild($headerRow->getElementsByTagName('th')->item($columnIndex));

        // Удалить ячейки из всех строк
        $rows = $dom->getElementsByTagName('tr');
        foreach ($rows as $row) {
            if ($row->getElementsByTagName('td')->length > $columnIndex) {
                $row->removeChild($row->getElementsByTagName('td')->item($columnIndex));
            }
        }

        // Вернуть обновленный HTML
        return $dom->saveHTML();
    }


    /**
     * Create or update product in Shop.
     *
     * @param ShopProduct $shop
     * @param array $products
     * @param bool $withoutImages
     * @param bool $visibilityPz
     * @param array $easyReturn
     * @param array $saleInfo
     * @param int $popularity
     * @param bool $withoutCreate
     * @return array|null
     */
    public function createOrUpdateInShop(ShopProduct $shop, array $products, bool $withoutImages = false, bool $visibilityPz = true, array $easyReturn = [], array $saleInfo = [], int $popularity = 1, bool $withoutCreate = false): ?array
    {
        $catalog = Feature::where('type', 'characteristic')->where('system', 'shop')->whereRaw("json_extract(data, '$.title.en') = 'Catalog'")->first();
        $characteristicsData = collect($catalog->data['characteristics']);
        $size = $characteristicsData->firstWhere('slug', 'size');
        $color = $characteristicsData->firstWhere('slug', 'color-1');
        if (!$size) {
            echo "Size feature not found\n";
            return null;
        }
        $sizes = $size['values'];
        $colors = $color['values'];

        $colorsChinese = [
            '金色' => 'gold',
            '黑色' => 'black',    // Черный
            '黑色,紫色' => 'black',    // Черный
            '黑色,白色' => 'black',    // Черный
            '白色' => 'white',    // Белый
            '红色' => 'red',      // Красный
            '黑色,红色' => 'red',      // Красный
            '红色,黄色' => 'red',      // Красный
            '白色,红色' => 'red',      // Красный
            '蓝色' => 'blue',     // Синий
            '白色,蓝色' => 'blue',     // Синий
            '绿色' => 'green',    // Зеленый
            '米色,绿色' => 'green',    // Зеленый
            '白色,绿色' => 'green',    // Зеленый
            '黑色,绿色' => 'green',    // Зеленый
            '黄色' => 'yellow',   // Желтый
            '白色,黄色' => 'yellow',   // Желтый
            '橙色' => 'orange',   // Оранжевый
            '白色,橙色' => 'orange',   // Оранжевый
            '紫色' => 'purple',   // Фиолетовый
            '白色,紫色' => 'purple',   // Фиолетовый
            '褐色' => 'brown',    // Коричневый
            '米色,棕色' => 'brown',    // Коричневый
            '棕色' => 'brown',    // Коричневый
            '灰色' => 'gray',     // Серый
            '灰色,银色' => 'gray',     // Серый
            '黑色,灰色' => 'black',     // Серый
            '灰色,蓝色' => 'gray',     // Серый
            '白色,灰色' => 'gray',     // Серый
            '蓝色,灰' => 'gray',     // Серый
            '粉红色' => 'pink',   // Розовый
            '青色' => 'cyan',     // Голубой
            '银色' => 'silver',   // Серебряный
            '米色' => 'beige',   // Серебряный
            '灰色,米色' => 'beige',   // Серебряный
            '粉色' => 'pink',    // Серебряный
            '粉色,褐色' => 'pink',    // Серебряный
        ];

        $groupedBySku = collect($products)->groupBy('sku');
        $responses = [];
        foreach ($groupedBySku as $group) {
            $isSaleCategory = false;
            $isEasyReturn = false;
            $preparedProducts = [];
            $productBrand = ucwords(strtolower($group[0]['brand']));
            $brand = Feature::where('type', 'brand')->where('system', 'shop')->whereRaw("json_extract(data, '$.title.en') = '$productBrand'")->first();
            if (!$brand) {
                $tempBrand = $group[0]['brand'];
                $brand = Feature::where('type', 'brand')->where('system', 'shop')->whereRaw("json_extract(data, '$.title.en') = '$tempBrand'")->first();
            }
            if (!$brand) {
                echo "Brand {$productBrand} not found\n";
                continue;
            }
            $productSku = $group[0]['sku'];
            // ShopProduct::whereRaw("json_extract(data, '$.barcode') = '$productSku'")->exists() ||
            $images = $withoutImages ? [] : $group[0]['images'];
            echo "Product SKU: {$productSku}\n";
            echo count($images) ? "Creating product with images\n" : "Updating product without images\n";

            $article = 'online-' . $group[0]['articleNumber'];

            $parentsFilterIds = [];
            if ($group[0]['categoryFiltersIds']) {
                foreach ($group[0]['categoryFiltersIds'] as $catFilter) {
                    $parentsFilterIds [] = [
                        "id" => $catFilter,
                        "value" => [
                            "en" => ''
                        ],
                    ];
                }
            }


            $minPriceProductInGroup = $group->sortBy('price')->first();

            $filteredProductsInGroup = $group->reject(function ($functionProduct) use ($minPriceProductInGroup) {
                return $functionProduct['size'] === $minPriceProductInGroup['size'];
            });

            $sortedProductsInGroup = $filteredProductsInGroup->sortBy('size');

            $sortedProductsInGroup->prepend($minPriceProductInGroup);

            $group = $sortedProductsInGroup;

            foreach ($group as $key => $product) {
                if($product['no_images']) $noImages = true;
                $sizeValue = $this->formatNumber($product['size']);
                $sizeId = collect($sizes)->first(function ($item) use ($sizeValue) {
                    return $item['title']['en'] === $sizeValue;
                });
                if (!$sizeId) {
                    echo "Size {$sizeValue} not found\n";
                    continue;
                }
                $characteristics = [
                    ...$this->deliveryDays,
                    "ID_{$size['id']}" => [
                        [
                            "id" => $sizeId['id'] ?? null,
                            "value" => [
                                "en" => $sizeValue,
                                "ru" => $sizeValue,
                                "kat" =>$sizeValue,
                            ],
                        ]
                    ],
                ];

                if(count($parentsFilterIds)) {
                    $characteristics["ID_{$this->categoryFilterId}"] = $parentsFilterIds;
                }


                if ($colors) {
                    try {
                        $colorId = collect($colors)->first(function ($item) use ($product, $colorsChinese) {
                            return $item['title']['en'] === ucfirst($colorsChinese[$product['colorName']]);
                        });
                        if ($colorId) {
                            $characteristics["ID_{$color['id']}"] = [
                                [
                                    "id" => $colorId['id'],
                                    "value" => [
                                        "en" => $colorsChinese[$product['colorName']]
                                    ]
                                ]
                            ];
                        }
                    } catch (\Exception $e) {
                        echo "Color {$product['colorName']} not found\n";
                    }
                }

                if($easyReturn['status'] ?? false) {
                    $easyReturnSizes = explode(',', $easyReturn['sizes']);
                    if(in_array($sizeValue, $easyReturnSizes) && $product['originalPriceInCNY'] <= $easyReturn['max_cny_price']) {
                        if(!($characteristics["ID_{$this->easyReturnFilterId}"] ?? false)) $characteristics["ID_{$this->easyReturnFilterId}"] = [];
                        $characteristics["ID_{$this->easyReturnFilterId}"][] = [
                            "id" => $this->categoriesFilterMapping['easy-return'],
                            "value" => [
                                "en" => ''
                            ],
                        ];

                        $isEasyReturn = true;
                    }
                }

                /*if($saleInfo['status'] ?? false) {
                    $product['price_old'] = $product['price'] + ($product['price'] * ($saleInfo['visible_discount'] - $saleInfo['real_discount']) / 100);
                    $product['price'] = $product['price_old'] - ($product['price_old'] * $saleInfo['visible_discount'] / 100);
                    $isSaleCategory = true;
                }*/

                $preparedProducts[] = $this->getVariationProductForShop($sizeValue, $product['name'], $product['sku'], $article, $product['productSku'], $product['price'], $characteristics, $visibilityPz, $product['price_old'] ?? null, $popularity, $key + 1);
            }

            $brandId = $brand?->system_id;
            $title = $group[0]['name'];
            $parentsIds = [];
            if($this->catalogId) $parentsIds[] = $this->catalogId;
            $parentsIds[] = $this->deliveryId;

            if ($group[0]['categoryIds']) {
                $parentsIds = array_merge($parentsIds, $group[0]['categoryIds']);
            }

            if($isSaleCategory) $parentsIds[] = $this->saleCategoryId;
            if($isEasyReturn) $parentsIds[] = $this->easyReturnCategoryId;

            $sizesTable = null;
            $sizesTableValue = $group[0]['sizesTable'];
            if ($sizesTableValue && is_array($sizesTableValue)) {
                $sizesTable = $this->createSizeTableForShop($sizesTableValue);
            } else if (is_string($sizesTableValue)) {
                $sizesTable = $sizesTableValue;
            }

            if ($sizesTable) {
                $sizesTable = $this->removeColumn($sizesTable, 'RU');
            }

            $stickers = [];
            if($isEasyReturn) $stickers[] = $this->easyReturnStickerId;

            $productsData = [
                ...$this->getParentProductForShop($title, $productSku, $brandId, $parentsIds, $article, $images, $stickers, $sizesTable, $visibilityPz, $popularity),
                ...$preparedProducts,
            ];
            //if($withoutImages) $productsData = $preparedProducts;

            echo 'Creating product with variations in shop: ' . $title . PHP_EOL;
            if (count($preparedProducts)) $responses[] = $withoutCreate ? $productsData : $shop->createShopProducts($productsData);
            else {
                $productsData[0]['presence'] = false;
                $productsData[0]["availability"] = "Unpublish";
                $responses[] = $withoutCreate ? $productsData : $shop->createShopProducts($productsData);
            }
        }

        return $responses;
    }

    /**
     * Get variation product for shop.
     *
     * @param string $title
     * @param string $parentTitle
     * @param string $articleNumber
     * @param string $parentSku
     * @param string $sku
     * @param mixed $price
     * @param array $characteristics
     * @param bool $visisbility
     * @param float|null $oldPrice
     * @param int $parentPosition
     * @param int $position
     * @return array
     */
    public function getVariationProductForShop(string $title, string $parentTitle, string $articleNumber, string $parentSku, string $sku, mixed $price, array $characteristics = [], bool $visisbility = true, ?float $oldPrice = null, int $parentPosition = 1, int $position = 1): array
    {
        $fields =  [
            "title" => [
                "en" => $title,
                "ru" => $title,
                "kat" => $title,
            ],
            "parent_title" => $parentTitle,
            "characteristics_mode" => "Reset",
            "sku" => $sku,
            "position" => $position,
            "parent_position" => $parentPosition,
            "parent_sku" => $parentSku,
            "barcode" => $parentSku,
            "currency" => "GEL",
            "price" => $price,
            "presence" => ($price > 999 || !$visisbility) ? 0 : 9999,
            "force_alias_update" => "1",
            "availability" => $visisbility ? "Publish" : "Unpublish",
            "characteristics" => $characteristics,
            "modification" => [
                "id" => $this->catalogModificationId,
                "value" => [
                    "en" => "Catalog"
                ],
            ]
        ];

        if($oldPrice) $fields['price_old'] = $oldPrice;

        return $fields;
    }

    /**
     * Get parent product for shop.
     *
     * @param string $title
     * @param string $sku
     * @param int $brandId
     * @param array $parentIds
     * @param string $articleNumber
     * @param array $images
     * @param array $stickerIds
     * @param string|null $sizesTable
     * @param bool $visibility
     * @param int $parentPosition
     * @return array
     */
    public function getParentProductForShop(string $title, string $sku, int $brandId, array $parentIds, string $articleNumber, array $images = [], array $stickerIds = [], string $sizesTable = null, bool $visibility = true, int $parentPosition = 1): array
    {
        $parents = [];
        foreach ($parentIds as $parentId) {
            $parents[] = [
                "id" => $parentId
            ];
        }
        $stickers = [];
        foreach ($stickerIds as $stickerId) {
            $stickers[] = [
                "id" => $stickerId
            ];
        }
        if($sizesTable) $sizesTable = strtolower($sizesTable);
        return [
            [
                "title" => [
                    "en" => $title,
                    "ru" => $title,
                    "kat" => $title,
                ],
                "sku" => $articleNumber,
                "presence" => $visibility ? "true" : "false",
                "images" => [
                    "links" => [
                        ...$images
                    ]
                ],
                "barcode" => $sku,
                "availability" => $visibility ? "Publish" : "Unpublish",
                "parent" => $parents,
                "brand" => [
                    "id" => $brandId
                ],
                "parent_position" => $parentPosition,
                "stickers" => $stickers,
                "description" => [
                    "en" => $sizesTable ?? '',
                    "ru" => $sizesTable ?? '',
                    "kat" =>$sizesTable ?? '',
                ],
            ]
        ];
    }

    /**
     * Format number.
     *
     * @param string $numberString
     * @return string
     */
    public function formatNumber(string $numberString): string
    {
        // Заменяем запятую на точку
        $numberString = str_replace(',', '.', $numberString);

        // Преобразуем строку в float
        $floatNumber = floatval($numberString);

        // Форматируем число обратно в строку
        // Если число целое, убираем десятичную часть
        if (intval($floatNumber) == $floatNumber) {
            return strval(intval($floatNumber));
        } else {
            // Форматирование с одним десятичным знаком, если это необходимо
            return number_format($floatNumber, 1, '.', '');
        }
    }


    /**
     * Create or update product in Bitrix.
     *
     * @param array $product
     * @param int $k
     * @param bool $withoutImages
     * @return void
     *
     * @throws GuzzleException
     */
    public function createOrUpdateProductInBitrix(array $product, int $k, bool $withoutImages = false): void
    {
        $bitrix = new BitrixProduct();

        $data = [
            'sku' => $product['sku'],
            'name' => $product['name'] . '-' . $product['size'],
            'price' => $product['price'],
            'originalPriceInCNY' => $product['originalPriceInCNY'],
            'originalPriceInLari' => $product['originalPriceInLari'],
            'originalPriceWithExpenses' => $product['originalPriceWithExpenses'],
            'income' => $product['income'],
            'size' => $product['size'],
            'brand' => $product['brand'],
            'articleNumber' => $product['articleNumber'],
            'productSku' => $product['productSku'],
            'images' => $withoutImages && $k === 1 || !$withoutImages ? [
                $product['images'][0]
            ] : []
        ];

        if (BitrixProduct::where('sku', $product['sku'])->where('product_sku', $product['productSku'])->exists()) {
            echo "Update product in Bitrix\n";
            $existingProductId = BitrixProduct::where('sku', $product['sku'])->where('product_sku', $product['productSku'])->first()->system_id;
            $data['id'] = $existingProductId;
            $bitrix->addProductToBitrix($data, true);
        } else {
            echo "Create product in Bitrix\n";
            $bitrix->addProductToBitrix($data);
        }
    }

    /**
     * Prepare product data for sync.
     *
     * @param $skus
     * @param $sku
     * @param $sizesPropertiesList
     * @param $priceModel
     * @param $poizonProduct
     * @return array
     */
    public function prepareProduct($skus, $sku, $sizesPropertiesList, $priceModel, $poizonProduct): array
    {
        echo "SKU: {$sku}\n";
        $neededSku = $skus->firstWhere('skuId', $sku);
        $propertyValueId = collect($neededSku['properties'])->last()['propertyValueId'];
        $property = $sizesPropertiesList->firstWhere('propertyValueId', $propertyValueId);
        $value = $this->parseFraction($property['value']);
        $price = $this->calculatePrice($priceModel['prices'][0]['price']);
        echo "SKU: {$sku} - Size: {$value} - Price: {$price['price']}\n";

        $color = collect($poizonProduct->data['basicParam']['basicList'])->firstWhere('key', '主色');
        $brand = $poizonProduct->data['brandRootInfo']['brandItemList'][0]['brandName'];
        if (str_contains(strtolower($brand), 'adidas')) {
            $brand = 'adidas';
        }


        $syncedProduct = [
            'sku' => $poizonProduct->sku,
            'productSku' => $sku,
            'name' => preg_replace('/[\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{20000}-\x{2A6DF}]+/u', '', $poizonProduct->data['detail']['title']),
            'size' => $value,
            'price' => $price['price'],
            'originalPriceInLari' => $price['originalPriceInLari'],
            'originalPriceInCNY' => $price['originalPriceInCNY'],
            'originalPriceWithExpenses' => $price['originalPriceWithExpenses'],
            'income' => $price['income'],
            'images' => collect($poizonProduct->data['image']['spuImage']['images'])->pluck('url')->toArray(),
            'brand' => $brand,
            'category' => $poizonProduct->data['detail']['categoryId'],
            'articleNumber' => $poizonProduct->data['detail']['articleNumber'],
            'colorName' => $color['value'] ?? null,
            'colorId' => $color['propertyValueId'] ?? null,
        ];

        return $syncedProduct;
    }

    /**
     * Map with shop categories.
     *
     * @param $category
     * @return array
     */
    public function mapWithShopCategories($category): array
    {
        if(!$category || !($category["category3"] ?? false)) return [];

        $mappedValue = [];
        $mappedFilterValue = [];
        $response = [];

        $childCategory = str_replace('footwear/', '', $category["category3"]);
        $mainCategory = explode('/', $childCategory)[0];
        $mappedValue[] = $this->categoriesMapping[$mainCategory] ?? null;
        $mappedValue[] = $this->categoriesMapping[$childCategory] ?? null;

        $response['categories'] = $mappedValue;
        $response['categoriesFilters'] = $mappedFilterValue;

        return $response;
    }

    /**
     * Prepare product data from poizon shop for sync.
     *
     * @param $skus
     * @param $sku
     * @param $sizesPropertiesList
     * @param $priceModel
     * @param $poizonProduct
     * @return array
     */
    public function prepareProductFromPoizonShop($poizonProduct, $priceModel): array
    {
        echo "SKU: {$priceModel['skuId']}\n";
        $name = preg_replace('/[\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{20000}-\x{2A6DF}]+/u', '', $poizonProduct->data['name']);
        $price = $this->calculatePrice($priceModel['cnyPrice'], false);
        $size = $this->parseFraction($priceModel['size']['primary']);

        $brand = $poizonProduct->data['brand'];
        if (str_contains(strtolower($brand), 'adidas')) {
            $brand = 'adidas';
        }

        $findCategoryIds = [];
        $findCategoryFilterIds = [];
        $poizonProductCategories = $poizonProduct->data['category'] ?? [];
        if ($poizonProductCategories && key_exists('category3', $poizonProductCategories)) {
            $mappedValue = $this->mapWithShopCategories($poizonProductCategories);

            $mappedValueCategories = $mappedValue['categories'];
            if (count($mappedValueCategories)) {
                $findCategoryIds = [...$mappedValueCategories];
            }

            $mappedValueCategoriesFilters = $mappedValue['categoriesFilters'];
            if (count($mappedValueCategoriesFilters)) {
                $findCategoryFilterIds = [...$mappedValueCategoriesFilters];
            }
            $fit = $poizonProduct->data['fit'];
            if ($fit) {
                if ($fit === "FEMALE") {
                    $findCategoryFilterIds[] = $this->categoriesFilterMapping['women'];
                } else if ($fit === "MALE") {
                    $findCategoryFilterIds[] = $this->categoriesFilterMapping['men'];
                } else {
                    $findCategoryFilterIds[] = $this->categoriesFilterMapping['unisex'];
                }
            }
        }
        $sizesTable = null;
        if ($poizonProduct->data['sizeTable'] ?? false) {
            $sizesTable = $this->createSizeTableForShop($poizonProduct->data['sizeTable'] ?? []);
        }

        $syncedProduct = [
            'sku' => $poizonProduct->data['spuId'],
            'productSku' => $priceModel['skuId'],
            'name' => ucwords($name),
            'size' => $size,
            'price' => $price['price'],
            'originalPriceInLari' => $price['originalPriceInLari'],
            'originalPriceInCNY' => $price['originalPriceInCNY'],
            'originalPriceWithExpenses' => $price['originalPriceWithExpenses'],
            'income' => $price['income'],
            'images' => $poizonProduct->data['images'],
            'no_images' => count($poizonProduct->data['images']) === 0,
            'brand' => $brand,
            'category' => $poizonProduct->data['category1'],
            'articleNumber' => $poizonProduct->data['article'],
            'colorName' => $poizonProduct->data['color']['main'] ?? null,
            'colorId' => $poizonProduct->data['colorTheme'] ?? null,
            'categoryIds' => $findCategoryIds,
            'categoryFiltersIds' => $findCategoryFilterIds,
            'sizesTable' => $sizesTable,
        ];

        return $syncedProduct;
    }

    /**
     * Create size table for shop.
     *
     * @param array $sizeTable
     * @return string
     */
    public function createSizeTableForShop(array $sizeTable): string
    {
        // Начинаем собирать HTML-код таблицы
        $html = "<table border='1'>";

        // Заголовок таблицы (thead)
        $html .= "<thead>";
        $html .= "<tr>";
        foreach ($sizeTable as $item) {
            $html .= "<th>{$item['type']}</th>";
        }
        $html .= "</tr>";
        $html .= "</thead>";

        // Определяем максимальное количество строк, чтобы заполнить все значения
        $maxRows = max(array_map(function ($item) {
            return count($item['values']);
        }, $sizeTable));

        // Тело таблицы (tbody)
        $html .= "<tbody>";
        for ($i = 0; $i < $maxRows; $i++) {
            $html .= "<tr>";
            foreach ($sizeTable as $item) {
                // Добавляем значение или пустую ячейку, если нет значения в этой строке
                $value = isset($item['values'][$i]) ? $item['values'][$i] : "";
                $html .= "<td>$value</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody>";

        // Заканчиваем таблицу
        $html .= "</table>";

        // Возвращаем собранный HTML-код
        return $html;
    }

    /**
     * Parse fraction from string.
     *
     * @param string $str
     * @return float
     */
    public function parseFraction(string $str): float
    {
        // Замена распространенных дробей на их десятичные эквиваленты
        $fractions = [
            '½' => 0.5,
            '⅓' => 0,
            '⅔' => 0.5,
        ];

        // Проверяем, есть ли дробная часть
        foreach ($fractions as $key => $value) {
            if (strpos($str, $key) !== false) {
                $baseNumber = floatval(substr($str, 0, strpos($str, $key)));
                return $baseNumber + $value;
            }
        }
        return floatval($str);
    }

    /**
     * Calculate price.
     *
     * @param $initialPrice
     * @param bool $isDivide
     * @return array
     */
    public function calculatePrice($initialPrice, bool $isDivide = true): array
    {
        $lari = 0.395;
        $shipment = 32;
        $terminalCommission = 1.02;
        $vat = 1.19;

        $originalPriceInCNY = $isDivide ? $initialPrice / 100 : $initialPrice; // original price
        $originalPriceInLari = $originalPriceInCNY * $lari; // price in lari
        $priceWithShipment = $originalPriceInLari + $shipment; // price with shipment
        $price = $this->calculateCoefficient($priceWithShipment) * $priceWithShipment; // price with coefficient


        $price = $this->roundToNearest5or9((($price * $terminalCommission) * $vat)); // price with commissions
        $priceAfterTerminalCommission = $price - $price * $terminalCommission;
        $income = $price - ($priceAfterTerminalCommission * abs(1 - $vat)) + ($priceWithShipment * (1 - $vat - 0.01)) - ($originalPriceInLari + $shipment);

        return [
            "price" => $price,
            "originalPriceInCNY" => $originalPriceInCNY,
            "originalPriceInLari" => $originalPriceInLari,
            "originalPriceWithExpenses" => $priceWithShipment * ($vat - 0.01),
            "income" => $income
        ];
    }

    /**
     * Calculate coefficient.
     *
     * @param float $price
     * @return float
     */
    public function calculateCoefficient(float $price): float
    {
        $min_price = 50;
        $max_price = 999;
        $min_coefficient = 1.45;
        $max_coefficient = 1.2;

        if ($price < $min_price) {
            return $min_coefficient;
        }

        if ($price > $max_price) {
            return $max_coefficient;
        }

        // Используем линейную интерполяцию для плавного изменения коэффициента
        $coefficient = $min_coefficient + (($price - $min_price) / ($max_price - $min_price)) * ($max_coefficient - $min_coefficient);

        return $coefficient;
    }

    /**
     * Round to nearest 5 or 9.
     *
     * @param $number
     * @return int
     */
    public function roundToNearest5or9($number): int
    {
        // Округляем число в большую сторону
        $rounded = ceil($number);

        // Получаем последнюю цифру округленного числа
        $lastDigit = $rounded % 10;

        // Определяем, до какой цифры округлять
        if ($lastDigit < 5) {
            $newLastDigit = 5;
        } else {
            $newLastDigit = 9;
        }

        // Вычисляем, на сколько нужно изменить последнюю цифру
        $difference = $newLastDigit - $lastDigit;

        // Возвращаем число, округленное до ближайшего 5 или 9
        return $rounded + $difference;
    }
}
