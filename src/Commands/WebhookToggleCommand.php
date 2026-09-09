<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Commands;

use Illuminate\Console\Command;
use Luigel\Paymongo\PaymongoManager;

final class WebhookToggleCommand extends Command
{
    protected $signature = 'paymongo:webhook:toggle
        {id : The webhook id (hook_...)}
        {--enable : Enable the webhook}
        {--disable : Disable the webhook}';

    protected $description = 'Enable or disable a PayMongo webhook endpoint';

    public function handle(PaymongoManager $paymongo): int
    {
        $enable = (bool) $this->option('enable');
        $disable = (bool) $this->option('disable');

        if ($enable === $disable) {
            $this->components->error('Pass exactly one of --enable or --disable.');

            return self::FAILURE;
        }

        $id = $this->argument('id');
        $id = is_string($id) ? $id : '';

        $webhook = $enable
            ? $paymongo->webhooks()->enable($id)
            : $paymongo->webhooks()->disable($id);

        $status = $webhook->status->value ?? 'unknown';

        $this->components->info("Webhook [{$webhook->id}] is now {$status}.");

        return self::SUCCESS;
    }
}
