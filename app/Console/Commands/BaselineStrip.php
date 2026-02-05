<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

class BaselineStrip extends Command
{
    protected $signature = 'baseline:strip';

    protected $description = 'Lock current features and remove BASELINE_* from .env (for client projects)';

    private const BASELINE_ENV_KEYS = ['BASELINE_ADMIN', 'BASELINE_BILLING', 'BASELINE_LOCALES'];

    public function handle(): int
    {
        $this->warn('This will:');
        $this->line('  • Lock current feature flags into config (env vars will be removed and ignored)');
        $this->line('  • Remove BASELINE_* from .env and .env.example');
        $this->newLine();

        if (! confirm('Strip Baseline for a client project? This cannot be undone.', default: false)) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        $features = config('baseline.features', ['admin' => true, 'billing' => true, 'locales' => true]);

        $this->bakeConfig($features);
        $this->removeBaselineEnvFromFile(base_path('.env'));
        $this->removeBaselineEnvFromFile(base_path('.env.example'));

        $this->call('config:clear');
        $this->info('Baseline stripped. Feature flags are locked; clients cannot re-enable features.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, bool>  $features
     */
    protected function bakeConfig(array $features): void
    {
        $admin = $features['admin'] ?? true;
        $billing = $features['billing'] ?? true;
        $locales = $features['locales'] ?? true;

        $content = "<?php\n\nreturn [\n    'features' => [\n        'admin' => ".($admin ? 'true' : 'false').",\n        'billing' => ".($billing ? 'true' : 'false').",\n        'locales' => ".($locales ? 'true' : 'false').",\n    ],\n];\n";

        file_put_contents(config_path('baseline.php'), $content);
    }

    protected function removeBaselineEnvFromFile(string $envPath): void
    {
        if (! is_file($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return;
        }

        $content = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            $skip = false;
            foreach (self::BASELINE_ENV_KEYS as $key) {
                if (str_starts_with($trimmed, $key.'=') || $trimmed === "# {$key}=true") {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }
            if (str_starts_with($trimmed, '# Baseline') || str_starts_with($trimmed, '# BASELINE_')) {
                continue;
            }
            $content .= $line.PHP_EOL;
        }

        file_put_contents($envPath, $content);
    }
}
