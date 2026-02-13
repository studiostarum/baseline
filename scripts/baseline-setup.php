#!/usr/bin/env php
<?php

/**
 * Combined Baseline: feature selection then setup with progress.
 * Run from project root: php scripts/baseline-setup.php
 */
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

$basePath = dirname(__DIR__);
chdir($basePath);

$envExample = $basePath.'/.env.example';
$envPath = $basePath.'/.env';

if (! is_file($envExample)) {
    fwrite(STDERR, ".env.example not found. Run from project root.\n");
    exit(1);
}

// ---------- Header ----------
echo "\n";
echo "  ██████╗  █████╗ ███████╗███████╗██╗     ██╗███╗   ██╗███████╗\n";
echo "  ██╔══██╗██╔══██╗██╔════╝██╔════╝██║     ██║████╗  ██║██╔════╝\n";
echo "  ██████╔╝███████║███████╗█████╗  ██║     ██║██╔██╗ ██║█████╗  \n";
echo "  ██╔══██╗██╔══██║╚════██║██╔══╝  ██║     ██║██║╚██╗██║██╔══╝  \n";
echo "  ██████╔╝██║  ██║███████║███████╗███████╗██║██║ ╚████║███████╗\n";
echo "  ╚═════╝ ╚═╝  ╚═╝╚══════╝╚══════╝╚══════╝╚═╝╚═╝  ╚═══╝╚══════╝\n";
echo "\n";

$envExistedBefore = is_file($envPath);
if (! is_file($envPath)) {
    copy($envExample, $envPath);
    echo "  Created .env from .env.example.\n\n";
}

$nonInteractive = getenv('BASELINE_SETUP_NON_INTERACTIVE') !== false;

// ---------- Step 1: Composer install (required for Laravel bootstrap and feature selection) ----------
echo "  Installing PHP dependencies (composer install)…\n\n";
$ok = run('composer install --no-interaction', $basePath);
if (! $ok) {
    fwrite(STDERR, "  Composer install failed.\n");
    exit(1);
}
echo "\n  ✓ PHP dependencies installed.\n\n";

// ---------- Step 2: Database configuration (interactive only) ----------
if (! $nonInteractive) {
    echo "  Database configuration\n\n";
    $dbVars = runDatabaseConfig($basePath);
    if ($dbVars === null) {
        fwrite(STDERR, "  Database configuration failed.\n");
        exit(1);
    }
    setEnvVars($envPath, $dbVars);
    echo "\n  ✓ Database configured.\n\n";
}

// ---------- Step 3: Feature selection (in-process, Laravel Prompts multiselect) ----------
$previousFeatures = $envExistedBefore ? readEnvFeatures($envPath) : null;

if ($nonInteractive) {
    $defaults = [
        'BASELINE_ADMIN' => 'true',
        'BASELINE_BILLING' => 'true',
        'BASELINE_LOCALES' => 'true',
    ];
    updateEnv($envPath, $defaults);
    echo "  Non-interactive: using default features (all enabled).\n\n";
} else {
    $selected = runFeatureSelection($basePath);
    if ($selected === null) {
        fwrite(STDERR, "  Feature selection failed.\n");
        exit(1);
    }
    $admin = in_array('admin', $selected, true);
    $billing = in_array('billing', $selected, true);
    $locales = in_array('locales', $selected, true);
    updateEnv($envPath, [
        'BASELINE_ADMIN' => $admin ? 'true' : 'false',
        'BASELINE_BILLING' => $billing ? 'true' : 'false',
        'BASELINE_LOCALES' => $locales ? 'true' : 'false',
    ]);

    $newFeatures = ['admin' => $admin, 'billing' => $billing, 'locales' => $locales];
    $deselected = getDeselectedFeatures($previousFeatures, $newFeatures);
    if ($deselected !== []) {
        $labels = ['admin' => 'Admin panel', 'billing' => 'Billing', 'locales' => 'Languages'];
        $list = implode(', ', array_map(static fn (string $k) => $labels[$k], $deselected));
        echo "  ⚠ You have disabled: {$list}.\n";
        echo "  These features will be turned off. Routes and UI for them will no longer be active.\n";
        echo '  Continue and apply these changes? [y/N]: ';
        $answer = fgets(STDIN);
        $answer = $answer !== false ? strtolower(trim($answer)) : '';
        if ($answer !== 'y' && $answer !== 'yes') {
            echo "\n  Cancelled. Restoring previous feature selection.\n\n";
            if ($previousFeatures !== null) {
                updateEnv($envPath, [
                    'BASELINE_ADMIN' => $previousFeatures['admin'] ? 'true' : 'false',
                    'BASELINE_BILLING' => $previousFeatures['billing'] ? 'true' : 'false',
                    'BASELINE_LOCALES' => $previousFeatures['locales'] ? 'true' : 'false',
                ]);
            }
            exit(0);
        }
        echo "\n";
    }
    run('php artisan config:clear', $basePath);
    echo "\n";
}

