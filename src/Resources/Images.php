<?php

declare(strict_types=1);

namespace Noxsi\GeminiNano\Resources;

use Illuminate\Support\Facades\Http;
use Noxsi\GeminiNano\Responses\Images\GenerateResponse;
use RuntimeException;
use Noxsi\GeminiNano\Client;

final readonly class Images
{
    public function __construct(
        private Client $client,
    ) {
    }

    /**
     * @param string $prompt
     * @param array<string, mixed> $options add fields like generateConfig
     * @return GenerateResponse
     */
    public function generate(string $prompt, array $options = []): GenerateResponse
    {
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
        ];

        if ($options !== []) {
            $payload = array_merge($payload, $options);
        }

        $model = config('gemininano.model', 'gemini-2.5-flash-image');

        $http = Http::baseUrl($this->client->baseUrl)
            ->timeout($this->client->timeout)
            ->withHeaders([
                'x-goog-api-key' => $this->client->apiKey,
                'Content-Type'   => 'application/json',
            ]);

        $endpoint = sprintf('/v1beta/models/%s:generateContent', $model);

        $response = $http->post($endpoint, $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                sprintf(
                    'Gemini image generation failed (%s): %s',
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
