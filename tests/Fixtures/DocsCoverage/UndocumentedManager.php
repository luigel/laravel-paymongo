<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Tests\Fixtures\DocsCoverage;

use LogicException;

/**
 * A manager exposing a service with a method no guide page mentions, for
 * DocsGuideCoverageTest.
 */
final class UndocumentedManager
{
    public function payouts(): UndocumentedPayoutService
    {
        throw new LogicException('Only reflected over, never called.');
    }
}
