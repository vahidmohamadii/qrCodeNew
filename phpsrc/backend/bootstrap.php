<?php

declare(strict_types=1);

use QrCatalog\Core\Config;

$basePath = __DIR__;

$composerAutoload = $basePath . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

require_once $basePath . '/app/Support/helpers.php';

spl_autoload_register(static function (string $class) use ($basePath): void {
    $prefix = 'QrCatalog\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $basePath . '/app/' . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

Config::load($basePath);
