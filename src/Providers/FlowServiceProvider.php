<?php

namespace Webtize\Flows\Providers;

use Illuminate\Support\ServiceProvider;
use Webtize\Flows\Console\MakeFlowJobCommand;

class FlowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // You may bind interfaces to implementations here if needed in future
    }

    public function boot(): void
    {
        // Load package migrations so that the host app can run them without publishing
        $this->loadMigrationsFrom(__DIR__ . '/../migration');

        // Register console commands when running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeFlowJobCommand::class,
            ]);
        }
    }
}
