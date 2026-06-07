## Why

We need a single web platform for managing product data internally and exposing polished public product pages externally. Right now there is no end-to-end flow for admin users to maintain categories/products, generate QR codes, and send customers to the right product page from a physical label.

## What Changes

- Add a .NET-backed admin experience for secure login and protected management of product catalog data.
- Add category management with nested category support, activation state, search, and image support.
- Add product management with e-commerce-style fields, multiple images, SEO-friendly slugs, and active/featured status.
- Add QR code generation for each product, including printable/downloadable QR labels that resolve to public product pages.
- Add public product listing and product detail pages for customers scanning QR codes or browsing directly.
- Add a public company/about page for business profile and contact information.
- Add backend APIs, EF Core data models, and database persistence for users, categories, products, images, and QR code records.
- Add Swagger/OpenAPI documentation and validation for the new API surface.

## Capabilities

### New Capabilities
- `admin-authentication`: Admin login, JWT-based access control, logout flow, and seed admin user support.
- `catalog-management`: CRUD, search, filtering, activation, and nested hierarchy support for categories and products.
- `product-media`: Multiple product image upload, main image selection, image ordering, and image metadata.
- `qr-code-generation`: Create, store, download, and print QR codes that link to public product pages.
- `public-product-site`: Public product listing and product detail pages with responsive browsing and search.
- `company-profile`: Public about/company information page with contact and business details.

### Modified Capabilities

- None

## Impact

- New ASP.NET Core Web API endpoints for auth, catalog, media upload, QR code generation, and company info.
- New Blazor frontend pages and shared components for admin and public experiences.
- New EF Core entities, migrations, and SQL Server tables for users, categories, products, images, and QR code metadata.
- New file/image storage handling for uploaded product images and generated QR assets.
- New dependency on a QR code generation library and JWT authentication support.
- New validation, error handling, and Swagger documentation across the API.
