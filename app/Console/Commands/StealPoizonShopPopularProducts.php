<?php

namespace App\Console\Commands;

use App\Models\Poizon\PoizonShopProduct;
use App\Models\System\TrackProduct;
use Illuminate\Console\Command;

class StealPoizonShopPopularProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poizon:steal-poizon-shop-popular-products {--pages=10} {--category=sneakers} {--popularity=3000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Steal Poizon Shop popular products.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo "Steal Poizon Shop popular products.\n";
        $pages = $this->option('pages');
        $popularity = $this->option('popularity');
        $category = $this->option('category');

        $poizon = new PoizonShopProduct();
        for ($i = 1; $i <= $pages; $i++) {
            echo "Page: $i\n";
            $products = $poizon->getPoizonShopPopularProducts($i, $category);
            foreach ($products as $product) {
                $sku = $product['spuId'];
                $name = $product['name'];
                echo "Product: $sku - $name\n";
                $existingTrackProduct = TrackProduct::where('sku', $sku)->where('system', 'poizon-shop')->exists();
                $popularityPoint = $popularity - $i;
                if(!$existingTrackProduct) {
                    TrackProduct::create([
                        'sku' => $sku,
                        'system' => 'poizon-shop',
                        'type' => $popularityPoint,
                    ]);
                }
            }
        }

        TrackProduct::chunk(100, function ($tracks) {
            foreach ($tracks as $track) {
                PoizonShopProduct::where('sku', $track->sku)->update(['popularity' => $track->type]);
            }
        });
    }
}
