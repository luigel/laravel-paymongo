<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Commands;

use Illuminate\Console\Command;
use Luigel\Paymongo\Enums\WebhookEventType;
use Luigel\Paymongo\PaymongoManager;

final class WebhookCreateCommand extends Command
{
    protected $signature = 'paymongo:webhook:create
        {url : The endpoint URL PayMongo should deliver events to}
        {--event=* : Event to subscribe (repeatable)}';

    protected $description = 'Register a webhook endpoint with PayMongo';

    public function handle(PaymongoManager $paymongo): int
    {
        $url = $this->argument('url');
        $url = is_string($url) ? $url : '';

        /** @var list<string> $events */
        $events = (array) $this->option('event');

        if ($events === []) {
            $events = [
                WebhookEventType::PaymentPaid->value,
                WebhookEventType::PaymentFailed->value,
            ];
        }

        $webhook = $paymongo->webhooks()->create($url, $events);

        $this->components->info("Webhook registered for [{$url}].");
        $this->components->twoColumnDetail('ID', $webhook->id);
        $this->components->twoColumnDetail('Secret key', $webhook->secretKey ?? '-');
        $this->components->twoColumnDetail('Status', $webhook->status->value ?? 'unknown');
        $this->components->twoColumnDetail('Events', implode(', ', $webhook->events));

        return self::SUCCESS;
    }
}
