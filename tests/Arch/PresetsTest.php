<?php

declare(strict_types=1);

use Luigel\Paymongo\Data\Resource;
use Luigel\Paymongo\Events\WebhookReceived;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Services\AbstractService;
use Luigel\Paymongo\Tests\ContractTestCase;
use Luigel\Paymongo\Tests\TestCase;

/*
 * Pest's built-in presets, applied to the package's composer "user namespaces".
 * The arch plugin skips PSR-4 roots that live under tests/, so this is Luigel\Paymongo (src/) only.
 */

arch('php preset: no debugging or legacy constructs')
    ->preset()
    ->php();

// src/ calls none of md5/sha1/uniqid/rand/unserialize/extract/assert/..., so nothing is ignored.
arch('security preset: no insecure or non-deterministic functions')
    ->preset()
    ->security();

arch('strict preset: final classes, strict types, strict equality, no protected methods')
    ->preset()
    ->strict()
    ->ignoring([
        // Deliberate extension points: abstract bases whose protected helpers serve their subclasses.
        AbstractService::class,
        Resource::class,
        PaymongoException::class,
        // Dispatched as-is for every webhook and subclassed per event name, so neither final nor abstract.
        WebhookReceived::class,
        // Laravel's Facade contract requires the protected static getFacadeAccessor().
        Paymongo::class,
        // Orchestra base classes. Defensive only: tests/ is outside the preset's namespaces.
        TestCase::class,
        ContractTestCase::class,
    ]);
