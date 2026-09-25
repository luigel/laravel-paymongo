<?php

use Illuminate\Support\Facades\Route;

// Verified with paymongo.webhooks.secret (PAYMONGO_WEBHOOK_SECRET):
Route::paymongoWebhooks();

// Verified with paymongo.webhooks.secrets.orders. Every call is named
// paymongo.webhooks, so give this one its own name: paymongo.webhooks.orders.
Route::paymongoWebhooks('webhooks/orders', 'orders')->name('.orders');
