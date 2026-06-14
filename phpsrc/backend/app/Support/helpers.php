<?php

declare(strict_types=1);

namespace QrCatalog\Support;

function now(): string
{
    return gmdate('Y-m-d H:i:s');
}

function slugify(string $value): string
{
    $normalized = trim($value);
    $normalized = function_exists('mb_strtolower') ? mb_strtolower($normalized, 'UTF-8') : strtolower($normalized);
    $normalized = preg_replace('/[^\pL\pN]+/u', '-', $normalized) ?? '';
    $slug = trim($normalized, '-');

    return $slug === '' ? 'item' : $slug;
}

function nullable(mixed $value): mixed
{
    return $value === '' ? null : $value;
}

function bool_value(mixed $value): bool
{
    if (is_bool($value)) {
        return $value;
    }

    if (is_numeric($value)) {
        return (int) $value === 1;
    }

    return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
}

function base64url_encode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function base64url_decode(string $value): string
{
    $padding = strlen($value) % 4;
    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    return base64_decode(strtr($value, '-_', '+/'), true) ?: '';
}
