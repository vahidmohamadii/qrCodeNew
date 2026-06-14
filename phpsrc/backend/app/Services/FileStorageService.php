<?php

declare(strict_types=1);

namespace QrCatalog\Services;

use QrCatalog\Core\Config;
use QrCatalog\Core\HttpException;

final class FileStorageService
{
    /** @param array<string, mixed> $file */
    public function saveUpload(array $file, string $folderName): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new HttpException(400, 'Image file is required.');
        }

        $sourcePath = (string) $file['tmp_name'];
        $originalName = basename((string) ($file['name'] ?? 'image'));
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
            throw new HttpException(400, 'Only image files are allowed.');
        }

        return $this->savePath($sourcePath, $originalName, $folderName, true);
    }

    public function saveBytes(string $bytes, string $fileName, string $folderName): string
    {
        $folderPath = Config::publicPath('uploads/' . $folderName);
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0775, true);
        }

        $safeFileName = $this->safeFileName($fileName);
        $absolutePath = $folderPath . DIRECTORY_SEPARATOR . $safeFileName;
        file_put_contents($absolutePath, $bytes);

        return '/uploads/' . $folderName . '/' . $safeFileName;
    }

    private function savePath(string $sourcePath, string $fileName, string $folderName, bool $uploadedFile): string
    {
        $folderPath = Config::publicPath('uploads/' . $folderName);
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0775, true);
        }

        $safeFileName = $this->safeFileName($fileName);
        $absolutePath = $folderPath . DIRECTORY_SEPARATOR . $safeFileName;
        $saved = $uploadedFile ? move_uploaded_file($sourcePath, $absolutePath) : copy($sourcePath, $absolutePath);

        if (!$saved) {
            throw new HttpException(500, 'Could not save file.');
        }

        return '/uploads/' . $folderName . '/' . $safeFileName;
    }

    private function safeFileName(string $fileName): string
    {
        $name = preg_replace('/[^a-zA-Z0-9._-]+/', '-', basename($fileName)) ?: 'file';

        return bin2hex(random_bytes(16)) . '_' . trim($name, '-');
    }
}
