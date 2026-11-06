# Eben Supply: Site Audit

Two audits are recorded here:

1. **3 November 2026, prototype** (the Issues table below). Commit hashes in its Status column refer to
   `KJMalaka/Eben-Supply-Prototype`, branch `fix/audit-kids-sales-promos`, where each fix was first built,
   and the tests it names are the prototype's. Each team member was to bring their area into this
   repository.
2. **6 November 2026, this repository** ([Audit of this repository](#audit-of-this-repository-6-november-2026)
   at the end). It checks which of those fixes actually arrived here and lists what was fixed. **Read that
   section for the current state of this repository.**

## Baseline

| Check | Result |
|---|---|
| `php artisan migrate:fresh --seed` | OK (8 migrations, UserSeeder + ProductSeeder) |
| `php artisan test` | **28 passed** (74 assertions) |
| `npm run build` | OK |
| Browser | Built-in browser, desktop and 375px mobile, server log watched |

Environment note: the local `eben_supply` MySQL database holds a third, non-seed user account. To avoid
destroying it, all audit and fix work ran against a separate `eben_supply_audit` database
(`DB_DATABASE=eben_supply_audit` env override). `.env` was not changed.

Severity: **Critical** = wrong money/stock or a crash on a core flow · **High** = a core flow broken or
clearly wrong data shown · **Medium** = incorrect but has a workaround · **Low** = cosmetic / polish.

Status is updated after Phase 2.

## Issues

| # | Sev | Area / location | Problem | Steps to reproduce | Status |
|---|---|---|---|---|---|
| A1 | Critical | `CheckoutController::process/confirmPayment` | Order total is frozen in the session at the details step. Items added to the cart before paying are included in the order but not charged. | Go to payment step (R309.00), add another item in another tab, click Pay. Confirmation shows Subtotal R448 + Delivery R60 but **Total Paid R309.00**. | Fixed: `c939a1a`, test `CheckoutIntegrityTest` |
| A2 | Critical | `CheckoutController::confirmPayment` | Stock is never decremented when an order is placed (product or size level). | Place an order; `products.stock_quantity` unchanged (25 → 25). | Fixed: `c939a1a`, test `CheckoutIntegrityTest` |
| A3 | Critical | `CartController::add/update`, checkout | No stock check anywhere. Can add/buy more than available, or an out-of-stock size. | POST `/cart/add` product 1, size XXL (stock 4), qty 20 → accepted. | Fixed: `45e420a`, test `CartStockTest` |
| A4 | Critical | `Admin\ProductController::store/update`, `admin/products/*.blade.php` | One-size products (caps, totes) have no stock field. Saving the form creates S–XXL rows at 0 and sets stock to 0. | Admin → edit "ES Dad Cap – Signature" (stock 25) → Update without changes → product shows **Out of Stock**. | Fixed: `1c0d633`, test `AdminProductTest` |
| A5 | Critical | Cart, checkout, order views (`$item->product->…`) | Deleting a product (soft delete) causes a 500 for every customer with it in their cart, and for admin/customer order pages that include it. | Admin deletes "ES Classic Tee – Pure White" → customer `/cart` and `/checkout` = 500; `/admin/orders/2` = 500 (`Attempt to read property "price" on null`). | Fixed: `45e420a`, `c939a1a`, `1c0d633`, tests `CartStockTest`, `CheckoutIntegrityTest` |
| A6 | High | `CartController::add` | Size not validated: any string accepted, sizes accepted on one-size products, sized products accepted with no size. | POST size `ZZZ` for a tee, size `XL` for a cap, no size for a tee → all saved to cart. | Fixed: `45e420a`, test `CartStockTest` |
| A7 | High | Login / register | Guest cart is lost on login or registration (session id regenerates; cart rows keyed on old id are orphaned). | Add tee as guest → Sign In → cart badge disappears. | Fixed: `c028c34`, test `GuestCartMergeTest` |
| A8 | High | `profile/edit.blade.php` | Profile page renders blank. It uses `<x-app-layout>` (slot) but `layouts/app` only renders `@yield('content')`. Customers cannot edit profile, change password or delete account. Existing test only checks for HTTP 200. | Log in → Profile → empty page. | Fixed: `82acd0e`, test `ProfilePageTest` |
| A9 | High | `ProductSeeder` | Product images do not match product names. All 5 caps show tee/sweater/model photos; 4 of 5 totes show tees or a clothing rack; "Classic Tee – Jet Black" shows a **green** tee; "Classic Tee – Pure White" shows a **blue striped button-up shirt**. Matching `cap-*.svg`, `tote-*.svg`, `tshirt-*.svg` illustrations exist in `public/images/products` but are unused. | Open `/products?category=cap`. | Fixed: `2e538a3`, test `ProductSeederTest` |
| A10 | High | `Admin\ProductController` | Uploaded images are stored on the `public` disk as `storage/products/…` but `public/storage` symlink does not exist and is not created by setup or Docker entrypoint → uploaded images 404. | Create product with image → broken image. | Fixed: `1c0d633` (uploads saved under `public/images/products/uploads`), test `AdminProductTest` |
| A11 | Medium | `CheckoutController::confirmPayment` | No guard against double submission (no lock / idempotency, Pay button not disabled). Not reproducible on `artisan serve` (single worker), but would create duplicate orders on a multi-worker server. | Fire 3 parallel POSTs to `/checkout/payment` → 1 order here (serialised server). | Fixed: `c939a1a` (row locks in one transaction + Pay button disables). Test covers a repeated submit; true parallel requests **not verified** (dev server is single-threaded) |
| A12 | Medium | Cart → login | `/login?redirect=…` parameter ignored; "Sign In to Checkout" lands on Home. | Cart as guest → Sign In to Checkout → log in → Home. | Fixed: `c028c34`, test `GuestCartMergeTest` |
| A13 | Medium | Quantity limits | Product page `max=10`, JS stepper allows 99, server allows 20, cart select only offers 1–10 (a qty of 11+ shows a wrong selected value). | Add qty 20 via request → cart select shows "1". | Fixed: `45e420a` (max 10, capped by stock) |
| A14 | Medium | `Admin\DashboardController` | "Low Stock" counts only product totals, ignoring size stock that the inventory page highlights as low. | Dashboard Low Stock = 0 while inventory flags Jet Black XXL (4). | Fixed: `1c0d633`, test `AdminProductTest` |
| A15 | Medium | `Product::getImageUrlAttribute` + views | Fallback image `images/products/placeholder.jpg` doesn't exist; views bypass the model accessor with their own `asset(... ?: placeholder)`. | Product with no image → broken image. | Fixed: `2e538a3` |
| A16 | Medium | Seeder / categories | "ES Jersey – Street Edition" and "ES Sweater – Woodstock Crew" are listed under **T-Shirts**. | `/products?category=tshirt`. | Fixed: new Sweatshirts & Hoodies category (`2026_11_03_000005`) |
| A17 | Medium | Category labels | Category label maps are hard-coded and duplicated in 8 views + model; adding a category means editing all of them. | Code review. | Fixed: `2e538a3` (`Product::CATEGORIES`) |
| A18 | Low | `products/show.blade.php` | If the first size is out of stock, the hidden size input defaults to the next in-stock size but no button is highlighted. | Set size S stock 0 → page shows nothing selected but submits M. | Fixed: `45e420a` |
| A19 | Low | `checkout/index.blade.php` JS | Total recomputed with `toFixed` → "R1578.00" vs server "R1,578.00"; delivery fee display wrong after a validation error with delivery selected. | Delivery selected on a ≥R1000 cart. | Fixed: `c939a1a` |
| A20 | Low | Checkout sidebar | "Order (1 items)" counts lines, cart counts units. | Checkout with 2 × one product. | Fixed: `c939a1a` |
| A21 | Low | Auth pages (forgot/reset/confirm/verify) | Still use Breeze guest layout with Laravel logo; `dashboard.blade.php`, `welcome.blade.php`, `layouts/navigation.blade.php` are unused templates (navigation links to a non-existent `dashboard` route). | Open a password reset link. | Fixed: `82acd0e` |
| A22 | Low | Layout | Page titles omit store name ("Shop: Woodstock, Cape Town"); nav highlights "Shop" on category pages instead of the category. | Any page. | Fixed: `109b108`, test `StorefrontTest` |
| A23 | Low | Home | Stats strip hard-codes "15+ Products / 3 Categories". | Home. | Fixed: `109b108` |
| A24 | Low | Search | Search matches name only (not description/category). | Search "canvas" → 1 result. | Fixed: `109b108`, test `StorefrontTest` |
| A25 | Low | Orders | Status timeline shows "Collected" for delivery orders; dates shown in UTC (store is in Cape Town). | Delivery order detail. | Timezone fixed (`109b108`); "Collected" label on delivery orders **Open** (status names are a business rule) |
| A26 | Low | Announcement bar | Says "Pay securely via PayFast / Ozow" while payment is a demo; product page says "(demo)". | Header. | Fixed: `109b108` |
| A27 | Low | Unknown category | `/products?category=hats` shows "All Products" title with 0 results. | URL above. | Fixed: `109b108`, test `StorefrontTest` |
| A28 | High | `ProductSeeder` + `docker-entrypoint.sh` | The deploy entrypoint runs `db:seed` on every boot and the seeder used `updateOrCreate`, so every deploy reset live stock, prices and images to the seed values. Found while fixing A9. | Change stock in admin, redeploy → stock reset. | Fixed: `2e538a3` (seeder only creates missing products), test `ProductSeederTest` |
| A29 | High | `layouts/admin.blade.php` | Admin panel unusable on phones: fixed 256px sidebar over the content, page rendered 1062px wide at 375px. Found in Phase 6 mobile pass. | Open `/admin` at 375px. | Fixed: `de0ca1b` (drawer below `lg`) |
| A30 | Low | `orders/index.blade.php` | Order history "Items" counted lines, checkout counts units. Found in Phase 6. | Order 2 × one product → history says 1. | Fixed: `de0ca1b` |
| A31 | Medium | `DatabaseSeeder` | New test promo codes would be seeded onto the live site by the deploy entrypoint. | Deploy. | Fixed: `7d72272` (promo seeder skipped in production) |

### Checked and working

- Register, log in, log out, password reset email + reset page (link generated, page works), profile update (HTTP).
- Home, listings, product pages, search, category filter. **No sort feature exists** (not added: out of scope).
- Logged-in cart persists after logout/login. Cart update/remove reject other users' items (403).
- Empty cart: `/checkout`, `POST /checkout`, `POST /checkout/payment` all redirect to cart.
- Non-admin → every `/admin/*` route returns 403. Viewing another user's order returns 403.
- Admin order status update, inventory update, toggle featured.
- Tampered `price` field on add-to-cart is ignored (price read from DB).
- Dashboard Total/Pending orders and product count match the database.
- Mobile (375px): no horizontal scroll, hamburger menu opens and lists all links.
- Browser console: no errors on any page visited.

---

## Audit of this repository (6 November 2026)

Checked on branch `feature/template-redesign` by reading the code and by running each problem as a
test. Every fix below is covered by `tests/Feature/AuditFixesTest.php` unless another test is named.

### Prototype fixes: status in this repository

| # | Status here | Covered by |
|---|---|---|
| A1 | **Was missing:** the total was frozen at the details step. **Fixed:** recalculated at payment from the live cart and prices; the payment page sends the total it showed, and a changed total is shown before charging. | `AuditFixesTest` |
| A2, A3, A6, A11 | Present: stock checked and reduced under row locks in one transaction; sizes validated. | `CartCheckoutTest` |
| A4, A10, A14 | Present: one-size stock, photo upload, product form validation. | `AdminAndStorePagesTest`, `KidsSectionTest` |
| A5 | **Was missing:** deleting a product gave a 500 on the cart, checkout, My Orders and admin order pages. **Fixed:** deleted products are removed from carts with a message; order lines keep the product name (new `product_name` column) and load soft-deleted products. | `AuditFixesTest` |
| A7, A12 | Present: guest cart merged on sign-in. | `CartCheckoutTest` |
| A8, A9, A15, A17, A21, A28 | Present (Member 1). | `ProfilePageTest`, `ProductSeederTest` |
| A13 | **Was inconsistent:** the cart page offered up to 10, the server allowed 20. **Fixed:** 10 everywhere (`CartItem::MAX_QUANTITY`), including repeated adds and guest-cart merges. | `AuditFixesTest` |
| A16, A22, A24, A26, A27 | Present. | `ListingFiltersTest`, `KidsSectionTest` |
| A23 | **Was missing:** the home stats still said "15+ Products / 3 Categories". **Fixed:** real product and category counts. | `AuditFixesTest` |
| A30 | **Was missing:** order history counted lines, not items. **Fixed:** counts units. | `AuditFixesTest` |
| A25 | **Was open.** **Fixed:** delivery orders say "Delivered" instead of "Collected" (tracker, badges, emails, admin status list). | `AuditFixesTest` |
| A29 | **Was missing:** no admin navigation below desktop width. **Fixed:** a menu in the phone header. | `AuditFixesTest` |
| A31 | **Was missing (with promo codes).** **Fixed:** `PromoCodeSeeder` runs outside production only. | — |

### New issues found and fixed

| # | Sev | Problem | Fix |
|---|---|---|---|
| B1 | Critical | Product pages showed sale prices, but the cart and checkout charged the full price. | Cart, checkout and order lines use `current_price`. |
| B2 | Medium | Promo codes existed only in the prototype. | Ported: shopper apply/remove (rate-limited, once per customer), admin create/edit/deactivate, discount stored on orders and shown on order pages and emails. |
| B3 | Medium | No branded error pages; `/admin` returned 404. | `resources/views/errors/` (403, 404, 419, 429, 500, 503) with a layout that needs no database; `/admin` redirects to the dashboard. |
| B4 | Medium | No Contact, Delivery, Returns, Terms or Privacy pages. | Added and linked in the footer. **Drafts:** the store owner must review them, and set `STORE_EMAIL` / `STORE_PHONE` in `.env` before launch. |
| B5 | Low | No `.env.example` (and `.gitignore` would have ignored one). | Added, with a `.gitignore` exception. |
| B6 | Medium | Admin pages and the store's account menu looked broken: the compiled CSS was stale, and Flux followed the device's dark mode on a light-only site. | Rebuild after pulling (`npm run build`); `darkMode: 'class'` and `@fluxAppearance` removed. |
| B7 | Medium | CI failed: `composer.lock` needed PHP 8.4, but the project and CI use 8.3. | `config.platform.php` pinned to 8.3 and the lock regenerated. |

### Still open

- Payments are simulated; a PayFast or Ozow merchant account is needed.
- Emails go to the log until a mail service is configured.
- The legal pages are drafts (see B4).
