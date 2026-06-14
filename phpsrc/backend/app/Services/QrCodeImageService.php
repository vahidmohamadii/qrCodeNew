<?php

declare(strict_types=1);

namespace QrCatalog\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use QrCatalog\Core\HttpException;

final class QrCodeImageService
{
    public function __construct(private FileStorageService $files)
    {
    }

    public function createImage(string $payload, string $fileName): string
    {
        if (!class_exists(QRCode::class) || !class_exists(QROptions::class)) {
            throw new HttpException(500, 'QR dependency is missing. Run composer install in phpsrc/backend.');
        }

        $options = new QROptions();
        $options->outputBase64 = false;
        $options->scale = 8;

        $svg = (new QRCode($options))->render($payload);

        return $this->files->saveBytes($svg, $fileName, 'qr-codes');
    }
}
