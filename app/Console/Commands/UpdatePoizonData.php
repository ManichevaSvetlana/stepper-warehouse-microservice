<?php

namespace App\Console\Commands;

use App\Models\Poizon\PoizonProduct;
use App\Models\System\TrackProduct;
use Illuminate\Console\Command;

class UpdatePoizonData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poizon:update-poizon-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poizon: update local poizon products data.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo "Poizon: update local poizon products data.\n";
        $poizon = new PoizonProduct();
        $trackSkus = TrackProduct::all()->pluck('sku')->toArray();
        foreach ($trackSkus as $track) {
            echo "SKU track product - : {$track}\n";

            try {
                $existingProduct = PoizonProduct::where('sku', $track)->first();

                if($existingProduct) $data = $existingProduct->data;
                else $data = $poizon->getPoizonProductData($track);

                $prices = $poizon->getPoizonPricesForProduct($track);
                echo "Product was received SKU: {$track}\n";
                if(!$data || !$prices || !count($prices)) {
                    echo "Product was not received SKU: {$track}\n";
                    continue;
                }
                PoizonProduct::updateOrCreate(
                    ['sku' => $track],
                    [
                        'data' => $data,
                        'prices' => $prices,
                    ]
                );
            } catch (\Exception $e) {
                echo "Product was not received SKU: {$track}\n";
            }
        }

        PoizonProduct::whereNotIn('sku', $trackSkus)->delete();
    }
}
