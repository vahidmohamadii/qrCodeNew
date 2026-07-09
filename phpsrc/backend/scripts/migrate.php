<?php

declare(strict_types=1);

use QrCatalog\Core\Config;
use function QrCatalog\Support\now;
use function QrCatalog\Support\slugify;

require_once dirname(__DIR__) . '/bootstrap.php';

$databaseName = Config::get('DB_DATABASE', 'qrcode_catalog') ?? 'qrcode_catalog';
$quotedDatabase = '`' . str_replace('`', '``', $databaseName) . '`';
$serverDsn = sprintf(
    'mysql:host=%s;port=%s;charset=utf8mb4',
    Config::get('DB_HOST', '127.0.0.1'),
    Config::get('DB_PORT', '3306')
);

$pdo = new PDO(
    $serverDsn,
    Config::get('DB_USERNAME', 'root'),
    Config::get('DB_PASSWORD', ''),
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$pdo->exec("CREATE DATABASE IF NOT EXISTS {$quotedDatabase} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE {$quotedDatabase}");

$schema = file_get_contents(Config::basePath('database/schema.sql'));
foreach (array_filter(array_map('trim', explode(';', $schema ?: ''))) as $statement) {
    if (preg_match('/^INSERT\s+INTO\s+company_infos\b/i', $statement) === 1) {
        continue;
    }

    $pdo->exec($statement);
}

$companyColumns = $pdo->query('SHOW COLUMNS FROM company_infos')->fetchAll();
$companyColumnNames = array_column($companyColumns, 'Field');
if (!in_array('home_hero_image_url', $companyColumnNames, true)) {
    $pdo->exec('ALTER TABLE company_infos ADD home_hero_image_url VARCHAR(1000) NULL AFTER social_media_links');
}

$now = now();
$adminEmail = 'admin@example.com';
$adminPassword = 'Admin123!';
$adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);

$statement = $pdo->prepare(
    'INSERT INTO app_users (full_name, email, password_hash, role, is_active, created_at, updated_at)
     VALUES (:full_name, :email, :password_hash, :role, 1, :created_at, :updated_at)
     ON DUPLICATE KEY UPDATE
       full_name = VALUES(full_name),
       password_hash = VALUES(password_hash),
       role = VALUES(role),
       is_active = 1,
       updated_at = VALUES(updated_at)'
);
$statement->execute([
    'full_name' => 'Administrator',
    'email' => $adminEmail,
    'password_hash' => $adminHash,
    'role' => 'Admin',
    'created_at' => $now,
    'updated_at' => $now,
]);

$companyStatement = $pdo->prepare(
    'INSERT INTO company_infos
     (id, company_name, description, mission, vision, services, contact_information, address, email, phone_number, social_media_links, home_hero_image_url, created_at, updated_at)
     VALUES
     (1, :company_name, :description, :mission, :vision, :services, :contact_information, :address, :email, :phone_number, :social_media_links, :home_hero_image_url, :created_at, :updated_at)
     ON DUPLICATE KEY UPDATE
       company_name = VALUES(company_name),
       description = VALUES(description),
       mission = VALUES(mission),
       vision = VALUES(vision),
       services = VALUES(services),
       contact_information = VALUES(contact_information),
       address = VALUES(address),
       email = VALUES(email),
       phone_number = VALUES(phone_number),
       social_media_links = VALUES(social_media_links),
       home_hero_image_url = COALESCE(company_infos.home_hero_image_url, VALUES(home_hero_image_url)),
       updated_at = VALUES(updated_at)'
);
$companyStatement->execute([
    'company_name' => 'Namelenam',
    'description' => 'Catalog and QR code platform',
    'mission' => 'Help customers find product details quickly.',
    'vision' => 'Provide a polished catalog experience.',
    'services' => 'Product management, QR labels, and public product pages.',
    'contact_information' => 'Support team',
    'address' => 'Demo address',
    'email' => 'info@example.com',
    'phone_number' => '+1 000 000 0000',
    'social_media_links' => '',
    'home_hero_image_url' => '/uploads/site/home-hero-default.png',
    'created_at' => $now,
    'updated_at' => $now,
]);

$categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
if ($categoryCount === 0) {
    $categoryStatement = $pdo->prepare(
        'INSERT INTO categories (name, slug, description, is_active, created_at, updated_at)
         VALUES (:name, :slug, :description, 1, :created_at, :updated_at)'
    );
    $categoryStatement->execute([
        'name' => 'Default Category',
        'slug' => slugify('Default Category'),
        'description' => 'Seeded category',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $categoryId = (int) $pdo->lastInsertId();
    $productStatement = $pdo->prepare(
        'INSERT INTO products
         (category_id, name, slug, sku, short_description, full_description, brand, model, product_code, currency, price, is_active, is_featured, created_at, updated_at)
         VALUES
         (:category_id, :name, :slug, :sku, :short_description, :full_description, :brand, :model, :product_code, :currency, :price, 1, 0, :created_at, :updated_at)'
    );
    $productStatement->execute([
        'category_id' => $categoryId,
        'name' => 'Sample Product',
        'slug' => slugify('Sample Product'),
        'sku' => 'SKU-001',
        'short_description' => 'Sample public product',
        'full_description' => 'This is a seeded sample product.',
        'brand' => 'QrCode',
        'model' => 'Demo',
        'product_code' => 'P-001',
        'currency' => 'USD',
        'price' => '10.00',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

echo "MySQL schema is ready.\n";
echo "Admin login: {$adminEmail} / {$adminPassword}\n";
