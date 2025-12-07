<?php

declare(strict_types=1);

namespace Noxsi\LaravelGemininano;

final readonly class Client
{
    public function __construct(
        public string $baseUrl,
        public string $apiKey,
        public int    $timeout,
    ) {
    }

    public static function factory(): ClientFactory
    {
        return new ClientFactory();
    }
}
