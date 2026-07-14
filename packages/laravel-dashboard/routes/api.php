<?php

use Illuminate\Support\Facades\Route;
use Khemraj\LaravelDashboard\Http\Controllers\DashboardController;
use Khemraj\LaravelDashboard\Http\Controllers\DashboardTabController;
use Khemraj\LaravelDashboard\Http\Controllers\DashboardWidgetController;
use Khemraj\LaravelDashboard\Http\Controllers\WidgetDataController;
use Khemraj\LaravelDashboard\Http\Controllers\ModuleDiscoveryController;

Route::prefix('api/dashboard')->middleware(config('dashboard.middleware', ['web']))->group(function () {
    // Dashboards
    Route::get('dashboards', [DashboardController::class, 'index'])->name('dashboard.dashboards.index');
    Route::post('dashboards', [DashboardController::class, 'store'])->name('dashboard.dashboards.store');
    Route::get('dashboards/{idOrSlug}', [DashboardController::class, 'show'])->name('dashboard.dashboards.show');
    Route::put('dashboards/{dashboard}', [DashboardController::class, 'update'])->name('dashboard.dashboards.update');
    Route::delete('dashboards/{dashboard}', [DashboardController::class, 'destroy'])->name('dashboard.dashboards.destroy');

    // Tabs
    Route::post('dashboards/{dashboard}/tabs', [DashboardTabController::class, 'store'])->name('dashboard.tabs.store');
    Route::put('tabs/{tab}', [DashboardTabController::class, 'update'])->name('dashboard.tabs.update');
    Route::delete('tabs/{tab}', [DashboardTabController::class, 'destroy'])->name('dashboard.tabs.destroy');

    // Widgets
    Route::post('widgets', [DashboardWidgetController::class, 'store'])->name('dashboard.widgets.store');
    Route::put('widgets/{widget}', [DashboardWidgetController::class, 'update'])->name('dashboard.widgets.update');
    Route::delete('widgets/{widget}', [DashboardWidgetController::class, 'destroy'])->name('dashboard.widgets.destroy');
    Route::post('widgets/positions', [DashboardWidgetController::class, 'updatePositions'])->name('dashboard.widgets.positions');

    // Widget Data
    Route::post('widgets/preview', [WidgetDataController::class, 'previewData'])->name('dashboard.widgets.preview');
    Route::get('widgets/{widget}/data', [WidgetDataController::class, 'getData'])->name('dashboard.widgets.data');

    // Modules
    Route::get('modules', [ModuleDiscoveryController::class, 'getModules'])->name('dashboard.modules.index');
    Route::get('modules/{module}/fields', [ModuleDiscoveryController::class, 'getFields'])->name('dashboard.modules.fields');

    // AI Chat
    Route::post('ai/chat', [DashboardController::class, 'aiChat'])->name('dashboard.ai.chat');
});

