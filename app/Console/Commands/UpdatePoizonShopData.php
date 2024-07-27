<?php

namespace App\Console\Commands;

use App\Models\Poizon\PoizonShopProduct;
use App\Models\System\CommandRunSku;
use App\Models\System\TrackProduct;
use Illuminate\Console\Command;

class UpdatePoizonShopData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poizon:update-poizon-shop-data {--mode=all} {--sku=}';

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
        try {
            $this->updatePoizonShopData();
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            $sku = $this->getCurrentSku();
            if($sku) {
                $this->call('poizon:update-poizon-shop-data', [
                    '--sku' => $sku,
                ]);
            }
        }
    }

    /**
     * Get current SKU.
     *
     * @return ?string
     */
    private function getCurrentSku(): ?string
    {
        return CommandRunSku::where('type', 'poizon-shop')->orderBy('sku', 'asc')->first()?->sku;
    }

    /**
     * Update poizon shop data.
     *
     * @return void
     */
    private function updatePoizonShopData(): void
    {
        echo "Poizon Shop: update local poizon products data.\n";
        $poizon = new PoizonShopProduct();
        $mode = $this->option('mode');
        $startSku = $this->option('sku');
        $startProcessing = false;

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

            if ($startSku) {
                if (!$startProcessing && $track->sku !== $startSku) {
                    echo 'Skip track: ' . $track->sku . "\n";
                    continue;
                }

                $startProcessing = true;
            }

            try {
                CommandRunSku::firstOrCreate(['sku' => $track->sku, 'type' => 'poizon-shop']);
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
                        'popularity' => $track->type,
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

        CommandRunSku::where('type', 'poizon-shop')->delete();
        PoizonShopProduct::whereNotIn('sku', $trackSkus->pluck('sku')->toArray())->delete();
        echo "Poizon: update local poizon products data completed. Run queries count: $runRequests \n";
    }
}
