---
layout: default
title: Installation
nav_order: 2
description: "ACADEX installation guide"
---

# Installation Guide
{: .fs-9 }

Complete guide for setting up ACADEX on your local machine.
{: .fs-6 .fw-300 }

---

## Prerequisites

Before installing ACADEX, ensure you have the following installed:

| Software | Minimum Version | Check Command |
|----------|-----------------|---------------|
| PHP | 8.2 | `php -v` |
| Composer | 2.x | `composer --version` |
| Node.js | 18.x | `node -v` |
| npm | 9.x | `npm -v` |
| Git | 2.x | `git --version` |
| MySQL/MariaDB | 8.0+ / 10.4+ | `mysql --version` |

---

## Quick Installation

### Option 1: Using ACADEX CLI (Recommended)

```bash
# Clone the repository
git clone https://github.com/xaviworks/AcadexV3.git
cd AcadexV3

# Make the CLI executable
chmod +x acadex

# Run automated setup
./acadex setup
```

The setup wizard will guide you through:
1. Checking system requirements
2. Installing dependencies
3. Configuring environment
4. Setting up database
5. Building assets

---

### Option 2: Manual Installation

#### Step 1: Clone Repository

```bash
git clone https://github.com/xaviworks/AcadexV3.git
cd AcadexV3
```

#### Step 2: Install PHP Dependencies

```bash
composer install
```

This installs all required packages including:
- Laravel Framework
- Maatwebsite Excel (import/export)
- Google 2FA (two-factor authentication)
- Laravel Socialite (OAuth)
- Jenssegers Agent (device detection)
- Alpine.js Intersect (notification system)

#### Step 3: Install Node Dependencies

```bash
npm install
```

#### Step 4: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### Step 5: Configure Database

Edit `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=acadex
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Step 6: Run Migrations

```bash
# Create database tables
php artisan migrate

# Seed with sample data (optional)
php artisan db:seed
```

#### Step 7: Build Assets

```bash
npm run build
```

#### Step 8: Start Server

```bash
php artisan serve
```

Visit [http://localhost:8000](http://localhost:8000)

---

## Installing the ACADEX Command

### Easy Installation (Recommended) 

**For Mac/Linux Users:**

1. Open Terminal and go to your project folder:
   ```bash
   cd /path/to/AcadexV3
   ```

2. Run the installer:
   ```bash
   ./install-cli.sh
   ```

3. Activate it:
   ```bash
   source ~/.zshrc
   ```

4. Test it:
   ```bash
   acadex check
   ```

 Done! The installer automatically detects your system and sets everything up.

---

**For Windows Users:**

1. Open PowerShell and go to your project folder:
   ```powershell
   cd C:\path\to\AcadexV3
   ```

2. Run the installer:
   ```powershell
   .\install-cli.bat
   ```

3. Close and reopen PowerShell

4. Test it:
   ```powershell
   acadex check
   ```

 Done! The installer sets up everything automatically.

---

### Manual Installation (Alternative)

<details>
<summary>Click here for manual installation steps</summary>

#### For Mac Users

1. **Open Terminal** and go to your project folder:
   ```bash
   cd /path/to/AcadexV3
   ```

2. **Get the full path** by typing:
   ```bash
   pwd
   ```
   Copy the result (example: `/Users/yourname/AcadexV3`)

3. **Open your shell configuration file:**
   ```bash
   nano ~/.zshrc
   ```
   
   *Note: If you use bash instead, use `nano ~/.bashrc`*

4. **Add this line at the end** (replace with your actual path):
   ```bash
   alias acadex="/Users/yourname/AcadexV3/acadex"
   ```

5. **Save the file:**
   - Press `Control + X`
   - Press `Y` to confirm
   - Press `Enter`

6. **Activate the changes:**
   ```bash
   source ~/.zshrc
   ```

7. **Test it:**
   ```bash
   acadex check
   ```

#### For Windows Users

1. **Open PowerShell** and go to your project folder:
   ```powershell
   cd C:\path\to\AcadexV3
   ```

2. **Get the full path:**
   ```powershell
   pwd
   ```
   Copy the result (example: `C:\Users\YourName\AcadexV3`)

3. **Create a PowerShell profile** (if you don't have one):
   ```powershell
   if (!(Test-Path -Path $PROFILE)) {
       New-Item -ItemType File -Path $PROFILE -Force
   }
   ```

4. **Open the profile file:**
   ```powershell
   notepad $PROFILE
   ```

5. **Add this function** (replace with your actual path):
   ```powershell
   function acadex {
       bash "C:\Users\YourName\AcadexV3\acadex" @args
   }
   ```

6. **Save and close** Notepad

7. **Restart PowerShell** and test:
   ```powershell
   acadex check
   ```

</details>

---

### Quick Method (No Installation)

If you don't want to install the command globally, run it directly from the project folder:

**Mac/Linux:**
```bash
cd /path/to/AcadexV3
./acadex setup
./acadex dev
```

**Windows:**
```powershell
cd C:\path\to\AcadexV3
bash acadex setup
bash acadex dev
```

---

## Post-Installation

### Verify Installation

```bash
acadex check
```

Expected output:

```
PHP:
  ✓ PHP 8.4.x
Composer:
  ✓ Composer version 2.x
Node.js:
  ✓ Node.js v22.x
npm:
  ✓ npm 11.x
Git:
  ✓ git version 2.x

Project Status:
  ✓ .env file exists
  ✓ Composer dependencies installed
  ✓ npm dependencies installed
  ✓ Assets built
```

### Start Development

```bash
acadex dev
```

This starts:
- Laravel server at http://127.0.0.1:8000
- Queue worker
- Log viewer (Pail)
- Vite dev server (hot reloading)

---

## XAMPP Installation

If using XAMPP on macOS:

1. Clone to XAMPP htdocs:
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs
   git clone https://github.com/xaviworks/AcadexV3.git
   ```

2. Create database in phpMyAdmin:
   - Open http://localhost/phpmyadmin
   - Create new database named `acadex`

3. Configure `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=acadex
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Run setup:
   ```bash
   ./acadex setup
   ```

---

## Laravel Herd Installation

If using Laravel Herd on macOS:

1. Clone to Herd directory:
   ```bash
   cd ~/Herd
   git clone https://github.com/xaviworks/AcadexV3.git
   ```

2. Site will be available at http://acadexv3.test

3. Configure database and run setup:
   ```bash
   ./acadex setup
   ```

---

## Troubleshooting

### Common Issues

**Permission denied when running acadex:**
```bash
chmod +x acadex
```

**Composer dependencies fail:**
```bash
composer install --ignore-platform-reqs
```

**npm install fails:**
```bash
rm -rf node_modules package-lock.json
npm install
```

**Migration fails:**
- Verify database credentials in `.env`
- Ensure database exists
- Check MySQL/MariaDB is running

**Assets not loading:**
```bash
npm run build
php artisan storage:link
```

---

## Next Steps

After installation:

1. [Configure the application](configuration)
2. [Learn CLI commands](commands)
3. [Set up the database](database)

---

