@echo off
REM Batch Draft & CO Template System - Setup Script (Windows)
REM This script helps set up the new system

echo ==================================
echo Batch Draft ^& CO Template Setup
echo ==================================
echo.

REM Step 1: Run migrations
echo Step 1: Running database migrations...
php artisan migrate

if %ERRORLEVEL% EQU 0 (
    echo [OK] Migrations completed successfully
) else (
    echo [ERROR] Migration failed - please check error messages
    exit /b 1
)

echo.

REM Step 2: Clear caches
echo Step 2: Clearing application caches...
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo [OK] Caches cleared
echo.

REM Step 3: Show next steps
echo ==================================
echo Setup Complete!
echo ==================================
echo.
echo Next Steps:
echo 1. Review documentation: docs\BATCH_DRAFT_CO_TEMPLATE_SYSTEM.md
echo 2. Create the remaining Blade views (see docs\BATCH_DRAFT_IMPLEMENTATION_SUMMARY.md)
echo 3. Test the system:
echo    - Create a CO template
echo    - Create a batch draft with student CSV
echo    - Apply configuration to subjects
echo    - Assign subjects to instructors
echo.
echo Sample student CSV template available at:
echo sample_imports\batch_draft_students_template.csv
echo.
echo For detailed workflow, see:
echo docs\BATCH_DRAFT_CO_TEMPLATE_SYSTEM.md
echo.

pause
