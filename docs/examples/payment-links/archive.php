<?php

use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::paymentLinks()->archive('plink_uSJXoxTBNqRrg35kj5w9dTVY');   // stops taking payments
$link = Paymongo::paymentLinks()->unarchive('plink_uSJXoxTBNqRrg35kj5w9dTVY'); // takes them again
