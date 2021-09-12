<?php

namespace Luigel\Paymongo\Traits;

use Luigel\Paymongo\Facades\Paymongo;

trait HasWebhooksTable
{
    /**
     * Display webhooks using table.
     *
     * @return void
     */
    public function displayWebhooks()
    {
        $headers = ['id', 'livemode', 'secret_key', 'status', 'url'];

        $this->table($headers, $this->webhooks($headers));
    }

    /**
     * Get all the webhooks.
     *
     * @param  array  $headers
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
