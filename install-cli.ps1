# ACADEX CLI Installation Script for PowerShell
# This script adds the acadex function to your PowerShell profile

param(
    [Parameter(Mandatory=$false)]
    [string]$BashPath,
    
    [Parameter(Mandatory=$false)]
    [string]$AcadexPath
)

# Get the script directory (where acadex.ps1 is located)
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$AcadexPs1Path = Join-Path $ScriptDir "acadex.ps1"

# Ensure profile exists
if (!(Test-Path -Path $PROFILE)) {
    New-Item -ItemType File -Path $PROFILE -Force | Out-Null
    Write-Host "Created PowerShell profile at: $PROFILE" -ForegroundColor Cyan
}

# Define the function text (uses native PowerShell script)
$functionText = @"

# ACADEX CLI
function acadex {
    & '$AcadexPs1Path' @args
}
"@

# Check if function already exists
$profileContent = Get-Content $PROFILE -Raw -ErrorAction SilentlyContinue
if ($null -eq $profileContent) {
    $profileContent = ''
}

if ($profileContent -notmatch 'function acadex\s*\{') {
    Add-Content -Path $PROFILE -Value $functionText
    Write-Host "Function added successfully!" -ForegroundColor Green
    Write-Host "Using native PowerShell script: $AcadexPs1Path" -ForegroundColor Cyan
} else {
    Write-Host "Function already exists in profile" -ForegroundColor Yellow
}
