<?php

namespace App\Console\Commands;

use App\Models\Shop\ShopProduct;
use Illuminate\Console\Command;

class UpdateShopData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:update-shop-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shop: update all related to a shop data.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $feature = new \App\Models\Feature();
        echo 'Setting shop auth' . "\n";
        $feature->setShopAuth();
        echo 'Updating shop brands' . "\n";
        $feature->updateListOfLocalShopBrands();
        echo 'Updating shop categories' . "\n";
        $feature->updateListOfLocalShopCategories();
        echo 'Updating shop characteristics' . "\n";
        $feature->updateListOfLocalShopCharacteristics();
        echo 'Updating shop stickers' . "\n";
        $feature->updateListOfLocalShopStickers();

        echo 'Updating shop products' . "\n";
        $product = new \App\Models\Shop\ShopProduct();
        $product->setShopAuth();

        $page = 1;
        do {
            $products = $product->listShopProducts(50, $page);
            foreach ($products as $productModel) {
                echo 'Update / create product: #' . $productModel['id'] . "\n";
                ShopProduct::updateOrCreate(
                    ['system_id' => $productModel['id']],
                    ['data' => $productModel, 'sku' => $productModel['sku'], 'parent_sku' => $productModel['parent_sku']]
                );
            }
            $page++;
        } while (!empty($products));
    }
}
