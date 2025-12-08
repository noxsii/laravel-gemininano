<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Noxsi\GeminiNano\Client;
use Noxsi\GeminiNano\ClientFactory;

it('applies fluent setters and trims base url', function (): void {
    $client = (new ClientFactory)
        ->withBaseUrl('https://api.example.test/')
        ->withApiKey('abc123')
        ->withTimeout(42)
        ->make();

    expect($client)->toBeInstanceOf(Client::class)
        ->and($client->baseUrl)->toBe('https://api.example.test')
        ->and($client->apiKey)->toBe('abc123')
        ->and($client->timeout)->toBe(42);
});

it('falls back to config when values are not provided', function (): void {
    Config::set('gemininano.base_url', 'https://conf.example.test');
    Config::set('gemininano.api_key', 'from-config');
    Config::set('gemininano.timeout', 77);

    $client = (new ClientFactory)->make();

    expect($client->baseUrl)->toBe('https://conf.example.test')
        ->and($client->apiKey)->toBe('from-config')
        ->and($client->timeout)->toBe(77);
});

it('uses default timeout 60 when missing in config', function (): void {
    Config::set('gemininano.base_url', 'https://conf2.example.test');
    Config::set('gemininano.api_key', 'k');
    Config::offsetUnset('gemininano.timeout');

    $client = (new ClientFactory)->make();

    expect($client->timeout)->toBe(60);
});
