---
layout: default
title: Configuration
nav_order: 4
description: "ACADEX configuration guide"
---

# Configuration
{: .fs-9 }

Environment and application settings guide.
{: .fs-6 .fw-300 }

---

## Environment File

The `.env` file contains all environment-specific configuration.

### Application Settings

```env
APP_NAME=ACADEX
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_TIMEZONE=Asia/Manila
APP_URL=http://localhost:8000
```

| Setting | Description |
|---------|-------------|
| `APP_NAME` | Application name displayed in UI |
| `APP_ENV` | Environment: `local`, `staging`, `production` |
| `APP_KEY` | Encryption key (auto-generated) |
| `APP_DEBUG` | Enable debug mode (`true` for development) |
| `APP_TIMEZONE` | Application timezone |
| `APP_URL` | Base URL of the application |

---

### Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=acadex
DB_USERNAME=root
DB_PASSWORD=secret
```

**Supported Databases:**
- MySQL 8.0+
- MariaDB 10.4+
- PostgreSQL 13+
- SQLite 3.x

---

### Mail Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@acadex.com
MAIL_FROM_NAME="${APP_NAME}"
```

**For Gmail:**
1. Enable 2-Step Verification
2. Generate App Password
3. Use App Password as `MAIL_PASSWORD`

---

### Session Configuration

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
```

| Driver | Description |
|--------|-------------|
| `file` | Sessions stored in `storage/framework/sessions` |
| `database` | Sessions stored in database (recommended) |
| `redis` | Sessions stored in Redis |
| `cookie` | Sessions stored in encrypted cookies |

---

### Queue Configuration

```env
QUEUE_CONNECTION=database
```

| Driver | Description |
|--------|-------------|
| `sync` | Jobs processed immediately (development) |
| `database` | Jobs stored in database |
| `redis` | Jobs stored in Redis (production) |

---

### Notification Configuration

Notifications use Alpine.js Intersect plugin for scroll-based animations:

```bash
# Install notification packages
npm install @alpinejs/intersect

# Or use ACADEX CLI
acadex install:notif
```

**Features:**
- Scroll-based notification triggers
- Intersection observer support
- Smooth animations

---

### Cache Configuration

```env
CACHE_STORE=database
```

---

## Config Files

Located in `config/` directory:

| File | Purpose |
|------|---------|
| `app.php` | Application settings |
| `auth.php` | Authentication guards |
| `database.php` | Database connections |
| `mail.php` | Mail configuration |
| `queue.php` | Queue connections |
| `session.php` | Session settings |

---

## Caching Configuration

After changing config files in production:

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Or use ACADEX CLI
acadex optimize
```

To clear caches:

```bash
acadex cache:clear
```

---

## Environment-Specific Settings

### Development

```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

**Important:** Never set `APP_DEBUG=true` in production.

---

## Two-Factor Authentication

2FA is enabled by default for admin users.

To configure, ensure these packages are installed:

```bash
acadex install:2fa
```

---

## File Storage

```env
FILESYSTEM_DISK=local
```

Create symbolic link for public storage:

```bash
php artisan storage:link
```

---

## Logging

```env
LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=debug
```

View logs:

```bash
acadex logs
```

Or use Laravel Pail (included in `acadex dev`):

```bash
php artisan pail
```

---

