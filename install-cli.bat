@echo off
REM ACADEX CLI Installer for Windows
REM Automatically installs the acadex command for global use

echo ======================================
echo   ACADEX CLI Installer for Windows
echo ======================================
echo.

REM Check if Git is installed and find Git Bash
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

REM Find Git Bash executable
set "GIT_BASH="
for %%i in (
    "C:\Program Files\Git\bin\bash.exe"
    "C:\Program Files (x86)\Git\bin\bash.exe"
    "%ProgramFiles%\Git\bin\bash.exe"
    "%ProgramFiles(x86)%\Git\bin\bash.exe"
    "%LOCALAPPDATA%\Programs\Git\bin\bash.exe"
) do (
    if exist %%i (
        set "GIT_BASH=%%~i"
        goto :found_bash
    )
)

REM Try to find bash in PATH
where bash >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    for /f "delims=" %%i in ('where bash') do set "GIT_BASH=%%i"
    goto :found_bash
)

echo ERROR: Could not find bash.exe
echo.
echo Git is installed, but bash.exe was not found.
echo Please make sure Git Bash is installed.
echo.
pause
exit /b 1

:found_bash
echo Git Bash found at: %GIT_BASH%
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

REM Create a temporary PowerShell script to add the function
set "TEMP_PS1=%TEMP%\acadex-install.ps1"
(
echo # ACADEX CLI Installation Script
echo $bashPath = '%GIT_BASH%'
echo $acadexPath = '%ACADEX_PATH%'
echo.
echo $functionText = @"
echo.
echo # ACADEX CLI
echo function acadex ^{
echo     ^& '$bashPath' '$acadexPath' @args
echo ^}
echo "@
echo.
echo $profileContent = if ^(Test-Path $PROFILE^) ^{ Get-Content $PROFILE -Raw ^} else ^{ '' ^}
echo.
echo if ^($profileContent -notmatch 'function acadex'^) ^{
echo     Add-Content -Path $PROFILE -Value "`n$functionText"
echo     Write-Host 'Function added successfully!' -ForegroundColor Green
echo ^} else ^{
echo     Write-Host 'Function already exists in profile' -ForegroundColor Yellow
echo ^}
) > "%TEMP_PS1%"

REM Execute the PowerShell script
powershell -ExecutionPolicy Bypass -File "%TEMP_PS1%"

REM Clean up
del "%TEMP_PS1%" >nul 2>nul

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
