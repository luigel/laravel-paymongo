<?php

use Luigel\Paymongo\Facades\Paymongo;

$page = Paymongo::payments()->list(['limit' => 25]);

foreach ($page as $payment) {
    $payment->id;
}

if ($page->hasMore) {
    $page = $page->nextPage(); // the next 25, after the last payment on this page
}

// Or walk every payment, one request per page, as you go:
foreach (Paymongo::payments()->list()->lazy() as $payment) {
    $payment->netAmount;
}
