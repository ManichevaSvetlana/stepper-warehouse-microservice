<?php

namespace App\Providers;

use App\Nova\Dashboards\Main;
use App\Nova\Models\Bitrix\BitrixProduct;
use App\Nova\Models\Poizon\PoizonProduct;
use App\Nova\Models\Poizon\PoizonShopProduct;
use App\Nova\Models\Shop\ShopProduct;
use App\Nova\Models\Stepper\Manager;
use App\Nova\Models\Stepper\Order;
use App\Nova\Models\Stepper\StockOrder;
use App\Nova\Models\System\Feature;
use App\Nova\Models\System\TrackProduct;
use App\Nova\User;
use Berika\ProductsToShopUploader\ProductsToShopUploader;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Nova::mainMenu(function () {
            return [
                MenuSection::dashboard(Main::class)->icon('chart-bar'),

                MenuSection::make('Заказы', [
                    MenuItem::resource(Order::class),
                    MenuItem::resource(StockOrder::class),
                ])->icon('')->collapsable(),

                MenuSection::make('Команда', [
                    MenuItem::resource(Manager::class),
                ])->icon('user-group')->collapsable(),

                MenuSection::make('Продукты', [
                    MenuItem::resource(TrackProduct::class),
                    MenuItem::resource(PoizonShopProduct::class),
                    MenuItem::resource(PoizonProduct::class),
                    MenuItem::resource(BitrixProduct::class),
                ])->icon('library')->collapsable()->canSee(function ($request) {
                    return $request->user()->email === 'admin@stepper.ge' || $request->user()->email === 'admin@berika.com';
                }),

                MenuSection::make('Сайт', [
                    MenuItem::resource(ShopProduct::class),
                    MenuItem::resource(Feature::class),
                ])->icon('truck')->collapsable(),

                MenuSection::make('Пользователи', [
                    MenuItem::resource(User::class),
                ])->icon('truck')->collapsable(),
            ];
        });
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes()
                ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            new \App\Nova\Dashboards\Main,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            new ProductsToShopUploader(),
            new \Stepanenko3\LogsTool\LogsTool(),
            new \Stepanenko3\NovaCommandRunner\CommandRunnerTool,
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
