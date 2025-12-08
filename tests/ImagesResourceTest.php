<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Noxsi\GeminiNano\Client;
use Noxsi\GeminiNano\Exceptions\GeminiNanoResourceException;

beforeEach(function (): void {
    Config::set('gemininano.model', 'gemini-2.5-flash-image');
});

it('generates image from text-only prompt', function (): void {
    $client = new Client(baseUrl: 'https://example.test', apiKey: 'secret', timeout: 15);

    $endpoint = 'https://example.test/v1beta/models/gemini-2.5-flash-image:generateContent';

    Http::fake([
        $endpoint => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [
                        ['inlineData' => ['data' => base64_encode('img')]],
                    ],
                ],
            ]],
        ], 200),
    ]);

    $response = $client->images()->generate('Draw a cat');

    expect($response->base64())->toBe(base64_encode('img'));

    Http::assertSent(static function ($request) use ($endpoint): bool {
        $json = $request->data();

        return $request->url() === $endpoint
            && $request->hasHeader('x-goog-api-key', 'secret')
            && $request->hasHeader('Content-Type', 'application/json')
            && ($json['contents'][0]['parts'][0]['text'] ?? null) === 'Draw a cat';
    });
});

it('includes inline image when image path is provided', function (): void {
    $client = new Client(baseUrl: 'https://api.test', apiKey: 'k', timeout: 10);

    $tmp = tempnam(sys_get_temp_dir(), 'img');
    assert(is_string($tmp));
    // write some bytes
    file_put_contents($tmp, 'PNGDATA');

    $endpoint = 'https://api.test/v1beta/models/gemini-2.5-flash-image:generateContent';

    Http::fake([
        $endpoint => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [
                        ['inline_data' => ['data' => base64_encode('ok')]],
                    ],
                ],
            ]],
        ], 200),
    ]);

    $resp = $client->images()->generate('with image', imagePath: $tmp, imageMimeType: 'image/png');

    expect($resp->base64())->toBe(base64_encode('ok'));

    Http::assertSent(static function ($request) use ($endpoint): bool {
        $json = $request->data();
        $parts = $json['contents'][0]['parts'] ?? [];
        $hasInline = isset($parts[0]['inline_data'])
            && ($parts[0]['inline_data']['mime_type'] ?? null) === 'image/png'
            && is_string($parts[0]['inline_data']['data'] ?? null)
            && $parts[1]['text'] === 'with image';

        return $request->url() === $endpoint && $hasInline;
    });

    @unlink($tmp);
});

it('throws when image path is not readable', function (): void {
    $client = new Client(baseUrl: 'https://x.test', apiKey: 'k', timeout: 10);

    $client->images()->generate('p', imagePath: __DIR__.'/does-not-exist.png');
})->throws(GeminiNanoResourceException::class);

it('throws when the HTTP request fails', function (): void {
    $client = new Client(baseUrl: 'https://fail.test', apiKey: 'k', timeout: 10);

    $endpoint = 'https://fail.test/v1beta/models/gemini-2.5-flash-image:generateContent';

    Http::fake([
        $endpoint => Http::response(['error' => 'nope'], 500),
    ]);

    $client->images()->generate('will fail');
})->throws(GeminiNanoResourceException::class);
