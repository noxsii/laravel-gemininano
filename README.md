# GeminiNano Laravel ⚡

**GeminiNano Laravel** is an elegant Laravel SDK for generating images with **Google Gemini** (e.g. `gemini-2.5-flash-image`).  
It wraps the official Gemini REST API in a clean, resource-based, Laravel-friendly API and can automatically:

- return **Base64 image data**, or
- **store images on your Laravel filesystem** and return a public URL – all controlled via config.

> Under the hood it calls:
> `POST https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent`
> with a text prompt and extracts the inline base64 image data.

---

## ✨ Features

- **Modern architecture** – clean `Client::factory()->make()` API
- **Resource-based** – separate `images()` resource for image generation
- **Laravel integration** – service provider + facade out of the box
- **Config-driven storage**
    - store images via Laravel’s filesystem and return URLs
    - or return raw Base64 directly (for APIs, tests, etc.)
- **Type-safe responses** with dedicated response classes
- **Framework-first DX** – built for Laravel 10/11/12 and PHP 8.2+

---

## 📦 Installation

Install the package via Composer:

```bash
composer require noxsi/laravel-gemininano
```

> For local development using a path repository, point your Laravel app’s `composer.json` to your package directory and then run the command above.

---

## ⚙️ Configuration

Publish the config file:

```bash
php artisan vendor:publish --provider="Noxsi\GeminiNano\GeminiNanoServiceProvider" --tag="config"
```

This will create `config/gemininano.php`.

### .env

Add your Gemini API configuration:

```env
GEMINI_NANO_URL=https://generativelanguage.googleapis.com
GEMINI_NANO_KEY=your-gemini-api-key
GEMINI_NANO_MODEL=gemini-2.5-flash-image
GEMINI_NANO_TIMEOUT=60

# Image handling
GEMINI_NANO_STORE=true          # true = store in filesystem, false = return base64
GEMINI_NANO_DISK=public         # Laravel filesystem disk
GEMINI_NANO_PATH=gemininano     # directory inside the disk
```

### Config file overview

```php
return [
    'base_url' => env('GEMINI_NANO_URL', 'https://generativelanguage.googleapis.com'),
    'api_key'  => env('GEMINI_NANO_KEY'),
    'model'    => env('GEMINI_NANO_MODEL', 'gemini-2.5-flash-image'),
    'timeout'  => (int) env('GEMINI_NANO_TIMEOUT', 60),

    'store' => (bool) env('GEMINI_NANO_STORE', true),
    'disk'  => env('GEMINI_NANO_DISK', 'public'),
    'path'  => env('GEMINI_NANO_PATH', 'gemininano'),
];
```

---

## 🚀 Usage

You can use the package either via the **client** or the **facade**.

### 1. Basic usage with the Facade

```php
use Noxsi\GeminiNano\Facades\GeminiNano; // Facade alias

// Generate an image from a text prompt
$response = GeminiNano::images()->generate(
    'Create a picture of a nano banana dish in a fancy restaurant with a Gemini theme'
);

// Depending on config:
// - GEMINI_NANO_STORE=true  -> result() returns a URL (stored via filesystem)
// - GEMINI_NANO_STORE=false -> result() returns a base64 string
$result = $response->result();

return response()->json([
    'image' => $result,
]);
```

### 2. Using the Client directly (DI-friendly)

```php
use Noxsi\GeminiNano\Client;

class BananaController
{
    public function __construct(
        private Client $gemini,
    ) {}

    public function __invoke()
    {
        $response = $this->gemini
            ->images()
            ->generate('A futuristic nano banana in a Gemini-themed lab');

        return [
            'image' => $response->result(),
        ];
    }
}
```

---

## 🧠 How the image pipeline works

When you call:

```php
$response = GeminiNano::images()->generate('Your prompt here');
```

the package:

1. Builds a request body compatible with the official Gemini API:

   ```json
   {
     "contents": [
       {
         "parts": [
           { "text": "Your prompt here" }
         ]
       }
     ]
   }
   ```

2. Sends a `POST` request to:

   ```text
   https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent
   ```

   with headers:
    - `x-goog-api-key: <your-api-key>`
    - `Content-Type: application/json`

3. Extracts the base64-encoded image data from the response:
    - `candidates[0].content.parts[*].inlineData.data`
    - (also supports snake_case `inline_data.data` for safety)

4. Wraps everything in a `GenerateResponse` object.

5. When you call `->result()`:

    - If `config('gemininano.store') === true`  
      → the package decodes the base64 string, stores it as a `.png` via `Storage::disk(...)`, and returns the public URL.

    - If `config('gemininano.store') === false`  
      → it simply returns the raw base64 string.

You can always access the raw data:

```php
$base64 = $response->base64();   // raw base64 image data
$raw    = $response->raw();      // full decoded Gemini JSON response
```

---

## 🎛 Advanced usage

### Passing extra options to Gemini

You can pass additional options like `generationConfig`, `safetySettings`, etc. They are merged into the root payload:

```php
$options = [
    'generationConfig' => [
        'temperature' => 0.7,
    ],
    'safetySettings' => [
        // ...
    ],
];

$response = GeminiNano::images()->generate(
    'A neon-lit banana robot in a Gemini-branded bar',
    $options
);

$image = $response->result();
```

### Returning base64 for an API endpoint

For API scenarios where you don’t want to store anything:

```env
GEMINI_NANO_STORE=false
```

Then in your controller:

```php
$response = GeminiNano::images()->generate('Minimalist banana icon, flat design');

return response()->json([
    'image_base64' => $response->result(), // now base64
]);
```

---

## 🧪 Testing

If you are testing **within your Laravel app** that uses the package, you can mock the HTTP layer as usual:

```php
use Illuminate\Support\Facades\Http;
use Noxsi\GeminiNano\Facades\GeminiNano;

test('it generates an image via gemini nano', function () {
    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'inlineData' => [
                                    'data' => base64_encode('dummy-image-binary'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $response = GeminiNano::images()->generate('Test prompt');

    expect($response->base64())->toBe(base64_encode('dummy-image-binary'));
});
```

If you’re testing **the package itself**, you can use Pest + Orchestra Testbench; the package is designed to run in a standard Laravel test environment.

---

## 🧱 Requirements

- PHP **8.2+**
- Laravel **10.x, 11.x, 12.x**
- `ext-mbstring`
- `ext-json`
- A valid Google Gemini API key

---

## 🔧 Local development (using a path repository)

If you’re developing this package locally, you can hook it into a test Laravel application via a path repository:

In your Laravel app’s `composer.json`:

```json
"repositories": [
  {
    "type": "path",
    "url": "../laravel-gemininano",
    "options": {
      "symlink": true
    }
  }
]
```

Then:

```bash
composer require noxsi/laravel-gemininano:*@dev
```

Any changes you make in the package directory will be reflected in your Laravel app (after `composer dump-autoload` when adding/removing classes).

---

## 📜 Changelog

All notable changes to this package will be documented in the `CHANGELOG.md` file.

---

## 🤝 Contributing

Pull requests are welcome!

If you plan something bigger (new resources, breaking changes, etc.), open an issue first to discuss your idea.

Steps:

1. Fork the repo
2. Create your feature branch: `git checkout -b feature/my-awesome-feature`
3. Run tests & formatter: `composer test` / `composer lint`
4. Commit your changes and open a PR

---

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
