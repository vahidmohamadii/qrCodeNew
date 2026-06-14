<?php

declare(strict_types=1);

namespace QrCatalog\Auth;

use QrCatalog\Core\Config;
use QrCatalog\Core\HttpException;
use function QrCatalog\Support\base64url_decode;
use function QrCatalog\Support\base64url_encode;

final class Jwt
{
    /** @return array<string, mixed> */
    public static function create(string $email, string $fullName, string $role): array
    {
        $minutes = (int) Config::get('JWT_MINUTES', '120');
        $payload = [
            'iss' => Config::get('JWT_ISSUER', 'QrCode'),
            'aud' => Config::get('JWT_AUDIENCE', 'QrCode'),
            'exp' => time() + ($minutes * 60),
            'email' => $email,
            'name' => $fullName,
            'role' => $role,
        ];

        return [
            'token' => self::encode($payload),
            'payload' => $payload,
        ];
    }

    /** @param array<string, mixed> $payload */
    private static function encode(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $headerPart = base64url_encode(json_encode($header, JSON_THROW_ON_ERROR));
        $payloadPart = base64url_encode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $headerPart . '.' . $payloadPart, Config::get('JWT_KEY', ''), true);

        return $headerPart . '.' . $payloadPart . '.' . base64url_encode($signature);
    }

    /** @return array<string, mixed> */
    public static function decode(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new HttpException(401, 'Invalid token.');
        }

        [$headerPart, $payloadPart, $signaturePart] = $parts;
        $expectedSignature = hash_hmac('sha256', $headerPart . '.' . $payloadPart, Config::get('JWT_KEY', ''), true);

        if (!hash_equals($expectedSignature, base64url_decode($signaturePart))) {
            throw new HttpException(401, 'Invalid token.');
        }

        $payload = json_decode(base64url_decode($payloadPart), true);
        if (!is_array($payload)) {
            throw new HttpException(401, 'Invalid token.');
        }

        if (($payload['exp'] ?? 0) < time()) {
            throw new HttpException(401, 'Token expired.');
        }

        if (($payload['iss'] ?? null) !== Config::get('JWT_ISSUER', 'QrCode')) {
            throw new HttpException(401, 'Invalid token issuer.');
        }

        if (($payload['aud'] ?? null) !== Config::get('JWT_AUDIENCE', 'QrCode')) {
            throw new HttpException(401, 'Invalid token audience.');
        }

        return $payload;
    }
}
