<?php

use Luigel\Paymongo\Facades\Paymongo;

$code = Paymongo::qrph()->generateStatic([
    'kind' => 'instore',                // the only kind PayMongo accepts
    'mobile_number' => '+639171234567', // gets an SMS for every payment
    'notes' => 'Counter 1',             // for you; customers do not see it
]);

$code->qrImage; // print this and put it on the counter
