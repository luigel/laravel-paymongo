<?php

use Luigel\Paymongo\Support\Money;

$subtotal = Money::ofCentavos(150050);
$shipping = Money::ofCentavos(12000);

$total = $subtotal->add($shipping);

$total->centavos();                   // 162050
$total->toDecimal();                  // "1620.50"
$total->format();                     // "₱1,620.50"
(string) $total;                      // "₱1,620.50"
json_encode(['amount' => $total]);    // {"amount":162050}

$total->subtract($shipping)->equals($subtotal); // true
