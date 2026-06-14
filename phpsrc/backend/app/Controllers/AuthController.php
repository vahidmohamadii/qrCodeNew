<?php

declare(strict_types=1);

namespace QrCatalog\Controllers;

use QrCatalog\Auth\AuthGuard;
use QrCatalog\Core\Request;
use QrCatalog\Core\Response;
use QrCatalog\Services\AuthService;

final class AuthController
{
    public function __construct(private AuthService $auth)
    {
    }

    public function login(Request $request): void
    {
        $result = $this->auth->login((string) $request->input('email', ''), (string) $request->input('password', ''));

        if ($result === null) {
            Response::error('Unauthorized.', 401);
            return;
        }

        Response::json($result);
    }

    public function me(Request $request): void
    {
        $payload = AuthGuard::requireAuthenticated($request);
        $email = (string) ($request->query('email') ?: ($payload['email'] ?? ''));
        $result = $this->auth->currentUser($email);

        if ($result === null) {
            Response::error('Not found.', 404);
            return;
        }

        Response::json($result);
    }
}
