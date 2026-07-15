<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use App\Http\Livewire\AiPlayground;

class AppServiceProvider extends ServiceProvider
    {
            /**
     * Register any application services.
             */
    public function register(): void
        {
                    //
        }

    /**
     * Bootstrap any application services.
             */
    public function boot(): void
        {
                    if (config('app.env') === 'production' || env('VERCEL_ENV') === 'preview') {
                                    URL::forceScheme('https');

                        $dbPath = '/tmp/database.sqlite';

                        Config::set('database.connections.sqlite.database', $dbPath);
                                    Config::set('database.default', 'sqlite');

                        if (!file_exists($dbPath)) {
                                            touch($dbPath);
                                            Artisan::call('migrate', ['--force' => true]);
                                            Artisan::call('db:seed', ['--force' => true]);
                        }

                        Config::set('session.driver', 'cookie');
                    }

                Livewire::component('app.http.livewire.ai-playground', AiPlayground::class);
                    Livewire::component('app.http.livewire.chat', \App\Http\Livewire\Chat::class);
                    Livewire::component('app.http.livewire.post', \App\Http\Livewire\Post::class);
        }
    }
