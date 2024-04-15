<?php

namespace App\Console\Commands;

use App\Models\Bitrix\BitrixProduct;
use App\Models\Poizon\PoizonProduct;
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
     */
    public function handle()
    {
        $poizonProducts = PoizonProduct::all();

        foreach ($poizonProducts as $poizonProduct) {
            echo "Product: {$poizonProduct->sku}\n";
            echo "Product: {$poizonProduct->data['detail']['title']}\n";
            $sizesPropertiesList = collect($poizonProduct->data['saleProperties']['list']);
            $skus = collect($poizonProduct->data['skus']);
            foreach ($poizonProduct->prices as $sku => $priceModel) {
                $syncedProduct = $this->prepareProduct($skus, $sku, $sizesPropertiesList, $priceModel, $poizonProduct);
                $this->createOrUpdateProductInBitrix($syncedProduct);
            }
        }
    }

    private function createOrUpdateProductInBitrix($product) {
        $bitrix = new BitrixProduct();

        $cleanTitle = preg_replace('/[\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{20000}-\x{2A6DF}]+/u', '', $product['name']);
        $data = [
            'sku' => $product['sku'],
            'name' => $cleanTitle,
            'quantity' => 9999,
            'price' => $product['price'] / 100,
            'size' => $product['size'],
            'brand' => $product['brand'],
            'articleNumber' => $product['articleNumber'],
            'images' => [
                'value' => [
                    'url' => $product['images'][0]
                ]
            ]
        ];

        if(BitrixProduct::where('sku', $product['sku'])->exists()) {
            echo "Update product in Bitrix\n";
            $existingProductId = BitrixProduct::where('sku', $product['sku'])->first()->id;
            $data['id'] = $existingProductId;
            $bitrix->addProductToBitrix($data);
        } else {
            echo "Create product in Bitrix\n";
            $bitrix->addProductToBitrix($data);
        }
    }

    private function prepareProduct($skus, $sku, $sizesPropertiesList, $priceModel, $poizonProduct) {
        echo "SKU: {$sku}\n";
        $neededSku = $skus->firstWhere('skuId', $sku);
        $propertyValueId = collect($neededSku['properties'])->sortByDesc('level')->first()['propertyValueId'];
        $property = $sizesPropertiesList->firstWhere('propertyValueId', $propertyValueId);
        $value = $this->parseFraction($property['value']);
        $price = $priceModel['prices'][0]['price'];
        echo "SKU: {$sku} - Size: {$value} - Price: {$price}\n";


        $syncedProduct = [
            'sku' => $poizonProduct->sku,
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

    private function parseFraction($str) {
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
