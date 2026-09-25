<?php

use Luigel\Paymongo\Facades\Paymongo;

$page = Paymongo::payouts()->list(['limit' => 20]); // CursorTokenPage<Payout>

$page->nextCursor;                     // ?string, null on the last page
$page->prevCursor;                     // ?string
$page->meta['total_records'] ?? null;  // totals PayMongo sends with the page

$page = $page->nextPage();             // ?CursorTokenPage
