<?php

declare(strict_types=1);

namespace Noxsi\GeminiNano\Resources;

use Illuminate\Support\Facades\Http;
use Noxsi\GeminiNano\Client;
use Noxsi\GeminiNano\Exceptions\GeminiNanoResourceException;
use Noxsi\GeminiNano\Responses\Images\GenerateResponse;

final readonly class Images
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * @param  string|null  $imagePath  Absolute or relative path to an image file (optional)
     * @param  string|null  $imageMimeType  e.g. image/jpeg, image/png (optional; auto-detected if null)
     * @param  array<string, mixed>  $options  Extra payload fields (generationConfig, safetySettings, ...)
     */
    public function generate(
        string $prompt,
        ?string $imagePath = null,
        ?string $imageMimeType = null,
        array $options = [],
    ): GenerateResponse {
        $parts = [];

        if ($imagePath !== null) {
            if (! is_readable($imagePath)) {
                throw new GeminiNanoResourceException(sprintf('Image file not readable: %s', $imagePath));
            }

            $binary = file_get_contents($imagePath);
            if ($binary === false) {
                throw new GeminiNanoResourceException(sprintf('Failed to read image file: %s', $imagePath));
            }

            $base64 = base64_encode($binary);

            $mimeType = $imageMimeType;
            if ($mimeType === null) {
                $detected = @mime_content_type($imagePath);
                $mimeType = is_string($detected) ? $detected : 'image/jpeg';
            }

            $parts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $base64,
                ],
            ];
        }

        $parts[] = [
            'text' => $prompt,
        ];

        $payload = [
            'contents' => [
                [
                    'parts' => $parts,
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
                'Content-Type' => 'application/json',
            ]);

        $endpoint = sprintf('/v1beta/models/%s:generateContent', $model);

        $response = $http->post($endpoint, $payload);

        if ($response->failed()) {
            throw new GeminiNanoResourceException(
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
