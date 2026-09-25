<?php

use Luigel\Paymongo\Facades\Paymongo;

$page = Paymongo::payments()->list(['limit' => 50]); // CursorPage<Payment>

foreach ($page as $payment) {
    $payment->id;
}

count($page);    // payments on this page
$page->first();  // ?Payment
$page->items;    // list<Payment>
$page->hasMore;  // bool: is there another page?

$next = $page->nextPage(); // ?CursorPage: requested with after = the last payment's id
