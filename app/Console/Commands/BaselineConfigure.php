<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\multiselect;

class BaselineConfigure extends Command
{
    protected $signature = 'baseline:configure';

    protected $description = 'Interactively choose which Baseline features to enable (admin, billing, languages)';

    public function handle(): int
    {
        $this->displayHeader();

        $options = [
            'admin' => 'Admin panel (user/role management, settings)',
            'billing' => 'Billing (Stripe subscriptions)',
            'locales' => 'Languages (locale switching / i18n)',
        ];

        $selected = multiselect(
            label: 'Which features would you like to include?',
            options: $options,
            default: ['admin', 'billing', 'locales'],
            required: true,
            hint: 'This will update .env. Use space to toggle, enter to confirm. For client handoff, run php artisan baseline:strip after configuring.',
        );

        $admin = in_array('admin', $selected, true);
        $billing = in_array('billing', $selected, true);
        $locales = in_array('locales', $selected, true);

        $envPath = base_path('.env');

        if (! is_file($envPath)) {
            $this->error('.env not found. Run composer run setup first or copy .env.example to .env');

            return self::FAILURE;
        }

        $this->updateEnv($envPath, [
            'BASELINE_ADMIN' => $admin ? 'true' : 'false',
            'BASELINE_BILLING' => $billing ? 'true' : 'false',
            'BASELINE_LOCALES' => $locales ? 'true' : 'false',
        ]);

        $this->info('Updated .env. Config cleared; restart the app if it is running.');
        $this->newLine();
        $this->line(' <fg=gray>For client projects, run php artisan baseline:strip to lock features so clients cannot re-enable them.</>');
        $this->call('config:clear');

        return self::SUCCESS;
    }

    protected function displayHeader(): void
    {
        $this->newLine();
        $this->line(' ██████╗  █████╗ ███████╗███████╗██╗     ██╗███╗   ██╗███████╗');
        $this->line(' ██╔══██╗██╔══██╗██╔════╝██╔════╝██║     ██║████╗  ██║██╔════╝');
        $this->line(' ██████╔╝███████║███████╗█████╗  ██║     ██║██╔██╗ ██║█████╗  ');
        $this->line(' ██╔══██╗██╔══██║╚════██║██╔══╝  ██║     ██║██║╚██╗██║██╔══╝  ');
        $this->line(' ██████╔╝██║  ██║███████║███████╗███████╗██║██║ ╚████║███████╗');
        $this->line(' ╚═════╝ ╚═╝  ╚═╝╚══════╝╚══════╝╚══════╝╚═╝╚═╝  ╚═══╝╚══════╝');
        $this->newLine();
        $this->line(' <fg=gray>✦ Baseline :: Configure :: Choose Your Features ✦</>');
        $this->newLine();
        $this->line(' Choose which features to include in this project.');
        $this->newLine();
    }

    /**
     * @param  array<string, string>  $vars
     */
    protected function updateEnv(string $envPath, array $vars): void
    {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new \RuntimeException('Could not read .env');
        }
        $updated = [];

        foreach ($vars as $key => $value) {
            $updated[$key] = false;
        }

        $content = '';
        foreach ($lines as $line) {
            $written = false;
            foreach ($vars as $key => $value) {
                if (str_starts_with(trim($line), "{$key}=")) {
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
}
