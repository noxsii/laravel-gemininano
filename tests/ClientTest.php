<?php

declare(strict_types=1);

use Noxsi\GeminiNano\Client;
use Noxsi\GeminiNano\ClientFactory;
use Noxsi\GeminiNano\Resources\Images;

it('creates a ClientFactory via factory()', function (): void {
    expect(Client::factory())->toBeInstanceOf(ClientFactory::class);
});

it('returns Images resource via images()', function (): void {
    $client = new Client(baseUrl: 'https://example.test', apiKey: 'key', timeout: 30);

    expect($client->images())->toBeInstanceOf(Images::class);
});
