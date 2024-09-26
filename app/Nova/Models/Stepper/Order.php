<?php

namespace App\Nova\Models\Stepper;

use Alexwenzel\DependencyContainer\DependencyContainer;
use App\Nova\Resource;
use Dnwjn\NovaButton\Button;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

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
     * Get the fields displayed by the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Panel::make('Тип заказа', [
                Boolean::make('Онлайн заказ', 'is_online_order')->sortable(),
            ]),

            Panel::make('Основная информация о заказе', [
                Date::make('Дата заказа', 'date_of_order')->required()->sortable(),
                Number::make('Цена', 'price')->required()->sortable()->step(0.01),
                Number::make('Скидка', 'sale_value')->sortable()->step(0.01),
                DependencyContainer::make([
                    Text::make('ID заказа на сайте', 'order_site_id')->required()->sortable(),
                    Text::make('Название продукта', 'product_name')->required()->sortable(),
                    Text::make('Артикул продукта', 'product_article')->required()->sortable(),
                    Text::make('Ссылка на продукт', 'product_link')->required()->hideFromIndex(),
                    Text::make('Размер продукта', 'product_size')->required()->sortable(),
                    Number::make('Первый платёж', 'first_payment')->hideFromIndex()->step(0.01),
                    Number::make('Второй платёж', 'second_payment')->hideFromIndex()->step(0.01),
                    Boolean::make('Полностью оплачено', 'is_fully_paid')->sortable(),
                ])->dependsOn('is_online_order', 1),
                DependencyContainer::make([
                    BelongsTo::make('Товар в магазине', 'stockOrder', StockOrder::class)->required(),
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
                ])->displayUsingLabels()->default('pick_up'),
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
                DependencyContainer::make([
                    Boolean::make('Заказано', 'is_ordered')->sortable(),
                    Boolean::make('На контроле', 'is_on_control')->sortable(),
                    Text::make('SKU', 'sku')->hideFromIndex(),
                    Text::make('Трек-номер', 'track_number')->hideFromIndex(),
                    Number::make('Цена в юанях', 'cny_price')->hideFromIndex()->step(0.01),
                    Date::make('Дата поступления на Poizon', 'poizon_date')->hideFromIndex(),
                    Date::make('Дата поступления в Onex', 'onex_date')->sortable(),
                    Date::make('Дата рейса', 'flight_date')->hideFromIndex(),
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
                        ->displayUsingLabels()->default('not_processed'),
                ])->dependsOnNot('is_online_order', 1),
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
                ])->displayUsingLabels()->default('waiting'),
                Text::make('Источник', 'source')->hideFromIndex(),
                Textarea::make('Комментарий', 'comment')->hideFromIndex(),
            ]),

            Panel::make('Добавить товар на сайт', [
                DependencyContainer::make([
                    Boolean::make('Добавлен на сайт', 'is_transformed_to_stock_order')->hideFromIndex()->canSee(function () {
                        return $this->is_transformed_to_stock_order;
                    })->readonly(),
                    Number::make('Цена для продажи', 'price_for_sale')->hideFromIndex()->step(0.01),
                    Button::make('Добавить товар на сайт')
                        ->link("/api-nova/transform-order-to-stock?order_id={$this->id}", '_self'),
                ])->dependsOn('status_notification', 'exchange')->canSee(function () {
                    return !$this->is_transformed_to_stock_order;
                }),
            ]),

            Date::make('Created', 'created_at')->exceptOnForms(),
            Date::make('Updated', 'updated_at')->exceptOnForms(),


            BelongsToMany::make('Менеджеры', 'managers', Manager::class),

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
        return [];
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
}
