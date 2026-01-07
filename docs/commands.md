---
layout: default
title: CLI Commands
nav_order: 3
description: "ACADEX CLI command reference"
---

# ACADEX CLI Commands
{: .fs-9 }

Convenient shortcuts for common development tasks.
{: .fs-6 .fw-300 }

---

## Setup & Installation

### `acadex setup`
First-time full installation. Runs all necessary setup steps automatically.

```bash
acadex setup
```

**What it does:**
1. Checks system requirements (PHP, Composer, Node.js, npm)
2. Creates `.env` file from `.env.example`
3. Installs Composer dependencies (Laravel, Excel, 2FA, Socialite, etc.)
4. Installs npm dependencies
5. Installs notification features (`@alpinejs/intersect`)
6. Generates application key
7. Prompts for database configuration
8. Runs database migrations
9. Optionally seeds the database
10. Builds frontend assets
11. Optimizes the application

---

### `acadex install:2fa`
Install 2FA packages on an existing Laravel installation.

```bash
acadex install:2fa
```

**Packages installed:**
- `pragmarx/google2fa-laravel` - Google Two-Factor Authentication
- `bacon/bacon-qr-code` - QR Code generation

---

### `acadex install:notif`
Install notification feature packages.

```bash
acadex install:notif
```

**Packages installed:**
- `@alpinejs/intersect` - Alpine.js Intersection Observer plugin for scroll-based notifications

---

### `acadex check`
Verify system requirements and project status.

```bash
acadex check
```

**Checks:**
- PHP version
- Composer installation
- Node.js version
- npm version
- Git installation
- `.env` file existence
- Vendor directory (Composer dependencies)
- Node modules directory
- Built assets

---

## Development

### `acadex dev`
Start all development servers with live reloading.

```bash
acadex dev
```

**Starts:**
- Laravel development server (http://127.0.0.1:8000)
- Queue worker (3 tries, 90s timeout)
- Laravel Pail (live log viewer)
- Vite development server (hot reloading)

**Color-coded output:**
- Blue - Server
- Purple - Queue
- Pink - Logs
- Orange - Vite

---

### `acadex serve`
Start production-like servers (without Vite hot reloading).

```bash
acadex serve
```

**Starts:**
- Laravel development server
- Queue worker
- Schedule worker

---

### `acadex start`
Start only the Laravel server.

```bash
acadex start
```

---

### `acadex build`
Build frontend assets for production.

```bash
acadex build
```

Runs `npm run build` to compile and minify CSS/JS assets.

---

### `acadex ui`
Rebuild UI assets and clear all caches.

```bash
acadex ui
```

**What it does:**
1. Runs `npm run build` - Rebuilds all frontend assets (CSS/JS)
2. Runs `php artisan optimize:clear` - Clears all caches:
   - Configuration cache
   - Route cache
   - View cache (compiled Blade templates)
   - Application cache

**Use when:** You've made UI changes (CSS, JS, Blade templates) and need to see them immediately.

**Tip:** After running this, hard refresh your browser (`Ctrl+Shift+R` or `Cmd+Shift+R`).

---

## Testing

### `acadex test`
Run PHPUnit tests.

```bash
acadex test

# With specific filter
acadex test --filter=UserTest
```

---

### `acadex test:coverage`
Run tests with code coverage report.

```bash
acadex test:coverage
```

---

## Database

### `acadex migrate`
Run database migrations.

```bash
acadex migrate

# With additional options
acadex migrate --seed
```

---

### `acadex migrate:fresh`
Drop all tables and re-run migrations with seeders.

```bash
acadex migrate:fresh
```

**Warning:** This will delete all data in the database.

---

### `acadex seed`
Run database seeders.

```bash
acadex seed

# Specific seeder
acadex seed --class=UserSeeder
```

---

### `acadex tinker`
Start Laravel Tinker (interactive REPL).

```bash
acadex tinker
```

---

## Maintenance

### `acadex cache:clear`
Clear all application caches.

```bash
acadex cache:clear
```

**Clears:**
- Configuration cache
- Application cache
- Route cache
- View cache

---

### `acadex optimize`
Optimize the application for production.

```bash
acadex optimize
```

Caches configuration, routes, and views.

---

### `acadex logs`
Tail Laravel logs in real-time.

```bash
acadex logs
```

Displays `storage/logs/laravel.log` output.

---

## Code Quality

### `acadex analyze`
Run PHPStan static analysis.

```bash
acadex analyze

# With specific paths
acadex analyze app/Models
```

---

### `acadex format`
Format code using Laravel Pint.

```bash
acadex format

# Specific files
acadex format app/Http/Controllers
```

---

## Dependencies

### `acadex install`
Install all dependencies (Composer + npm + notification features).

```bash
acadex install
```

**Installs:**
- Composer dependencies
- npm dependencies
- Notification features (`@alpinejs/intersect`)

---

### `acadex update`
Update all dependencies.

```bash
acadex update
```

---

## Other Commands

### `acadex routes`
List all registered routes.

```bash
acadex routes

# Filter by name
acadex routes --name=admin

# Filter by path
acadex routes --path=api
```

---

### `acadex queue`
Start a queue worker.

```bash
acadex queue

# With options
acadex queue --tries=5 --timeout=120
```

---

### `acadex schedule`
Run scheduled tasks.

```bash
acadex schedule
```

---

### `acadex docs`
Open ACADEX documentation in browser.

```bash
acadex docs
```

Opens [https://xaviworks.github.io/AcadexV3/](https://xaviworks.github.io/AcadexV3/)

---

### `acadex help`
Display all available commands.

```bash
acadex help
acadex --help
acadex -h
```

---

## Command Summary Table

| Command | Description |
|---------|-------------|
| `acadex setup` | First-time full installation |
| `acadex install:2fa` | Install 2FA packages |
| `acadex check` | Check system requirements |
| `acadex dev` | Start dev servers (Laravel + Queue + Logs + Vite) |
| `acadex serve` | Start production servers (Laravel + Queue + Scheduler) |
| `acadex start` | Start Laravel server only |
| `acadex build` | Build assets for production |
| `acadex test` | Run PHPUnit tests |
| `acadex test:coverage` | Run tests with coverage |
| `acadex migrate` | Run database migrations |
| `acadex migrate:fresh` | Fresh migration with seeders |
| `acadex seed` | Run database seeders |
| `acadex tinker` | Start Laravel Tinker |
| `acadex cache:clear` | Clear all caches |
| `acadex optimize` | Optimize application |
| `acadex logs` | Tail Laravel logs |
| `acadex analyze` | Run PHPStan analysis |
| `acadex format` | Format code with Pint |
| `acadex install` | Install all dependencies |
| `acadex update` | Update all dependencies |
| `acadex routes` | List all routes |
| `acadex queue` | Start queue worker |
| `acadex schedule` | Run scheduled tasks |
| `acadex docs` | Open documentation |
| `acadex help` | Display help |

---

