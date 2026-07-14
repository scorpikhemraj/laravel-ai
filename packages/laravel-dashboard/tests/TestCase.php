<?php

namespace Khemraj\LaravelDashboard\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Khemraj\LaravelDashboard\DashboardServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            DashboardServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
