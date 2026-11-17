<?php

namespace ItmoSoft\PurchaseOrders;

use Illuminate\Support\ServiceProvider;

class PurchaseOrdersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/purchase-orders.php', 'purchase-orders');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/purchase-orders.php' => config_path('purchase-orders.php'),
        ], 'purchase-orders-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
