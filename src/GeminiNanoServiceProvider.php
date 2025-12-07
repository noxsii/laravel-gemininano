<?php

declare(strict_types=1);

namespace Noxsi\GeminiNano;

use Illuminate\Support\ServiceProvider;

class GeminiNanoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/gemininano.php',
            'gemininano'
        );

        $this->app->singleton(Client::class, fn (): Client => Client::factory()->make());
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/gemininano.php' => config_path('gemininano.php'),
            ], 'config');
        }
    }
}
