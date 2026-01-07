<#
.SYNOPSIS
    ACADEX - Custom command wrapper for AcadexV3 (Windows PowerShell)
.DESCRIPTION
    Usage: acadex <command> [arguments]
#>

param(
    [Parameter(Position=0)]
    [string]$Command,
    
    [Parameter(Position=1, ValueFromRemainingArguments=$true)]
    [string[]]$Arguments
)

# Project root directory
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ScriptDir

# Display help
function Show-Help {
    Write-Host "===========================================================" -ForegroundColor Cyan
    Write-Host "                 ACADEX - AcadexV3 Commands                " -ForegroundColor Green
    Write-Host "===========================================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Usage: acadex <command> [arguments]" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Available Commands:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "  Setup and Installation:" -ForegroundColor Cyan
    Write-Host "  setup           " -ForegroundColor Green -NoNewline
    Write-Host "First-time full installation"
    Write-Host "  install:2fa     " -ForegroundColor Green -NoNewline
    Write-Host "Install 2FA packages (existing install)"
    Write-Host "  install:notif   " -ForegroundColor Green -NoNewline
    Write-Host "Install notification feature packages"
    Write-Host "  check           " -ForegroundColor Green -NoNewline
    Write-Host "Check system requirements"
    Write-Host ""
    Write-Host "  Development:" -ForegroundColor Cyan
    Write-Host "  serve           " -ForegroundColor Green -NoNewline
    Write-Host "Start production servers (Laravel + Queue + Scheduler)"
    Write-Host "  dev             " -ForegroundColor Green -NoNewline
    Write-Host "Start dev servers (Laravel + Queue + Logs + Vite)"
    Write-Host "  build           " -ForegroundColor Green -NoNewline
    Write-Host "Build assets for production (npm run build)"
    Write-Host "  ui              " -ForegroundColor Green -NoNewline
    Write-Host "Rebuild UI assets and clear caches"
    Write-Host "  start           " -ForegroundColor Green -NoNewline
    Write-Host "Start Laravel server only"
    Write-Host ""
    Write-Host "  Testing:" -ForegroundColor Cyan
    Write-Host "  test            " -ForegroundColor Green -NoNewline
    Write-Host "Run PHPUnit tests"
    Write-Host "  test:coverage   " -ForegroundColor Green -NoNewline
    Write-Host "Run tests with coverage"
    Write-Host ""
    Write-Host "  Database:" -ForegroundColor Cyan
    Write-Host "  migrate         " -ForegroundColor Green -NoNewline
    Write-Host "Run database migrations"
    Write-Host "  migrate:fresh   " -ForegroundColor Green -NoNewline
    Write-Host "Fresh migration with seeders"
    Write-Host "  seed            " -ForegroundColor Green -NoNewline
    Write-Host "Run database seeders"
    Write-Host "  tinker          " -ForegroundColor Green -NoNewline
    Write-Host "Start Laravel Tinker"
    Write-Host ""
    Write-Host "  Maintenance:" -ForegroundColor Cyan
    Write-Host "  cache:clear     " -ForegroundColor Green -NoNewline
    Write-Host "Clear all caches"
    Write-Host "  optimize        " -ForegroundColor Green -NoNewline
    Write-Host "Optimize the application"
    Write-Host "  logs            " -ForegroundColor Green -NoNewline
    Write-Host "Tail Laravel logs"
    Write-Host ""
    Write-Host "  Code Quality:" -ForegroundColor Cyan
    Write-Host "  analyze         " -ForegroundColor Green -NoNewline
    Write-Host "Run PHPStan static analysis"
    Write-Host "  format          " -ForegroundColor Green -NoNewline
    Write-Host "Format code with Pint"
    Write-Host ""
    Write-Host "  Dependencies:" -ForegroundColor Cyan
    Write-Host "  install         " -ForegroundColor Green -NoNewline
    Write-Host "Install all dependencies"
    Write-Host "  update          " -ForegroundColor Green -NoNewline
    Write-Host "Update all dependencies"
    Write-Host ""
    Write-Host "  Other:" -ForegroundColor Cyan
    Write-Host "  routes          " -ForegroundColor Green -NoNewline
    Write-Host "List all routes"
    Write-Host "  queue           " -ForegroundColor Green -NoNewline
    Write-Host "Start queue worker"
    Write-Host "  schedule        " -ForegroundColor Green -NoNewline
    Write-Host "Run scheduled tasks"
    Write-Host "  docs            " -ForegroundColor Green -NoNewline
    Write-Host "Open ACADEX documentation"
    Write-Host ""
    Write-Host "Examples:" -ForegroundColor Yellow
    Write-Host "  acadex setup          # First-time installation"
    Write-Host "  acadex dev            # Start development"
    Write-Host "  acadex test           # Run tests"
    Write-Host "  acadex migrate:fresh  # Reset database"
    Write-Host ""
}

