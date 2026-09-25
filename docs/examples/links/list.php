<?php

use Luigel\Paymongo\Facades\Paymongo;

foreach (Paymongo::links()->list(['limit' => 20]) as $link) {
    $link->description;
    $link->money()?->format(); // "₱1,500.50"
}
