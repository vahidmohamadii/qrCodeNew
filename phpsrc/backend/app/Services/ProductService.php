<?php

declare(strict_types=1);

namespace QrCatalog\Services;

use PDO;
use QrCatalog\Core\Config;
use QrCatalog\Core\HttpException;
use function QrCatalog\Support\bool_value;
use function QrCatalog\Support\now;
use function QrCatalog\Support\nullable;
use function QrCatalog\Support\slugify;

final class ProductService
{
    public const MAX_IMAGES_PER_PRODUCT = 5;

    public function __construct(
        private PDO $db,
        private QrCodeImageService $qrCodes
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(?string $search = null, ?int $categoryId = null): array
    {
        $sql = $this->baseProductSql();
        $where = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $where[] = '(p.name LIKE :search OR p.sku LIKE :search OR p.product_code LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($categoryId !== null) {
            $where[] = 'p.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY p.name';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        $products = array_map(fn (array $row): array => $this->mapProduct($row), $statement->fetchAll());

        return $this->attachImages($products);
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->db->prepare($this->baseProductSql() . ' WHERE p.id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        if (!$row) {
            return null;
        }

        return $this->attachImages([$this->mapProduct($row)])[0] ?? null;
    }

    /** @return array<string, mixed>|null */
    public function findPublicBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare($this->baseProductSql() . ' WHERE p.slug = :slug AND p.is_active = 1 LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        if (!$row) {
            return null;
        }

        $product = $this->attachImages([$this->mapProduct($row)])[0] ?? null;
        if ($product === null) {
            return null;
        }

        return [
            'name' => $product['name'],
            'slug' => $product['slug'],
            'categoryName' => $product['categoryName'],
            'sku' => $product['sku'],
            'productCode' => $product['productCode'],
            'shortDescription' => $product['shortDescription'],
            'fullDescription' => $product['fullDescription'],
            'brand' => $product['brand'],
            'model' => $product['model'],
            'price' => $product['price'],
            'currency' => $product['currency'],
            'warrantyInfo' => $product['warrantyInfo'],
            'technicalSpecifications' => $product['technicalSpecifications'],
            'additionalInformation' => $product['additionalInformation'],
            'images' => $product['images'],
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): array
    {
        $statement = $this->db->prepare(
            'INSERT INTO products
             (category_id, name, slug, sku, short_description, full_description, brand, model, price, currency, stock_quantity,
              product_code, barcode, weight, dimensions, material, color, warranty_info, technical_specifications,
              additional_information, meta_title, meta_description, is_active, is_featured, created_at, updated_at)
             VALUES
             (:category_id, :name, :slug, :sku, :short_description, :full_description, :brand, :model, :price, :currency, :stock_quantity,
              :product_code, :barcode, :weight, :dimensions, :material, :color, :warranty_info, :technical_specifications,
              :additional_information, :meta_title, :meta_description, :is_active, :is_featured, :created_at, :updated_at)'
        );

        $now = now();
        $statement->execute($this->productParams($data, $now, $now));

        return $this->find((int) $this->db->lastInsertId()) ?? [];
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): ?array
    {
        if ($this->find($id) === null) {
            return null;
        }

        $statement = $this->db->prepare(
            'UPDATE products
             SET category_id = :category_id,
                 name = :name,
                 slug = :slug,
                 sku = :sku,
                 short_description = :short_description,
                 full_description = :full_description,
                 brand = :brand,
                 model = :model,
                 price = :price,
                 currency = :currency,
                 stock_quantity = :stock_quantity,
                 product_code = :product_code,
                 barcode = :barcode,
                 weight = :weight,
                 dimensions = :dimensions,
                 material = :material,
                 color = :color,
                 warranty_info = :warranty_info,
                 technical_specifications = :technical_specifications,
                 additional_information = :additional_information,
                 meta_title = :meta_title,
                 meta_description = :meta_description,
                 is_active = :is_active,
                 is_featured = :is_featured,
                 updated_at = :updated_at
             WHERE id = :id'
        );

        $params = $this->productParams($data, null, now());
        unset($params['created_at']);
        $params['id'] = $id;
        $statement->execute($params);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    /** @return array<string, mixed>|null */
    public function addImage(int $productId, string $imageUrl, ?string $altText, bool $isMainImage, int $sortOrder): ?array
    {
        if (!$this->productExists($productId)) {
            return null;
        }

        if ($this->imageCount($productId) >= self::MAX_IMAGES_PER_PRODUCT) {
            throw new HttpException(400, 'A product can have up to 5 images.');
        }

        if ($isMainImage) {
            $statement = $this->db->prepare('UPDATE product_images SET is_main_image = 0, updated_at = :updated_at WHERE product_id = :product_id');
            $statement->execute(['product_id' => $productId, 'updated_at' => now()]);
        }

        $statement = $this->db->prepare(
            'INSERT INTO product_images (product_id, image_url, alt_text, sort_order, is_main_image, created_at, updated_at)
             VALUES (:product_id, :image_url, :alt_text, :sort_order, :is_main_image, :created_at, :updated_at)'
        );

        $now = now();
        $statement->execute([
            'product_id' => $productId,
            'image_url' => $imageUrl,
            'alt_text' => $altText,
            'sort_order' => $sortOrder,
            'is_main_image' => $isMainImage ? 1 : 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->findImage((int) $this->db->lastInsertId());
    }

    public function remainingImageSlots(int $productId): ?int
    {
        if (!$this->productExists($productId)) {
            return null;
        }

        return max(0, self::MAX_IMAGES_PER_PRODUCT - $this->imageCount($productId));
    }

    public function deleteImage(int $imageId): bool
    {
        $statement = $this->db->prepare('DELETE FROM product_images WHERE id = :id');
        $statement->execute(['id' => $imageId]);

        return $statement->rowCount() > 0;
    }

    /** @param array<string, mixed> $data */
    public function createQrCode(int $productId, array $data): ?array
    {
        $product = $this->find($productId);
        if ($product === null) {
            return null;
        }

        $publicBaseUrl = rtrim(Config::get('PUBLIC_BASE_URL', Config::get('APP_URL', 'https://www.namelenam.com')) ?? '', '/');
        $qrUrl = $publicBaseUrl . '/products/' . $product['slug'];
        $imagePath = $this->qrCodes->createImage($qrUrl, $product['slug'] . '-qr.svg');

        $existing = $this->db->prepare('SELECT id FROM product_qr_codes WHERE product_id = :product_id LIMIT 1');
        $existing->execute(['product_id' => $productId]);
        $existingId = $existing->fetchColumn();

        $params = [
            'product_id' => $productId,
            'qr_code_url' => $qrUrl,
            'qr_code_image_path' => $imagePath,
            'label_title' => nullable($data['labelTitle'] ?? null),
            'label_description' => nullable($data['labelDescription'] ?? null),
            'batch_number' => nullable($data['batchNumber'] ?? null),
            'serial_number' => nullable($data['serialNumber'] ?? null),
            'manufacturing_date' => $this->dateOrNull($data['manufacturingDate'] ?? null),
            'expiry_date' => $this->dateOrNull($data['expiryDate'] ?? null),
            'custom_note' => nullable($data['customNote'] ?? null),
            'updated_at' => now(),
        ];

        if ($existingId) {
            $params['id'] = (int) $existingId;
            $statement = $this->db->prepare(
                'UPDATE product_qr_codes
                 SET qr_code_url = :qr_code_url,
                     qr_code_image_path = :qr_code_image_path,
                     label_title = :label_title,
                     label_description = :label_description,
                     batch_number = :batch_number,
                     serial_number = :serial_number,
                     manufacturing_date = :manufacturing_date,
                     expiry_date = :expiry_date,
                     custom_note = :custom_note,
                     updated_at = :updated_at
                 WHERE id = :id'
            );
            unset($params['product_id']);
            $statement->execute($params);

            return $this->findQrCode((int) $existingId);
        }

        $params['created_at'] = now();
        $statement = $this->db->prepare(
            'INSERT INTO product_qr_codes
             (product_id, qr_code_url, qr_code_image_path, label_title, label_description, batch_number, serial_number,
              manufacturing_date, expiry_date, custom_note, created_at, updated_at)
             VALUES
             (:product_id, :qr_code_url, :qr_code_image_path, :label_title, :label_description, :batch_number, :serial_number,
              :manufacturing_date, :expiry_date, :custom_note, :created_at, :updated_at)'
        );
        $statement->execute($params);

        return $this->findQrCode((int) $this->db->lastInsertId());
    }

    private function baseProductSql(): string
    {
        return 'SELECT p.*, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id';
    }

    private function productExists(int $productId): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM products WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $productId]);

        return (bool) $statement->fetchColumn();
    }

    private function imageCount(int $productId): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = :product_id');
        $statement->execute(['product_id' => $productId]);

        return (int) $statement->fetchColumn();
    }

