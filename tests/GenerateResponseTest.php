<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Noxsi\GeminiNano\Exceptions\GeminiNanoImageResponseException;
use Noxsi\GeminiNano\Responses\Images\GenerateResponse;

it('parses base64 from candidates.content.parts.inlineData', function (): void {
    $data = [
        'candidates' => [
            [
                'content' => [
                    'parts' => [
                        ['inlineData' => ['data' => base64_encode('png-bytes')]],
                    ],
                ],
            ],
        ],
    ];

    $resp = GenerateResponse::fromArray($data);

    expect($resp->base64())->toBe(base64_encode('png-bytes'))
        ->and($resp->raw())->toBe($data);
});

it('parses base64 from parts.inline_data', function (): void {
    $data = [
        'parts' => [
            ['inline_data' => ['data' => base64_encode('image')]],
        ],
    ];

    $resp = GenerateResponse::fromArray($data);

    expect($resp->base64())->toBe(base64_encode('image'));
});

it('throws when no image data present', function (): void {
    GenerateResponse::fromArray([]);
})->throws(GeminiNanoImageResponseException::class);

it('returns base64 when store=false', function (): void {
    Config::set('gemininano.store', false);

    $resp = new GenerateResponse(base64_encode('abc'), ['raw' => true]);

    expect($resp->result())->toBe(base64_encode('abc'));
});

it('stores image and returns url when store=true', function (): void {
    Storage::fake('public');
    Config::set('gemininano.store', true);
    Config::set('gemininano.disk', 'public');
    Config::set('gemininano.path', 'gemininano');

    $resp = new GenerateResponse(base64_encode('file-bytes'), []);

    $url = $resp->result();

    // File should be written under the configured path with .png extension
    $files = Storage::disk('public')->allFiles('gemininano');
    expect($files)->toHaveCount(1)
        ->and($files[0])->toEndWith('.png')
        ->and(Storage::disk('public')->exists($files[0]))->toBeTrue()
        ->and($url)->toContain('gemininano/')
        ->and($url)->toEndWith('.png');
});

it('throws when base64 cannot be decoded on store', function (): void {
    Storage::fake('public');
    Config::set('gemininano.store', true);
    Config::set('gemininano.disk', 'public');

    // invalid base64 string
    $resp = new GenerateResponse('@@@not-base64@@@', []);
    $resp->result();
})->throws(GeminiNanoImageResponseException::class);
