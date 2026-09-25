<?php

use Luigel\Paymongo\Facades\Paymongo;

$plan = Paymongo::plans()->retrieve('plan_9VrvpRkkYqK6twbhuvcVTtjM');

$plan->interval;           // ?PlanInterval
$plan->intervalCount;
$plan->money()?->format(); // "₱2,997.00"

$plan = Paymongo::plans()->update('plan_9VrvpRkkYqK6twbhuvcVTtjM', [
    'name' => 'Pro (quarterly)', // name, description, amount, and metadata can change
]);

foreach (Paymongo::plans()->list() as $plan) {
    $plan->name;
}
