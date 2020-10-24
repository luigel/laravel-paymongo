<?php

namespace Luigel\Paymongo\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Luigel\Paymongo\Facades\Paymongo;

class WebhookListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paymongo:list-webhooks';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'List all the webhooks from Paymongo.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $headers = ['id', 'livemode', 'secret_key', 'status', 'url'];

        $this->table($headers, $this->webhooks($headers));
    }

    /**
     * Get all the webhooks.
     *
     * @param array $headers
     * @return \Illuminate\Support\Collection
     */
    protected function webhooks($headers)
    {
        return Paymongo::webhook()->all()->map(function ($webhook) use ($headers) {
            return collect($webhook->getData())->only($headers)->toArray();
        })->map(function ($webhook) {
            $webhook['livemode'] = $webhook['livemode'] ? 'YES' : 'NO';

            return $webhook;
        })->toArray();
    }
}
