<?php

use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::post('/orders/{order}/checkout', CheckoutController::class)->name('orders.checkout');

Route::paymongoWebhooks(); // POST /paymongo/webhook
