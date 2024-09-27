<?php

namespace App\Nova\Actions;

use App\Models\Stepper\StockOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Http\Requests\NovaRequest;

class GetProductsToWebsiteData extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Get products to website data';

    /**
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function getProductsData()
    {
        // Получаем все заказы, которые еще не выведены на сайт
        $modelsToUpload = StockOrder::where('is_on_website', false)
            ->get()
            ->groupBy('sku');



        if(count($modelsToUpload)) {
            $result = '';

            // Обработка каждой группы SKU
            foreach ($modelsToUpload as $sku => $orders) {
                // Собираем уникальные размеры, сортируем и объединяем их через запятую
                $sizes = $orders->pluck('size')->unique()->sort()->implode(', ');
                // Рассчитываем среднюю цену
                $averagePrice = $orders->avg('price_for_sale');

                // Формируем строку результата
                $result .= sprintf(
                    "%s | %s | %s <br>", // Используем sprintf для форматирования строки
                    $sku,
                    $sizes,
                    number_format($averagePrice, 2, '.', '')
                );
            }

            $result .= "<b style='margin-top: 15px'>Вы хотите отметить все эти позиции, как добавлено в сток?</b>";
            return $result;
        } else {
            return '<b>Нет товаров для добавления на сайт</b>';
        }

    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        StockOrder::where('is_on_website', false)->update(['is_on_website' => true]);
        return ActionResponse::redirect('/backoffice/resources/stock-orders');
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Heading::make($this->getProductsData())->asHtml()
        ];
    }
}
