<?php

namespace Luigel\Paymongo\Tests;

use Illuminate\Support\Collection;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\Webhook;

it('can list all webhooks', function () {
    $webhooks = Paymongo::webhook()->all();

    expect($webhooks)
        ->toBeInstanceOf(Collection::class)
        ->not->toBeEmpty();
});

it('can retrieve webhook', function () {
    $webhooks = Paymongo::webhook()->all();

    $webhook = Paymongo::webhook()->find($webhooks[0]->getId());

    expect($webhook)->toBeInstanceOf(Webhook::class);
    expect($webhook->getId())->toBe($webhooks[0]->getId());
});
