<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('shop:update-shop-data --section=features')->weeklyOn(1, '18:00');
Schedule::command('bitrix:update-bitrix-data --section=features')->weeklyOn(1, '19:00');

Schedule::command('shop:update-shop-data --section=products')->dailyAt('22:00');
Schedule::command('bitrix:update-bitrix-data --section=products')->dailyAt('23:00');
Schedule::command('poizon:update-poizon-data')->dailyAt('00:00');
Schedule::command('sync:systems-products --section=bitrix')->dailyAt('03:00');
Schedule::command('sync:systems-products --section=shop')->dailyAt('05:30');
