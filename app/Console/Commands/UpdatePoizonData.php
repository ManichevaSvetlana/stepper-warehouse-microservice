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
        foreach (TrackProduct::all() as $track) {
            echo "SKU track product - : {$track->sku}\n";
            $data = $poizon->getPoizonProductData($track->sku);
            $prices = $poizon->getPoizonPricesForProduct($track->sku);
            echo "Product was received SKU: {$track->sku}\n";
            PoizonProduct::updateOrCreate(
                ['sku' => $track->sku],
                [
                    'data' => $data,
                    'prices' => $prices,
                ]
            );
        }
    }
}
