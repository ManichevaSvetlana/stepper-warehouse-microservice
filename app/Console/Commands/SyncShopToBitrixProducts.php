<?php

namespace App\Console\Commands;

use App\Models\Feature;
use App\Models\Shop\ShopProduct;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncShopToBitrixProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:shop-to-bitrix-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync shop products to Bitrix products';

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
        $shopProducts = \App\Models\Shop\ShopProduct::whereRaw("json_extract(data, '$.presence') > 0")->get();
        $shopProducts = $shopProducts->groupBy(function ($item) {
            return $item->data['parent_id'];
        });
        $bitrix = new \App\Models\Bitrix\BitrixProduct();
        $shop = new ShopProduct();
        $shop->setShopAuth();

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

        foreach ($shopProducts as $group) {
            $productBrand = $group[0]->data['brand']['value']['en'];
            $brand = Feature::where('type', 'brand')->where('system', 'shop')->whereRaw("json_extract(data, '$.title.en') = '$productBrand'")->first();
            $brandId = $brand?->system_id;
            $parentsIds = [616369, 616370, 616371, 615754];

            $parentProduct = $this->getParentProductForShop($group[0]->data["parent_title"]["en"], $group[0]->data["sku"], $brandId, $parentsIds, $group[0]->data["sku"], $group[0]->data["images"]);
            $variations = [];
            foreach ($group as $shopProduct) {
                $hasId = collect($shopProduct->data["parent"])->contains('id', 607322);
                if (!$hasId) {
                    continue;
                }
                $sizevalue = $shopData['characteristics']['ID_629285'][0]['value']['en'] ?? null;
                echo "SKU shop product - : {$shopProduct->system_id}\n";
                echo "Size shop product - : {$sizevalue}\n";

                $shopData = $shopProduct->data;
                /*$data = [
                    'sku' => $shopData['sku'],
                    'name' => $shopData['parent_title']['en'] . '-' . $shopData['title']['en'],
                    'price' => $shopData['price'],
                    'originalPriceInLari' => 0,
                    'originalPriceWithExpenses' => 0,
                    'income' => 0,
                    'size' => $size,
                    'brand' => $shopData['brand']['value']['en'],
                    'articleNumber' => $shopData['sku'],
                    'productSku' => $shopData['sku'],
                    'images' => [
                        $shopData['images'][0]
                    ],
                    'shopId' => $shopProduct->system_id,
                    'quantity' => $shopData['presence'],
                ];

                $bitrixProduct = \App\Models\Bitrix\BitrixProduct::whereRaw("json_extract(data, '$.property136') = '{$shopProduct->system_id}'")->get();
                if ($bitrixProduct->isEmpty()) {
                    $bitrix->addProductToBitrix($data, false, false);
                } else {
                    $bitrix->addProductToBitrix($data, true, false);
                }*/

                $sizeValue = $shopProduct->data["characteristics"]["ID_629285"][0]["value"]["en"];
                $colorValue = $shopProduct->data["characteristics"]["ID_563598"][0]["value"]["en"];

                $sizeId = collect($sizes)->first(function ($item) use ($sizeValue) {
                    return $item['title']['en'] === $sizeValue;
                });
                if (!$sizeId) {
                    echo "Size {$sizeValue} not found\n";
                    continue;
                }

                $colorId = collect($colors)->first(function ($item) use ($colorValue) {
                    return $item['title']['en'] === $colorValue;
                });
                if (!$colorId) {
                    echo "COlor {$colorValue} not found\n";
                    continue;
                }

                $characteristics = [
                    "ID_759629" => [
                        [
                            "id" => 4644056,
                            "value" => [
                                "en" => "Delivery in 2 hours"
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
                    "ID_{$color['id']}" => [
                        [
                            "id" => $colorId['id'] ?? null,
                            "value" => [
                                "en" => $colorValue
                            ],
                        ]
                    ],
                    "ID_760721" => [
                        [
                            "id" => 4646924,
                            "value" => [
                                "en" => "For men"
                            ],
                        ],
                        [
                            "id" => 4646925,
                            "value" => [
                                "en" => "For women"
                            ],
                        ],
                    ],
                ];
                // string $title, string $parentTitle, string $articleNumber, string $parentSku, string $sku, mixed $price, array $characteristics = []
                $variations[] = $this->getVariationProductForShop($shopProduct->data["title"]["en"], $shopProduct->data["parent_title"]["en"], $shopProduct->data["sku"], $shopProduct->data["sku"], rand(1000, 569864),
                    $shopProduct->data["price"], $characteristics, $shopProduct->data["presence"]);
            }

            if(count($variations)) {
                $response = $shop->createShopProducts([
                    ...$parentProduct,
                    ...$variations
                ]);
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
    private function getVariationProductForShop(string $title, string $parentTitle, string $articleNumber, string $parentSku, string $sku, mixed $price, array $characteristics = [], $presence = 1): array
    {
        return [
            "title" => $title,
            "parent_title" => $parentTitle,
            //"characteristics_mode" => "Reset",
            "sku" => $sku,
            "parent_sku" => $parentSku,
            //"barcode" => $parentSku,
            "currency" => "GEL",
            "price" => $price,
            "presence" => $presence,
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
}
