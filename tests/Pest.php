<?php

declare(strict_types=1);

use Luigel\Paymongo\Tests\TestCase;

uses(TestCase::class)->in('Unit', 'Feature');

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
