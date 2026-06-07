## Context

This change starts from an empty repository and needs to deliver a full-stack product catalog application with an admin panel, public product pages, and QR code generation. The design has to support secure admin access, structured catalog data, image handling, and printable QR labels while staying maintainable for future expansion.

The main stakeholders are admin users who manage catalog content and public visitors who browse products or land on them through QR scans. The backend must serve both the admin UI and public UI safely through a shared API contract.

## Goals / Non-Goals

**Goals:**
- Establish a clean foundation for API-first catalog management.
- Support secure admin authentication with JWT and protected admin screens.
- Model categories, products, product images, and QR code metadata in SQL Server through EF Core.
- Provide public product browsing and product detail pages that can be reached from QR code links.
- Keep the UI responsive and English-only.

**Non-Goals:**
- Full e-commerce checkout, cart, payment, or order management.
- Advanced CMS features beyond the required company/about page.
- Multi-tenant support or complex role hierarchies beyond admin/public access.
- Offline QR generation workflows or batch label printing automation.

## Decisions

1. **Use ASP.NET Core Web API as the system backbone**
   - The API owns auth, validation, persistence, QR generation, and all catalog operations.
   - Alternatives considered: a monolithic Blazor app with embedded data access, or separate microservices. The API-first approach keeps the frontend thin and preserves a clean contract for future clients.

2. **Use a layered solution structure**
   - Keep API, domain models, application services, infrastructure, and Blazor UI separated by project boundaries.
   - Alternatives considered: a single-project app or a strict DDD/CQRS split. A layered structure is simpler to start with, but still keeps the codebase understandable as features grow.

3. **Use JWT authentication for admin access**
   - The admin panel needs protected routes and a stateless API boundary, which JWT fits well.
   - Alternatives considered: cookie auth only or external identity providers. Cookie auth would be simpler for a server-rendered app, but JWT keeps the API reusable for future clients and mobile/SPA usage.

4. **Represent QR codes as product-specific links, not separate business entities**
   - QR output should resolve to a product detail page using a stable public URL.
   - The QR record stores generated metadata and asset paths, but the canonical destination remains the product page. Alternatives considered: encoding direct product JSON or a separate redirect service. A public product URL is simpler and more durable.

5. **Store uploaded images on disk with database references**
   - Product images should be saved as files and referenced from the database with ordering and main-image flags.
   - Alternatives considered: storing blobs in SQL Server or only remote object storage. File-based storage is the easiest first implementation and keeps migration risk low.

6. **Start with Blazor Server-style interactivity for the frontend**
   - A server-rendered Blazor experience is quicker to bootstrap for admin/public pages and avoids separate client-hosting complexity in the MVP.
   - Alternatives considered: Blazor WebAssembly with a separate hosted client. WASM is viable later, but it adds more moving parts for the first milestone.

7. **Keep the public routing slug-based**
   - Product detail pages should use `/products/{slug}` to support SEO-friendly public links and QR destinations.
   - Alternatives considered: numeric IDs only or opaque tokens. Slugs are more human-friendly and better for public discovery.

## Risks / Trade-offs

- [Risk] File-based image storage can complicate deployment across multiple instances -> Mitigation: keep storage behind a service abstraction so it can be swapped to cloud storage later.
- [Risk] JWT storage in the browser can be mishandled -> Mitigation: keep token handling centralized and protect admin routes consistently.
- [Risk] QR print layout requirements may evolve -> Mitigation: isolate printable label rendering into a dedicated component and keep the QR content URL stable.
- [Risk] Starting with one UI stack may limit future client options -> Mitigation: keep the API contract stable and avoid frontend-only business logic.

## Migration Plan

1. Create the solution and shared project structure.
2. Implement the core domain models and EF Core DbContext.
3. Add auth, category, product, image, and QR endpoints.
4. Add Blazor admin and public pages that consume the API.
5. Seed the initial admin user and company profile record.
6. Verify the app locally with Swagger and end-to-end navigation from QR link to product page.

Rollback strategy:
- If a feature introduces instability, disable the related UI route and keep the API endpoint dormant behind missing registration or feature-level wiring.
- Since the repository is greenfield, rollback primarily means reverting the most recent changeset rather than database migration backouts.

## Open Questions

- Should the initial frontend be Blazor Server, or do we want a hosted Blazor WebAssembly split from the start?
- Should product images be stored locally only for MVP, or should the storage abstraction target cloud storage immediately?
- Do we want a dedicated redirect endpoint for QR codes, or should QR codes link directly to the public product page URL?
