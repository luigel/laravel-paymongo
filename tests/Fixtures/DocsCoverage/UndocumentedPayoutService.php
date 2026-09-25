<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Tests\Fixtures\DocsCoverage;

use Luigel\Paymongo\Services\AbstractService;

/**
 * A payouts service with list(), which the Payouts page calls, and a method
 * no guide page mentions. Only its method names are reflected over.
 */
final class UndocumentedPayoutService extends AbstractService
{
    public function list(): void {}

    public function reverseSettlement(): void {}
}
