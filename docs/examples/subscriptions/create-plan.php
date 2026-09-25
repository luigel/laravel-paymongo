<?php

use Luigel\Paymongo\Enums\PlanInterval;
use Luigel\Paymongo\Facades\Paymongo;

$plan = Paymongo::plans()->create([
    'name' => 'Pro',
    'description' => 'Pro tier, billed every 3 months',
    'amount' => 299700, // PHP 2,997.00 per cycle, in centavos
    'currency' => 'PHP',
    'interval' => PlanInterval::Monthly, // or Weekly, Yearly
    'interval_count' => 3,               // 1 to 10 intervals per cycle
    'cycle_count' => 4,                  // optional: stop after 4 payments, first one included
    'metadata' => ['tier' => 'pro'],
]);

$plan->id; // "plan_...", reuse it for every Pro subscriber
