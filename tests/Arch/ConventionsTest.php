<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Facade;
use Luigel\Paymongo\Client\ApiResponse;
use Luigel\Paymongo\Client\ClientConfig;
use Luigel\Paymongo\Data\ApiError;
use Luigel\Paymongo\Data\LineItem;
use Luigel\Paymongo\Data\MpmQr;
use Luigel\Paymongo\Data\PaymentLink;
use Luigel\Paymongo\Data\QrExecution;
use Luigel\Paymongo\Data\Resource;
use Luigel\Paymongo\Events\WebhookReceived;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Services\AbstractService;

/*
 * Naming and shape conventions per namespace.
 *
 * Every rule that needs an `ignoring()` is its own statement: chaining a further expectation onto
 * an arch expectation verifies the first one immediately, before `ignoring()` could apply to it.
 */

// Package-wide

arch('every file declares strict types')
    ->expect('Luigel\Paymongo')
    ->toUseStrictTypes();

arch('no loose equality anywhere')
    ->expect('Luigel\Paymongo')
    ->toUseStrictEquality();

arch('enums live only in the Enums namespace')
    ->expect('Luigel\Paymongo')
    ->not->toBeEnums()
    ->ignoring('Luigel\Paymongo\Enums');

// Services

arch('services are final')
    ->expect('Luigel\Paymongo\Services')
    ->toBeFinal()
    ->ignoring(AbstractService::class);

arch('services extend AbstractService')
    ->expect('Luigel\Paymongo\Services')
    ->toExtend(AbstractService::class)
    ->ignoring(AbstractService::class);

arch('services are suffixed with Service')
    ->expect('Luigel\Paymongo\Services')
    ->toHaveSuffix('Service');

arch('AbstractService is abstract')
    ->expect(AbstractService::class)
    ->toBeAbstract();

// Data

arch('data objects are final')
    ->expect('Luigel\Paymongo\Data')
    ->toBeFinal()
    ->ignoring(Resource::class);

arch('data objects are built with fromArray()')
    ->expect('Luigel\Paymongo\Data')
    ->toHaveMethod('fromArray');

arch('Resource is abstract')
    ->expect(Resource::class)
    ->toBeAbstract();

// Resource subclasses only have readonly properties; everything outside that hierarchy is a readonly class.
arch('value objects outside the Resource hierarchy are readonly classes')
    ->expect([
        ApiError::class,
        LineItem::class,
        MpmQr::class,
        PaymentLink::class,
        QrExecution::class,
        'Luigel\Paymongo\Data\Shared',
        'Luigel\Paymongo\Support',
        ApiResponse::class,
        ClientConfig::class,
    ])
    ->toBeReadonly();

// Enums

arch('enums are string-backed')
    ->expect('Luigel\Paymongo\Enums')
    ->toBeStringBackedEnums();

// Exceptions

arch('exceptions extend PaymongoException')
    ->expect('Luigel\Paymongo\Exceptions')
    ->toExtend(PaymongoException::class)
    ->ignoring(PaymongoException::class);

arch('exceptions are final')
    ->expect('Luigel\Paymongo\Exceptions')
    ->toBeFinal()
    ->ignoring(PaymongoException::class);

arch('PaymongoException is an abstract RuntimeException')
    ->expect(PaymongoException::class)
    ->toBeAbstract()
    ->toExtend(RuntimeException::class);

// Events

arch('events extend WebhookReceived')
    ->expect('Luigel\Paymongo\Events')
    ->toExtend(WebhookReceived::class)
    ->ignoring(WebhookReceived::class);

arch('typed events are final')
    ->expect('Luigel\Paymongo\Events')
    ->toBeFinal()
    ->ignoring(WebhookReceived::class);

arch('WebhookReceived is an instantiable, extensible base event')
    ->expect(WebhookReceived::class)
    ->not->toBeAbstract()
    ->not->toBeFinal();

// Commands

arch('commands follow the console command conventions')
    ->expect('Luigel\Paymongo\Commands')
    ->toExtend(Command::class)
    ->toHaveSuffix('Command')
    ->toHaveMethod('handle')
    ->toBeFinal();

// Http

arch('middleware is final and exposes handle()')
    ->expect('Luigel\Paymongo\Http\Middleware')
    ->toHaveMethod('handle')
    ->toBeFinal();

arch('controllers are final and invokable')
    ->expect('Luigel\Paymongo\Http\Controllers')
    ->toHaveMethod('__invoke')
    ->toBeFinal();

// Facades

arch('facades are final and extend the Laravel Facade')
    ->expect('Luigel\Paymongo\Facades')
    ->toExtend(Facade::class)
    ->toBeFinal();

// Placement: each base class is only extended inside its own namespace

arch('nothing outside Services extends AbstractService')
    ->expect('Luigel\Paymongo')
    ->not->toExtend(AbstractService::class)
    ->ignoring('Luigel\Paymongo\Services');

arch('nothing outside Data extends Resource')
    ->expect('Luigel\Paymongo')
    ->not->toExtend(Resource::class)
    ->ignoring('Luigel\Paymongo\Data');

arch('nothing outside Exceptions extends PaymongoException')
    ->expect('Luigel\Paymongo')
    ->not->toExtend(PaymongoException::class)
    ->ignoring('Luigel\Paymongo\Exceptions');

arch('nothing outside Events extends WebhookReceived')
    ->expect('Luigel\Paymongo')
    ->not->toExtend(WebhookReceived::class)
    ->ignoring('Luigel\Paymongo\Events');

arch('nothing outside Commands extends the console Command')
    ->expect('Luigel\Paymongo')
    ->not->toExtend(Command::class)
    ->ignoring('Luigel\Paymongo\Commands');

arch('nothing outside Facades extends the Laravel Facade')
    ->expect('Luigel\Paymongo')
    ->not->toExtend(Facade::class)
    ->ignoring('Luigel\Paymongo\Facades');
