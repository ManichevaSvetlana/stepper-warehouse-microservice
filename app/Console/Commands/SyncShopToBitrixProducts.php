<?php

namespace App\Console\Commands;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

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
     * Execute the console command.
     * @throws GuzzleException
     */
    public function handle()
    {
        $shopProducts = \App\Models\Shop\ShopProduct::whereRaw("json_extract(data, '$.presence') > 0")->get();
        $bitrix = new \App\Models\Bitrix\BitrixProduct();
        foreach ($shopProducts as $shopProduct) {
            $size =  $shopData['characteristics']['ID_629285'][0]['value']['en'] ?? null;
            echo "SKU shop product - : {$shopProduct->system_id}\n";
            echo "Size shop product - : {$size}\n";

            $shopData = $shopProduct->data;
            $data = [
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
            }
        }
    }
}
