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
     * @var string // sync:systems-products --system=poizon-shop --section=shop
     */
    protected $signature = 'sync:systems-products {--system=poizon} {--section=all} {--sku=} {--images=1}';

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
        /*'canvas' => 615748,
        'vintage_basketball' => 615749,
        'sport' => 615750,
        'daddy' => 615751,
        'sneakers' => 615752,
        'slippers' => 615753,
        'women' => 607326,
        'men' => 607327,*/

        'canvas' => 615761,
        'vintage_basketball' => 615764,
        'running' => 615760,
        'sport' => 615760,
        'daddy' => 615762,
        'sneakers' => 615763,
        'slippers' => 615758,
        'women' => 615756,
        'men' => 615757,
        'unisex' => 616001,

        'parentSneakers' => 615759
    ];

    /**
     * Categories mapping.
     *
     * @var array
     */
    protected array $categoriesFilterMapping = [
        'canvas' => 4646923,
        'vintage_basketball' => 4646922,
        'running' => 4646929,
        'sport' => 4646929,
        'daddy' => 4646926,
        'sneakers' => 4646928,
        'slippers' => 4646927,
        'women' => 4646925,
        'men' => 4646924,
    ];

    /**
     * Categories mapping.
     *
     * @var array
     */
    private array $deliveryDays = [
        /*"ID_563599" => [
            [
                "id" => 4054579,
                "value" => [
                    "en" => "Delivery from 10 days"
                ],
            ]
        ]*/
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
    //private int $catalogId = 487155;
    private int $catalogId = 0;

    /**
     * Category filter ID.
     *
     * @var int
     */
    private int $categoryFilterId = 760721;

    /**
     * Delivery ID.
     *
     * @var int
     */
    //private int $deliveryId = 607325;
    private int $deliveryId = 615677;

    /**
     * Catalog modification ID.
     *
     * @var int
     */
    //private int $catalogModificationId = 58481;
    private int $catalogModificationId = 87201;

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

        $poizonProducts = $systemName === 'poizon-shop' ? PoizonShopProduct::all() : PoizonProduct::all();
        $failedProductsShop = [];
        $startProcessing = false;

        foreach ($poizonProducts as $poizonProduct) {

            if ($startSku) {
                if (!$startProcessing && $poizonProduct->sku !== $startSku) {
                    continue;
                }

                $startProcessing = true;
            }


            $syncedProductsForShop = $this->syncProductsForBitrix($systemName, $poizonProduct, $section);

            if ($section === 'all' || $section === 'shop') {
                $shop = new ShopProduct();
                $shop->setShopAuth();

                try {
                    $this->createOrUpdateInShop($shop, $syncedProductsForShop, $withoutImages);
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

        $count = count($failedProductsShop);
        echo "Failed products for shop: $count \n";
    }

    /**
     * Sync products for Bitrix.
     *
     * @param string $systemName
     * @param PoizonProduct|PoizonShopProduct $poizonProduct
     * @param string $section
     * @return array
     * @throws GuzzleException
     */
    private function syncProductsForBitrix(string $systemName, mixed $poizonProduct, string $section): array
    {
        if ($systemName !== 'poizon-shop') {
            echo "Product Poizon: {$poizonProduct->sku}\n";
            echo "Product Poizon: {$poizonProduct->data['detail']['title']}\n";

            $sizesPropertiesList = collect($poizonProduct->data['saleProperties']['list']);
            $skus = collect($poizonProduct->data['skus']);

            $syncedProductsForShop = [];
            foreach ($poizonProduct->prices as $sku => $priceModel) {
                try {
                    $syncedProduct = $this->prepareProduct($skus, $sku, $sizesPropertiesList, $priceModel, $poizonProduct);
                    $syncedProductsForShop[] = $syncedProduct;
                    if ($section === 'all' || $section === 'bitrix') $this->createOrUpdateProductInBitrix($syncedProduct);
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

            foreach ($prices as $priceModel) {
                if ($priceModel['cnyPrice'] < 5 || !($priceModel['size']['primary'] ?? false)) continue;
                try {
                    $syncedProduct = $this->prepareProductFromPoizonShop($poizonProduct, $priceModel);
                    $syncedProductsForShop[] = $syncedProduct;
                    if ($section === 'all' || $section === 'bitrix') $this->createOrUpdateProductInBitrix($syncedProduct);
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

    private function removeColumn($html, $columnName) {
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
     */
    private function createOrUpdateInShop(ShopProduct $shop, array $products, bool $withoutImages = false): void
    {
        $catalog = Feature::where('type', 'characteristic')->where('system', 'shop')->whereRaw("json_extract(data, '$.title.en') = 'Catalog'")->first();
        $characteristicsData = collect($catalog->data['characteristics']);
        $size = $characteristicsData->firstWhere('slug', 'size');
        $color = $characteristicsData->firstWhere('slug', 'color-1');
        if (!$size) {
            echo "Size feature not found\n";
            return;
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
            '红色,黄色' => 'red',      // Красный
            '白色,红色' => 'red',      // Красный
            '蓝色' => 'blue',     // Синий
            '白色,蓝色' => 'blue',     // Синий
            '绿色' => 'green',    // Зеленый
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

        foreach ($groupedBySku as $group) {
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
            $images = ShopProduct::whereRaw("json_extract(data, '$.barcode') = '$productSku'")->exists() || $withoutImages ? [] : $group[0]['images'];
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

            foreach ($group as $key => $product) {
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
                                "en" => $sizeValue
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

                $preparedProducts[] = $this->getVariationProductForShop($sizeValue, $product['name'], $article, $product['sku'], $product['productSku'], $product['price'], $characteristics);
            }

            $brandId = $brand?->system_id;
            $title = $group[0]['name'];
            $parentsIds = [];
            if($this->catalogId) $parentsIds[] = $this->catalogId;
            $parentsIds[] = $this->deliveryId;

            if ($group[0]['categoryIds']) {
                $parentsIds = array_merge($parentsIds, $group[0]['categoryIds']);
            }

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

            $productsData = [
                ...$this->getParentProductForShop($title, $productSku, $brandId, $parentsIds, $article, $images, [], $sizesTable),
                ...$preparedProducts,
            ];
            echo 'Creating product with variations in shop: ' . $title . PHP_EOL;
            if (count($preparedProducts)) $response = $shop->createShopProducts($productsData);
            else {
                $productsData[0]['presence'] = false;
                $productsData[0]["availability"] = "Unpublish";
                $response = $shop->createShopProducts($productsData);
            }
        }
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
     * @return array
     * */
    private function getVariationProductForShop(string $title, string $parentTitle, string $articleNumber, string $parentSku, string $sku, mixed $price, array $characteristics = []): array
    {
        return [
            "title" => $title,
            "parent_title" => $parentTitle,
            "characteristics_mode" => "Reset",
            "sku" => $sku,
            "parent_sku" => $articleNumber,
            "barcode" => $parentSku,
            "currency" => "GEL",
            "price" => $price,
            "presence" => $price > 999 ? 0 : 9999,
            "force_alias_update" => "1",
            "availability" => "Publish",
            "characteristics" => $characteristics,
            "modification" => [
                "id" => $this->catalogModificationId,
                "value" => [
                    "en" => "Catalog"
                ],
            ]
        ];
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
     * @return array
     */
    private function getParentProductForShop(string $title, string $sku, int $brandId, array $parentIds, string $articleNumber, array $images = [], array $stickerIds = [], string $sizesTable = null): array
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
        return [
            [
                "title" => $title,
                "sku" => $articleNumber,
                "presence" => "true",
                "images" => [
                    "links" => [
                        ...$images
                    ]
                ],
                "barcode" => $sku,
                "availability" => "Publish",
                "parent" => $parents,
                "brand" => [
                    "id" => $brandId
                ],
                "stickers" => $stickers,
                "description" => [
                    "en" => $sizesTable ?? ''
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
    private function formatNumber(string $numberString): string
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
     * @return void
     *
     * @throws GuzzleException
     */
    private function createOrUpdateProductInBitrix(array $product): void
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
            'images' => [
                $product['images'][0]
            ]
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
    private function prepareProduct($skus, $sku, $sizesPropertiesList, $priceModel, $poizonProduct): array
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
        if ($brand === 'adidas originals') {
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
    private function mapWithShopCategories($category): array
    {
        $categoryPath = explode('/', $category["category3"]);
        $lastCategory = end($categoryPath);
        $mappedValue = [];
        $mappedFilterValue = [];
        $response = [];

        if (stripos($category["category3"], 'slippers') !== false) {
            $mappedValue[] = $this->categoriesMapping['slippers'];
            $mappedFilterValue[] = $this->categoriesFilterMapping['slippers'];
        } else {
            $val = $this->categoriesMapping[$lastCategory] ?? null;
            if($val) $mappedValue[] = $val;

            $val = $this->categoriesFilterMapping[$lastCategory] ?? null;
            if($val) $mappedFilterValue[] = $val;

            $mappedValue[] = $this->categoriesMapping['parentSneakers'];
        }

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
    private function prepareProductFromPoizonShop($poizonProduct, $priceModel): array
    {
        echo "SKU: {$priceModel['skuId']}\n";
        $name = preg_replace('/[\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{20000}-\x{2A6DF}]+/u', '', $poizonProduct->data['name']);
        $price = $this->calculatePrice($priceModel['cnyPrice'], false);
        $size = $this->parseFraction($priceModel['size']['primary']);

        $brand = $poizonProduct->data['brand'];
        if ($brand === 'adidas originals') {
            $brand = 'adidas';
        }

        $findCategoryIds = [];
        $findCategoryFilterIds = [];
        $poizonProductCategories = $poizonProduct->data['category'];
        if ($poizonProductCategories && $poizonProductCategories['category3'] ?? false) {
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
                    $findCategoryIds[] = $this->categoriesMapping['women'];
                    $findCategoryFilterIds[] = $this->categoriesFilterMapping['women'];
                } else if ($fit === "MALE") {
                    $findCategoryIds[] = $this->categoriesMapping['men'];
                    $findCategoryFilterIds[] = $this->categoriesFilterMapping['men'];
                } else {
                    $findCategoryIds[] = $this->categoriesMapping['women'];
                    $findCategoryFilterIds[] = $this->categoriesFilterMapping['women'];
                    $findCategoryIds[] = $this->categoriesMapping['men'];
                    $findCategoryFilterIds[] = $this->categoriesFilterMapping['men'];
                    $findCategoryIds[] = $this->categoriesMapping['unisex'];
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
    private function createSizeTableForShop(array $sizeTable): string
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
    private function parseFraction(string $str): float
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
    private function calculatePrice($initialPrice, bool $isDivide = true): array
    {
        $lari = 0.37;
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
    private function calculateCoefficient(float $price): float
    {
        $min_price = 50;
        $max_price = 800;
        $min_coefficient = 1.4;
        $max_coefficient = 1.15;

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
    private function roundToNearest5or9($number): int
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
