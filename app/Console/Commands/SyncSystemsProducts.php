<?php

namespace App\Console\Commands;

use App\Models\Bitrix\BitrixProduct;
use App\Models\Feature;
use App\Models\Poizon\PoizonProduct;
use App\Models\Shop\ShopProduct;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncSystemsProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:systems-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync systems products.';

    /**
     * Execute the console command.
     * @throws GuzzleException
     */
    public function handle()
    {
        Artisan::call('shop:update-shop-data');
        Artisan::call('bitrix:update-bitrix-data');
        //Artisan::call('poizon:update-poizon-data');



        $poizonProducts = PoizonProduct::all();

        foreach ($poizonProducts as $poizonProduct) {
            echo "Product: {$poizonProduct->sku}\n";
            echo "Product: {$poizonProduct->data['detail']['title']}\n";
            $sizesPropertiesList = collect($poizonProduct->data['saleProperties']['list']);
            $skus = collect($poizonProduct->data['skus']);
            $shop = new ShopProduct();
            $shop->setShopAuth();
            $syncedProductsForShop = [];
            foreach ($poizonProduct->prices as $sku => $priceModel) {
                $syncedProduct = $this->prepareProduct($skus, $sku, $sizesPropertiesList, $priceModel, $poizonProduct);
                $syncedProductsForShop[] = $syncedProduct;
                $this->createOrUpdateProductInBitrix($syncedProduct);
            }

            $this->createOrUpdateInShop($shop, $syncedProductsForShop);
        }
    }

    /**
     * Create or update product in Shop.
     *
     * @param ShopProduct $shop
     * @param array $products
     * @throws GuzzleException
     */
    private function createOrUpdateInShop(ShopProduct $shop, array $products): void
    {
        $catalog = Feature::where('type', 'characteristic')->where('system', 'shop')->whereRaw("json_extract(data, '$.title.en') = 'Catalog'")->first();
        $characteristicsData = collect($catalog->data['characteristics']);
        $size = $characteristicsData->firstWhere('slug', 'size-1');
        $color = $characteristicsData->firstWhere('slug', 'color');
        if (!$size) {
            echo "Size feature not found\n";
            return;
        }
        $sizes = $size['values'];
        $colors = $color['values'];

        $colorsChinese = [
            '黑色' => 'black',    // Черный
            '白色' => 'white',    // Белый
            '红色' => 'red',      // Красный
            '蓝色' => 'blue',     // Синий
            '绿色' => 'green',    // Зеленый
            '黄色' => 'yellow',   // Желтый
            '橙色' => 'orange',   // Оранжевый
            '紫色' => 'purple',   // Фиолетовый
            '褐色' => 'brown',    // Коричневый
            '灰色' => 'gray',     // Серый
            '粉红色' => 'pink',   // Розовый
            '青色' => 'cyan',     // Голубой
            '银色' => 'silver'    // Серебряный
        ];

        $groupedBySku = collect($products)->groupBy('sku');

        foreach ($groupedBySku as $group) {
            $preparedProducts = [];
            $productBrand = ucwords(strtolower($group[0]['brand']));
            $brand = Feature::where('type', 'brand')->where('system', 'shop')->whereRaw("json_extract(data, '$.title.en') = '$productBrand'")->first();
            $productSku = $group[0]['sku'];
            $images = ShopProduct::whereRaw("json_extract(data, '$.barcode') = '$productSku'")->exists() ? [] : $group[0]['images'];
            echo "Product SKU: {$productSku}\n";
            echo count($images) ? "Creating product with images\n" : "Updating product without images\n";

            $article = 'online-' . $group[0]['articleNumber'];
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
                    "ID_563599" => [
                        [
                            "id" => 4054579,
                            "value" => [
                                "en" => "Delivery from 7 days"
                            ],
                        ]
                    ],
                    "ID_{$size['id']}" => [
                        [
                            "id" => $sizeId['id'] ?? null,
                            "value" => [
                                "en" => $sizeValue
                            ],
                        ]
                    ],
                ];
                if ($colors) {
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
                }

                $preparedProducts[] = $this->getVariationProductForShop($sizeValue, $product['name'], $article, $product['sku'], $product['productSku'], $product['price'], $characteristics);
            }

            $brandId = $brand?->system_id;
            $title = $group[0]['name'];
            $parentsIds = [487155, 487157, 487168];

            $productsData = [
               ...$this->getParentProductForShop($title, $productSku, $brandId, $parentsIds, $article, $images),
                ...$preparedProducts,
            ];
            echo 'Creating product with variations in shop: ' . $title . PHP_EOL;
            $response = $shop->createShopProducts($productsData);
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
    private function getVariationProductForShop(string $title, string $parentTitle, string $articleNumber, string $parentSku, string $sku, mixed $price, array $characteristics = []) : array
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
            "presence" => "false",
            "force_alias_update" => "1",
            "availability" => "Unpublish",
            "characteristics" => $characteristics,
            "modification" => [
                "id" => 58481,
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
     * @return array
     */
    private function getParentProductForShop(string $title, string $sku, int $brandId, array $parentIds, string $articleNumber, array $images = [], array $stickerIds = []) : array
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
                "presence" => "false",
                "images" => [
                    "links" => [
                        ...$images
                    ]
                ],
                "barcode" => $sku,
                "availability" => "Unpublish",
                "parent" => $parents,
                "brand" => [
                    "id" => $brandId
                ],
                "stickers" => $stickers,
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
            'name' => $product['name'],
            'price' => $product['price'],
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
        $propertyValueId = collect($neededSku['properties'])->sortByDesc('level')->first()['propertyValueId'];
        $property = $sizesPropertiesList->firstWhere('propertyValueId', $propertyValueId);
        $value = $this->parseFraction($property['value']);
        $price = $this->calculatePrice($priceModel['prices'][0]['price']);
        echo "SKU: {$sku} - Size: {$value} - Price: {$price['price']}\n";

        $color = collect($poizonProduct->data['basicParam']['basicList'])->firstWhere('key', '主色');


        $syncedProduct = [
            'sku' => $poizonProduct->sku,
            'productSku' => $sku,
            'name' => preg_replace('/[\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{20000}-\x{2A6DF}]+/u', '', $poizonProduct->data['detail']['title']),
            'size' => $value,
            'price' => $price['price'],
            'originalPriceInLari' => $price['originalPriceInLari'],
            'originalPriceWithExpenses' => $price['originalPriceWithExpenses'],
            'income' => $price['income'],
            'images' => collect($poizonProduct->data['image']['spuImage']['images'])->pluck('url')->toArray(),
            'brand' => $poizonProduct->data['brandRootInfo']['brandItemList'][0]['brandName'],
            'category' => $poizonProduct->data['detail']['categoryId'],
            'articleNumber' => $poizonProduct->data['detail']['articleNumber'],
            'colorName' => $color['value'] ?? null,
            'colorId' => $color['propertyValueId'] ?? null,
        ];

        return $syncedProduct;
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
     * @return array
     */
    private function calculatePrice($initialPrice) : array
    {
        $lari = 0.37;
        $shipment = 32;
        $terminalCommission = 1.02;
        $vat = 1.19;

        $upTo50 = 1.9;
        $upTo100 = 1.8;
        $upTo200 = 1.6;
        $upTo300 = 1.5;
        $upTo400 = 1.45;
        $upTo500 = 1.4;
        $upTo600 = 1.35;
        $after600 = 1.25;

        $originalPriceInLari = ($initialPrice / 100) * $lari; // price in lari
        $price = $originalPriceInLari + $shipment; // price with shipment
        if($price <= 50)
            $price *= $upTo50;
        elseif($price <= 100)
            $price *= $upTo100;
        elseif($price <= 200)
            $price *= $upTo200;
        elseif($price <= 300)
            $price *= $upTo300;
        elseif($price <= 400)
            $price *= $upTo400;
        elseif($price <= 500)
            $price *= $upTo500;
        elseif($price <= 600)
            $price *= $upTo600;
        else
            $price *= $after600;

        $price = $this->roundToNearest5or9(($price * $terminalCommission) * $vat); // price with commissions
        $income = $price + ($price * (1 - $vat)) + ($price * (1 - $terminalCommission)) + ($originalPriceInLari * (1 - $vat - 0.01)) - $originalPriceInLari;

        return [
            "price" => $price,
            "originalPriceInLari" => $originalPriceInLari,
            "originalPriceWithExpenses" => ($originalPriceInLari) * ($vat - 0.01) + $shipment,
            "income" => $income
        ];
    }

    /**
     * Round to nearest 5 or 9.
     *
     * @param $number
     * @return int
     */
    private function roundToNearest5or9($number) : int {
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
