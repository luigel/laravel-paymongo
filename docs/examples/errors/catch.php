<?php

use Illuminate\Support\Facades\Log;
use Luigel\Paymongo\Exceptions\ConnectionException;
use Luigel\Paymongo\Exceptions\InvalidRequestException;
use Luigel\Paymongo\Exceptions\PaymentDeclinedException;
use Luigel\Paymongo\Exceptions\PaymongoException;
use Luigel\Paymongo\Exceptions\RateLimitException;
use Luigel\Paymongo\Exceptions\ServerException;
use Luigel\Paymongo\Facades\Paymongo;

try {
    $intent = Paymongo::paymentIntents()->create([
        'amount' => 150050,
        'currency' => 'PHP',
        'payment_method_allowed' => ['card', 'gcash'],
    ]);
} catch (InvalidRequestException $e) {
    // 400, 403, 422, ...: fix the request, retrying it will not help.
    foreach ($e->errors() as $error) {
        $error->code;      // "parameter_below_minimum"
        $error->detail;    // "The minimum value for the amount is 2000."
        $error->attribute; // "amount", when PayMongo names one
        $error->pointer;   // e.g. "/data/attributes/amount"
    }
} catch (PaymentDeclinedException $e) {
    $reason = $e->firstError()?->code; // 402: ask for another payment method
} catch (RateLimitException $e) {
    $wait = $e->retryAfter ?? 60; // 429: seconds from Retry-After, when sent
} catch (ServerException|ConnectionException $e) {
    // 5xx, or PayMongo unreachable: already retried; try again later.
} catch (PaymongoException $e) {
    // Authentication (401) or not found (404).
    Log::error('PayMongo request failed', ['status' => $e->status, 'message' => $e->getMessage()]);
}
