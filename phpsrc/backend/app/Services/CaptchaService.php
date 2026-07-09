<?php

declare(strict_types=1);

namespace QrCatalog\Services;

use QrCatalog\Core\Config;
use function QrCatalog\Support\base64url_decode;
use function QrCatalog\Support\base64url_encode;

final class CaptchaService
{
    private const PURPOSE = 'login-captcha';
    private const TTL_SECONDS = 300;

    /** @return array<string, mixed> */
    public function createChallenge(): array
    {
        $left = random_int(2, 12);
        $right = random_int(1, 9);
        $operator = random_int(0, 1) === 0 ? '+' : '-';

        if ($operator === '-' && $right > $left) {
            [$left, $right] = [$right, $left];
        }

        $answer = $operator === '+' ? $left + $right : $left - $right;
        $payload = [
            'purpose' => self::PURPOSE,
            'answer' => $answer,
            'exp' => time() + self::TTL_SECONDS,
            'nonce' => bin2hex(random_bytes(8)),
        ];

        return [
            'question' => sprintf('%d %s %d = ?', $left, $operator, $right),
            'token' => $this->encode($payload),
            'expiresIn' => self::TTL_SECONDS,
        ];
    }

    public function validate(string $token, string $answer): bool
    {
        if ($token === '' || !preg_match('/^\d+$/', trim($answer))) {
            return false;
        }

        $payload = $this->decode($token);
        if ($payload === null) {
            return false;
        }

        if (($payload['purpose'] ?? '') !== self::PURPOSE || (int) ($payload['exp'] ?? 0) < time()) {
            return false;
        }

        return (int) $answer === (int) ($payload['answer'] ?? -1);
    }

    /** @param array<string, mixed> $payload */
    private function encode(array $payload): string
    {
        $payloadPart = base64url_encode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $payloadPart, Config::get('JWT_KEY', ''), true);

        return $payloadPart . '.' . base64url_encode($signature);
    }

    /** @return array<string, mixed>|null */
    private function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }

        [$payloadPart, $signaturePart] = $parts;
        $expectedSignature = hash_hmac('sha256', $payloadPart, Config::get('JWT_KEY', ''), true);
        if (!hash_equals($expectedSignature, base64url_decode($signaturePart))) {
            return null;
        }

        $payload = json_decode(base64url_decode($payloadPart), true);

        return is_array($payload) ? $payload : null;
    }
}
