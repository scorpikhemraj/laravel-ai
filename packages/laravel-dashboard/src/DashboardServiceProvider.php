<?php

namespace Khemraj\LaravelDashboard;

use Illuminate\Support\ServiceProvider;
use Khemraj\LaravelDashboard\Console\InstallCommand;
use Khemraj\LaravelDashboard\Services\ModuleRegistry;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/dashboard.php', 'dashboard'
        );

        // Bind ModuleRegistry as a singleton
        $this->app->singleton(ModuleRegistry::class, function ($app) {
            $registry = new ModuleRegistry();
            
            // Auto-register modules from config
            $modules = config('dashboard.modules', []);
            foreach ($modules as $slug => $class) {
                $registry->register($slug, $class);
            }
            
            return $registry;
        });
    }

    public function boot(): void
    {
        // Load package database migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load package blade views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'dashboard');

        // Load package routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Configure publishing tags for assets / configurations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/dashboard.php' => config_path('dashboard.php'),
            ], 'dashboard-config');

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'dashboard-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/dashboard'),
            ], 'dashboard-views');

            // Register commands
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}
