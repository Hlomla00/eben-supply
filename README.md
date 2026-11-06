# Eben Supply — Online Store

**Wear the Culture.** The online store and back office for Eben Supply, a branded merchandise
business in Woodstock, Cape Town. Customers browse T-shirts, sweatshirts and hoodies, pants,
caps, tote bags and a Kids range, fill a cart, and check out for free store pickup or R60
nationwide delivery. Staff manage products, stock, sales and orders in the Admin Panel.

Built by **Group KN3** for **PRT362S (Project 3)**, Diploma in ICT, CPUT. Payments are
simulated: no card is charged and no money moves.

---

## Contents

- [Features](#features)
- [Tech stack](#tech-stack)
- [Quick start with Docker (recommended)](#quick-start-with-docker-recommended)
- [Local install without Docker](#local-install-without-docker)
- [Demo accounts](#demo-accounts)
- [Running the tests](#running-the-tests)
- [Everyday commands](#everyday-commands)
- [Troubleshooting](#troubleshooting)
- [Project structure](#project-structure)
- [Known limitations](#known-limitations)
- [Contributing](#contributing)
- [Team](#team)

---

## Features

**Customers**
- Browse by category, plus dedicated **Kids** (`/kids`) and **Sale** (`/sale`) pages
- Search, filter by size, price and stock, and sort
- Product pages with a size picker (sold-out sizes disabled) and a size guide
- A cart that works for guests and is kept when they sign in or register
- Two-step checkout: details with pickup or delivery, then a simulated PayFast payment
- Order confirmation, **My Orders** and an order tracker
- Order confirmation and status-update emails (written to the log until an email service is connected)
- Account registration, sign in, password reset, profile and account deletion

**Administrators** (Admin Panel at `/admin/admin-dashboard`)
- Dashboard with order totals, low-stock count and recent orders
- Add, edit and delete products: per-size or one-size stock, photo upload, Kids flag, featured switch
- Inventory page to update stock for every product and size at once
- Order list and detail with statuses: Pending, Paid, Confirmed, Ready, Collected, Cancelled
- Admins land in the Admin Panel after signing in; customers are blocked from it (403)

**Quality**
- Fits every screen from 320 px phones to 1920 px desktops, respects "reduce motion"
- Compressed photos, lazy-loaded images, browser caching and compression on Apache
- Stock checked and reduced inside a database transaction when an order is placed
- Automated PHPUnit test suite, run by GitHub Actions on every push and pull request to `main`

---

## Tech stack

| Layer | Technology |
|---|---|
| Language | PHP 8.3 or 8.4 |
| Framework | Laravel 13, Laravel Breeze (authentication) |
| Front end | Blade templates, Livewire 4 + Flux 2.20 components, Tailwind CSS 3, Vite 8 |
| Database | MySQL 8 (SQLite in-memory for tests) |
| Web server | Apache (Docker image) or Laravel Herd / `php artisan serve` locally |
| Containers | Docker, Docker Compose (app + MySQL 8.4.11) |
| CI | GitHub Actions: install, build and test on PHP 8.4 and Node 20 |

> **Flux is pinned to 2.20.0 on purpose.** Later Flux releases need Tailwind CSS 4, and this
> project still runs Tailwind 3. Upgrade both together; see [Known limitations](#known-limitations).

---

## Quick start with Docker (recommended)

Docker gives everyone the same environment, so this is the most reliable way to run the store.
You only need **Docker Desktop** (on Windows it uses WSL 2). PHP, Composer, Node and MySQL do
**not** need to be installed.

> **Free disk space first.** Allow at least **15 GB free**. On Windows, Docker's virtual disk
> grows with every build and a full drive makes downloads silently come out broken.

```bash
git clone https://github.com/KJMalaka/eben-supply.git
cd eben-supply
docker compose up -d --build
```

The first build takes a few minutes. Check that both containers are **healthy**:

```bash
docker compose ps
```

Then open **http://localhost:8080** and sign in with one of the [demo accounts](#demo-accounts).

On the first start the container creates the database tables and loads the demo products and
accounts by itself. **No `.env` file or app key is needed**: Compose supplies the settings, and the
start-up script creates an app key and keeps it. If a `.env` file exists, its `APP_KEY` is used.

| Service | Address | Notes |
|---|---|---|
| Store | http://localhost:8080 | Change with `APP_PORT=9000 docker compose up -d` |
| MySQL | `localhost:3307` | Database `eben_supply`, user `eben`, password `eben_secret` (local demo only) |

**What happens on each start** (`docker/entrypoint.sh`): wait for MySQL, run migrations, seed
**only if the database is empty** (so restarts never overwrite stock or prices edited in the
Admin Panel), cache config, routes and views, then start Apache.

**Stop, restart and reset**

```bash
docker compose down        # stop; the database and uploaded photos are kept
docker compose up -d       # start again
docker compose down -v     # stop and DELETE the database and uploads (fresh demo data next start)
docker compose logs -f app # follow the application log
```

---

## Local install without Docker

Use this if you develop with **Laravel Herd**, XAMPP or your own PHP set-up.

### Requirements

- PHP 8.3 or 8.4 with `pdo_mysql`, `mbstring`, `bcmath`, `gd`, `zip`, `exif`, `intl`, `fileinfo`
  (and `pdo_sqlite` for the tests)
- Composer 2
- Node.js 20 or later and npm
- MySQL 8, or SQLite for a quick try

### 1. Get the code and install dependencies

```bash
git clone https://github.com/KJMalaka/eben-supply.git
cd eben-supply
composer install
npm install
```

### 2. Create the `.env` file

There is no `.env.example` in the repository, so create a file called `.env` in the project
folder with this content and adjust the database lines:

```dotenv
APP_NAME="Eben Supply"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Africa/Johannesburg

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eben_supply
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=log
```

**Using SQLite instead of MySQL:** replace the six `DB_` lines with `DB_CONNECTION=sqlite` and
create an empty file at `database/database.sqlite`.

**Using MySQL:** create an empty database called `eben_supply` first.

> Never leave `APP_KEY` empty when you run the site. Without a key, every page returns
> **500 Server Error**. The next step fills it in.

### 3. Set up the database and build the front end

```bash
php artisan key:generate
php artisan migrate --seed
npm run build
```

### 4. Start the store

- **Laravel Herd:** put the project in your Herd folder and open `http://<folder-name>.test`
  (for example `http://eben-supply.test`). Set `APP_URL` to that address.
- **Anything else:**

  ```bash
  php artisan serve
  ```

  and open **http://localhost:8000**.

While you edit CSS or JavaScript, run `npm run dev` in a second terminal so changes appear
straight away.

> If your terminal says `php` is not recognised while using Herd, call Herd's PHP directly, for
> example `C:\Users\<you>\.config\herd\bin\php84\php.exe artisan serve`.

---

## Demo accounts

Created by the seeders. **Change these before any public launch.**

| Role | Email | Password | Lands on after sign in |
|---|---|---|---|
| Administrator | `admin@ebensupply.co.za` | `password` | Admin Panel (`/admin/admin-dashboard`) |
| Customer | `customer@test.co.za` | `password` | The page they came from, or the store home |

---

## Running the tests

The tests run on their own in-memory SQLite database, so they never touch your real data.

```bash
php artisan test
```

Useful options:

```bash
php artisan test tests/Feature/CartCheckoutTest.php   # one file
php artisan test --filter=admin                       # tests whose name contains "admin"
php artisan test --stop-on-failure                    # stop at the first failure
```

The suite has **102 tests**. GitHub Actions runs it on every push and pull request to `main`.

> **Known failures (6 November 2026):** 2 tests in `AdminAndStorePagesTest`
> (`test_admin_can_create_a_product_with_sizes` and `test_admin_can_edit_a_product`) fail.
> They were written before the product form started requiring a photo for new products and a
> `sizing` field (`sized` or `one_size`). The app works; the two tests need updating to send
> those fields.

---

## Everyday commands

| Task | Without Docker | With Docker |
|---|---|---|
| Start | `php artisan serve` (or open the Herd site) | `docker compose up -d` |
| Stop | `Ctrl + C` | `docker compose down` |
| Apply new migrations, keep data | `php artisan migrate` | Happens on every start |
| Reset to fresh demo data | `php artisan migrate:fresh --seed` | `docker compose down -v` then `up -d` |
| Rebuild CSS/JS | `npm run build` | `docker compose up -d --build` |
| Clear Laravel caches | `php artisan optimize:clear` | `docker compose exec app php artisan optimize:clear` |
| Read the log | `storage/logs/laravel.log` | `docker compose logs app` |
| Back up the database | `mysqldump -u root -p eben_supply > backup.sql` | `docker compose exec db mysqldump -u eben -p eben_supply > backup.sql` |

> `migrate:fresh` **deletes everything** in the database it points at. Never run it on a
> database with real orders.

---

## Troubleshooting

| Problem | Cause | Fix |
|---|---|---|
| Every page shows **500 Server Error** | `.env` is missing or `APP_KEY` is empty | Create `.env` (see above) and run `php artisan key:generate` |
| *"Vite manifest not found"* | The CSS/JS has not been built | Run `npm run build` |
| Admin menus or buttons look unstyled | Old build, or a build from before the Tailwind config listed Flux | Run `npm run build` again |
| `SQLSTATE[HY000] [2002]` / connection refused | MySQL is not running or the `DB_` settings are wrong | Start MySQL and check `.env` |
| Port 8080 or 3307 already in use | Another app uses the port | `APP_PORT=9000 DB_FORWARD_PORT=3308 docker compose up -d` |
| `docker compose ps` shows the app *unhealthy* | Start-up failed | Read `docker compose logs app` |
| Docker or WSL freezes, or images unpack with empty files | The drive is almost full | Free space, then `docker system prune` and restart Docker Desktop |
| `npm ci` says the lock file is out of sync | The lock file was made on another operating system | Run `npm install` (the Docker build falls back to this by itself) |
| Emails never arrive | No email service is connected | Read them in the log (`MAIL_MAILER=log`) |

---

## Project structure

```
app/
  Http/Controllers/          Storefront: Home, Product, Cart, Checkout, Order, Profile
  Http/Controllers/Admin/    Dashboard, Product, Inventory, Order
  Http/Middleware/           EnsureUserIsAdmin (403 for non-admins)
  Mail/                      OrderPlaced, OrderStatusUpdated
  Models/                    User, Product, ProductSize, CartItem, Order, OrderItem
database/
  migrations/                Schema; always add a new migration, never edit an old one
  seeders/                   Demo users and the product catalogue
resources/views/             Blade pages; layouts/ and partials/ hold the shared parts
public/images/products/      Product photos
tests/Feature/               Feature tests
docker/                      Container start-up script, Apache site, PHP settings
Dockerfile                   Three-stage image: Composer, Vite build, PHP 8.4 + Apache
docker-compose.yml           App + MySQL 8.4.11 for local demonstrations
.github/workflows/ci.yml     Continuous integration
docs/AUDIT.md                Audit findings and their fixes
```

---

## Known limitations

The store is ready to demonstrate, not to take real orders:

- **Payments are simulated.** A PayFast or Ozow merchant account is needed.
- **Emails go to the log.** An email service and sender address are needed.
- **Uploaded photos** are kept in the Docker volume locally, but would be lost on hosts that
  rebuild the container on each deploy until permanent storage (such as S3) is added.
- **Legal pages** (Terms, Privacy Policy under POPIA, Returns, Shipping, Contact) still need
  the business's wording.
- **Tailwind CSS 3 → 4 upgrade** is pending; it would let Flux update past 2.20.0.
- **Demo passwords** are `password`; change them before going live.

---

## Contributing

1. Branch from `main` and keep each change small (for example `feat/order-export`).
2. Run `php artisan test` and `npm run build` before you push. Add a test with every bug fix.
3. Open a pull request to `main`. CI must pass before merging.
4. Read the full diff of every merge; past problems included leftover conflict markers and a
   duplicated migration.
5. Change the schema only with a new migration.

---

## Team

**Group KN3** — Katlego Jeffrey Malaka, Hillary Itlhabanyeng, Dumisane Madondo,
Hlomla Magopeni, Matuma Malapile and Phelo Mguca.
Supervisor: Kruben Naidoo.

This is an academic project for PRT362S at CPUT, built with the permission of Eben Supply.
