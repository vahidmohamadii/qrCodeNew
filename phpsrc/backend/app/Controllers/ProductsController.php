<?php

declare(strict_types=1);

namespace QrCatalog\Controllers;

use QrCatalog\Auth\AuthGuard;
use QrCatalog\Core\Request;
use QrCatalog\Core\Response;
use QrCatalog\Services\FileStorageService;
use QrCatalog\Services\ProductService;
use function QrCatalog\Support\bool_value;

final class ProductsController
{
    public function __construct(
        private ProductService $products,
        private FileStorageService $files
    ) {
    }

    public function index(Request $request): void
    {
        $categoryId = $request->query('categoryId');
        Response::json($this->products->all(
            $request->query('search'),
            $categoryId === null || $categoryId === '' ? null : (int) $categoryId
        ));
    }

    public function show(Request $request, int $id): void
    {
        $product = $this->products->find($id);
        $product === null ? Response::error('Not found.', 404) : Response::json($product);
    }

    public function bySlug(Request $request, string $slug): void
    {
        $product = $this->products->findPublicBySlug($slug);
        $product === null ? Response::error('Not found.', 404) : Response::json($product);
    }

    public function create(Request $request): void
    {
        AuthGuard::requireAdmin($request);
        Response::json($this->products->create($request->all()), 201);
    }

    public function update(Request $request, int $id): void
    {
        AuthGuard::requireAdmin($request);
        $product = $this->products->update($id, $request->all());
        $product === null ? Response::error('Not found.', 404) : Response::json($product);
    }

    public function delete(Request $request, int $id): void
    {
        AuthGuard::requireAdmin($request);
        $this->products->delete($id) ? Response::noContent() : Response::error('Not found.', 404);
    }

    public function uploadImage(Request $request, int $id): void
    {
        AuthGuard::requireAdmin($request);
        $image = $request->file('image');

        if ($image === null) {
            Response::text('Image file is required.', 400);
            return;
        }

        $remainingSlots = $this->products->remainingImageSlots($id);
        if ($remainingSlots === null) {
            Response::error('Not found.', 404);
            return;
        }

        if ($remainingSlots === 0) {
            Response::text('A product can have up to 5 images.', 400);
            return;
        }

        $url = $this->files->saveUpload($image, 'product-images');
        $result = $this->products->addImage(
            $id,
            $url,
            (string) $request->input('altText', ''),
            bool_value($request->input('isMainImage', false)),
            (int) $request->input('sortOrder', 0)
        );

        $result === null ? Response::error('Not found.', 404) : Response::json($result);
    }

    public function deleteImage(Request $request, int $imageId): void
    {
        AuthGuard::requireAdmin($request);
        $this->products->deleteImage($imageId) ? Response::noContent() : Response::error('Not found.', 404);
    }

    public function createQrCode(Request $request, int $id): void
    {
        AuthGuard::requireAdmin($request);
        $qrCode = $this->products->createQrCode($id, $request->all());
        $qrCode === null ? Response::error('Not found.', 404) : Response::json($qrCode);
    }
}
