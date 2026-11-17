<?php

namespace ItmoSoft\PurchaseOrders\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use ItmoSoft\PurchaseOrders\PurchaseOrdersServiceProvider;
use ItmoSoft\Suppliers\SuppliersServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SuppliersServiceProvider::class,
            PurchaseOrdersServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
