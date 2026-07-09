<?php

declare(strict_types=1);

namespace QrCatalog\Core;

use Throwable;

final class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: callable}> */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function put(string $pattern, callable $handler): void
    {
        $this->add('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, callable $handler): void
    {
        $this->add('DELETE', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler');
    }

    public function dispatch(Request $request): void
    {
        if ($request->method === 'OPTIONS') {
            Response::noContent();
            return;
        }

        try {
            foreach ($this->routes as $route) {
                if ($route['method'] !== $request->method) {
                    continue;
                }

                $regex = '#^' . preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $route['pattern']) . '$#';
                if (preg_match($regex, $request->path, $matches) !== 1) {
                    continue;
                }

                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[] = ctype_digit($value) ? (int) $value : $value;
                    }
                }

                ($route['handler'])($request, ...$params);
                return;
            }

            Response::error('Not found.', 404);
        } catch (HttpException $exception) {
            Response::error($exception->getMessage(), $exception->statusCode());
        } catch (Throwable $exception) {
            error_log('[QrCatalog] ' . $exception::class . ': ' . $exception->getMessage());
            $isDebug = filter_var(Config::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
            Response::error($isDebug ? $exception->getMessage() : 'Server error.', 500);
        }
    }
}
