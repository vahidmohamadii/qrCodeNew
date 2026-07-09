<?php

declare(strict_types=1);

namespace QrCatalog\Controllers;

use QrCatalog\Auth\AuthGuard;
use QrCatalog\Core\Request;
use QrCatalog\Core\Response;
use QrCatalog\Services\CompanyInfoService;
use QrCatalog\Services\FileStorageService;

final class CompanyInfoController
{
    public function __construct(
        private CompanyInfoService $companyInfo,
        private FileStorageService $files
    ) {
    }

    public function show(Request $request): void
    {
        Response::json($this->companyInfo->get());
    }

    public function update(Request $request): void
    {
        AuthGuard::requireAdmin($request);
        Response::json($this->companyInfo->update($request->all()));
    }

    public function uploadHomeHeroImage(Request $request): void
    {
        AuthGuard::requireAdmin($request);
        $image = $request->file('image');
        if ($image === null) {
            Response::error('Image file is required.', 400);
            return;
        }

        $imageUrl = $this->files->saveUpload($image, 'site');
        Response::json($this->companyInfo->updateHomeHeroImage($imageUrl));
    }
}
