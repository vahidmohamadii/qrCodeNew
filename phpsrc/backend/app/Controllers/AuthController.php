<?php

declare(strict_types=1);

namespace QrCatalog\Controllers;

use QrCatalog\Auth\AuthGuard;
use QrCatalog\Core\Request;
use QrCatalog\Core\Response;
use QrCatalog\Services\AuthService;
use QrCatalog\Services\CaptchaService;

final class AuthController
{
    public function __construct(private AuthService $auth, private CaptchaService $captcha)
    {
    }

    public function captcha(Request $request): void
    {
        Response::json($this->captcha->createChallenge());
    }

    public function login(Request $request): void
    {
        $isCaptchaValid = $this->captcha->validate(
            (string) $request->input('captchaToken', ''),
            (string) $request->input('captchaAnswer', '')
        );

        if (!$isCaptchaValid) {
            Response::error('Captcha is incorrect.', 400);
            return;
        }

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
