<?php

declare(strict_types=1);

namespace QrCatalog\Controllers;

use QrCatalog\Auth\AuthGuard;
use QrCatalog\Core\Request;
use QrCatalog\Core\Response;
use QrCatalog\Services\CompanyInfoService;

final class CompanyInfoController
{
    public function __construct(private CompanyInfoService $companyInfo)
    {
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
}
