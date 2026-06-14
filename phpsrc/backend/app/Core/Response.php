<?php

declare(strict_types=1);

namespace QrCatalog\Core;

final class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function text(string $message, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
    }

    public static function noContent(): void
    {
        http_response_code(204);
    }

    public static function error(string $message, int $status): void
    {
        self::json(['message' => $message], $status);
    }
}
