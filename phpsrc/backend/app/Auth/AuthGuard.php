<?php

declare(strict_types=1);

namespace QrCatalog\Auth;

use QrCatalog\Core\HttpException;
use QrCatalog\Core\Request;

final class AuthGuard
{
    /** @return array<string, mixed> */
    public static function requireAuthenticated(Request $request): array
    {
        $token = $request->bearerToken();
        if ($token === null || $token === '') {
            throw new HttpException(401, 'Authentication is required.');
        }

        return Jwt::decode($token);
    }

    /** @return array<string, mixed> */
    public static function requireAdmin(Request $request): array
    {
        $payload = self::requireAuthenticated($request);
        if (strcasecmp((string) ($payload['role'] ?? ''), 'Admin') !== 0) {
            throw new HttpException(403, 'Admin access is required.');
        }

        return $payload;
    }
}
