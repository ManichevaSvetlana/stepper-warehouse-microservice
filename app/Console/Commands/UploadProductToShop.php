<?php

namespace App\Console\Commands;

use App\Models\Poizon\PoizonShopProduct;
use App\Models\Shop\ShopProduct;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class UploadProductToShop extends Command
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
    protected $signature = 'poizon:upload-product-to-shop {--sku=} {--sizes=} {--prices=} {--presence=} {--stock=1}';
    // php artisan poizon:upload-product-to-shop --sku=ID5412 --sizes=36,37,38 --prices=100.56,200.99,300 --presence=1,1,1

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload product to shop';

    /**
     * Execute the console command.
     * @throws GuzzleException
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

        $poizonShop = new PoizonShopProduct();

        if (!$sku) {
            $this->error('Please provide a SKU');
            return false;
        }
        $this->info("Uploading product with SKU: $sku");

        $product = PoizonShopProduct::where('sku', $sku)->orWhereJsonContains('data->article', $sku)->first();
        if (!$product) {
            $product = $poizonShop->getPoizonShopProductByArticle($sku);
            if (!$product) {
                $this->error('Product not found');
                return false;
            }
        }

        $command = new SyncSystemsProducts();

        $preparedProduct = collect($command->syncProductsForBitrix('poizon-shop', $product, 'shop'));

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
        } else {
            $preparedVariations = $preparedProduct->toArray();
        }

        $preparedVariations = collect($preparedVariations)->map(function ($item, $index) use ($prices) {
            if (isset($prices[$index])) {
                $item['price'] = $prices[$index];
            }
            return $item;
        });
        $preparedVariations = $preparedVariations->toArray();

        $shop = new ShopProduct();
        $shop->setShopAuth();

        $preparedVariations = $command->createOrUpdateInShop($shop, $preparedVariations, false, true, true)[0];

        $uploadingProducts = [];
        foreach ($preparedVariations as $k => $variation) {
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
            }

            $uploadingProducts[] = $variation;
        }

        echo('Product is uploading to shop...');
        $response = $shop->createShopProducts($uploadingProducts);
        echo('Product uploaded to shop');

        return $response;
    }
}
