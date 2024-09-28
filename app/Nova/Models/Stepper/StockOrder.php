<?php

namespace App\Nova\Models\Stepper;

use Alexwenzel\DependencyContainer\DependencyContainer;
use App\Nova\Resource;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class StockOrder extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Stepper\StockOrder>
     */
    public static $model = \App\Models\Stepper\StockOrder::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->name . ' - ' . $this->size . ': #' . $this->id;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Наименование', 'name')->required()->sortable(),
            Text::make('Артикул', 'article')->required()->sortable(),
            Text::make('SKU', 'sku')->required()->sortable(),
            Text::make('Размер', 'size')->required()->sortable(),
            Number::make('Цена в CNY', 'cny_price')->required()->sortable()->step(0.01),
            Number::make('Цена для продажи', 'price_for_sale')->nullable()->sortable()->readonly(),
            Date::make('Дата поизона', 'poizon_date')->nullable()->sortable()->filterable(),
            Text::make('Трек-номер', 'track_number')->nullable()->sortable(),
            Boolean::make('На контроле', 'is_on_control')->nullable()->sortable()->filterable(),
            Select::make('Статус Onex', 'onex_status')->nullable()->sortable()->options([
                'not_processed' => 'Не обработано',
                'expected' => 'Ожидается',
                'in_sort_centre' => 'На сортировочном центре',
                'on_its_way' => 'В пути',
                'customs_clearance' => 'Таможенное прохождение',
                'pick_up' => 'Получено'
            ])->filterable(),
            Boolean::make('Загружен на сайт', 'is_on_website')->nullable()->sortable()->filterable(),
            Date::make('Дата Onex', 'onex_date')->nullable()->sortable()->filterable(),
            Date::make('Дата рейса', 'flight_date')->nullable()->sortable(),
            Textarea::make('Комментарий', 'comment')->nullable()->hideFromIndex(),
            Text::make('Ссылка на продукт', 'product_link')->hideFromIndex(),

            DateTime::make('Created')->exceptOnForms(),
            DateTime::make('Updated')->exceptOnForms(),

            HasMany::make('Orders', 'orders', Order::class),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            new \App\Nova\Actions\GetProductsToWebsiteData
        ];
    }
}
