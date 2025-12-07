<?php

declare(strict_types=1);

namespace Noxsi\GeminiNano\Responses\Images;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class GenerateResponse
{
    public function __construct(
        private string $base64Image,
        /** @var array<string, mixed> */
        private array $raw,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $base64 = self::extractBase64($data);

        if ($base64 === null || $base64 === '') {
            throw new RuntimeException('Gemini response did not contain image data.');
        }

        return new self($base64, $data);
    }

    /**
     * Holt das Base64-Bild aus der Gemini-Response.
     *
     * Struktur laut Doku:
     * response.candidates[0].content.parts[*].inlineData.data (oder inline_data.data)
     */
    private static function extractBase64(array $data): ?string
    {
        $parts = $data['candidates'][0]['content']['parts'] ?? null;

        if (! is_array($parts)) {
            $parts = $data['parts'] ?? null;
        }

        if (! is_array($parts)) {
            return null;
        }

        foreach ($parts as $part) {
            if (isset($part['inlineData']['data']) && is_string($part['inlineData']['data'])) {
                return $part['inlineData']['data'];
            }

            if (isset($part['inline_data']['data']) && is_string($part['inline_data']['data'])) {
                return $part['inline_data']['data'];
            }
        }

        return null;
    }

    public function base64(): string
    {
        return $this->base64Image;
    }

    /**
     * @return array<string, mixed>
     */
    public function raw(): array
    {
        return $this->raw;
    }

    public function result(): string
    {
        if (! config('gemininano.store')) {
            return $this->base64Image;
        }

        $disk = (string) config('gemininano.disk', 'public');
        $pathPrefix = trim((string) config('gemininano.path', 'gemininano'), '/');

        $filename = Str::uuid().'.png';
        $path = $pathPrefix !== '' ? $pathPrefix.'/'.$filename : $filename;

        $binary = base64_decode($this->base64Image, true);
        if ($binary === false) {
            throw new RuntimeException('Failed to decode base64 image data.');
        }

        Storage::disk($disk)->put($path, $binary);

        return Storage::disk($disk)->url($path);
    }
}
