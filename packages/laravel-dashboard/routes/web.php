<?php

use Illuminate\Support\Facades\Route;

Route::get(config('dashboard.path', 'dashboard') . '/{any?}', function () {
    return view('dashboard::index');
})->where('any', '.*')
  ->middleware(config('dashboard.middleware', ['web']))
  ->name('dashboard.index');
