<?php

use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->cancel('pi_hsJNpsRFU1LxgVbxW4YJHRs6');
