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
    protected $signature = 'shop:update-shop-data {--section=all}';

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
        $section = $this->option('section');

        $feature = new \App\Models\Feature();
        echo 'Setting shop auth' . "\n";
        $feature->setShopAuth();
        if ($section === 'all' || $section === 'brands' || $section === 'features') {
            echo 'Updating shop brands' . "\n";
            $feature->updateListOfLocalShopBrands();
        }
        // Обновление категорий
        if ($section === 'all' || $section === 'categories' || $section === 'features') {
            echo 'Updating shop categories' . "\n";
            $feature->updateListOfLocalShopCategories();
        }

        // Обновление характеристик
        if ($section === 'all' || $section === 'characteristics' || $section === 'features') {
            echo 'Updating shop characteristics' . "\n";
            $feature->updateListOfLocalShopCharacteristics();
        }

        // Обновление наклеек
        if ($section === 'all' || $section === 'stickers' || $section === 'features') {
            echo 'Updating shop stickers' . "\n";
            $feature->updateListOfLocalShopStickers();
        }

        if ($section === 'all' || $section === 'products') {
            echo 'Updating shop products' . "\n";
            $product = new \App\Models\Shop\ShopProduct();
            $product->setShopAuth();

            $ids = [];
            $page = 1;
            do {
                $products = $product->listShopProducts(50, $page);
                foreach ($products as $productModel) {
                    $ids[] = $productModel['id'];
                    echo 'Update / create product: #' . $productModel['id'] . "\n";
                    ShopProduct::updateOrCreate(
                        ['system_id' => $productModel['id']],
                        ['data' => $productModel, 'sku' => $productModel['sku']]
                    );
                }
                $page++;
            } while (!empty($products));

            ShopProduct::whereNotIn('system_id', $ids)->delete();
        }
    }
}