    /** @param array<string, mixed> $data */
    private function productParams(array $data, ?string $createdAt, string $updatedAt): array
    {
        $name = (string) ($data['name'] ?? '');
        $slugSource = trim((string) ($data['slug'] ?? '')) === '' ? $name : (string) $data['slug'];
        $params = [
            'category_id' => (int) ($data['categoryId'] ?? 0),
            'name' => $name,
            'slug' => slugify($slugSource),
            'sku' => (string) ($data['sku'] ?? $data['SKU'] ?? ''),
            'short_description' => (string) ($data['shortDescription'] ?? ''),
            'full_description' => (string) ($data['fullDescription'] ?? ''),
            'brand' => (string) ($data['brand'] ?? ''),
            'model' => (string) ($data['model'] ?? ''),
            'price' => nullable($data['price'] ?? null),
            'currency' => nullable($data['currency'] ?? null),
            'stock_quantity' => nullable($data['stockQuantity'] ?? null),
            'product_code' => (string) ($data['productCode'] ?? ''),
            'barcode' => nullable($data['barcode'] ?? null),
            'weight' => nullable($data['weight'] ?? null),
            'dimensions' => nullable($data['dimensions'] ?? null),
            'material' => nullable($data['material'] ?? null),
            'color' => nullable($data['color'] ?? null),
            'warranty_info' => nullable($data['warrantyInfo'] ?? null),
            'technical_specifications' => nullable($data['technicalSpecifications'] ?? null),
            'additional_information' => nullable($data['additionalInformation'] ?? null),
            'meta_title' => nullable($data['metaTitle'] ?? null),
            'meta_description' => nullable($data['metaDescription'] ?? null),
            'is_active' => bool_value($data['isActive'] ?? true) ? 1 : 0,
            'is_featured' => bool_value($data['isFeatured'] ?? false) ? 1 : 0,
            'updated_at' => $updatedAt,
        ];

        if ($createdAt !== null) {
            $params['created_at'] = $createdAt;
        }

        return $params;
    }

