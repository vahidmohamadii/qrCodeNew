# PHP + Angular + MySQL Conversion

This folder is a side-by-side conversion of the current .NET catalog app. The original `src` folder is not required by this PHP/Angular version.

## Production URL

The production public URL is configured as:

```text
https://www.namelenam.com
```

It is already set in:

- `backend/.env.example` as `APP_URL` and `PUBLIC_BASE_URL`
- `frontend/src/environments/environment.prod.ts` as `apiBaseUrl`

## Backend

The backend is a lightweight PHP API with PDO/MySQL and the same main routes:

- `POST /api/auth/login`
- `GET /api/products`
- `GET /api/products/{id}`
- `GET /api/products/by-slug/{slug}`
- `POST /api/products`
- `PUT /api/products/{id}`
- `DELETE /api/products/{id}`
- `POST /api/products/{id}/images`
- `DELETE /api/products/images/{imageId}`
- `POST /api/products/{id}/qr-code`
- `GET /api/categories`
- `POST /api/categories`
- `PUT /api/categories/{id}`
- `DELETE /api/categories/{id}`
- `GET /api/company-info`
- `PUT /api/company-info`

QR image generation uses Composer package `chillerlan/php-qrcode` and writes SVG files into `backend/public/uploads/qr-codes`.

### Backend Setup

```powershell
cd phpsrc/backend
Copy-Item .env.example .env
composer install
php scripts/migrate.php
php -S localhost:8080 -t public public/router.php
```

Default seeded admin:

```text
admin@example.com / Admin123!
```

## Frontend

The frontend is an Angular app with public product pages and admin pages.

```powershell
cd phpsrc/frontend
npm install
npm start
```

Local Angular points to `http://localhost:8080`. Production Angular points to `https://www.namelenam.com`.

## MySQL

Schema lives in:

```text
backend/database/schema.sql
```

The migration script creates the database if possible, applies the schema, seeds company info, seeds the admin account, and adds one default category/product when the catalog is empty.

## Production Deploy Shape

For cPanel, use this structure:

- Backend outside `public_html`: `/home/CPANEL_USERNAME/namelenam-backend`
- Public files inside `public_html`: `/home/CPANEL_USERNAME/public_html`

1. Configure `backend/.env` with the real MySQL credentials and a strong `JWT_KEY`.
2. Run `composer install --no-dev --optimize-autoloader` in `backend`.
3. Run `php scripts/migrate.php`.
4. Run `npm run build` in `frontend`.
5. Upload the whole `backend` folder outside `public_html` and rename it to `namelenam-backend`.
6. Copy the contents of `backend/public` into `public_html`, including:
   - `api.php`
   - `api/`
   - `.htaccess`
   - `uploads`
   - built Angular files such as `index.html`, `main.js`, `polyfills.js`, `styles.css`
7. In `public_html`, create `backend-path.php` from `backend-path.php.example` and set the absolute backend path.

Example `public_html/backend-path.php`:

```php
<?php

declare(strict_types=1);

return '/home/your-cpanel-user/namelenam-backend';
```

`api.php` will first try `public_html/backend-path.php`. If that file does not exist, it will try the default sibling path `../namelenam-backend`.

The `.htaccess` keeps `/api/...` on PHP and lets Angular handle routes such as `/products/sample-product`.
