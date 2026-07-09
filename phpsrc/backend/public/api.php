<?php

declare(strict_types=1);

use QrCatalog\Controllers\AuthController;
use QrCatalog\Controllers\CategoriesController;
use QrCatalog\Controllers\CompanyInfoController;
use QrCatalog\Controllers\ProductsController;
use QrCatalog\Core\Config;
use QrCatalog\Core\Database;
use QrCatalog\Core\HttpException;
use QrCatalog\Core\Request;
use QrCatalog\Core\Response;
use QrCatalog\Core\Router;
use QrCatalog\Services\AuthService;
use QrCatalog\Services\CaptchaService;
use QrCatalog\Services\CategoryService;
use QrCatalog\Services\CompanyInfoService;
use QrCatalog\Services\FileStorageService;
use QrCatalog\Services\ProductService;
use QrCatalog\Services\QrCodeImageService;

if (!isset($_ENV['PUBLIC_PATH']) && getenv('PUBLIC_PATH') === false) {
    $_ENV['PUBLIC_PATH'] = __DIR__;
    putenv('PUBLIC_PATH=' . __DIR__);
}

$resolveBackendBasePath = static function (): ?string {
    $candidates = [];

    $pathConfig = __DIR__ . '/backend-path.php';
    if (is_file($pathConfig)) {
        $configuredPath = require $pathConfig;
        if (is_string($configuredPath) && trim($configuredPath) !== '') {
            $candidates[] = rtrim($configuredPath, DIRECTORY_SEPARATOR . '/');
        }
    }

    foreach ([
        $_SERVER['BACKEND_BASE_PATH'] ?? null,
        getenv('BACKEND_BASE_PATH') ?: null,
        dirname(__DIR__),
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'backend',
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'namelenam-backend',
    ] as $candidate) {
        if (is_string($candidate) && trim($candidate) !== '') {
            $candidates[] = rtrim($candidate, DIRECTORY_SEPARATOR . '/');
        }
    }

    foreach (array_unique($candidates) as $candidate) {
        if (is_file($candidate . DIRECTORY_SEPARATOR . 'bootstrap.php')) {
            return $candidate;
        }
    }

    return null;
};

$backendBasePath = $resolveBackendBasePath();
if ($backendBasePath === null) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Backend bootstrap path was not found. Create public_html/backend-path.php or place the backend in ../namelenam-backend.';
    exit;
}

require_once $backendBasePath . '/bootstrap.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = array_filter(array_map('trim', explode(',', Config::get('CORS_ALLOWED_ORIGINS', 'https://www.namelenam.com') ?? '')));

if (in_array('*', $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: *');
} elseif ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}

header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

$request = Request::capture();
if ($request->method === 'OPTIONS') {
    Response::noContent();
    exit;
}

if ($request->method === 'GET' && $request->path === '/api/auth/captcha') {
    Response::json((new CaptchaService())->createChallenge());
    exit;
}

try {
    $db = Database::connection();
    $files = new FileStorageService();
    $qrCodes = new QrCodeImageService($files);

    $authController = new AuthController(new AuthService($db), new CaptchaService());
    $categoriesController = new CategoriesController(new CategoryService($db));
    $companyInfoController = new CompanyInfoController(new CompanyInfoService($db), $files);
    $productsController = new ProductsController(new ProductService($db, $qrCodes), $files);

    $router = new Router();

    $router->get('/api/auth/captcha', [$authController, 'captcha']);
    $router->post('/api/auth/login', [$authController, 'login']);
    $router->get('/api/auth/me', [$authController, 'me']);

    $router->get('/api/categories', [$categoriesController, 'index']);
    $router->get('/api/categories/{id}', [$categoriesController, 'show']);
    $router->post('/api/categories', [$categoriesController, 'create']);
    $router->put('/api/categories/{id}', [$categoriesController, 'update']);
    $router->delete('/api/categories/{id}', [$categoriesController, 'delete']);

    $router->get('/api/company-info', [$companyInfoController, 'show']);
    $router->put('/api/company-info', [$companyInfoController, 'update']);
    $router->post('/api/company-info/home-hero-image', [$companyInfoController, 'uploadHomeHeroImage']);

    $router->get('/api/products', [$productsController, 'index']);
    $router->get('/api/products/{id}', [$productsController, 'show']);
    $router->get('/api/products/by-slug/{slug}', [$productsController, 'bySlug']);
    $router->post('/api/products', [$productsController, 'create']);
    $router->put('/api/products/{id}', [$productsController, 'update']);
    $router->delete('/api/products/{id}', [$productsController, 'delete']);
    $router->post('/api/products/{id}/images', [$productsController, 'uploadImage']);
    $router->delete('/api/products/images/{imageId}', [$productsController, 'deleteImage']);
    $router->post('/api/products/{id}/qr-code', [$productsController, 'createQrCode']);

    $router->dispatch($request);
} catch (HttpException $exception) {
    Response::error($exception->getMessage(), $exception->statusCode());
} catch (Throwable $exception) {
    error_log('[QrCatalog] ' . $exception::class . ': ' . $exception->getMessage());
    $isDebug = filter_var(Config::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
    Response::error($isDebug ? $exception->getMessage() : 'Server error.', 500);
}
