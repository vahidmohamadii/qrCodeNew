<?php

declare(strict_types=1);

namespace QrCatalog\Services;

use PDO;
use function QrCatalog\Support\bool_value;
use function QrCatalog\Support\now;
use function QrCatalog\Support\nullable;
use function QrCatalog\Support\slugify;

final class CategoryService
{
    public function __construct(private PDO $db)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(?string $search = null): array
    {
        $sql = 'SELECT * FROM categories';
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $sql .= ' WHERE name LIKE :search OR slug LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY name';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return array_map([$this, 'map'], $statement->fetchAll());
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $category = $statement->fetch();

        return $category ? $this->map($category) : null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): array
    {
        $statement = $this->db->prepare(
            'INSERT INTO categories (name, slug, description, parent_category_id, image_url, is_active, created_at, updated_at)
             VALUES (:name, :slug, :description, :parent_category_id, :image_url, :is_active, :created_at, :updated_at)'
        );

        $now = now();
        $statement->execute([
            'name' => (string) ($data['name'] ?? ''),
            'slug' => slugify((string) ($data['name'] ?? '')),
            'description' => nullable($data['description'] ?? null),
            'parent_category_id' => nullable($data['parentCategoryId'] ?? null),
            'image_url' => nullable($data['imageUrl'] ?? null),
            'is_active' => bool_value($data['isActive'] ?? true) ? 1 : 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->find((int) $this->db->lastInsertId()) ?? [];
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): ?array
    {
        if ($this->find($id) === null) {
            return null;
        }

        $statement = $this->db->prepare(
            'UPDATE categories
             SET name = :name,
                 slug = :slug,
                 description = :description,
                 parent_category_id = :parent_category_id,
                 image_url = :image_url,
                 is_active = :is_active,
                 updated_at = :updated_at
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'name' => (string) ($data['name'] ?? ''),
            'slug' => slugify((string) ($data['name'] ?? '')),
            'description' => nullable($data['description'] ?? null),
            'parent_category_id' => nullable($data['parentCategoryId'] ?? null),
            'image_url' => nullable($data['imageUrl'] ?? null),
            'is_active' => bool_value($data['isActive'] ?? true) ? 1 : 0,
            'updated_at' => now(),
        ]);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    /** @param array<string, mixed> $row */
    private function map(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'description' => $row['description'],
            'parentCategoryId' => $row['parent_category_id'] === null ? null : (int) $row['parent_category_id'],
            'imageUrl' => $row['image_url'],
            'isActive' => (bool) $row['is_active'],
        ];
    }
}
