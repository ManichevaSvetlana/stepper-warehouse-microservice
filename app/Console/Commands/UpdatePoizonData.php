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
    protected $signature = 'poizon:update-poizon-data {--mode=all}';

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
        $mode = $this->option('mode');

        $trackSkus = TrackProduct::all();

        // Получаем TrackProduct, которые не существуют в PoizonProduct
        $nonExistentSkus = $trackSkus->filter(function($track) {
            return !PoizonProduct::where('sku', $track->sku)->exists();
        });

        // Получаем TrackProduct, которые существуют, но были обновлены давнее всего
        $existingSkus = $trackSkus->reject(function($track) {
            return !PoizonProduct::where('sku', $track->sku)->exists();
        })->sortBy('updated_at');

        // Объединяем списки, чтобы сначала шли те, которые не существуют, а затем те, которые были обновлены давнее всего
        if($mode === 'all') $sortedTrackSkus = $nonExistentSkus->concat($existingSkus);
        else if($mode === 'new') $sortedTrackSkus = $nonExistentSkus;
        else if($mode === 'update') $sortedTrackSkus = $existingSkus;

        $availableRequests = 500;
        $runRequests = 0;
        foreach ($sortedTrackSkus as $track) {
            echo "SKU track product - : {$track->sku}\n";

            try {
                $existingProduct = PoizonProduct::where('sku', $track->sku)->first();

                if($existingProduct) $data = $existingProduct->data;
                else {
                    $data = $poizon->getPoizonProductData($track->sku);
                    if($data) $runRequests++;
                }

                $prices = $poizon->getPoizonPricesForProduct($track->sku);
                echo "Product was received SKU: {$track->sku}\n";
                if(!$data || !$prices || !count($prices)) {
                    echo "Product was not received SKU: {$track->sku}\n";
                    continue;
                } else {
                    $runRequests++;
                }
                PoizonProduct::updateOrCreate(
                    ['sku' => $track->sku],
                    [
                        'data' => $data,
                        'prices' => $prices,
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

        PoizonProduct::whereNotIn('sku', $trackSkus->pluck('sku')->toArray())->delete();
        echo "Poizon: update local poizon products data completed. Run queries count: $runRequests \n";
    }
}
