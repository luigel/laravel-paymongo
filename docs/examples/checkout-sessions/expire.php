<?php

use Luigel\Paymongo\Facades\Paymongo;

$session = Paymongo::checkoutSessions()->expire('cs_CbFCTDfxvMFNjwjVi26Uzhtj');
