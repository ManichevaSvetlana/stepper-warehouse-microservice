<?php

namespace App\Console\Commands;

use App\Models\Poizon\PoizonShopProduct;
use App\Models\System\TrackProduct;
use Illuminate\Console\Command;

class UpdatePoizonShopData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poizon:update-poizon-shop-data {--mode=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo "Poizon Shop: update local poizon products data.\n";
        $poizon = new PoizonShopProduct();
        $mode = $this->option('mode');

        $trackSkus = TrackProduct::where('system', 'poizon-shop')->get();

        // Получаем TrackProduct, которые не существуют в PoizonShopProduct
        $nonExistentSkus = $trackSkus->filter(function($track) {
            return !PoizonShopProduct::where('sku', $track->sku)->exists();
        });

        // Получаем TrackProduct, которые существуют, но были обновлены давнее всего
        $existingSkus = $trackSkus->reject(function($track) {
            return !PoizonShopProduct::where('sku', $track->sku)->exists();
        })->sortBy('updated_at');

        // Объединяем списки, чтобы сначала шли те, которые не существуют, а затем те, которые были обновлены давнее всего
        if($mode === 'new') $sortedTrackSkus = $nonExistentSkus;
        else if($mode === 'update') $sortedTrackSkus = $existingSkus;
        else $sortedTrackSkus = $nonExistentSkus->concat($existingSkus);

        $availableRequests = 30000;
        $runRequests = 0;

        $trackCount = count($sortedTrackSkus);
        echo "Track products count: $trackCount.\n";

        foreach ($sortedTrackSkus as $track) {
            echo "SKU track product - : {$track->sku}\n";

            try {
                $data = $poizon->getPoizonShopProductData($track->sku);
                if($data) $runRequests++;

                if(!$data) {
                    echo "Product was not received SKU: {$track->sku}\n";
                    continue;
                } else echo "Product was received SKU: {$track->sku}\n";

                PoizonShopProduct::updateOrCreate(
                    ['sku' => $track->sku],
                    [
                        'data' => $data,
                    ]
                );
                $track->update(['updated_at' => now()]);
                if($runRequests >= $availableRequests) {
                    echo "Limit of requests was reached\n";
                    break;
                }
            } catch (\Exception $e) {
                echo "Product was not received SKU: {$track->sku}\n";
            }
        }

        PoizonShopProduct::whereNotIn('sku', $trackSkus->pluck('sku')->toArray())->delete();
        echo "Poizon: update local poizon products data completed. Run queries count: $runRequests \n";
    }
}