    /** @param array<int, array<string, mixed>> $products */
    private function attachImages(array $products): array
    {
        if ($products === []) {
            return [];
        }

        $ids = array_column($products, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $statement = $this->db->prepare("SELECT * FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order, id");
        $statement->execute($ids);

        $imagesByProduct = [];
        foreach ($statement->fetchAll() as $row) {
            $imagesByProduct[(int) $row['product_id']][] = $this->mapImage($row);
        }

        foreach ($products as &$product) {
            $product['images'] = $imagesByProduct[(int) $product['id']] ?? [];
        }

        return $products;
    }

    /** @return array<string, mixed>|null */
    private function findImage(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM product_images WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row ? $this->mapImage($row) : null;
    }

    /** @return array<string, mixed>|null */
    private function findQrCode(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM product_qr_codes WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row ? $this->mapQrCode($row) : null;
    }

    private function dateOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timestamp = strtotime((string) $value);

        return $timestamp === false ? null : gmdate('Y-m-d H:i:s', $timestamp);
    }

    /** @param array<string, mixed> $row */
    private function mapProduct(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'categoryId' => (int) $row['category_id'],
            'categoryName' => $row['category_name'] ?? '',
            'name' => $row['name'],
            'slug' => $row['slug'],
            'sku' => $row['sku'],
            'shortDescription' => $row['short_description'],
            'fullDescription' => $row['full_description'],
            'brand' => $row['brand'],
            'model' => $row['model'],
            'price' => $row['price'] === null ? null : (float) $row['price'],
            'currency' => $row['currency'],
            'stockQuantity' => $row['stock_quantity'] === null ? null : (int) $row['stock_quantity'],
            'productCode' => $row['product_code'],
            'barcode' => $row['barcode'],
            'weight' => $row['weight'] === null ? null : (float) $row['weight'],
            'dimensions' => $row['dimensions'],
            'material' => $row['material'],
            'color' => $row['color'],
            'warrantyInfo' => $row['warranty_info'],
            'technicalSpecifications' => $row['technical_specifications'],
            'additionalInformation' => $row['additional_information'],
            'metaTitle' => $row['meta_title'],
            'metaDescription' => $row['meta_description'],
            'isActive' => (bool) $row['is_active'],
            'isFeatured' => (bool) $row['is_featured'],
            'images' => [],
        ];
    }

    /** @param array<string, mixed> $row */
    private function mapImage(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'productId' => (int) $row['product_id'],
            'imageUrl' => $row['image_url'],
            'altText' => $row['alt_text'],
            'sortOrder' => (int) $row['sort_order'],
            'isMainImage' => (bool) $row['is_main_image'],
        ];
    }

    /** @param array<string, mixed> $row */
    private function mapQrCode(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'productId' => (int) $row['product_id'],
            'qrCodeUrl' => $row['qr_code_url'],
            'qrCodeImagePath' => $row['qr_code_image_path'],
            'labelTitle' => $row['label_title'],
            'labelDescription' => $row['label_description'],
            'batchNumber' => $row['batch_number'],
            'serialNumber' => $row['serial_number'],
            'manufacturingDate' => $row['manufacturing_date'],
            'expiryDate' => $row['expiry_date'],
            'customNote' => $row['custom_note'],
        ];
    }
}
