<?php

declare(strict_types=1);

namespace QrCatalog\Core;

use RuntimeException;

final class HttpException extends RuntimeException
{
    public function __construct(private int $statusCode, string $message)
    {
        parent::__construct($message, $statusCode);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
