<?php

declare(strict_types=1);

namespace QrCatalog\Core;

final class Request
{
    /**
     * @param array<string, mixed> $body
     * @param array<string, mixed> $query
     * @param array<string, mixed> $files
     * @param array<string, string> $headers
     */
    public function __construct(
        public string $method,
        public string $path,
        private array $body,
        private array $query,
        private array $files,
        private array $headers
    ) {
    }

    public static function capture(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        $body = $_POST;
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if (str_contains(strtolower($contentType), 'application/json')) {
            $decoded = json_decode(file_get_contents('php://input') ?: '{}', true);
            $body = is_array($decoded) ? $decoded : [];
        }

        return new self($method, rtrim($path, '/') ?: '/', $body, $_GET, $_FILES, $headers);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->body;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /** @return array<string, mixed>|null */
    public function file(string $key): ?array
    {
        return isset($this->files[$key]) && is_array($this->files[$key]) ? $this->files[$key] : null;
    }

    public function bearerToken(): ?string
    {
        $authorization = $this->headers['Authorization']
            ?? $this->headers['authorization']
            ?? $_SERVER['HTTP_AUTHORIZATION']
            ?? '';

        if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) === 1) {
            return trim($matches[1]);
        }

        return null;
    }
}
