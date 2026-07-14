<?php

namespace Khemraj\LaravelDashboard\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'dashboard:install';
    protected $description = 'Install the Scorpi Laravel Dynamic Dashboard Package';

    public function handle(): int
    {
        $this->info('Installing Scorpi Dynamic Dashboard Package...');

        // 1. Publish Configuration
        $this->info('Publishing configuration...');
        $this->call('vendor:publish', [
            '--provider' => "Khemraj\LaravelDashboard\DashboardServiceProvider",
            '--tag' => 'dashboard-config'
        ]);

        // 2. Publish Migrations
        $this->info('Publishing database migrations...');
        $this->call('vendor:publish', [
            '--provider' => "Khemraj\LaravelDashboard\DashboardServiceProvider",
            '--tag' => 'dashboard-migrations'
        ]);

        // 3. Publish Views
        $this->info('Publishing blade views...');
        $this->call('vendor:publish', [
            '--provider' => "Khemraj\LaravelDashboard\DashboardServiceProvider",
            '--tag' => 'dashboard-views'
        ]);

        $this->info('Scorpi Dynamic Dashboard Package installed successfully!');
        return 0;
    }
}
