#!/usr/bin/env php
<?php
/**
 * Child Theme Creator Script
 * 
 * Creates a new child theme based on the starter template
 * 
 * Usage:
 *   php scripts/create-child-theme.php <theme-name>
 * 
 * Example:
 *   php scripts/create-child-theme.php mbn-child-theme-theme
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

// Color output helpers
function colorize($text, $color) {
    $colors = [
        'red' => "\033[0;31m",
        'green' => "\033[0;32m",
        'yellow' => "\033[1;33m",
        'blue' => "\033[0;34m",
        'reset' => "\033[0m"
    ];
    return ($colors[$color] ?? '') . $text . $colors['reset'];
}

function success($msg) { echo colorize("✓ ", "green") . $msg . "\n"; }
function error($msg) { echo colorize("✗ ", "red") . $msg . "\n"; }
function info($msg) { echo colorize("ℹ ", "blue") . $msg . "\n"; }
function warning($msg) { echo colorize("⚠ ", "yellow") . $msg . "\n"; }

// Header
echo "\n";
echo colorize("═══════════════════════════════════════\n", "blue");
echo colorize("   MBN Child Theme Creator\n", "blue");
echo colorize("═══════════════════════════════════════\n", "blue");
echo "\n";

// Get theme name
if (!isset($argv[1]) || empty($argv[1])) {
    error("Usage: php scripts/create-child-theme.php <theme-name>");
    echo "\n";
    echo "Example:\n";
    echo "  php scripts/create-child-theme.php mbn-child-theme-theme\n";
    echo "  php scripts/create-child-theme.php \"Client Project Theme\"\n";
    echo "\n";
    exit(1);
}

$themeSlug = $argv[1];
$themeName = isset($argv[2]) ? $argv[2] : ucwords(str_replace(['-', '_'], ' ', $themeSlug));

// Validate theme slug
if (!preg_match('/^[a-z0-9-_]+$/i', $themeSlug)) {
    error("Invalid theme name. Use only letters, numbers, hyphens, and underscores.");
    exit(1);
}

// Paths
$mbnThemeDir = dirname(__DIR__);
$starterDir = $mbnThemeDir . '/child-theme-starter';
$themesDir = dirname($mbnThemeDir);
$targetDir = $themesDir . '/' . $themeSlug;

info("Theme slug: $themeSlug");
info("Theme name: $themeName");
echo "\n";

// Check if starter template exists
if (!is_dir($starterDir)) {
    error("Starter template not found at: $starterDir");
    exit(1);
}

// Check if target directory already exists
if (file_exists($targetDir)) {
    error("Theme directory already exists: $targetDir");
    echo "Please choose a different name or remove the existing directory.\n";
    exit(1);
}

// Copy starter template
info("Copying child theme starter template...");
if (!@mkdir($targetDir, 0755, true)) {
    error("Failed to create directory: $targetDir");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($starterDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $target = $targetDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
    if ($item->isDir()) {
        mkdir($target, 0755, true);
    } else {
        copy($item, $target);
    }
}

success("Child theme files copied");

// Update style.css
info("Updating theme information...");
$stylePath = $targetDir . '/style.css';
$styleContent = file_get_contents($stylePath);

$styleContent = str_replace(
    'Theme Name: MBN Child Theme',
    "Theme Name: $themeName",
    $styleContent
);

$styleContent = str_replace(
    'Text Domain: mbn-child-theme',
    "Text Domain: $themeSlug",
    $styleContent
);

$styleContent = str_replace(
    'Description: Child theme for MBN Theme - Customize this for your project',
    "Description: Child theme for MBN Theme - $themeName",
    $styleContent
);

file_put_contents($stylePath, $styleContent);
success("Updated style.css");

// Update functions.php
$functionsPath = $targetDir . '/functions.php';
$functionsContent = file_get_contents($functionsPath);

$functionsContent = str_replace(
    'mbn-child-theme',
    $themeSlug,
    $functionsContent
);

file_put_contents($functionsPath, $functionsContent);
success("Updated functions.php");

// Update README.md
$readmePath = $targetDir . '/README.md';
if (file_exists($readmePath)) {
    $readmeContent = file_get_contents($readmePath);
    
    $readmeContent = str_replace(
        'MBN Child Theme Starter',
        $themeName,
        $readmeContent
    );
    
    $readmeContent = str_replace(
        'your-child-theme',
        $themeSlug,
        $readmeContent
    );
    
    file_put_contents($readmePath, $readmeContent);
    success("Updated README.md");
}

echo "\n";
success("Child theme created successfully!");
echo "\n";

// Next steps
info("Next steps:");
echo "  1. Customize theme details in: $themeSlug/style.css\n";
echo "  2. Add custom functions in: $themeSlug/functions.php\n";
echo "  3. Activate the theme in WordPress Admin → Appearance → Themes\n";
echo "\n";

info("Parent theme version:");
echo "  Make sure mbn-theme is at a stable version:\n";
echo "  cd $mbnThemeDir\n";
echo "  git checkout v1.1.0\n";
echo "  composer install --no-dev && npm install && npm run build\n";
echo "\n";

info("Documentation:");
echo "  Child Theme Guide: $mbnThemeDir/docs/CHILD-THEME-GUIDE.md\n";
echo "  Theme README: $targetDir/README.md\n";
echo "\n";

exit(0);
