<?php

use Luigel\Paymongo\Facades\Paymongo;

it('will display a table of webhooks in console', function () {
    $headers = ['id', 'livemode', 'secret_key', 'status', 'url'];
    $this->artisan('paymongo:list-webhooks')
        ->expectsTable($headers, getWebhooks($headers));
});

function getWebhooks(array $headers)
{
    return Paymongo::webhook()->all()->map(function ($webhook) use ($headers) {
        return collect($webhook->getData())->only($headers)->toArray();
    })->map(function ($webhook) {
        $webhook['livemode'] = $webhook['livemode'] ? 'YES' : 'NO';

        return $webhook;
    })->toArray();
}
