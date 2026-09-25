<?php

use Luigel\Paymongo\Enums\PayoutStatus;
use Luigel\Paymongo\Facades\Paymongo;

$page = Paymongo::payouts()->list([
    'payout_status' => PayoutStatus::Deposited,
    'created_at.between' => '2026-08-01..2026-08-31', // YYYY-MM-DD..YYYY-MM-DD
    'sort_by' => 'net_amount', // or created_at
    'order' => 'desc',
    'limit' => 20,
]);

foreach ($page as $payout) {
    $payout->money()?->format(); // the net amount, e.g. "₱48,550.00"
}

$page->meta['total_records'] ?? null; // totals PayMongo sends with the page
$page->nextCursor;                    // ?string, null on the last page
$page = $page->nextPage();            // ?CursorTokenPage, requested with after = nextCursor

// Or walk every payout, one request per page, as you go:
foreach (Paymongo::payouts()->list()->lazy() as $payout) {
    $payout->status;
}
