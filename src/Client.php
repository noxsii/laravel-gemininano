<?php

declare(strict_types=1);

namespace Noxsi\GeminiNano;

use Noxsi\GeminiNano\Resources\Images;

final readonly class Client
{
    public function __construct(
        public string $baseUrl,
        public string $apiKey,
        public int $timeout,
    ) {}

    public static function factory(): ClientFactory
    {
        return new ClientFactory;
    }

    public function images(): Images
    {
        return new Images($this);
    }
}
