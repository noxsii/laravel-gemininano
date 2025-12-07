<?php

declare(strict_types=1);

namespace Noxsi\GeminiNano\Facades;

use Illuminate\Support\Facades\Facade;
use Noxsi\GeminiNano\Client;
use Noxsi\GeminiNano\Resources\Images;

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
