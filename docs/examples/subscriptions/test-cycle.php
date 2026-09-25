<?php

use Luigel\Paymongo\Facades\Paymongo;

// Test mode only: bill the next cycle now instead of on its date.
Paymongo::subscriptions()->triggerTestCycle('sub_iEbGuGDrxPZoTg9r6BLbdCfV');
