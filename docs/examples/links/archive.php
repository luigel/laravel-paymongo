<?php

use Luigel\Paymongo\Facades\Paymongo;

$link = Paymongo::links()->archive('link_wWaibr22CzEnficNhQNPUdoo');   // can no longer be paid
$link = Paymongo::links()->unarchive('link_wWaibr22CzEnficNhQNPUdoo'); // can be paid again
