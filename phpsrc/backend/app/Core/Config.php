<?php

declare(strict_types=1);

namespace QrCatalog\Core;

final class Config
{
    private static string $basePath;

    /** @var array<string, string> */
    private static array $values = [];

    public static function load(string $basePath): void
    {
        self::$basePath = $basePath;
        self::$values = [
            'APP_ENV' => 'production',
            'APP_URL' => 'https://www.namelenam.com',
            'PUBLIC_BASE_URL' => 'https://www.namelenam.com',
            'CORS_ALLOWED_ORIGINS' => 'https://www.namelenam.com',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'qrcode_catalog',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
            'JWT_KEY' => 'change-this-secret-key-before-production',
            'JWT_ISSUER' => 'QrCode',
            'JWT_AUDIENCE' => 'QrCode',
            'JWT_MINUTES' => '120',
        ];

        $envPath = $basePath . '/.env';
        if (is_file($envPath)) {
            foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }

                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $value = trim($value, "\"'");
                self::$values[$key] = $value;
                $_ENV[$key] = $value;
                putenv($key . '=' . $value);
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $environmentValue = getenv($key);
        if ($environmentValue !== false) {
            return $environmentValue;
        }

        return self::$values[$key] ?? $default;
    }

    public static function basePath(string $append = ''): string
    {
        return self::$basePath . ($append === '' ? '' : DIRECTORY_SEPARATOR . ltrim($append, DIRECTORY_SEPARATOR));
    }

    public static function publicPath(string $append = ''): string
    {
        return self::basePath('public' . ($append === '' ? '' : DIRECTORY_SEPARATOR . ltrim($append, DIRECTORY_SEPARATOR)));
    }
}
