<?php

declare(strict_types=1);

use Luigel\Paymongo\Testing\Fixtures;

// A `payment.paid` event envelope as PayMongo posts it to webhook endpoints,
// wrapping a full payment resource (same shape as tests/Fixtures/payment.php).
return Fixtures::event('payment.paid', Fixtures::payment());
