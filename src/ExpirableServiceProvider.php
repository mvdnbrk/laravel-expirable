<?php

namespace Mvdnbrk\EloquentExpirable;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class ExpirableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerBlueprintMacros();

        // Merge default config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/expirable.php',
            'expirable'
        );

        // Register commands
        $this->commands([
            PurgeExpired::class,
        ]);
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/expirable.php' => config_path('expirable.php'),
        ]);
    }

    protected function registerBlueprintMacros(): void
    {
        if ($this->app->runningInConsole()) {
            Blueprint::macro('expires', function (string $column = 'expires_at', int $precision = 0) {
                /* @var \Illuminate\Database\Schema\Blueprint $this */
                return $this->timestamp($column, $precision)->nullable();
            });

            Blueprint::macro('dropExpires', function (string $column = 'expires_at') {
                /* @var \Illuminate\Database\Schema\Blueprint $this */
                return $this->dropColumn($column);
            });
        }
    }
}
