<?php

declare(strict_types=1);

namespace Noxsi\GeminiNano;

use Illuminate\Support\Facades\Config;

final class ClientFactory
{
    private ?string $apiKey = null;

    private ?string $baseUrl = null;

    private ?int $timeout = null;

    public function withApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function withBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');

        return $this;
    }

    public function withTimeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function make(): Client
    {
        /** @var array<string, mixed> $config */
        $config = (array) Config::get('gemininano');

        $baseUrl = $this->baseUrl ?? $config['base_url'] ?? '';
        $apiKey = $this->apiKey ?? $config['api_key'] ?? '';
        $timeout = $this->timeout ?? (int) ($config['timeout'] ?? 60);

        return new Client(
            baseUrl: $baseUrl,
            apiKey: $apiKey,
            timeout: $timeout,
        );
    }
}
