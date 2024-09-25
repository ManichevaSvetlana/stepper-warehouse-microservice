<?php

namespace App\Console\Commands;

use App\Models\Poizon\PoizonProduct;
use App\Models\Poizon\PoizonShopProduct;
use App\Models\Shop\ShopProduct;
use App\Models\System\TrackProduct;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;

class UploadProductToShopFromPoizon extends Command
{
    /**
     * Categories mapping.
     *
     * @var array
     */
    public array $categoriesMapping = [
        'parent_is_stock' => 615754,
        'for_women' => 616369,
        'for_men' => 616370,
        'unisex' => 616371,
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poizon:upload-product-to-shop {--sku=} {--is_sku=1} {--sizes=} {--prices=} {--sale_prices=} {--presence=} {--stock=1}';
    // php artisan poizon:upload-product-to-shop --sku=ID5412 --sizes=36,37,38 --prices=100.56,200.99,300 --presence=1,1,1

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload product to shop from poizon';

    /**
     * Execute the console command.
     * @throws GuzzleException
     * @throws ConnectionException
     */
    public function handle()
    {
        $sku = $this->option('sku');
        $sizes = $this->option('sizes') ?? '';
        $sizes = explode(',', $sizes);
        $prices = $this->option('prices') ?? '';
        $prices = explode(',', $prices);
        $presence = $this->option('presence') ?? '';
        $presence = explode(',', $presence);
        $isStock = $this->option('stock') == 1;
        $isSku = $this->option('is_sku') == 1;
        $salePrices = $this->option('sale_prices') ?? '';
        $salePrices = explode(',', $salePrices);

        $poizonShop = new PoizonShopProduct();
        $poizon = new PoizonProduct();
        $command = new SyncSystemsProducts();

        if (!$sku) {
            echo('Please provide a SKU\n');
            return false;
        }
        echo("Uploading product with SKU: $sku\n");

        $product = PoizonShopProduct::where('sku', $sku)->orWhereJsonContains('data->article', $sku)->first();
        if (!$product) {
            $product = $poizonShop->getPoizonShopProductByArticle($sku, $isSku);
        }

        if (!$product) {
            echo('Product in poizonshop not found\n');
            $product = $poizon->getPoizonProductData($sku);
            $preparedProduct = $poizon->getShopFormatFromData($product, $this->categoriesMapping['parent_is_stock'], [615754], []);
        } else {
            $preparedProduct = $command->syncProductsForBitrix('poizon-shop', $product, 'shop', false, false);
        }

        $preparedProduct = collect($preparedProduct);
        $popularity = TrackProduct::where('sku', $sku)->max('type') ?? 10000;
        if($isStock) {
            $preparedVariations = [];
            foreach ($sizes as $size) {
                if ($preparedProduct->where('size', $size)->count() > 0) {
                    $preparedVariations[] = $preparedProduct->where('size', $size)->first();
                } else {
                    $tempProduct = $preparedProduct->first();
                    $tempProduct['size'] = $size;
                    $tempProduct['productSku'] = $tempProduct['productSku'] . $size;
                    $preparedVariations[] = $tempProduct;
                }
            }

            $preparedVariations = collect($preparedVariations)->map(function ($item, $index) use ($prices, $salePrices) {
                $tempSalePrice = $salePrices[$index] ?? 0;
                if (isset($tempSalePrice) && $tempSalePrice > 0) {
                    $item['price'] = $tempSalePrice;
                }
                else {
                    $item['price'] = $prices[$index] ?? $prices[0];
                }
                return $item;
            });

        } else {
            $preparedVariations = $preparedProduct->toArray();
        }

        if(!is_array($preparedVariations)) $preparedVariations = $preparedVariations->toArray();

        $shop = new ShopProduct();
        $shop->setShopAuth();

        $preparedVariations = $command->createOrUpdateInShop($shop, $preparedVariations, false, true, [], [], $popularity, true);

        if(count($preparedVariations) === 1 && $this->isArrayOfArrays($preparedVariations[0])) {
            $preparedVariations = $preparedVariations[0];
        }

        $uploadingProducts = [];
        foreach ($preparedVariations as $k => $variation) {
            if (isset($salePrices[$k])) {
                $variation['old_price'] = $prices[$k] ?? $prices[0] ?? $salePrices[$k];
            }
            if ($isStock) {
                if (isset($variation['sku'])) $variation['sku'] = str_replace('online-', '', $variation['sku']);
                if (isset($variation['parent_sku'])) {
                    $variation['sku'] = $variation['sku'] . '-stock';
                    $variation['parent_sku'] = str_replace('online-', '', $variation['parent_sku']);
                }
                if (isset($variation['barcode'])) $variation['barcode'] = str_replace('online-', '', $variation['barcode']);
                if (isset($variation['presence'])) $variation['presence'] = $presence[$k] ?? 1;
                if (isset($variation['ID_759629'])) unset($variation['ID_759629']);
                if (isset($variation['parent'])) {
                    $parents = [];
                    $parentIds = array_values($this->categoriesMapping);
                    foreach ($parentIds as $parentId) {
                        $parents[] = [
                            "id" => $parentId
                        ];
                    }

                    $variation['parent'] = $parents;
                }

                if(isset($variation['characteristics'])) {
                    if (isset($variation['characteristics']['ID_759629'])) unset($variation['characteristics']['ID_759629']);
                    if (isset($variation['characteristics']['ID_759681'])) unset($variation['characteristics']['ID_759681']);
                }
            }

            $uploadingProducts[] = $variation;
        }

        echo("Product is uploading to shop...\n");
        $response = $shop->createShopProducts($uploadingProducts);
        echo("Product uploaded to shop\n");

        return $response;
    }

    public function isArrayOfArrays($array): bool
    {
        if (!is_array($array)) {
            return false;
        }

        foreach ($array as $element) {
            if (!is_array($element)) {
                return false;
            }
        }

        return true;
    }
}
