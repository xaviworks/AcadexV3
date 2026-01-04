@echo off
REM ACADEX CLI Installer for Windows
REM Automatically installs the acadex command for global use

echo ======================================
echo   ACADEX CLI Installer for Windows
echo ======================================
echo.

REM Get the current directory
set "SCRIPT_DIR=%~dp0"
set "ACADEX_PATH=%SCRIPT_DIR%acadex"

echo Project location: %SCRIPT_DIR%
echo.

REM Check if PowerShell profile exists
powershell -Command "if (!(Test-Path -Path $PROFILE)) { New-Item -ItemType File -Path $PROFILE -Force | Out-Null; Write-Host 'Created PowerShell profile' } else { Write-Host 'PowerShell profile exists' }"

echo.
echo Adding acadex function to PowerShell profile...
echo.

REM Add the function to PowerShell profile
powershell -Command "$functionText = \"`nfunction acadex { bash '%ACADEX_PATH%' @args }`n\"; if ((Get-Content $PROFILE -Raw) -notmatch 'function acadex') { Add-Content -Path $PROFILE -Value $functionText; Write-Host 'Function added successfully!' } else { Write-Host 'Function already exists in profile' }"

echo.
echo ======================================
echo   Installation Complete!
echo ======================================
echo.
echo To start using the acadex command:
echo.
echo 1. Close this PowerShell window
echo 2. Open a NEW PowerShell window
echo 3. Test it with: acadex check
echo.
echo ======================================
echo.
pause
