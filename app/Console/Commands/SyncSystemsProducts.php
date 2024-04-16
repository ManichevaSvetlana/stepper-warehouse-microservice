<?php

namespace App\Console\Commands;

use App\Models\Bitrix\BitrixProduct;
use App\Models\Poizon\PoizonProduct;
use App\Models\Shop\ShopProduct;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

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
        }
    }

    private function createOrUpdateInShop(ShopProduct $shop, array $products): void
    {

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

        $cleanTitle = preg_replace('/[\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{20000}-\x{2A6DF}]+/u', '', $product['name']);
        $data = [
            'sku' => $product['sku'],
            'name' => $cleanTitle,
            'price' => $product['price'] / 100,
            'size' => $product['size'],
            'brand' => $product['brand'],
            'articleNumber' => $product['articleNumber'],
            'productSku' => $product['productSku'],
            'images' => [
                $product['images'][0]
            ]
        ];

        if(BitrixProduct::where('sku', $product['sku'])->exists()) {
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
        $price = $priceModel['prices'][0]['price'];
        echo "SKU: {$sku} - Size: {$value} - Price: {$price}\n";


        $syncedProduct = [
            'sku' => $poizonProduct->sku,
            'productSku' => $sku,
            'name' => $poizonProduct->data['detail']['title'],
            'size' => $value,
            'price' => $price,
            'images' => collect($poizonProduct->data['image']['spuImage']['images'])->pluck('url')->toArray(),
            'brand' => $poizonProduct->data['brandRootInfo']['brandItemList'][0]['brandName'],
            'category' => $poizonProduct->data['detail']['categoryId'],
            'articleNumber' => $poizonProduct->data['detail']['articleNumber'],
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
}
