<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
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
        }

        Livewire::component('app.http.livewire.ai-playground', AiPlayground::class);
        Livewire::component('app.http.livewire.chat', \App\Http\Livewire\Chat::class);
        Livewire::component('app.http.livewire.post', \App\Http\Livewire\Post::class);
    }
}
