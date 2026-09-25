<?php

use Luigel\Paymongo\Enums\DefaultDevice;
use Luigel\Paymongo\Facades\Paymongo;

$customer = Paymongo::customers()->create([
    'first_name' => 'Juan',
    'last_name' => 'dela Cruz',
    'email' => 'juan@example.com',        // unique per customer
    'phone' => '+639171234567',           // unique per customer
    'default_device' => DefaultDevice::Email, // or DefaultDevice::Phone
]);

$customer->id; // "cus_...", store it on your user
