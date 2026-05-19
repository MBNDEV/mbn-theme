# MBN Child Theme Creator - PowerShell Version
# Usage: .\scripts\create-child-theme.ps1 <theme-name> [theme-display-name]
# Example: .\scripts\create-child-theme.ps1 mbn-child-theme-theme "Acme Company Theme"

param(
    [Parameter(Mandatory=$true)]
    [string]$ThemeSlug,
    
    [Parameter(Mandatory=$false)]
    [string]$ThemeName
)

# Header
Write-Host ""
Write-Host "════════════════════════════════════════" -ForegroundColor Blue
Write-Host "   MBN Child Theme Creator" -ForegroundColor Blue
Write-Host "════════════════════════════════════════" -ForegroundColor Blue
Write-Host ""

# Set theme name if not provided
if ([string]::IsNullOrWhiteSpace($ThemeName)) {
    $ThemeName = (Get-Culture).TextInfo.ToTitleCase($ThemeSlug -replace '[_-]', ' ')
}

# Validate theme slug
if ($ThemeSlug -notmatch '^[a-z0-9-_]+$') {
    Write-Host "✗ Invalid theme name. Use only letters, numbers, hyphens, and underscores." -ForegroundColor Red
    exit 1
}

# Paths
$MbnThemeDir = Split-Path -Parent $PSScriptRoot
$StarterDir = Join-Path $MbnThemeDir "child-theme-starter"
$ThemesDir = Split-Path -Parent $MbnThemeDir
$TargetDir = Join-Path $ThemesDir $ThemeSlug

Write-Host "ℹ Theme slug: $ThemeSlug" -ForegroundColor Cyan
Write-Host "ℹ Theme name: $ThemeName" -ForegroundColor Cyan
Write-Host ""

# Check if starter template exists
if (-not (Test-Path $StarterDir)) {
    Write-Host "✗ Starter template not found at: $StarterDir" -ForegroundColor Red
    exit 1
}

# Check if target directory already exists
if (Test-Path $TargetDir) {
    Write-Host "✗ Theme directory already exists: $TargetDir" -ForegroundColor Red
    Write-Host "Please choose a different name or remove the existing directory." -ForegroundColor Yellow
    exit 1
}

# Copy starter template
Write-Host "ℹ Copying child theme starter template..." -ForegroundColor Cyan
try {
    Copy-Item -Path $StarterDir -Destination $TargetDir -Recurse -Force
    Write-Host "✓ Child theme files copied" -ForegroundColor Green
} catch {
    Write-Host "✗ Failed to copy starter template: $_" -ForegroundColor Red
    exit 1
}

# Update style.css
Write-Host "ℹ Updating theme information..." -ForegroundColor Cyan
$StylePath = Join-Path $TargetDir "style.css"
$StyleContent = Get-Content -Path $StylePath -Raw

$StyleContent = $StyleContent -replace 'Theme Name: MBN Child Theme', "Theme Name: $ThemeName"
$StyleContent = $StyleContent -replace 'Text Domain: mbn-child-theme', "Text Domain: $ThemeSlug"
$StyleContent = $StyleContent -replace 'Description: Child theme for MBN Theme - Customize this for your project', "Description: Child theme for MBN Theme - $ThemeName"

Set-Content -Path $StylePath -Value $StyleContent -NoNewline
Write-Host "✓ Updated style.css" -ForegroundColor Green

# Update functions.php
$FunctionsPath = Join-Path $TargetDir "functions.php"
$FunctionsContent = Get-Content -Path $FunctionsPath -Raw
$FunctionsContent = $FunctionsContent -replace 'mbn-child-theme', $ThemeSlug
Set-Content -Path $FunctionsPath -Value $FunctionsContent -NoNewline
Write-Host "✓ Updated functions.php" -ForegroundColor Green

# Update README.md
$ReadmePath = Join-Path $TargetDir "README.md"
if (Test-Path $ReadmePath) {
    $ReadmeContent = Get-Content -Path $ReadmePath -Raw
    $ReadmeContent = $ReadmeContent -replace 'MBN Child Theme Starter', $ThemeName
    $ReadmeContent = $ReadmeContent -replace 'your-child-theme', $ThemeSlug
    Set-Content -Path $ReadmePath -Value $ReadmeContent -NoNewline
    Write-Host "✓ Updated README.md" -ForegroundColor Green
}

Write-Host ""
Write-Host "✓ Child theme created successfully!" -ForegroundColor Green
Write-Host ""

# Next steps
Write-Host "ℹ Next steps:" -ForegroundColor Cyan
Write-Host "  1. Customize theme details in: $ThemeSlug\style.css"
Write-Host "  2. Add custom functions in: $ThemeSlug\functions.php"
Write-Host "  3. Activate the theme in WordPress Admin → Appearance → Themes"
Write-Host ""

Write-Host "ℹ Parent theme version:" -ForegroundColor Cyan
Write-Host "  Make sure mbn-theme is at a stable version:"
Write-Host "  cd $MbnThemeDir"
Write-Host "  git checkout v1.1.0"
Write-Host "  composer install --no-dev; npm install; npm run build"
Write-Host ""

Write-Host "ℹ Documentation:" -ForegroundColor Cyan
Write-Host "  Child Theme Guide: $MbnThemeDir\docs\CHILD-THEME-GUIDE.md"
Write-Host "  Theme README: $TargetDir\README.md"
Write-Host ""
