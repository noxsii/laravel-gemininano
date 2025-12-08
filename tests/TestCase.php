<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\ServiceProvider;
use Noxsi\GeminiNano\GeminiNanoServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param  mixed  $app
     * @return array<int, class-string<ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [
            GeminiNanoServiceProvider::class,
        ];
    }
}