echo "  Installing selected features…\n\n";

// ---------- Setup steps with progress (install what config selected) ----------
$steps = [
    ['label' => 'Application key', 'cmd' => 'php artisan key:generate --force'],
    ['label' => 'Storage link', 'cmd' => 'php artisan storage:link'],
    ['label' => 'Database migrations', 'cmd' => 'php artisan migrate --force'],
    ['label' => 'Route types (wayfinder)', 'cmd' => 'php artisan wayfinder:generate'],
    ['label' => 'Node dependencies (npm install)', 'cmd' => 'npm install'],
    ['label' => 'Frontend build (npm run build)', 'cmd' => 'npm run build'],
];

$total = count($steps);
$barWidth = 32;

function progressLine(int $current, int $total, int $barWidth): string
{
    $pct = $total > 0 ? $current / $total : 0;
    $filled = (int) round($pct * $barWidth);
    $bar = str_repeat('█', $filled).str_repeat('░', $barWidth - $filled);

    return sprintf('  [%s] %d/%d', $bar, $current, $total);
}

function stepStatus(int $index, int $total, string $label, string $status): void
{
    $n = $index + 1;
    $icon = $status === 'done' ? '✓' : ($status === 'running' ? '●' : '○');
    echo sprintf("  %s [%d/%d] %s %s\n", $icon, $n, $total, $label, $status === 'running' ? '…' : '');
}

echo "  ┌─ Setup progress ────────────────────────────────────────────┐\n";
foreach ($steps as $i => $step) {
    stepStatus($i, $total, $step['label'], 'pending');
}
echo '  │ '.progressLine(0, $total, $barWidth)."                 │\n";
echo "  └──────────────────────────────────────────────────────────┘\n\n";

foreach ($steps as $i => $step) {
    $n = $i + 1;
    echo "\n  [{$n}/{$total}] {$step['label']}…\n\n";

    $ok = run($step['cmd'], $basePath);
    if (! $ok) {
        fwrite(STDERR, "\n  Setup failed at step {$n}: {$step['label']}\n");
        exit(1);
    }

    echo "\n  [{$n}/{$total}] ✓ {$step['label']}\n";
    echo '  '.progressLine($n, $total, $barWidth)."\n";
}

// ---------- Feature-specific: seed roles when admin is enabled ----------
if (envBool($envPath, 'BASELINE_ADMIN', true)) {
    $n = $total + 1;
    echo "\n  [{$n}] Seeding roles and permissions (admin feature)…\n\n";
    $ok = run('php artisan db:seed --class=RolesAndPermissionsSeeder --force', $basePath);
    if ($ok) {
        echo "\n  [{$n}] ✓ Roles and permissions seeded.\n";
    }
}

// ---------- New git repository (fresh history for the new project) ----------
$gitDir = $basePath.'/.git';
if (is_dir($gitDir)) {
    echo "\n  Reinitializing git repository (new project)…\n\n";
    deleteDirectory($gitDir);
}
if (! is_dir($gitDir)) {
    $ok = run('git init', $basePath);
    if ($ok) {
        echo "\n  ✓ New git repository initialized.\n";
    }
}

echo "\n  ┌──────────────────────────────────────────────────────────┐\n";
echo "  │ ✓ Setup complete.                                        │\n";
echo "  │   Assign first admin: php artisan baseline:install        │\n";
echo "  └──────────────────────────────────────────────────────────┘\n\n";

exit(0);

