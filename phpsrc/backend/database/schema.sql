CREATE TABLE IF NOT EXISTS app_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(200) NOT NULL,
  email VARCHAR(200) NOT NULL,
  password_hash VARCHAR(500) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'Admin',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_app_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  description TEXT NULL,
  parent_category_id INT NULL,
  image_url VARCHAR(1000) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_categories_slug (slug),
  KEY ix_categories_parent_category_id (parent_category_id),
  CONSTRAINT fk_categories_parent_category
    FOREIGN KEY (parent_category_id) REFERENCES categories(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  sku VARCHAR(100) NOT NULL,
  short_description TEXT NOT NULL,
  full_description MEDIUMTEXT NOT NULL,
  brand VARCHAR(120) NOT NULL,
  model VARCHAR(120) NOT NULL,
  price DECIMAL(18, 2) NULL,
  currency VARCHAR(10) NULL,
  stock_quantity INT NULL,
  product_code VARCHAR(100) NOT NULL,
  barcode VARCHAR(120) NULL,
  weight DECIMAL(18, 3) NULL,
  dimensions VARCHAR(255) NULL,
  material VARCHAR(255) NULL,
  color VARCHAR(120) NULL,
  warranty_info TEXT NULL,
  technical_specifications MEDIUMTEXT NULL,
  additional_information MEDIUMTEXT NULL,
  meta_title VARCHAR(255) NULL,
  meta_description TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_products_slug (slug),
  UNIQUE KEY uq_products_sku (sku),
  KEY ix_products_category_id (category_id),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image_url VARCHAR(1000) NOT NULL,
  alt_text VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_main_image TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  KEY ix_product_images_product_id (product_id),
  CONSTRAINT fk_product_images_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_qr_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  qr_code_url VARCHAR(1000) NOT NULL,
  qr_code_image_path VARCHAR(1000) NOT NULL,
  label_title VARCHAR(255) NULL,
  label_description TEXT NULL,
  batch_number VARCHAR(120) NULL,
  serial_number VARCHAR(120) NULL,
  manufacturing_date DATETIME NULL,
  expiry_date DATETIME NULL,
  custom_note TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_product_qr_codes_product_id (product_id),
  CONSTRAINT fk_product_qr_codes_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS company_infos (
  id INT PRIMARY KEY,
  company_name VARCHAR(255) NOT NULL,
  description MEDIUMTEXT NOT NULL,
  mission MEDIUMTEXT NOT NULL,
  vision MEDIUMTEXT NOT NULL,
  services MEDIUMTEXT NOT NULL,
  contact_information MEDIUMTEXT NOT NULL,
  address VARCHAR(1000) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone_number VARCHAR(100) NOT NULL,
  social_media_links TEXT NULL,
  home_hero_image_url VARCHAR(1000) NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO company_infos
  (id, company_name, description, mission, vision, services, contact_information, address, email, phone_number, social_media_links, home_hero_image_url, created_at, updated_at)
VALUES
  (1, 'Namelenam', 'Catalog and QR code platform', 'Help customers find product details quickly.', 'Provide a polished catalog experience.', 'Product management, QR labels, and public product pages.', 'Support team', 'Demo address', 'info@example.com', '+1 000 000 0000', '', '/uploads/site/home-hero-default.png', UTC_TIMESTAMP(), UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE id = id;
