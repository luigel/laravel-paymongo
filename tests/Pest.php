<?php

declare(strict_types=1);

use Luigel\Paymongo\Tests\ContractTestCase;
use Luigel\Paymongo\Tests\TestCase;

uses(TestCase::class)->in('Unit', 'Feature');
uses(ContractTestCase::class)->in('Contract');

/**
 * Load a fixture array from tests/Fixtures/{name}.php.
 *
 * Named fixture_data() because Pest v4 already declares a global fixture()
 * helper (returning a fixture file path) that always loads first.
 *
 * @return array<string, mixed>
 */
function fixture_data(string $name): array
{
    return require __DIR__.'/Fixtures/'.$name.'.php';
}

/**
 * Run a Package docs example file (docs/examples) in its own scope, the way
 * a reader would paste it into their app.
 */
function run_docs_example(string $path): void
{
    (static function () use ($path): void {
        require $path;
    })();
}