function envBool(string $envPath, string $key, bool $default): bool
{
    $lines = file($envPath, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return $default;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_starts_with($line, $key.'=')) {
            $value = trim(substr($line, strlen($key) + 1));

            return strtolower($value) === 'true' || $value === '1';
        }
    }

    return $default;
}

/** @return array{admin: bool, billing: bool, locales: bool} */
function readEnvFeatures(string $envPath): array
{
    return [
        'admin' => envBool($envPath, 'BASELINE_ADMIN', true),
        'billing' => envBool($envPath, 'BASELINE_BILLING', true),
        'locales' => envBool($envPath, 'BASELINE_LOCALES', true),
    ];
}

/**
 * @param  array{admin: bool, billing: bool, locales: bool}|null  $previous
 * @param  array{admin: bool, billing: bool, locales: bool}  $new
 * @return list<string>
 */
function getDeselectedFeatures(?array $previous, array $new): array
{
    if ($previous === null) {
        return [];
    }
    $deselected = [];
    foreach (['admin', 'billing', 'locales'] as $key) {
        if ($previous[$key] && ! $new[$key]) {
            $deselected[] = $key;
        }
    }

    return $deselected;
}

/**
 * Bootstrap Laravel and run interactive database configuration (Laravel Prompts).
 * Returns DB_* env vars to write, or null on failure.
 *
 * @return array<string, string>|null
 */
function runDatabaseConfig(string $basePath): ?array
{
    if (! is_file($basePath.'/vendor/autoload.php')) {
        return null;
    }
    require $basePath.'/vendor/autoload.php';
    $app = require $basePath.'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    $driver = \Laravel\Prompts\select(
        label: 'Which database will you use?',
        options: [
            'sqlite' => 'SQLite (file, good for local development)',
            'mysql' => 'MySQL',
            'mariadb' => 'MariaDB',
            'pgsql' => 'PostgreSQL',
        ],
        default: 'sqlite',
        hint: 'SQLite does not require a server. Others need host, database name, and credentials.',
    );

    $vars = ['DB_CONNECTION' => $driver];

    if ($driver === 'sqlite') {
        $name = \Laravel\Prompts\text(
            label: 'Database name',
            default: 'database',
            placeholder: 'database',
            required: true,
            hint: 'Filename without path or extension (stored as database/<name>.sqlite).',
        );
        $vars['DB_DATABASE'] = 'database/'.preg_replace('/\.sqlite$/i', '', $name).'.sqlite';
    } else {
        $vars['DB_DATABASE'] = \Laravel\Prompts\text(
            label: 'Database name',
            default: 'laravel',
            placeholder: 'laravel',
            required: true,
            hint: 'Name of the database to use.',
        );
    }

    if ($driver !== 'sqlite') {
        $defaultPort = $driver === 'pgsql' ? '5432' : '3306';
        $vars['DB_HOST'] = \Laravel\Prompts\text(
            label: 'Database host',
            default: '127.0.0.1',
            placeholder: '127.0.0.1',
        );
        $vars['DB_PORT'] = \Laravel\Prompts\text(
            label: 'Database port',
            default: $defaultPort,
            placeholder: $defaultPort,
        );
        $vars['DB_USERNAME'] = \Laravel\Prompts\text(
            label: 'Database username',
            default: 'root',
            placeholder: 'root',
        );
        $vars['DB_PASSWORD'] = \Laravel\Prompts\password(
            label: 'Database password',
            placeholder: '(leave empty if none)',
            required: false,
        );
    }

    // Always set all DB_ vars so .env lines get uncommented/overwritten
    $vars += [
        'DB_HOST' => $vars['DB_HOST'] ?? '',
        'DB_PORT' => $vars['DB_PORT'] ?? '',
        'DB_USERNAME' => $vars['DB_USERNAME'] ?? '',
        'DB_PASSWORD' => $vars['DB_PASSWORD'] ?? '',
    ];

    return $vars;
}

/**
 * Bootstrap Laravel and run interactive feature selection (Laravel Prompts multiselect).
 * Returns selected feature keys or null on failure.
 *
 * @return list<string>|null
 */
