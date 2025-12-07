<?php

declare(strict_types=1);

namespace Noxsi\LaravelGemininano\Responses\Images;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final readonly class GenerateResponse
{
    public function __construct(
        private string $base64Image,
        private array  $raw,
    ) {
    }

    /**
     * Baue das Response-Objekt aus der API-Response.
     *
     * Erwartet z.B.:
     * [
     *   'image_base64' => '...',
     *   ... weitere Felder ...
     * ]
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        // Passe hier den Key an die echte API an:
        $base64 = $data['image_base64'] ?? '';

        return new self(
            base64Image: $base64,
            raw: $data,
        );
    }

    /**
     * Gibt den nackten Base64-String zurück.
     */
    public function base64(): string
    {
        return $this->base64Image;
    }

    /**
     * Gibt die komplette rohe API-Response zurück.
     *
     * @return array<string, mixed>
     */
    public function raw(): array
    {
        return $this->raw;
    }

    /**
     * Haupt-Methode:
     * - Wenn store=true: Bild speichern und URL zurückgeben
     * - Wenn store=false: Base64-String zurückgeben
     */
    public function result(): string
    {
        if (! config('gemininano.store')) {
            return $this->base64Image;
        }

        $disk = config('gemininano.disk', 'public');
        $pathPrefix = trim(config('gemininano.path', 'gemininano'), '/');

        $filename = Str::uuid().'.png';
        $path = $pathPrefix !== '' ? $pathPrefix.'/'.$filename : $filename;

        Storage::disk($disk)->put($path, base64_decode($this->base64Image));

        return Storage::disk($disk)->url($path);
    }
}
