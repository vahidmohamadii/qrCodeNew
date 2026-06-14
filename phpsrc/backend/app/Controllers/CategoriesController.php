<?php

declare(strict_types=1);

namespace QrCatalog\Controllers;

use PDOException;
use QrCatalog\Auth\AuthGuard;
use QrCatalog\Core\Request;
use QrCatalog\Core\Response;
use QrCatalog\Services\CategoryService;

final class CategoriesController
{
    public function __construct(private CategoryService $categories)
    {
    }

    public function index(Request $request): void
    {
        Response::json($this->categories->all($request->query('search')));
    }

    public function show(Request $request, int $id): void
    {
        $category = $this->categories->find($id);
        $category === null ? Response::error('Not found.', 404) : Response::json($category);
    }

    public function create(Request $request): void
    {
        AuthGuard::requireAdmin($request);
        Response::json($this->categories->create($request->all()), 201);
    }

    public function update(Request $request, int $id): void
    {
        AuthGuard::requireAdmin($request);
        $category = $this->categories->update($id, $request->all());
        $category === null ? Response::error('Not found.', 404) : Response::json($category);
    }

    public function delete(Request $request, int $id): void
    {
        AuthGuard::requireAdmin($request);

        try {
            $deleted = $this->categories->delete($id);
        } catch (PDOException) {
            Response::error('Category is in use.', 409);
            return;
        }

        $deleted ? Response::noContent() : Response::error('Not found.', 404);
    }
}
