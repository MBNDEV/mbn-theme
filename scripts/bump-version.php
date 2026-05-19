#!/usr/bin/env php
<?php
/**
 * Version Bump Script for MBN Theme
 * 
 * This script updates the version number across all theme files:
 * - style.css
 * - package.json
 * - README.md
 * - CHANGELOG.md (prepares for release)
 * 
 * Usage:
 *   php scripts/bump-version.php <version> [--dry-run]
 * 
 * Examples:
 *   php scripts/bump-version.php 1.1.0
 *   php scripts/bump-version.php 1.0.3 --dry-run
 *   php scripts/bump-version.php patch    (auto-increment patch)
 *   php scripts/bump-version.php minor    (auto-increment minor)
 *   php scripts/bump-version.php major    (auto-increment major)
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

// Script configuration
$themeDir = dirname(__DIR__);
$dryRun = in_array('--dry-run', $argv) || in_array('-d', $argv);

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

// Get current version from style.css
function getCurrentVersion($themeDir) {
    $stylePath = "$themeDir/style.css";
    if (!file_exists($stylePath)) {
        error("style.css not found!");
        exit(1);
    }
    
    $content = file_get_contents($stylePath);
    if (preg_match('/Version:\s*([0-9]+\.[0-9]+\.[0-9]+)/', $content, $matches)) {
        return $matches[1];
    }
    
    error("Could not find version in style.css");
    exit(1);
}

// Validate semantic version format
function isValidVersion($version) {
    return preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $version);
}

// Parse version string
function parseVersion($version) {
    $parts = explode('.', $version);
    return [
        'major' => (int)$parts[0],
        'minor' => (int)$parts[1],
        'patch' => (int)$parts[2]
    ];
}

// Auto-increment version
function autoIncrementVersion($current, $type) {
    $parts = parseVersion($current);
    
    switch ($type) {
        case 'major':
            $parts['major']++;
            $parts['minor'] = 0;
            $parts['patch'] = 0;
            break;
        case 'minor':
            $parts['minor']++;
            $parts['patch'] = 0;
            break;
        case 'patch':
            $parts['patch']++;
            break;
        default:
            return null;
    }
    
    return "{$parts['major']}.{$parts['minor']}.{$parts['patch']}";
}

// Update style.css
function updateStyleCss($filePath, $oldVersion, $newVersion, $dryRun) {
    $content = file_get_contents($filePath);
    $newContent = preg_replace(
        '/Version:\s*' . preg_quote($oldVersion, '/') . '/',
        "Version: $newVersion",
        $content
    );
    
    if ($dryRun) {
        info("Would update style.css: $oldVersion → $newVersion");
        return true;
    }
    
    file_put_contents($filePath, $newContent);
    success("Updated style.css");
    return true;
}

// Update package.json
function updatePackageJson($filePath, $newVersion, $dryRun) {
    if (!file_exists($filePath)) {
        warning("package.json not found, skipping");
        return false;
    }
    
    $content = file_get_contents($filePath);
    $data = json_decode($content, true);
    
    if ($data === null) {
        error("Could not parse package.json");
        return false;
    }
    
    $data['version'] = $newVersion;
    
    if ($dryRun) {
        info("Would update package.json version to $newVersion");
        return true;
    }
    
    file_put_contents(
        $filePath, 
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
    );
    success("Updated package.json");
    return true;
}

// Update README.md
function updateReadme($filePath, $oldVersion, $newVersion, $dryRun) {
    if (!file_exists($filePath)) {
        warning("README.md not found, skipping");
        return false;
    }
    
    $content = file_get_contents($filePath);
    $newContent = str_replace(
        "Version: `$oldVersion`",
        "Version: `$newVersion`",
        $content
    );
    
    if ($dryRun) {
        info("Would update README.md: $oldVersion → $newVersion");
        return true;
    }
    
    file_put_contents($filePath, $newContent);
    success("Updated README.md");
    return true;
}

// Prepare CHANGELOG.md for release
function prepareChangelog($filePath, $newVersion, $dryRun) {
    if (!file_exists($filePath)) {
        warning("CHANGELOG.md not found, skipping");
        return false;
    }
    
    $content = file_get_contents($filePath);
    $today = date('Y-m-d');
    
    // Replace [Unreleased] with new version
    $newContent = preg_replace(
        '/## \[Unreleased\]/',
        "## [Unreleased]\n\n## [$newVersion] - $today",
        $content,
        1
    );
    
    // Update comparison links at bottom
    $newContent = preg_replace(
        '/\[Unreleased\]: (.+?)\/compare\/(.+?)\.\.\.HEAD/',
        "[Unreleased]: $1/compare/v$newVersion...HEAD\n[$newVersion]: $1/compare/$2...v$newVersion",
        $newContent
    );
    
    if ($dryRun) {
        info("Would update CHANGELOG.md with version $newVersion and date $today");
        return true;
    }
    
    file_put_contents($filePath, $newContent);
    success("Updated CHANGELOG.md");
    return true;
}

// Main execution
echo "\n";
echo colorize("═══════════════════════════════════════\n", "blue");
echo colorize("   MBN Theme Version Bump Script\n", "blue");
echo colorize("═══════════════════════════════════════\n", "blue");
echo "\n";

// Get version argument
if (!isset($argv[1])) {
    error("Usage: php scripts/bump-version.php <version|patch|minor|major> [--dry-run]");
    error("Examples:");
    echo "  php scripts/bump-version.php 1.1.0\n";
    echo "  php scripts/bump-version.php patch\n";
    echo "  php scripts/bump-version.php minor --dry-run\n";
    exit(1);
}

$versionArg = $argv[1];
$currentVersion = getCurrentVersion($themeDir);

info("Current version: $currentVersion");

// Determine new version
if (in_array($versionArg, ['major', 'minor', 'patch'])) {
    $newVersion = autoIncrementVersion($currentVersion, $versionArg);
    info("Auto-incrementing $versionArg version");
} else {
    $newVersion = $versionArg;
}

// Validate new version
if (!isValidVersion($newVersion)) {
    error("Invalid version format: $newVersion");
    error("Version must follow semantic versioning: MAJOR.MINOR.PATCH (e.g., 1.0.0)");
    exit(1);
}

// Check if version is newer
if (version_compare($newVersion, $currentVersion, '<=')) {
    error("New version ($newVersion) must be greater than current version ($currentVersion)");
    exit(1);
}

echo "\n";
info("New version: " . colorize($newVersion, "green"));

if ($dryRun) {
    warning("DRY RUN MODE - No files will be modified");
}

echo "\n";

// Perform updates
$success = true;
$success = updateStyleCss("$themeDir/style.css", $currentVersion, $newVersion, $dryRun) && $success;
$success = updatePackageJson("$themeDir/package.json", $newVersion, $dryRun) && $success;
$success = updateReadme("$themeDir/README.md", $currentVersion, $newVersion, $dryRun) && $success;
$success = prepareChangelog("$themeDir/CHANGELOG.md", $newVersion, $dryRun) && $success;

echo "\n";

if ($success) {
    success("Version bump completed successfully!");
    
    if (!$dryRun) {
        echo "\n";
        info("Next steps:");
        echo "  1. Review the changes: git diff\n";
        echo "  2. Update CHANGELOG.md with release notes\n";
        echo "  3. Commit: git add -A && git commit -m \"chore: bump version to $newVersion\"\n";
        echo "  4. Tag: git tag -a v$newVersion -m \"Release v$newVersion\"\n";
        echo "  5. Push: git push origin main --tags\n";
        echo "  6. Create GitHub Release from the tag\n";
    }
} else {
    error("Some updates failed!");
    exit(1);
}

echo "\n";
exit(0);
