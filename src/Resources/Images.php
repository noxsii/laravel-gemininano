<?php

declare(strict_types=1);

namespace Noxsi\GeminiNano\Resources;

use Illuminate\Support\Facades\Http;
use Noxsi\GeminiNano\Client;
use Noxsi\GeminiNano\Responses\Images\GenerateResponse;
use RuntimeException;

final readonly class Images
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function generate(string $prompt, array $options = []): GenerateResponse
    {
        $payload = array_merge([
            'prompt' => $prompt,
            // ggf. weitere default options wie size, steps, etc.
        ], $options);

        $http = Http::baseUrl($this->client->baseUrl)
            ->timeout($this->client->timeout)
            ->withToken($this->client->apiKey);

        $response = $http->post('/v1/generate-image', $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                sprintf(
                    'GeminiNano image generation failed (%s): %s',
                    $response->status(),
                    $response->body()
                )
            );
        }

        /** @var array<string, mixed> $data */
        $data = $response->json();

        return GenerateResponse::fromArray($data);
    }
}
