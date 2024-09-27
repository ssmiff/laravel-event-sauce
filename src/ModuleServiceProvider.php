<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/eventsauce.php' => $this->app->configPath('eventsauce.php'),
            ], 'ssmiff-laravel-eventsauce-config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/eventsauce.php',
            'eventsauce'
        );
    }
}
