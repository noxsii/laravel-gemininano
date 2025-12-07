<?php

declare(strict_types=1);

namespace Noxsi\LaravelGemininano\Facades;

use Illuminate\Support\Facades\Facade;
use Noxsi\LaravelGemininano\Client;
use Noxsi\LaravelGemininano\Resources\Images;

/**
 * @method static Images images()
 */
class GeminiNano extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
