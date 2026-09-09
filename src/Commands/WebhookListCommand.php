<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Commands;

use Illuminate\Console\Command;
use Luigel\Paymongo\Data\Webhook;
use Luigel\Paymongo\PaymongoManager;

final class WebhookListCommand extends Command
{
    protected $signature = 'paymongo:webhook:list';

    protected $description = 'List the webhook endpoints registered with PayMongo';

    public function handle(PaymongoManager $paymongo): int
    {
        $webhooks = $paymongo->webhooks()->list();

        if ($webhooks === []) {
            $this->components->info('No webhooks are registered.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'URL', 'Status', 'Events'],
            array_map(static fn (Webhook $webhook): array => [
                $webhook->id,
                $webhook->url ?? '-',
                $webhook->status->value ?? 'unknown',
                implode(', ', $webhook->events),
            ], $webhooks),
        );

        return self::SUCCESS;
    }
}
