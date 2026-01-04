---
layout: default
title: Database Setup
---

# Database Setup

Guide for setting up and managing the ACADEX database.

[Back to Home](.)

---

## Database Requirements

| Database | Minimum Version |
|----------|-----------------|
| MySQL | 8.0 |
| MariaDB | 10.4 |
| PostgreSQL | 13.0 |
| SQLite | 3.x |

---

## Creating the Database

### MySQL / MariaDB

```sql
CREATE DATABASE acadex CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or via command line:

```bash
mysql -u root -p -e "CREATE DATABASE acadex CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### phpMyAdmin

1. Open phpMyAdmin
2. Click "New" in the sidebar
3. Enter database name: `acadex`
4. Select collation: `utf8mb4_unicode_ci`
5. Click "Create"

---

## Configuration

Update `.env` with database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=acadex
DB_USERNAME=root
DB_PASSWORD=your_password
```

---

## Running Migrations

### First Time Setup

```bash
acadex migrate
```

Or with seeders:

```bash
acadex migrate:fresh
```

### Migration Commands

| Command | Description |
|---------|-------------|
| `acadex migrate` | Run pending migrations |
| `acadex migrate:fresh` | Drop all tables and re-migrate with seeders |
| `php artisan migrate:status` | Check migration status |
| `php artisan migrate:rollback` | Rollback last migration batch |
| `php artisan migrate:reset` | Rollback all migrations |

---

## Database Tables

ACADEX creates the following tables:

### Core Tables

| Table | Description |
|-------|-------------|
| `users` | User accounts (admin, faculty, students) |
| `roles` | User roles |
| `sessions` | Active user sessions |
| `password_reset_tokens` | Password reset requests |

### Academic Tables

| Table | Description |
|-------|-------------|
| `programs` | Academic programs (BSIT, BSCS, etc.) |
| `subjects` | Course subjects |
| `sections` | Class sections |
| `enrollments` | Student enrollments |
| `grades` | Student grades |

### System Tables

| Table | Description |
|-------|-------------|
| `activity_log` | User activity tracking |
| `failed_jobs` | Failed queue jobs |
| `jobs` | Pending queue jobs |
| `cache` | Cache storage |

---

## Seeding Data

### Run All Seeders

```bash
acadex seed
```

### Run Specific Seeder

```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ProgramSeeder
```

### Default Users

After seeding, these accounts are available:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@acadex.com | password |
| Chairperson | chair@acadex.com | password |
| Faculty | faculty@acadex.com | password |
| Student | student@acadex.com | password |

**Note:** Change these passwords immediately in production.

---

## Database Backups

### Manual Backup

```bash
php artisan backup:run
```

### Backup Location

Backups are stored in `storage/app/backups/`

### Scheduled Backups

Backups run automatically when using:

```bash
acadex serve
```

The scheduler runs backup tasks as configured.

---

## Using Tinker

Interactive database queries:

```bash
acadex tinker
```

Example queries:

```php
// Count users
User::count()

// Find user by email
User::where('email', 'admin@acadex.com')->first()

// List all programs
Program::all()

// Get students in a program
Student::where('program_id', 1)->get()
```

---

## Database Monitoring

### Check Database Status

```bash
php artisan db:show
```

### View Table Information

```bash
php artisan db:table users
```

### Monitor Connections

```bash
php artisan db:monitor
```

---

## Resetting the Database

**Warning:** This deletes all data.

```bash
acadex migrate:fresh
```

This will:
1. Drop all tables
2. Run all migrations
3. Run all seeders

---

## Importing Student Data

ACADEX supports Excel import for student data.

### Sample Import Files

Located in `sample_imports/`:

- `BSIT_students.csv`
- `BSN_students.csv`
- `BSBA_students.csv`
- etc.

### Import Format

| Column | Description |
|--------|-------------|
| student_id | Unique student ID |
| first_name | Student first name |
| last_name | Student last name |
| email | Student email |
| program | Program code |
| year_level | Year level (1-4) |

---

## Troubleshooting

### Migration Fails

1. Check database credentials in `.env`
2. Ensure database exists
3. Verify MySQL/MariaDB is running:
   ```bash
   mysql -u root -p -e "SELECT 1"
   ```

### Permission Denied

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Clear Database Cache

```bash
php artisan config:clear
php artisan cache:clear
```

---

[Back to Home](.)
