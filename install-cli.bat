@echo off
REM ACADEX CLI Installer for Windows
REM Automatically installs the acadex command for global use

echo ======================================
echo   ACADEX CLI Installer for Windows
echo ======================================
echo.

REM Check if Git is installed
where git >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Git is not installed or not in PATH
    echo.
    echo Please install Git for Windows from:
    echo https://git-scm.com/download/win
    echo.
    echo Git Bash is required to run the acadex script.
    echo.
    pause
    exit /b 1
)

echo Git Bash detected!
echo.

REM Get the current directory
set "SCRIPT_DIR=%~dp0"
set "ACADEX_PATH=%SCRIPT_DIR%acadex"

REM Convert Windows path to Unix-style path for Git Bash
set "ACADEX_PATH=%ACADEX_PATH:\=/%"

echo Project location: %SCRIPT_DIR%
echo.

REM Check if PowerShell profile exists
powershell -Command "if (!(Test-Path -Path $PROFILE)) { New-Item -ItemType File -Path $PROFILE -Force | Out-Null; Write-Host 'Created PowerShell profile' } else { Write-Host 'PowerShell profile exists' }"

echo.
echo Adding acadex function to PowerShell profile...
echo.

REM Add the function to PowerShell profile
powershell -Command "$functionText = \"`n# ACADEX CLI`nfunction acadex {`n    $acadexPath = '%ACADEX_PATH%'`n    & bash $acadexPath @args`n}`n\"; $profileContent = if (Test-Path $PROFILE) { Get-Content $PROFILE -Raw } else { '' }; if ($profileContent -notmatch 'function acadex') { Add-Content -Path $PROFILE -Value $functionText; Write-Host 'Function added successfully!' -ForegroundColor Green } else { Write-Host 'Function already exists in profile' -ForegroundColor Yellow }"

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
