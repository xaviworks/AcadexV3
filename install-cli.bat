@echo off
REM ACADEX CLI Installer for Windows
REM Automatically installs the acadex command for global use (Native PowerShell)

echo ======================================
echo   ACADEX CLI Installer for Windows
echo ======================================
echo.

REM Get the current directory
set "SCRIPT_DIR=%~dp0"

echo Project location: %SCRIPT_DIR%
echo.

REM Check if acadex.ps1 exists
if not exist "%SCRIPT_DIR%acadex.ps1" (
    echo ERROR: acadex.ps1 not found in %SCRIPT_DIR%
    echo.
    pause
    exit /b 1
)

echo Found acadex.ps1 - using native PowerShell
echo.

REM Check if PowerShell profile exists and create if needed
powershell -Command "if (!(Test-Path -Path $PROFILE)) { New-Item -ItemType File -Path $PROFILE -Force | Out-Null; Write-Host 'Created PowerShell profile' } else { Write-Host 'PowerShell profile exists' }"

echo.
echo Adding acadex function to PowerShell profile...
echo.

REM Use the helper PowerShell script
powershell -ExecutionPolicy Bypass -File "%SCRIPT_DIR%install-cli.ps1"

echo.
echo ======================================
echo   Installation Complete!
echo ======================================
echo.
echo To start using the acadex command:
echo.
echo 1. CLOSE this PowerShell window
echo 2. OPEN a NEW PowerShell window
echo 3. Test it with: acadex check
echo.
echo NOTE: You MUST restart PowerShell for changes to take effect.
echo.
echo ======================================
echo.
pause
