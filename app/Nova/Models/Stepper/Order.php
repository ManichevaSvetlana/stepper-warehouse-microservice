<?php

namespace App\Nova\Models\Stepper;

use Alexwenzel\DependencyContainer\DependencyContainer;
use App\Nova\Metrics\NewOfflineOrders;
use App\Nova\Metrics\NewOnlineOrders;
use App\Nova\Metrics\NewOrders;
use App\Nova\Metrics\OrdersPerDay;
use App\Nova\Metrics\OrdersPerMonths;
use App\Nova\Metrics\OrdersPerType;
use App\Nova\Resource;
use Dnwjn\NovaButton\Button;
use Ebess\AdvancedNovaMediaLibrary\Fields\Files;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use ZiffMedia\NovaSelectPlus\SelectPlus;

class Order extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Stepper\Order>
     */
    public static $model = \App\Models\Stepper\Order::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'order_site_id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'product_name', 'order_site_id', 'product_article', 'contact_value', 'track_number', 'sku'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->order_site_id . ' - ' . $this->product_name . ': #' . $this->id . ' (' . $this->product_article . ')';
    }

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'created_at' => 'desc'
    ];

    /**
     * Indicates whether Nova should check for modifications between viewing and updating a resource.
     *
     * @var bool
     */
    public static $trafficCop = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Panel::make('Тип заказа', [
                Boolean::make('Индивидуальный заказ (10-20 дней)', 'is_online_order')->sortable()->filterable(),
            ]),

            Text::make('Дней прошло', function () {
                $isOnlineOrder = $this->is_online_order;
                $days = round($this->created_at->diffInDays());
                $statusDelivery = $this->status_delivery;
                if(!($days >= 16 && $statusDelivery !== 'pick_up' && $isOnlineOrder)) {
                    return "<span ></span>";
                }
                $style = 'display: inline-block; padding: 6px 8px; border-radius: 50%; color: white;';

                $color = $days >= 20 ? 'red' : 'orange';
                return "<span style='{$style} background-color: {$color};'>{$days}</span>";
            })->asHtml()->onlyOnIndex(),

            Panel::make('Основная информация о заказе', [
                Text::make('Номер заказа на сайте', 'order_site_id')->sortable()->onlyOnIndex(), // For index only
                Date::make('Дата заказа', 'date_of_order')->required()->sortable()->filterable(),
                Number::make('Цена', 'price')->required()->sortable()->step(0.01)->filterable()->onlyOnIndex(),  // For index only
                Text::make('Название продукта', 'product_name')->sortable()->onlyOnIndex(), // For index only
                Text::make('Размер продукта', 'product_size')->sortable()->filterable()->onlyOnIndex(), // For index only

                DependencyContainer::make([
                    Text::make('Номер заказа на сайте', 'order_site_id')->required()->hideFromIndex(),
                    Text::make('Название продукта', 'product_name')->required()->hideFromIndex(),
                    Text::make('Размер продукта', 'product_size')->required()->hideFromIndex(),
                    Number::make('Цена', 'price')->required()->step(0.01)->hideFromIndex(),
                    Number::make('Скидка', 'sale_value')->sortable()->step(0.01),
                    Text::make('Артикул продукта', 'product_article')->required()->sortable(),
                    Text::make('Ссылка на продукт', 'product_link')->required()->hideFromIndex(),
                    Number::make('Первый платёж', 'first_payment')->hideFromIndex()->step(0.01),
                    Number::make('Второй платёж', 'second_payment')->hideFromIndex()->step(0.01),
                    Boolean::make('Полностью оплачено', 'is_fully_paid')->sortable()->filterable(),
                    Boolean::make('Возможен обмен', 'is_return_possible')->sortable()->filterable(),
                ])->dependsOn('is_online_order', 1),

                BelongsTo::make('Товар в магазине', 'stockOrder', StockOrder::class)->nullable()->hideFromIndex()->searchable()->dependsOn('is_online_order', function ($field, NovaRequest $request, FormData $formData) {
                    if ($formData->is_online_order == 0) {
                        $field->show();
                    } else {
                        $field->hide();
                    }
                }),

                DependencyContainer::make([
                    Text::make('Название продукта', 'product_name'),
                    Text::make('Размер продукта', 'product_size')->required(),
                    Number::make('Цена', 'price')->step(0.01)->hideFromIndex(),
                    Number::make('Скидка', 'sale_value')->sortable()->step(0.01),
                ])->dependsOn('is_online_order', 0),
            ]),

            Panel::make('Контактная информация', [
                Select::make('Тип контакта', 'contact_type')->required()->hideFromIndex()->options([
                    'phone' => 'Телефон',
                    'email' => 'Email',
                    'whatsapp' => 'WhatsApp',
                    'viber' => 'Viber',
                    'telegram' => 'Telegram',
                    'facebook' => 'Facebook',
                    'instagram' => 'Instagram',
                    'other' => 'Другое',
                ])->displayUsingLabels(),
                Text::make('Контактные данные', 'contact_value')->required()->hideFromIndex(),
                Text::make('Имя пользователя', 'site_name')->hideFromIndex(),
                Text::make('Телефон пользователя', 'site_phone')->hideFromIndex(),
                DependencyContainer::make([
                    Text::make('Электронная почта пользователя', 'site_email')->hideFromIndex(),
                ])->dependsOn('is_online_order', 1),
            ]),

            Panel::make('Доставка', [
                Select::make('Тип доставки', 'delivery_type')->hideFromIndex()->options([
                    'pick_up' => 'Самовывоз',
                    'tbilisi_courier' => 'Курьер в Тбилиси',
                    'tbilisi_courier_door' => 'Курьер до двери в Тбилиси',
                    'delivo_point' => 'Пункт выдачи Delivo',
                    'delivo_door' => 'Доставка Delivo на адрес'
                ])->displayUsingLabels()->default('pick_up')->filterable(),
                DependencyContainer::make([
                    Select::make('Город доставки', 'delivery_city')->options([
                        'tbilisi' => 'Tbilisi',
                        'batumi' => 'Batumi',
                        'kutaisi' => 'Kutaisi',
                        'rustavi' => 'Rustavi',
                        'zestafoni' => 'Zestafoni',
                        'zugdidi' => 'Zugdidi',
                        'khashuri' => 'Khashuri',
                        'borjomi' => 'Borjomi',
                        'gori' => 'Gori',
                        'mtskheta' => 'Mtskheta',
                        'marneuli' => 'Marneuli',
                        'akhaltsikhe' => 'Akhaltsikhe',
                        'gardabani' => 'Gardabani',
                        'kobuleti' => 'Kobuleti',
                        'sagarejo' => 'Sagarejo',
                        'sighnagi' => 'Sighnagi',
                        'kvareli' => 'Kvareli',
                        'telavi' => 'Telavi',
                        'ozurgeti' => 'Ozurgeti',
                        'senaki' => 'Senaki',
                        'kareli' => 'Kareli',
                        'poti' => 'Poti',
                        'akhalkalaki' => 'Akhalkalaki',
                        'other' => 'Other',
                        'kaspi' => 'Kaspi',
                    ])->hideFromIndex(),
                ])->dependsOnNotIn('delivery_type', ['pick_up', 'tbilisi_courier', 'tbilisi_courier_door']),
                DependencyContainer::make([
                    Text::make('Адрес доставки', 'delivery_address')->hideFromIndex(),
                ])->dependsOnNot('delivery_type', 'pick_up'),
            ]),

            Panel::make('Логистические данные', [
                Boolean::make('Заказано', 'is_ordered')->sortable()->filterable()->dependsOn('is_online_order', function ($field, NovaRequest $request, FormData $formData) {
                    if ($formData->is_online_order == 1) {
                        $field->show();
                    } else {
                        $field->hide();
                    }
                }),
                Boolean::make('На контроле', 'is_on_control')->sortable()->filterable()->dependsOn('is_online_order', function ($field, NovaRequest $request, FormData $formData) {
                    if ($formData->is_online_order == 1) {
                        $field->show();
                    } else {
                        $field->hide();
                    }
                }),
                Text::make('Трек-номер', 'track_number')->dependsOn('is_online_order', function ($field, NovaRequest $request, FormData $formData) {
                    if ($formData->is_online_order == 1) {
                        $field->show();
                    } else {
                        $field->hide();
                    }
                })->sortable(),
                DependencyContainer::make([
                    Text::make('SKU', 'sku')->hideFromIndex(),
                    Number::make('Цена в юанях', 'cny_price')->hideFromIndex()->step(0.01),
                    Date::make('Дата поступления на Poizon', 'poizon_date')->hideFromIndex(),
                    Date::make('Дата поступления в Onex', 'onex_date')->sortable(),
                    Date::make('Дата рейса', 'flight_date')->hideFromIndex(),
                ])->dependsOn('is_online_order', 1),

                Select::make('Статус доставки', 'status_delivery')
                    ->hideFromIndex()
                    ->options([
                        'not_processed' => 'Не обработано',
                        'expected' => 'Ожидается',
                        'in_sort_centre' => 'На сортировочном центре',
                        'on_its_way' => 'В пути',
                        'customs_clearance' => 'Таможенное прохождение',
                        'pick_up' => 'Получено'
                    ])
                    ->filterable()
                    ->displayUsingLabels()->default('not_processed')->dependsOn('is_online_order', function ($field, NovaRequest $request, FormData $formData) {
                        if ($formData->is_online_order == 1) {
                            $field->show();
                        } else {
                            $field->hide();
                        }
                    })
                ,
            ]),

            Panel::make('Менеджеры', [
                SelectPlus::make('Менеджеры', 'managers', Manager::class),
            ]),

            Panel::make('Уведомления и комментарии', [
                Select::make('Статус уведомления', 'status_notification')->hideFromIndex()->options([
                    'waiting' => 'Ожидание',
                    'picked_up' => 'Получено с почты',
                    'notified' => 'Уведомлено о поступлении',
                    'order_accepted' => 'Заказ принят клиентом',
                    'prepayment_returned' => 'Возврат предоплаты',
                    'exchange' => 'Обмен',
                    'review_expected' => 'Ожидание отзыва'
                ])->displayUsingLabels()->default('waiting')->filterable(),
                Text::make('Источник', 'source')->hideFromIndex(),
                Textarea::make('Комментарий', 'comment')->hideFromIndex(),
            ]),

            Panel::make('Возврат и Обмен', [
                BelongsTo::make('Заказ на обмен', 'returnOrder', Order::class)->nullable()->hideFromIndex()->searchable(),
                Select::make('Статус возврата', 'return_status')->hideFromIndex()->options([
                    'return' => 'Возврат',
                    'exchange' => 'Обмен',
                ])->displayUsingLabels()->filterable(),


                Number::make('Сумма возврата', 'return_sum')->step(0.01)->hideFromIndex()->filterable()->dependsOn('return_status', function ($field, NovaRequest $request, FormData $formData) {
                    if (in_array($formData->return_status, ['return', 'exchange'])) {
                        $field->show();
                    } else {
                        $field->hide();
                    }
                }),
                Boolean::make('Деньги вернули', 'is_paid_back')->hideFromIndex()->filterable()->dependsOn('return_status', function ($field, NovaRequest $request, FormData $formData) {
                    if (in_array($formData->return_status, ['return', 'exchange'])) {
                        $field->show();
                    } else {
                        $field->hide();
                    }
                }),

                DependencyContainer::make([
                    Textarea::make('Причина возврата', 'return_reason')->hideFromIndex(),
                    Text::make('Номер счета получателя', 'return_number')->hideFromIndex(),
                    Text::make('Имя получателя', 'return_name')->hideFromIndex(),
                    Date::make('Дата возврата', 'return_date')->hideFromIndex(),
                    Files::make('Возврат: чек', 'return_file'),


                    Boolean::make('Добавлен на сайт', 'is_transformed_to_stock_order')->hideFromIndex()->canSee(function () {
                        return $this->is_transformed_to_stock_order;
                    })->readonly(),
                    Number::make('Цена для продажи', 'price_for_sale')->hideFromIndex()->step(0.01),
                    Button::make('Добавить товар на сайт')
                        ->link("/api-nova/transform-order-to-stock?order_id={$this->id}", '_self'),
                ])->dependsOnIn('return_status', ['return', 'exchange'])->canSee(function () {
                    return !$this->is_transformed_to_stock_order;
                }),
            ]),

        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [

        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        if (empty($request->get('orderBy'))) {
            $query->getQuery()->orders = [];

            return $query->orderBy(key(static::$sort), reset(static::$sort));
        }

        return $query;
    }
}