# Check if a command exists
function Test-CommandExists {
    param([string]$Cmd)
    $null -ne (Get-Command $Cmd -ErrorAction SilentlyContinue)
}

# Command handlers
switch ($Command) {
    "setup" {
        Write-Host "===========================================================" -ForegroundColor Cyan
        Write-Host "            ACADEX - First Time Installation              " -ForegroundColor Green
        Write-Host "===========================================================" -ForegroundColor Cyan
        Write-Host ""
        
        # Step 1: Check requirements
        Write-Host "[1/9] Checking requirements..." -ForegroundColor Yellow
        
        # Check PHP
        if (Test-CommandExists "php") {
            $phpVersion = php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;"
            Write-Host "  [OK] PHP $phpVersion found" -ForegroundColor Green
        } else {
            Write-Host "  [X] PHP not found. Please install PHP 8.2+" -ForegroundColor Red
            exit 1
        }
        
        # Check Composer
        if (Test-CommandExists "composer") {
            Write-Host "  [OK] Composer found" -ForegroundColor Green
        } else {
            Write-Host "  [X] Composer not found. Please install Composer" -ForegroundColor Red
            exit 1
        }
        
        # Check Node
        if (Test-CommandExists "node") {
            $nodeVersion = node -v
            Write-Host "  [OK] Node.js $nodeVersion found" -ForegroundColor Green
        } else {
            Write-Host "  [X] Node.js not found. Please install Node.js" -ForegroundColor Red
            exit 1
        }
        
        # Check npm
        if (Test-CommandExists "npm") {
            Write-Host "  [OK] npm found" -ForegroundColor Green
        } else {
            Write-Host "  [X] npm not found. Please install npm" -ForegroundColor Red
            exit 1
        }
        
        Write-Host ""
        
        # Step 2: Copy .env
        Write-Host "[2/9] Setting up environment file..." -ForegroundColor Yellow
        if (!(Test-Path ".env")) {
            if (Test-Path ".env.example") {
                Copy-Item ".env.example" ".env"
                Write-Host "  [OK] Created .env from .env.example" -ForegroundColor Green
            } else {
                Write-Host "  [X] .env.example not found!" -ForegroundColor Red
                exit 1
            }
        } else {
            Write-Host "  [OK] .env already exists" -ForegroundColor Green
        }
        Write-Host ""
        
        # Step 3: Install Composer dependencies
        Write-Host "[3/9] Installing Composer dependencies..." -ForegroundColor Yellow
        Write-Host "  -> This includes: Laravel, Excel, 2FA, Socialite, notifications, etc." -ForegroundColor Cyan
        composer install --no-interaction
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  [OK] Composer dependencies installed" -ForegroundColor Green
        } else {
            Write-Host "  [X] Composer install failed!" -ForegroundColor Red
            exit 1
        }
        Write-Host ""
        
        # Step 4: Install npm dependencies
        Write-Host "[4/9] Installing npm dependencies..." -ForegroundColor Yellow
        npm install
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  [OK] npm dependencies installed" -ForegroundColor Green
        } else {
            Write-Host "  [X] npm install failed!" -ForegroundColor Red
            exit 1
        }
        Write-Host "  -> Installing notification features..." -ForegroundColor Cyan
        npm install @alpinejs/intersect --save
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  [OK] Notification packages installed" -ForegroundColor Green
        }
        Write-Host ""
        
        # Step 5: Generate app key
        Write-Host "[5/9] Generating application key..." -ForegroundColor Yellow
        $envContent = Get-Content ".env" -Raw
        if ($envContent -match "APP_KEY=base64:") {
            Write-Host "  [OK] APP_KEY already set" -ForegroundColor Green
        } else {
            php artisan key:generate --ansi
            Write-Host "  [OK] APP_KEY generated" -ForegroundColor Green
        }
        Write-Host ""
        
        # Step 6: Database setup prompt
        Write-Host "[6/9] Database configuration..." -ForegroundColor Yellow
        Write-Host "  -> Please configure your database in .env file" -ForegroundColor Cyan
        Write-Host "  -> DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD" -ForegroundColor Cyan
        Write-Host ""
        $dbConfigured = Read-Host "  Have you configured the database? (y/n)"
        if ($dbConfigured -ne "y" -and $dbConfigured -ne "Y") {
            Write-Host "  [!] Please configure the database in .env and run: acadex migrate" -ForegroundColor Yellow
            Write-Host ""
        } else {
            # Step 7: Run migrations
            Write-Host ""
            Write-Host "[7/9] Running database migrations..." -ForegroundColor Yellow
            php artisan migrate --force
            if ($LASTEXITCODE -eq 0) {
                Write-Host "  [OK] Migrations completed" -ForegroundColor Green
            } else {
                Write-Host "  [X] Migration failed! Check your database configuration." -ForegroundColor Red
            }
            Write-Host ""
            
            # Seeding prompt
            $runSeeders = Read-Host "  Run database seeders? (y/n)"
            if ($runSeeders -eq "y" -or $runSeeders -eq "Y") {
                php artisan db:seed --force
                Write-Host "  [OK] Database seeded" -ForegroundColor Green
            }
        }
        Write-Host ""
        
        # Step 8: Build assets
        Write-Host "[8/9] Building frontend assets..." -ForegroundColor Yellow
        npm run build
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  [OK] Assets built" -ForegroundColor Green
        } else {
            Write-Host "  [X] Build failed!" -ForegroundColor Red
        }
        Write-Host ""
        
        # Step 9: Optimize
        Write-Host "[9/9] Optimizing application..." -ForegroundColor Yellow
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        Write-Host "  [OK] Application optimized" -ForegroundColor Green
        Write-Host ""
        
        # Done!
        Write-Host "===========================================================" -ForegroundColor Cyan
        Write-Host "              Installation Complete!                       " -ForegroundColor Green
        Write-Host "===========================================================" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "Next steps:" -ForegroundColor Yellow
        Write-Host "  1. Configure your .env file (database, mail, etc.)"
        Write-Host "  2. Run 'acadex dev' to start development servers" -ForegroundColor Green
        Write-Host "  3. Visit http://localhost:8000" -ForegroundColor Cyan
        Write-Host ""
    }
    
    "install:2fa" {
        Write-Host "Installing 2FA packages..." -ForegroundColor Green
        Write-Host ""
        composer require pragmarx/google2fa-laravel bacon/bacon-qr-code
        if ($LASTEXITCODE -eq 0) {
            Write-Host ""
            Write-Host "[OK] 2FA packages installed successfully!" -ForegroundColor Green
            Write-Host "  - pragmarx/google2fa-laravel"
            Write-Host "  - bacon/bacon-qr-code"
        } else {
            Write-Host "[X] Installation failed!" -ForegroundColor Red
            exit 1
        }
    }
    
    "install:notif" {
        Write-Host "Installing notification feature packages..." -ForegroundColor Green
        Write-Host ""
        npm install @alpinejs/intersect --save
        if ($LASTEXITCODE -eq 0) {
            Write-Host ""
            Write-Host "[OK] Notification packages installed successfully!" -ForegroundColor Green
            Write-Host "  - @alpinejs/intersect"
        } else {
            Write-Host "[X] Installation failed!" -ForegroundColor Red
            exit 1
        }
    }
    
    "check" {
        Write-Host "===========================================================" -ForegroundColor Cyan
        Write-Host "              ACADEX - System Requirements                " -ForegroundColor Green
        Write-Host "===========================================================" -ForegroundColor Cyan
        Write-Host ""
        
        # PHP
        Write-Host "PHP:" -ForegroundColor Yellow
        if (Test-CommandExists "php") {
            $phpVersion = php -v | Select-Object -First 1
            Write-Host "  [OK] $phpVersion" -ForegroundColor Green
        } else {
            Write-Host "  [X] PHP not found (required: 8.2+)" -ForegroundColor Red
        }
        
        # Composer
        Write-Host "Composer:" -ForegroundColor Yellow
        if (Test-CommandExists "composer") {
            $composerVersion = composer --version
            Write-Host "  [OK] $composerVersion" -ForegroundColor Green
        } else {
            Write-Host "  [X] Composer not found" -ForegroundColor Red
        }
        
        # Node.js
        Write-Host "Node.js:" -ForegroundColor Yellow
        if (Test-CommandExists "node") {
            $nodeVersion = node -v
            Write-Host "  [OK] Node.js $nodeVersion" -ForegroundColor Green
        } else {
            Write-Host "  [X] Node.js not found" -ForegroundColor Red
        }
        
        # npm
        Write-Host "npm:" -ForegroundColor Yellow
        if (Test-CommandExists "npm") {
            $npmVersion = npm -v
            Write-Host "  [OK] npm $npmVersion" -ForegroundColor Green
        } else {
            Write-Host "  [X] npm not found" -ForegroundColor Red
        }
        
        # Git
        Write-Host "Git:" -ForegroundColor Yellow
        if (Test-CommandExists "git") {
            $gitVersion = git --version
            Write-Host "  [OK] $gitVersion" -ForegroundColor Green
        } else {
            Write-Host "  [X] Git not found" -ForegroundColor Red
        }
        
        Write-Host ""
        
        # Project status
        Write-Host "Project Status:" -ForegroundColor Yellow
        
        if (Test-Path ".env") {
            Write-Host "  [OK] .env file exists" -ForegroundColor Green
        } else {
            Write-Host "  [X] .env file missing" -ForegroundColor Red
        }
        
        if (Test-Path "vendor") {
            Write-Host "  [OK] Composer dependencies installed" -ForegroundColor Green
        } else {
            Write-Host "  [X] Composer dependencies not installed" -ForegroundColor Red
        }
        
        if (Test-Path "node_modules") {
            Write-Host "  [OK] npm dependencies installed" -ForegroundColor Green
        } else {
            Write-Host "  [X] npm dependencies not installed" -ForegroundColor Red
        }
        
        if (Test-Path "public/build") {
            Write-Host "  [OK] Assets built" -ForegroundColor Green
        } else {
            Write-Host "  [X] Assets not built (run: acadex build)" -ForegroundColor Red
        }
        
        Write-Host ""
    }
    
    "serve" {
        Write-Host "Starting production servers (Laravel + Queue + Scheduler)..." -ForegroundColor Green
        npx concurrently -c "#93c5fd,#c4b5fd,#4ade80" "php artisan serve" "php artisan queue:work --tries=3 --timeout=90" "php artisan schedule:work" --names=server,queue,scheduler
    }
    
    "dev" {
        Write-Host "Starting development servers (Laravel + Queue + Logs + Vite)..." -ForegroundColor Green
        npx concurrently -c "#93c5fd,#c4b5fd,#fb7185,#fdba74" "php artisan serve" "php artisan queue:work --tries=3 --timeout=90" "php artisan pail --timeout=0" "npm run dev" --names=server,queue,logs,vite
    }
    
    "build" {
        Write-Host "Building assets for production..." -ForegroundColor Green
        npm run build
    }
    
    "ui" {
        Write-Host "Rebuilding UI and clearing caches..." -ForegroundColor Green
        Write-Host "  -> Building frontend assets..." -ForegroundColor Cyan
        npm run build
        Write-Host "  -> Clearing all caches..." -ForegroundColor Cyan
        php artisan optimize:clear
        Write-Host "[OK] UI refreshed! Hard refresh your browser (Ctrl+Shift+R or Cmd+Shift+R)" -ForegroundColor Green
    }
    
    "start" {
        Write-Host "Starting Laravel server only..." -ForegroundColor Green
        php artisan serve
    }
    
    "test" {
        Write-Host "Running tests..." -ForegroundColor Green
        if ($Arguments) {
            php artisan test @Arguments
        } else {
            php artisan test
        }
    }
    
    "test:coverage" {
        Write-Host "Running tests with coverage..." -ForegroundColor Green
        if ($Arguments) {
            php artisan test --coverage @Arguments
        } else {
            php artisan test --coverage
        }
    }
    
    "migrate" {
        Write-Host "Running migrations..." -ForegroundColor Green
        if ($Arguments) {
            php artisan migrate @Arguments
        } else {
            php artisan migrate
        }
    }
    
    "migrate:fresh" {
        Write-Host "Running fresh migration with seeders..." -ForegroundColor Yellow
        if ($Arguments) {
            php artisan migrate:fresh --seed @Arguments
        } else {
            php artisan migrate:fresh --seed
        }
    }
    
    "seed" {
        Write-Host "Running seeders..." -ForegroundColor Green
        if ($Arguments) {
            php artisan db:seed @Arguments
        } else {
            php artisan db:seed
        }
    }
    
    "tinker" {
        Write-Host "Starting Tinker..." -ForegroundColor Green
        php artisan tinker
    }
    
    "cache:clear" {
        Write-Host "Clearing all caches..." -ForegroundColor Green
        php artisan config:clear
        php artisan cache:clear
        php artisan route:clear
        php artisan view:clear
        Write-Host "All caches cleared!" -ForegroundColor Green
    }
    
    "optimize" {
        Write-Host "Optimizing application..." -ForegroundColor Green
        php artisan optimize
    }
    
    "logs" {
        Write-Host "Tailing Laravel logs..." -ForegroundColor Green
        Get-Content "storage/logs/laravel.log" -Wait -Tail 50
    }
    
    "analyze" {
        Write-Host "Running PHPStan analysis..." -ForegroundColor Green
        if ($Arguments) {
            & ./vendor/bin/phpstan analyse @Arguments
        } else {
            & ./vendor/bin/phpstan analyse
        }
    }
    
    "format" {
        Write-Host "Formatting code with Pint..." -ForegroundColor Green
        if ($Arguments) {
            & ./vendor/bin/pint @Arguments
        } else {
            & ./vendor/bin/pint
        }
    }
    
    "install" {
        Write-Host "Installing dependencies..." -ForegroundColor Green
        composer install
        npm install
        Write-Host "  -> Installing notification features..." -ForegroundColor Cyan
        npm install @alpinejs/intersect --save
        Write-Host "Dependencies installed!" -ForegroundColor Green
    }
    
    "update" {
        Write-Host "Updating dependencies..." -ForegroundColor Green
        composer update
        npm update
        Write-Host "Dependencies updated!" -ForegroundColor Green
    }
    
    "routes" {
        Write-Host "Listing routes..." -ForegroundColor Green
        if ($Arguments) {
            php artisan route:list @Arguments
        } else {
            php artisan route:list
        }
    }
    
    "queue" {
        Write-Host "Starting queue worker..." -ForegroundColor Green
        if ($Arguments) {
            php artisan queue:work @Arguments
        } else {
            php artisan queue:work
        }
    }
    
    "schedule" {
        Write-Host "Running scheduled tasks..." -ForegroundColor Green
        php artisan schedule:run
    }
    
    "docs" {
        Write-Host "Opening ACADEX documentation..." -ForegroundColor Green
        Start-Process "https://xaviworks.github.io/AcadexV3/"
    }
    
    { $_ -in @("help", "--help", "-h", "", $null) } {
        Show-Help
    }
    
    default {
        Write-Host "Unknown command: $Command" -ForegroundColor Red
        Write-Host ""
        Show-Help
        exit 1
    }
}
