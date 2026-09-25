<?php

use Luigel\Paymongo\Facades\Paymongo;

foreach (Paymongo::paymentLinks()->list(['status' => 'active']) as $link) {
    $link->description;
    $link->url;
}
