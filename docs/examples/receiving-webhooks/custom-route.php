<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Luigel\Paymongo\Webhooks\WebhookEvent;

// routes/api.php: a route in the web group also needs CSRF turned off for it.
Route::post('webhooks/paymongo/ledger', function (Request $request) {
    $event = WebhookEvent::fromArray($request->all());

    Log::info("Ledger entry for {$event->resourceId()}", [
        'amount' => $event->resourceAttribute('amount'),
    ]);

    return response()->json(['received' => true]);
})->middleware('paymongo.signature'); // or paymongo.signature:orders for a named secret
