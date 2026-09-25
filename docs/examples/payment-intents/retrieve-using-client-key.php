<?php

use Luigel\Paymongo\Facades\Paymongo;

$intent = Paymongo::paymentIntents()->retrieveUsingClientKey(
    'pi_hsJNpsRFU1LxgVbxW4YJHRs6',
    'pi_hsJNpsRFU1LxgVbxW4YJHRs6_client_Kh6AjSNGfZDoLw5wBWKqpP8u',
);