function runFeatureSelection(string $basePath): ?array
{
    if (! is_file($basePath.'/vendor/autoload.php')) {
        return null;
    }
    require $basePath.'/vendor/autoload.php';
    $app = require $basePath.'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $options = [
        'admin' => 'Admin panel (user/role management, settings)',
        'billing' => 'Billing (Stripe subscriptions)',
        'locales' => 'Languages (locale switching / i18n)',
    ];
    $selected = \Laravel\Prompts\multiselect(
        label: 'Which features would you like to include?',
        options: $options,
        default: ['admin', 'billing', 'locales'],
        required: true,
        hint: 'This will update .env. Use space to toggle, enter to confirm. For client handoff, run php artisan baseline:strip after configuring.',
    );

    return $selected;
}

function updateEnv(string $envPath, array $vars): void
{
    $lines = file($envPath, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        throw new RuntimeException('Could not read .env');
    }
    $updated = array_fill_keys(array_keys($vars), false);
    $content = '';
    foreach ($lines as $line) {
        $written = false;
        foreach ($vars as $key => $value) {
            if (str_starts_with(trim($line), $key.'=')) {
                $updated[$key] = true;
                $content .= $key.'='.$value.PHP_EOL;
                $written = true;
                break;
            }
        }
        if (! $written) {
            $content .= $line.PHP_EOL;
        }
    }
    foreach ($vars as $key => $value) {
        if (! $updated[$key]) {
            $content .= $key.'='.$value.PHP_EOL;
        }
    }
    file_put_contents($envPath, $content);
}

/**
 * Set env vars, overwriting existing or commented lines (e.g. # DB_HOST=...).
 * Matches exact key names only (e.g. DB_DATABASE does not match DB_DATABASE_URL).
 */
function setEnvVars(string $envPath, array $vars): void
{
    $lines = file($envPath, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        throw new RuntimeException('Could not read .env');
    }
    $updated = array_fill_keys(array_keys($vars), false);
    $content = '';
    foreach ($lines as $line) {
        $trimmed = trim($line);
        $normalized = preg_replace('/^\s*#?\s*/', '', $trimmed);
        $written = false;
        foreach ($vars as $key => $value) {
            $prefix = $key.'=';
            if (str_starts_with($normalized, $prefix) && (strlen($normalized) === strlen($prefix) || $normalized[strlen($prefix)] !== '_')) {
                $updated[$key] = true;
                $content .= $key.'='.$value.PHP_EOL;
                $written = true;
                break;
            }
        }
        if (! $written) {
            $content .= $line.PHP_EOL;
        }
    }
    foreach ($vars as $key => $value) {
        if (! $updated[$key]) {
            $content .= $key.'='.$value.PHP_EOL;
        }
    }
    file_put_contents($envPath, $content);
}

function deleteDirectory(string $path): void
{
    if (! is_dir($path)) {
        return;
    }
    $entries = @scandir($path);
    if ($entries === false) {
        return;
    }
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $full = $path.DIRECTORY_SEPARATOR.$entry;
        if (is_dir($full)) {
            deleteDirectory($full);
        } else {
            @unlink($full);
        }
    }
    @rmdir($path);
}

function run(string $command, string $cwd): bool
{
    $filterDeprecations = str_starts_with($command, 'composer ');
    if ($filterDeprecations) {
        $silence = ' -d '.escapeshellarg('error_reporting='.(E_ALL & ~E_DEPRECATED & ~E_STRICT));
        $composerBin = trim((string) shell_exec('command -v composer 2>/dev/null') ?: 'composer');
        $command = 'php'.$silence.' '.escapeshellarg($composerBin).' '.substr($command, 9).' 2>&1';
    }
    $prevCwd = getcwd();
    chdir($cwd);
    if ($filterDeprecations) {
        $exitCode = runAndFilterDeprecations($command);
    } else {
        passthru($command, $exitCode);
    }
    chdir($prevCwd);

    return $exitCode === 0;
}

function runAndFilterDeprecations(string $command): int
{
    $p = popen($command, 'r');
    if ($p === false) {
        return 1;
    }
    while (($line = fgets($p)) !== false) {
        $trimmed = trim($line);
        if ($trimmed !== '' && str_contains($trimmed, 'Deprecation Notice')) {
            continue;
        }
        echo $line;
    }
    $exitCode = pclose($p);

    return $exitCode === -1 ? 1 : $exitCode;
}
