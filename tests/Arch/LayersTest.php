<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Luigel\Paymongo\Data\ApiError;
use Luigel\Paymongo\PaymongoManager;
use Luigel\Paymongo\PaymongoServiceProvider;

/*
 * Dependency direction between the package layers.
 *
 *   Facades -> PaymongoManager -> Services -> Client -> (Exceptions, Data\ApiError)
 *   Services -> Data -> (Enums, Support)
 *   Http / Webhooks / Events (inbound) never reach back into Services or Client (outbound)
 *
 * `ignoring()` on a `toUse` rule removes the given names from BOTH the target and the dependency
 * layer (a rule ignoring its own target namespace passes vacuously), so it is only used here to
 * carve a single class out of a dependency. "Everything else" rules list the namespaces instead.
 */

arch('debug and environment helpers never leak into the package')
    ->expect('Luigel\Paymongo')
    ->not->toUse(['env', 'dd', 'dump', 'var_dump', 'print_r', 'ray', 'exit', 'die']);

arch('package internals never go through the facade')
    ->expect('Luigel\Paymongo')
    ->not->toUse('Luigel\Paymongo\Facades');

arch('data objects do not depend on the service, transport, or HTTP layers')
    ->expect('Luigel\Paymongo\Data')
    ->not->toUse([
        'Luigel\Paymongo\Services',
        'Luigel\Paymongo\Client',
        'Luigel\Paymongo\Http',
    ]);

arch('enums depend on nothing else in the package')
    ->expect('Luigel\Paymongo\Enums')
    ->not->toUse([
        'Luigel\Paymongo\Client',
        'Luigel\Paymongo\Commands',
        'Luigel\Paymongo\Data',
        'Luigel\Paymongo\Events',
        'Luigel\Paymongo\Exceptions',
        'Luigel\Paymongo\Facades',
        'Luigel\Paymongo\Http',
        'Luigel\Paymongo\Pagination',
        'Luigel\Paymongo\Services',
        'Luigel\Paymongo\Support',
        'Luigel\Paymongo\Testing',
        'Luigel\Paymongo\Webhooks',
        PaymongoManager::class,
        PaymongoServiceProvider::class,
    ]);

arch('services reach the API only through PaymongoClient')
    ->expect('Luigel\Paymongo\Services')
    ->not->toUse([
        'Illuminate\Http\Client',
        Http::class,
        'GuzzleHttp',
    ]);

arch('the client does not depend on the layers built on top of it')
    ->expect('Luigel\Paymongo\Client')
    ->not->toUse([
        'Luigel\Paymongo\Services',
        'Luigel\Paymongo\Http',
        'Luigel\Paymongo\Webhooks',
    ]);

arch('the client only knows ApiError from the data layer')
    ->expect('Luigel\Paymongo\Client')
    ->not->toUse('Luigel\Paymongo\Data')
    ->ignoring(ApiError::class);

arch('inbound webhook handling never calls back into the API layers')
    ->expect(['Luigel\Paymongo\Webhooks', 'Luigel\Paymongo\Http', 'Luigel\Paymongo\Events'])
    ->not->toUse(['Luigel\Paymongo\Services', 'Luigel\Paymongo\Client']);
