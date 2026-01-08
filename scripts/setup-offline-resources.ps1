# PowerShell Script for Windows
# ACADEX Offline Resources Setup Script

$ErrorActionPreference = "Stop"

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = Split-Path -Parent $ScriptDir
$FontsDir = Join-Path $ProjectRoot "public\fonts"

Write-Host "ACADEX Offline Resources Setup" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Create fonts directory if it doesn't exist
if (-not (Test-Path $FontsDir)) {
    New-Item -ItemType Directory -Path $FontsDir | Out-Null
}

Write-Host "Font Setup Instructions:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Inter Font Family:" -ForegroundColor White
Write-Host "   - Visit: https://fonts.google.com/specimen/Inter"
Write-Host "   - Download the font family"
Write-Host "   - Extract woff2 files (weights: 300, 400, 500, 600, 700)"
Write-Host "   - Place in: $FontsDir\inter\"
Write-Host ""
Write-Host "2. Poppins Font (Bold):" -ForegroundColor White
Write-Host "   - Visit: https://fonts.google.com/specimen/Poppins"
Write-Host "   - Download Bold (700) weight"
Write-Host "   - Extract woff2 file"
Write-Host "   - Place in: $FontsDir\poppins\"
Write-Host ""
Write-Host "3. Feeling Passionate (if needed):" -ForegroundColor White
Write-Host "   - Visit: https://www.cdnfonts.com/feeling-passionate.font"
Write-Host "   - Download font files"
Write-Host "   - Place in: $FontsDir\feeling-passionate\"
Write-Host ""
Write-Host "Alternative: Use google-webfonts-helper" -ForegroundColor Green
Write-Host "   - Visit: https://gwfh.mranftl.com/fonts"
Write-Host "   - Select fonts and download woff2 files"
Write-Host ""

# Check if fonts already exist
$InterDir = Join-Path $FontsDir "inter"
$PoppinsDir = Join-Path $FontsDir "poppins"

if ((Test-Path $InterDir) -and (Get-ChildItem $InterDir -File).Count -gt 0) {
    Write-Host "Inter fonts found" -ForegroundColor Green
} else {
    Write-Host "Inter fonts not found in $InterDir\" -ForegroundColor Yellow
}

if ((Test-Path $PoppinsDir) -and (Get-ChildItem $PoppinsDir -File).Count -gt 0) {
    Write-Host "Poppins fonts found" -ForegroundColor Green
} else {
    Write-Host "Poppins fonts not found in $PoppinsDir\" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Installing npm packages..." -ForegroundColor Cyan

Set-Location $ProjectRoot
npm install

Write-Host ""
Write-Host "Building assets..." -ForegroundColor Cyan
npm run build

Write-Host ""
Write-Host "Setup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Download fonts manually (see instructions above)"
Write-Host "2. Run: composer dump-autoload"
Write-Host "3. Run: php artisan config:clear"
Write-Host "4. Run: php artisan view:clear"
Write-Host "5. Test offline functionality"
Write-Host ""
Write-Host "Your ACADEX system is now configured for offline use!" -ForegroundColor Green
